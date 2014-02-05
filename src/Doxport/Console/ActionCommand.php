<?php

namespace Doxport\Console;

use Doxport\Action\Base\Action;
use Doxport\Action\Base\FileActionTrait;
use Doxport\Action\Base\QueryAction;
use Doxport\File\Factory;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ActionCommand extends Command
{
    /**
     * @var Action
     */
    protected $action;

    /**
     * @var Factory
     */
    protected $fileFactory;

    /**
     * @return Action
     */
    abstract protected function getAction();

    /**
     * @return Factory
     */
    protected function getFileFactory()
    {
        return new Factory();
    }

    /**
     * Configures the file factory
     *
     * @param InputInterface $input
     */
    protected function configureFileFactory(InputInterface $input)
    {
        if ($input->getOption('data-dir')) {
            $this->fileFactory->setPath($input->getOption('data-dir'));
        }

        if ($input->hasOption('format') && $input->getOption('format')) {
            $this->fileFactory->setFormat($input->getOption('format'));
        }

        $this->fileFactory->join($this->action->getName());
    }

    /**
     * Configures the action
     *
     * @param InputInterface $input
     * @return void
     */
    protected function configureAction(InputInterface $input)
    {
        $this->action->setLogger($this->logger);
        $this->action->setFileFactory($this->fileFactory);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->action      = $this->getAction();
        $this->fileFactory = $this->getFileFactory();

        $this->configureFileFactory($input);
        $this->configureAction($input);

        $this->fileFactory->createPath();

        $this->logger->log(LogLevel::NOTICE, 'Output directory: {dir}', ['dir' => $this->fileFactory->getPath()]);
        $this->logger->log(LogLevel::DEBUG, 'Configured action: {action_class}', ['action_class' => get_class($this->action)]);
    }
}
