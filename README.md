# Gentle-force bundle: brute-force, error and request rate limiting

This is a symfony bundle for rate-limiting both brute-force attempts
(like invalid credentials) and ordinary requests.

It integrates standalone [gentle-force](https://github.com/mariusbalcytis/gentle-force) library
into Symfony framework.

## Features

- can be used to limit brute-force attempts;
- can be used for request rate limiting;
- uses leaky / token bucket algorithm. This means that user does not have to wait
for next hour or day - additional attempts are possible as time goes by. This
also means that requests do not come in big batches when every hour starts;
- handles race-conditions. This is important for brute-force limiting. For example,
if 1000 requests are issued at the same time to check same user's password, only
configured number of attempts will be possible;
- can have several limits configured for single use-case (for example maximum of
100 requests per minute and 200 per hour);
- does not make assumptions about where and what it's used for - it can be used
with user identifiers, API tokens, IP addresses or any other data to group usages;
- provides integration with [Google reCAPTCHA](https://www.google.com/recaptcha/). 

## Installation

```bash
composer require maba/gentle-force-bundle
```

Register bundle inside `AppKernel.php`:

```php
new \Maba\Bundle\GentleForceBundle\MabaGentleForceBundle(),
```

### If using recaptcha

```bash
composer require google/recaptcha
```

Import routing in `app/routing.yml`:

```yaml
gentle_force:
    resource: '@MabaGentleForceBundle/Resources/config/routing.xml'
```

## Usage

Usually it's enough to configure listeners in `config.yml` file.

You can also inject limiting service and incorporate your custom logic -
 see [advanced usage](#advanced-usage) below.

## Configuration

### Examples

Example configuration for API request limiting by IP address and user:

```yaml
maba_gentle_force:
    limits:
        api_request:
                # Allow 10 requests each minute.
                # User can "save up" hour of usage if not using API.
                # This means up to 610 requests at once, after that - 
                # 10 requests per minute, which could again save-up up to 610.
            - max_usages: 10
              period: 1m
              bucketed_period: 1h
    listeners:
        - path: ^/api/          # automatically limit matching requests
          limits_key: api_request
          identifiers: [ip]     # limit by IP address
          
        - path: ^/api
          limits_key: api_request
            # additionally limit by username, if available
          identifiers: [username]
```

Example configuration for limiting failures in login form:

```yaml
maba_gentle_force:
    limits:
        credentials_error:
                # Allow 3 errors per hour,
                # 2 additional errors if no errors were made during last hour:
            - max_usages: 3
              period: 1h
              bucketed_usages: 2

                # Allow 10 errors per day:
            - max_usages: 10
              period: 1d
    listeners:
        - path: ^/login         # match only POST requests to /login*
          methods: [POST]
          limits_key: credentials_error
          identifiers: [ip]
          strategy: recaptcha_page
            # only status 302 is successful in our case
            # response code 200 usually displays error message,
            # while we redirect after success
          success_statuses: ['302']
    strategies:
        recaptcha_template:
            template: custom.html.twig  # optional - overwrite template
    recaptcha:
        site_key: my_recaptcha_site_key # get this at google.com/recaptcha
        secret: my_recaptcha_secret     # this also
```

### Limits

Limits are defined by concrete use-case. It may be your API request,
credentials failure, password reset attempt, registering for email
subscription, checking if username is available etc.

Use any unique key for identifying limit configuration - use same
limit key later to calculate if concrete limit is reached.

Each limit configuration can have several limits defined. This is useful
if you want to have blocking with bigger intervals on more repeating
failures or requests. For example, you can have different limits for
minute, day and week for the same use-case. If any of defined limits
is reached, request is blocked.

You can configure following keys for limits:
- `max_usages`;
- `period`. Measured in seconds, you can use suffixes `s` (seconds),
`m` (minutes), `h` (hours), `d` (days) or `w` (weeks);
- `bucketed_usages`. Optional, additional usages available on top of
`max_usages`. Does not effect speed of additional tokens
(see [token bucket](https://en.wikipedia.org/wiki/Token_bucket));
- `bucketed_period`. Optional, mutually exclusive with `bucketed_usages`.
Period for additional usages to be added on top of `max_usages` if not
being used. Period suffixes available.

### Listeners

Each configured listener can potentially block the request.

#### Filtering

To filter requests on which limit must be applied, use following keys:
- `path`. Regex to match request path;
- `methods`. List of request methods;
- `hosts`. List of hosts.

#### Limiting

You must always configure `limits_key` and `identifiers` for each
listener to use for limiting requests.

`identifiers` are used to specify items from request that will be used
for limiting. `ip` and `username` identifiers are available by default,
you can also register [additional identifiers](#additional-identifiers).

If several identifiers are specified, all of them must match for available
limit to be decreased.

Keep in mind, that if at least one identifier is unavailable, limit is not
applied at all. So, if limiting by `[ip, username]`, unauthorised requests
will not be limited at all.

#### Handling successful requests

For brute-force attempts, bundle needs to check if request was successful or not.
By design, bundle checks and increases usage count in advance, even before
checking if everything is fine. Thus, if request was valid, this count must
be decreased.

For configuring what's considered successful response, use one of the following:
- `success_statuses`. Provide list of HTTP response statuses that indicates successful response;
- `failure_statuses`. Same as `success_statuses`, but in reverse - everything else is considered successful;
- `success_matcher`. Service ID to use for identifying whether response is
successful. Service must implement `SuccessMatcherInterface`.

If you skip all three, all requests are considered as a failure - that is,
functionality handles basic request limiting.

#### Defining strategy for reached limit

Use `strategy` key to identify strategy to use if limit for this
listener is reached. See [strategies](#strategies) below for more information.

### Strategies

Following strategies are available:
- `headers`. Returns pre-configured response with `429 Too Many Requests`
 status code. This is default one;
- `log`. Does not modify response, just logs failures. Usable in configuration
testing phase;
- `recaptcha_headers`. Same as `headers` but adds recaptcha site key. Can
be used by JavaScript code to initiate recaptcha widget;
- `recaptcha_template`. Returns HTML response with recaptcha widget.
After successfully submitting recaptcha, current page is refreshed.

You can configure and use your own strategy - just provide service ID
instead of pre-configured key. Strategy must implement `StrategyInterface`
and optionally `ResponseModifyingStrategyInterface` to modify successful responses.

#### Headers

Configuration options:
- `content`. Content to return in response;
- `content_type`. Content type for response;
- `wait_for_header`. Header name to use in rate exceeded responses.
This response header defines minimum time to wait in seconds before
repeating the request;
- `requests_available_header`. Header name to use in successful responses
to identify how many requests are available at this moment.

#### Log

You can configure `level` to use for logging (defaults to `error`).

#### Recaptcha

For `recaptcha_headers` you can configure `site_key_header` and 
`unlock_url_header` to specify header names to use in rate exceeded response 
to provide configured recaptcha site key and unlock absolute url.

For `recaptcha_template` you can configure `template` to use for generating
response. See templates inside the bundle for more information about
what data is passed. `TwigBundle` is needed for this strategy to work.

For both strategies, you must install recaptcha
(see [installation](#if-using-recaptcha)) and configure recaptcha site data
(see configuration examples).

When routing is imported, `maba_gentle_force_unlock_recaptcha` route
is available (`POST` method). Pass recaptcha response
in `g-recaptcha-response` field using `application/x-www-form-urlencoded`
encoding. Empty `200` response means that rate limit was reset.
In case of error, `400` response is returned with JSON content, `errors`
key will hold array of errors from recaptcha service. See
`RecaptchaUnlockController` and JavaScript code in twig templates
for more information.

### Redis

To configure redis client, either use `host` (defaults to `localhost`)
or `parameters` and `options` (allows to configure connection to redis sentinels)
or `service_id` to provide custom `Predis\Redis` service

You can configure `prefix` for additional prefix for all created keys.

If you prefer to avoid rate limiting at all if redis connection would
fail, but still serve requests as usual, configure `failure_strategy`
as `ignore`. In case of connection failure, you'd get error logged
instead of unhandled exception causing `500` responses.

### Full configuration reference

```yaml
maba_gentle_force:
    redis:
        host:                 ~
        parameters:           []
        options: 
            replication:      ~
            service:          ~
            parameters:
                password:     ~
        service_id:           ~
        prefix:               ~
        failure_strategy:     fail
    limits:
        my_limit_name:
            -
                max_usages: ~
                period: ~
                bucketed_usages: ~
                bucketed_period: ~
    strategies:
        default:              headers
        headers:
            wait_for_header:      null
            requests_available_header: null
            content:              'Too many requests'
            content_type:         'text/plain; charset=UTF-8'
        log:
            level:                error
        recaptcha_headers:
            site_key_header:      null
            unlock_url_header:    null
        recaptcha_template:
            template:             null
    listeners:
        -
            path:                 ^/
            limits_key:           ~
            identifiers:          []
            strategy:             ~
            success_matcher:      ~
            success_statuses:     []
            failure_statuses:     []
            methods:              []
            hosts:                []
    recaptcha:
        site_key:             ~
        secret:               ~
    listener_priorities:
        default: 1000
        post_authentication: 0
```

## Additional identifiers

You can provide additional identifiers to configure in your listeners.

You need to create service which implements `IdentifierProviderInterface`
and tag it with `maba_gentle_force.identifier_provider`
(provide name in `identifierType` attribute).

For example:

```php
<?php

namespace Acme;

use Symfony\Component\HttpFoundation\Request;
use Maba\Bundle\GentleForceBundle\Service\IdentifierProvider\IdentifierProviderInterface;

class UserAgentProvider implements IdentifierProviderInterface
{
    public function getIdentifier(Request $request)
    {
        return $request->headers->get('User-Agent');
    }
}
```

```xml
<service class="Acme\UserAgentProvider">
    <tag name="maba_gentle_force.identifier_provider"
         identifierType="user_agent"/>
</service>
```

```yaml
# ...
    listeners:
        - limits_key: api_request
                # limit by IP and User-Agent combination
          identifiers: [ip, user_agent]
```

## Advanced usage

Rate limiting:

```php
/** @var Maba\GentleForce\Throttler $throttler */
$throttler = $container->get('maba_gentle_force.throttler');

try {
    $result = $throttler->checkAndIncrease('api_request', $request->getClientIp());
    $response->headers->set('Requests-Available', $result->getUsagesAvailable());
    
} catch (RateLimitReachedException $exception) {
    return new Response('', 429, ['Wait-For' => $exception->getWaitForInSeconds()]);
}
```

Brute force limiting:

```php
try {
    // we must increase error count in-advance before even checking credentials
    // this avoids race-conditions with lots of requests
    $credentialsResult = $throttler->checkAndIncrease('credentials_error', $username);
} catch (RateLimitReachedException $exception) {
    $error = sprintf('Too much password tries for user. Please try after %s seconds', $exception->getWaitForInSeconds());
    
    return $this->showError($error);
}

$credentialsValid = $credentialsManager->checkCredentials($username, $password);

if ($credentialsValid) {
    // as we've increased error count in advance, we need to decrease it if everything went fine
    $credentialsResult->decrease();
    
    // log user into system
}
```

## Resetting specific limits manually

There are two commands that can reset the limit manually, if needed.

`maba:gentle-force:reset` command interactively asks for wanted listener configuration and each identifier
(like username, IP etc.)

`maba:gentle-force:reset-limit` command takes 2 arguments - limit key and identifier to reset the limit. This
could be used when limits are set with advanced usage and concrete identifier to use is known.

## Semantic versioning

This bundle follows [semantic versioning](http://semver.org/spec/v2.0.0.html).

Public API of this bundle (in other words, you should only use these features if you want to easily update
to new versions):
- only services that are not marked as `public="false"`
- only classes, interfaces and class methods that are marked with `@api`
- console commands
- supported DIC tags
- configuration reference
- routing keys and controllers with routes

For example, if only class method is marked with `@api`, you should not extend that class, as constructor
could change in any release.

See [Symfony BC rules](https://symfony.com/doc/current/contributing/code/bc.html) for basic information
about what can be changed and what not in the API. Keep in mind, that in this bundle everything is
`@internal` by default.

## Running tests

[![Travis status](https://travis-ci.org/mariusbalcytis/gentle-force-bundle.svg?branch=master)](https://travis-ci.org/mariusbalcytis/gentle-force-bundle)

```bash
composer update
vendor/bin/phpunit
```

## Contributing

Feel free to create issues and give pull requests.

You can fix any code style issues using this command:

```bash
vendor/bin/php-cs-fixer fix --config=.php_cs
```
