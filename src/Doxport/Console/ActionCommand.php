<?php

namespace Doxport\Console;

use Doxport\Action\Base\Action;
use Doxport\Action\Base\FileActionTrait;
use Doxport\Action\Base\QueryAction;
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
     * @return Action
     */
    abstract protected function getAction();

    /**
     * Configures the action
     *
     * @param Action $action
     * @param InputInterface $input
     * @return void
     */
    protected function configureAction(Action $action, InputInterface $input)
    {
        $action->setLogger($this->logger);

        if ($input->getOption('data-dir')) {
            $action->setFilePath($input->getOption('data-dir'));
        }

        if ($action instanceof QueryAction
            && $input->hasArgument('column')
            && $input->hasArgument('value')
        ) {
            $action->addRootCriteria(
                $input->getArgument('column'),
                $input->getArgument('value')
            );

            $action->setFilePath(
                $action->getFilePath()
                . \DIRECTORY_SEPARATOR
                . $input->getArgument('column')
                . '_'
                . $input->getArgument('value')
            );
        }

        $action->createFilePath();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->action = $this->getAction();
        $this->configureAction($this->action, $input);

        $this->logger->log(LogLevel::DEBUG, 'Configured action: {action_class}', ['action_class' => get_class($this->action)]);
    }
}
