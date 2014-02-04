<?php

namespace Doxport\Console;

use Doctrine\ORM\Mapping\MappingException;
use Doxport\Action\Base\Action;
use Doxport\Action\Delete;
use Doxport\Schema;
use Doxport\EntityGraph;
use Doxport\Util\QueryAliases;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doxport\Pass\ConstraintPass;
use Doxport\Pass\JoinPass;

class DeleteCommand extends ActionCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('delete')
            ->addOption('data-dir', 'd', InputOption::VALUE_REQUIRED, 'The data directory to archive to (default build/{action})', null)
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity to begin deleting from', null)
            ->addArgument('column', InputArgument::REQUIRED, 'A column to limit deleting', null)
            ->addArgument('value', InputArgument::REQUIRED, 'The value to limit by', null)
            ->setDescription('Deletes a set of data from the database, beginning with a specified type, but not including it');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->validateInput($input);

        $driver = $this->getMetadataDriver();
        $entity = $input->getArgument('entity');

        $this->logger->log(LogLevel::NOTICE, 'Creating entity graph for {entity}', ['entity' => $entity]);
        $graph = new EntityGraph($entity);

        $this->logger->log(LogLevel::NOTICE, 'Doing constraint pass');
        $pass = new ConstraintPass($driver, $graph, $this->action);
        $pass->setLogger($this->logger);
        $vertices = $pass->run();

        $this->logger->log(LogLevel::NOTICE, 'Creating another entity graph for {entity}', ['entity' => $entity]);
        $graph = new EntityGraph($entity);

        $this->logger->log(LogLevel::NOTICE, 'Doing join pass');
        $pass = new JoinPass($driver, $graph, $vertices, $this->action);
        $pass->setLogger($this->logger);
        $pass->run();

        $this->logger->notice('All done.');
    }

    protected function validateInput(InputInterface $input)
    {
        $entity = $input->getArgument('entity');
        $this->getMetadataDriver()->getEntityMetadata($entity);
    }

    /**
     * @return Action
     */
    protected function getAction()
    {
        return new Delete($this->getEntityManager());
    }
}
