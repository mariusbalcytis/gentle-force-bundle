<?php

namespace Maba\Bundle\GentleForceBundle\Command;

use Maba\Bundle\GentleForceBundle\Listener\ConfigurationRegistry;
use Maba\Bundle\GentleForceBundle\Listener\ListenerConfiguration;
use Maba\Bundle\GentleForceBundle\Service\IdentifierBuilder;
use Maba\GentleForce\ThrottlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ResetCommand extends Command
{
    private $throttler;
    private $identifierBuilder;
    private $configurationRegistry;

    public function __construct(
        ThrottlerInterface $throttler,
        IdentifierBuilder $identifierBuilder,
        ConfigurationRegistry $configurationRegistry
    ) {
        parent::__construct();
        $this->throttler = $throttler;
        $this->identifierBuilder = $identifierBuilder;
        $this->configurationRegistry = $configurationRegistry;
    }

    protected function configure()
    {
        $this->setName('maba:gentle-force:reset');
        $this->setDescription('Clears limit on provided listener configuration and identifier.
Parameters are taken interactively.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configurationList = $this->configurationRegistry->getConfigurationList();
        if (\count($configurationList) === 0) {
            /** @var FormatterHelper $formatter */
            $formatter = $this->getHelper('formatter');
            $output->writeln($formatter->formatBlock(
                'No listener configurations defined',
                'error',
                true
            ));

            return;
        }

        $configuration = $this->askConfiguration($input, $output, $configurationList);

        $identifiers = $this->askIdentifiers($input, $output, $configuration);
        $identifier = $this->identifierBuilder->buildIdentifier($identifiers);

        $this->throttler->reset($configuration->getLimitsKey(), $identifier);

        $output->writeln(sprintf(
            'Limit was reset successfully for key "%s" and identifier "%s"',
            $configuration->getLimitsKey(),
            $identifier
        ));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param ListenerConfiguration[] $configurationList
     * @return ListenerConfiguration
     */
    private function askConfiguration(InputInterface $input, OutputInterface $output, $configurationList)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $options = [];
        $i = 'a';
        foreach ($configurationList as $configuration) {
            $options[$i++] = $this->describeConfiguration($configuration);
        }

        $question = new ChoiceQuestion(
            'Please choose configuration to reset',
            $options
        );

        $result = $helper->ask($input, $output, $question);

        $selection = array_search($result, array_keys($options));

        return $configurationList[$selection];
    }

    private function describeConfiguration(ListenerConfiguration $configuration)
    {
        $plainConfiguration = [
            'Limits key: ' => $configuration->getLimitsKey(),
            'Path pattern: ' => $configuration->getPathPattern(),
            'Hosts: ' => implode(', ', $configuration->getHosts()),
            'Methods: ' => implode(', ', $configuration->getMethods()),
            'Identifiers: ' => implode(', ', $configuration->getIdentifierTypes()),
        ];

        $parts = [];
        foreach (array_filter($plainConfiguration) as $key => $value) {
            $parts[] = $key . $value;
        }

        return implode(', ', $parts);
    }

    private function askIdentifiers(
        InputInterface $input,
        OutputInterface $output,
        ListenerConfiguration $configuration
    ) {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $identifiers = [];
        foreach ($configuration->getIdentifierTypes() as $identifierType) {
            $identifiers[$identifierType] = $helper->ask(
                $input,
                $output,
                new Question(
                    sprintf(
                        'What is the value for identifier "%s"? ',
                        $identifierType
                    )
                )
            );
        }

        return $identifiers;
    }
}
