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
with user identifiers, API tokens, IP addresses or any other data to group usages.

## Installation

```bash
composer require maba/gentle-force-bundle
```

Register bundle inside `AppKernel.php`:

```php
new \Maba\Bundle\GentleForceBundle\MabaGentleForceBundle(),
```

## Configuration

```yaml
maba_gentle_force:
    redis:
        host: localhost             # localhost is default one
        prefix: my_rate_limiter     # empty by default
    limits:
        credentials_error:
                # Allow 3 errors per hour,
                # 2 additional errors if no errors were made during last hour:
            - max_usages: 3
              period: 3600
              bucketed_usages: 2
          
                # Allow 10 errors per day:
            - max_usages: 10
              period: 86400
        api_request:
                # Allow 10 requests each minute.
                # User can "save up" hour of usage if not using API.
                # This means up to 610 requests at once, after that - 
                # 10 requests per minute, which could again save-up up to 610.
            - max_usages: 10
              period: 60
              bucketed_period: 3600
```

## Usage

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

## Semantic versioning

This bundle follows [semantic versioning](http://semver.org/spec/v2.0.0.html).

See [Symfony BC rules](http://symfony.com/doc/current/contributing/code/bc.html) for basic
information about what can be changed and what not in the API.

## Running tests

[![Travis status](https://travis-ci.org/mariusbalcytis/gentle-force-bundle.svg?branch=master)](https://travis-ci.org/mariusbalcytis/gentle-force-bundle)

Functional tests require Redis. So, generally, it's easier to run them in docker.

```bash
composer install
cd docker
docker-compose up -d
docker exec -it gentle_force_bundle_test_php vendor/bin/phpunit
docker-compose down
```

## Contributing

Feel free to create issues and give pull requests.

You can fix any code style issues using this command:

```bash
vendor/bin/php-cs-fixer fix --config=.php_cs
```
