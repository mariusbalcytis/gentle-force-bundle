<?php

namespace Maba\Bundle\GentleForce\Tests\Unit\DependencyInjection;

use Maba\Bundle\GentleForceBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class ConfigurationTest extends TestCase
{
    /**
     * @param array $expected   expected parsed configuration
     * @param array $yamlConfig contents of maba_gentle_force inside yaml
     *
     * @dataProvider configurationTestCaseProvider
     */
    public function testGetConfigTreeBuilder($expected, $yamlConfig)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [$yamlConfig['maba_gentle_force']]);
        $this->assertSame($expected, $config);
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
                ],
                Yaml::parse(file_get_contents(__DIR__ . '/Fixtures/config_limits.yml')),
            ],
        ];
    }
}
