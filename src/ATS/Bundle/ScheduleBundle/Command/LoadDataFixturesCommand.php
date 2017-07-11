<?php

namespace ATS\Bundle\ScheduleBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;

class LoadDataFixturesCommand extends LoadDataFixturesDoctrineCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption(
            'source',
            's',
            InputOption::VALUE_OPTIONAL,
            "The data source used to update the data. Either 'ods' or 'book'.",
            'ods'
        )->addOption(
            'period',
            'p',
            InputOption::VALUE_OPTIONAL,
            'The academic periods that need parsing',
            'all'
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getOption('source');
        $period = $input->getOption('period');
        $helper = $this->getContainer()->get('schedule.import_helper');
        
        $helper
            ->setServiceId($source)
            ->setAcademicPeriod($period)
            ->toggleFKChecks(false)
        ;
        
        parent::execute($input, $output);
    }
}