<?php

namespace Maba\Bundle\GentleForce\Tests\Unit\DependencyInjection;

use Maba\Bundle\GentleForceBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class ConfigurationTest extends TestCase
{
    /**
     * @param array $expected expected parsed configuration
     * @param string $configFilename filename in Fixtures directory for yaml file to load
     *
     * @dataProvider configurationTestCaseProvider
     */
    public function testGetConfigTreeBuilder($expected, $configFilename)
    {
        $this->assertEquals($expected, $this->processForFile($configFilename));
    }

    /**
     * @dataProvider invalidConfigurationTestCaseProvider
     * @param mixed $configFilename
     */
    public function testInvalidConfiguration($configFilename)
    {
        try {
            $this->processForFile($configFilename);
        } catch (InvalidConfigurationException $exception) {
            $this->addToAssertionCount(1);
            return;
        }

        $this->fail('Configuration processing should have failed');
    }

    private function processForFile($configFilename)
    {
        $fullConfiguration = Yaml::parse(file_get_contents(__DIR__ . '/Fixtures/' . $configFilename));
        $configuration = new Configuration();
        $processor = new Processor();
        return $processor->processConfiguration($configuration, [$fullConfiguration['maba_gentle_force']]);
    }

    public function configurationTestCaseProvider()
    {
        return [
            'Parses redis and limits nodes' => [
                [
                    'redis' => [
                        'host' => 'localhost',
                        'prefix' => 'my_prefix',
                    ],
                    'limits' => [
                        '2_in_03_no_bucketed' => [
                            [
                                'max_usages' => 2,
                                'period' => 0.3,
                            ],
                        ],
                        '2_in_03_bucketed_period_03' => [
                            [
                                'max_usages' => 2,
                                'period' => 0.3,
                                'bucketed_period' => 0.3,
                            ],
                        ],
                        '2_in_03_bucketed_usages_1' => [
                            [
                                'max_usages' => 2,
                                'period' => 0.3,
                                'bucketed_usages' => 1,
                            ],
                        ],
                        '2_in_06_and_3_in_1' => [
                            [
                                'max_usages' => 2,
                                'period' => 0.6,
                            ],
                            [
                                'max_usages' => 3,
                                'period' => 1,
                            ],
                        ],
                    ],
                    'strategies' => [
                        'default' => 'headers',
                        'headers' => [
                            'requests_available_header' => null,
                            'wait_for_header' => null,
                            'content' => 'Too many requests',
                            'content_type' => 'text/plain; charset=UTF-8',
                        ],
                    ],
                    'listeners' => [],
                ],
                'limits.yml',
            ],
            'Parses limit suffixes' => [
                [
                    'redis' => [
                        'host' => 'localhost',
                        'prefix' => 'my_prefix',
                    ],
                    'limits' => [
                        '10_s' => [
                            [
                                'max_usages' => 1,
                                'period' => 10,
                            ],
                        ],
                        '10_3_s' => [
                            [
                                'max_usages' => 1,
                                'period' => 10.3,
                            ],
                        ],
                        '30_m' => [
                            [
                                'max_usages' => 1,
                                'period' => 1800,
                            ],
                        ],
                        '0_6_h_with_20_d' => [
                            [
                                'max_usages' => 1,
                                'period' => 2160,
                                'bucketed_period' => 1728000,
                            ],
                        ],
                        '3_w' => [
                            [
                                'max_usages' => 1,
                                'period' => 1814400,
                            ],
                        ],
                    ],
                    'strategies' => [
                        'default' => 'headers',
                        'headers' => [
                            'requests_available_header' => null,
                            'wait_for_header' => null,
                            'content' => 'Too many requests',
                            'content_type' => 'text/plain; charset=UTF-8',
                        ],
                    ],
                    'listeners' => [],
                ],
                'periods.yml',
            ],
            [
                [
                    'redis' => [
                        'service_id' => 'redis_service_id',
                        'prefix' => 'my_prefix',
                    ],
                    'limits' => [],
                    'strategies' => [
                        'default' => 'headers',
                        'headers' => [
                            'requests_available_header' => null,
                            'wait_for_header' => null,
                            'content' => 'Too many requests',
                            'content_type' => 'text/plain; charset=UTF-8',
                        ],
                    ],
                    'listeners' => [],
                ],
                'redis_service_id.yml',
            ],
            [
                [
                    'redis' => [
                        'host' => 'localhost',
                        'prefix' => 'my_prefix',
                    ],
                    'limits' => [
                        'api_request' => [
                            [
                                'max_usages' => 100,
                                'period' => 3600,
                            ],
                        ],
                    ],
                    'strategies' => [
                        'default' => 'headers',
                        'headers' => [
                            'requests_available_header' => null,
                            'wait_for_header' => null,
                            'content' => 'Too many requests',
                            'content_type' => 'text/plain; charset=UTF-8',
                        ],
                    ],
                    'listeners' => [
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'success_statuses' => [],
                            'failure_statuses' => [],
                            'methods' => [],
                            'hosts' => [],
                        ],
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['username', 'ip'],
                            'success_statuses' => [],
                            'failure_statuses' => [],
                            'methods' => [],
                            'hosts' => [],
                        ],
                    ],
                ],
                'identifiers.yml',
            ],
            [
                [
                    'redis' => [
                        'host' => 'localhost',
                        'prefix' => 'my_prefix',
                    ],
                    'limits' => [
                        'api_request' => [
                            [
                                'max_usages' => 100,
                                'period' => 3600,
                            ],
                        ],
                    ],
                    'strategies' => [
                        'default' => 'strategy.default',
                        'headers' => [
                            'requests_available_header' => 'Requests-Available',
                            'wait_for_header' => 'Wait-For',
                            'content' => '{"error":"rate_limit_exceeded"}',
                            'content_type' => 'application/json',
                        ],
                        'log' => [
                            'level' => 'error',
                        ],
                    ],
                    'listeners' => [
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'strategy' => 'strategy.for_listener',
                            'success_statuses' => [],
                            'failure_statuses' => [],
                            'methods' => [],
                            'hosts' => [],
                        ],
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'strategy' => 'headers',
                            'success_statuses' => [],
                            'failure_statuses' => [],
                            'methods' => [],
                            'hosts' => [],
                        ],
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'strategy' => 'log',
                            'success_statuses' => [],
                            'failure_statuses' => [],
                            'methods' => [],
                            'hosts' => [],
                        ],
                    ],
                ],
                'strategies.yml',
            ],
            [
                [
                    'redis' => [
                        'host' => 'localhost',
                        'prefix' => 'my_prefix',
                    ],
                    'limits' => [
                        'api_request' => [
                            [
                                'max_usages' => 100,
                                'period' => 3600,
                            ],
                        ],
                    ],
                    'strategies' => [
                        'default' => 'headers',
                        'headers' => [
                            'requests_available_header' => null,
                            'wait_for_header' => null,
                            'content' => 'Too many requests',
                            'content_type' => 'text/plain; charset=UTF-8',
                        ],
                    ],
                    'listeners' => [
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'success_matcher' => 'success_matcher_id',
                            'success_statuses' => [],
                            'failure_statuses' => [],
                            'methods' => [],
                            'hosts' => [],
                        ],
                    ],
                ],
                'success_matcher.yml',
            ],
            [
                [
                    'redis' => [
                        'host' => 'localhost',
                        'prefix' => null,
                    ],
                    'limits' => [],
                    'strategies' => [
                        'default' => 'headers',
                        'headers' => [
                            'requests_available_header' => null,
                            'wait_for_header' => null,
                            'content' => 'Too many requests',
                            'content_type' => 'text/plain; charset=UTF-8',
                        ],
                    ],
                    'listeners' => [
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'success_statuses' => [200],
                            'failure_statuses' => [],
                            'methods' => [],
                            'hosts' => [],
                        ],
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'success_statuses' => [],
                            'failure_statuses' => [401, 403],
                            'methods' => [],
                            'hosts' => [],
                        ],
                    ],
                ],
                'success_and_failure_statuses.yml',
            ],
            [
                [
                    'redis' => [
                        'host' => 'localhost',
                        'prefix' => 'my_prefix',
                    ],
                    'limits' => [
                        'api_request' => [
                            [
                                'max_usages' => 100,
                                'period' => 3600,
                            ],
                        ],
                    ],
                    'strategies' => [
                        'default' => 'headers',
                        'headers' => [
                            'requests_available_header' => null,
                            'wait_for_header' => null,
                            'content' => 'Too many requests',
                            'content_type' => 'text/plain; charset=UTF-8',
                        ],
                    ],
                    'listeners' => [
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'success_statuses' => [],
                            'failure_statuses' => [],
                            'methods' => ['PUT', 'POST'],
                            'hosts' => [],
                        ],
                        [
                            'path' => '^/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'success_statuses' => [],
                            'failure_statuses' => [],
                            'methods' => [],
                            'hosts' => ['api.example.com'],
                        ],
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'success_statuses' => [],
                            'failure_statuses' => [],
                            'methods' => ['GET'],
                            'hosts' => ['docs.example.com'],
                        ],
                    ],
                ],
                'advanced_filtering.yml',
            ],
            [
                [
                    'redis' => [
                        'host' => 'localhost',
                        'prefix' => 'my_prefix',
                    ],
                    'limits' => [
                        'api_request' => [
                            [
                                'max_usages' => 100,
                                'period' => 3600,
                            ],
                        ],
                    ],
                    'strategies' => [
                        'default' => 'headers',
                        'headers' => [
                            'requests_available_header' => null,
                            'wait_for_header' => null,
                            'content' => 'Too many requests',
                            'content_type' => 'text/plain; charset=UTF-8',
                        ],
                        'recaptcha_headers' => [
                            'site_key_header' => 'Recaptcha-Site-Key',
                        ],
                        'recaptcha_template' => [
                            'template' => 'MabaGentleForceBundle:Recaptcha:unlock.html.twig',
                        ],
                    ],
                    'listeners' => [
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'strategy' => 'recaptcha_headers',
                            'success_statuses' => [],
                            'failure_statuses' => [],
                            'methods' => [],
                            'hosts' => [],
                        ],
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'strategy' => 'recaptcha_template',
                            'success_statuses' => [],
                            'failure_statuses' => [],
                            'methods' => [],
                            'hosts' => [],
                        ],
                    ],
                    'recaptcha' => [
                        'site_key' => 'my_recaptcha_site_key',
                        'secret' => 'my_recaptcha_secret',
                    ],
                ],
                'recaptcha.yml',
            ],
        ];
    }

    public function invalidConfigurationTestCaseProvider()
    {
        return [
            ['invalid/redis.yml'],
            ['invalid/listeners_no_identifiers.yml'],
            ['invalid/both_success_and_failure_statuses.yml'],
            ['invalid/success_statuses_and_matcher.yml'],
            ['invalid/failure_statuses_and_matcher.yml'],
            ['invalid/invalid_success_status.yml'],
            ['invalid/period_invalid.yml'],
            ['invalid/period_zero.yml'],
        ];
    }
}
