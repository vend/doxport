<?php

namespace Doxport\Console;

use Doxport\Action\Base\Action;
use Doxport\Action\Base\FileActionTrait;
use Doxport\File\Factory;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ActionCommand extends Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->addOption('data-dir', 'd', InputOption::VALUE_REQUIRED, 'The data directory to use (default build/{action})', null)
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'The file format to use (default json)', null);
    }

    /**
     * @return Action
     */
    abstract protected function getAction();

    /**
     * @inheritDoc
     */
    protected function configureDoxport(InputInterface $input)
    {
        parent::configureDoxport($input);

        $this->configureFileFactory($input);
        $this->configureAction($input);

        return $this->doxport;
    }

    /**
     * @param InputInterface $input
     * @return Factory
     */
    protected function configureFileFactory(InputInterface $input)
    {
        $factory = $this->doxport->getFileFactory();

        if ($input->hasArgument('data-dir') && $input->getArgument('data-dir')) {
            $factory->setPath($input->getArgument('data-dir'));
        } elseif ($input->hasOption('data-dir') && $input->getOption('data-dir')) {
            $factory->setPath($input->getOption('data-dir'));
        }

        if ($input->hasOption('format') && $input->getOption('format')) {
            $factory->setFormat($input->getOption('format'));
        }

        return $factory;
    }

    /**
     * Configures the action
     *
     * @param InputInterface $input
     * @return Action
     */
    protected function configureAction(InputInterface $input)
    {
        $action = $this->getAction();

        $action->setLogger($this->doxport->getLogger());
        $action->setFileFactory($this->doxport->getFileFactory());
        $action->setMetadataDriver($this->doxport->getMetadataDriver());

        $this->doxport->setAction($action);

        return $action;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->logger->log(LogLevel::NOTICE, 'Configured action: {action_class}', ['action_class' => get_class($this->doxport->getAction())]);
    }
}
