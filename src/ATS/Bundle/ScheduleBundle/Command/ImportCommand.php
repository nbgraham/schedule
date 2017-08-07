<?php

namespace ATS\Bundle\ScheduleBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;

/**
 * Extends doctrine's fixtures command for integration into the
 * import driver system.
 * 
 * @author Austin Shinpaugh
 */
class ImportCommand extends LoadDataFixturesDoctrineCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('schedule:import')
            ->setDescription('Populate the database.')
            ->addOption(
                'source',
                's',
                InputOption::VALUE_OPTIONAL,
                "The data source used to update the data. Either 'ods' or 'book'.",
                'ods'
            )->addOption(
                'year',
                'y',
                InputOption::VALUE_OPTIONAL,
                'The starting year to import. IE: 2015',
                'all'
            )->setHelp('Import data from varying sources into the database.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getOption('source');
        $period = $input->getOption('year');
        $helper = $this->getContainer()->get('schedule.import_helper');
        
        $helper
            ->setServiceId($source)
            ->setAcademicPeriod($period)
            ->toggleFKChecks(false)
        ;
        
        parent::execute($input, $output);
    }
}