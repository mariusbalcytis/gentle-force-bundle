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
                        'default' => 'maba_gentle_force.strategy.headers',
                    ],
                    'listeners' => [],
                ],
                'limits.yml',
            ],
            [
                [
                    'redis' => [
                        'service_id' => 'redis_service_id',
                        'prefix' => 'my_prefix',
                    ],
                    'limits' => [],
                    'strategies' => [
                        'default' => 'maba_gentle_force.strategy.headers',
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
                        'default' => 'maba_gentle_force.strategy.headers',
                    ],
                    'listeners' => [
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                        ],
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['username', 'ip'],
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
                    ],
                    'listeners' => [
                        [
                            'path' => '^/api/',
                            'limits_key' => 'api_request',
                            'identifiers' => ['ip'],
                            'strategy' => 'strategy.for_listener',
                        ],
                    ],
                ],
                'strategies.yml',
            ],
        ];
    }

    public function invalidConfigurationTestCaseProvider()
    {
        return [
            ['invalid_redis.yml'],
            ['listeners_no_identifiers.yml'],
        ];
    }
}
