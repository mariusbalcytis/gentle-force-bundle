<?php

namespace Maba\Bundle\GentleForceBundle\Command;

use Maba\GentleForce\ThrottlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetLimitCommand extends Command
{
    private $throttler;

    public function __construct(ThrottlerInterface $throttler)
    {
        parent::__construct();
        $this->throttler = $throttler;
    }

    protected function configure()
    {
        $this->setName('maba:gentle-force:reset-limit');
        $this->setDescription('Clears limit by provided key and identifier');
        $this->addArgument('limit_key', InputArgument::REQUIRED);
        $this->addArgument('identifier', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limitKey = $input->getArgument('limit_key');
        $identifier = $input->getArgument('identifier');
        $this->throttler->reset($limitKey, $identifier);

        $output->writeln(sprintf(
            'Limit was reset successfully for key "%s" and identifier "%s"',
            $limitKey,
            $identifier
        ));
    }
}
