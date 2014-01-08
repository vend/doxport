<?php

namespace Doxport\Console;

use Doxport\Action\Base\Action;
use Doxport\Action\Base\FileActionTrait;
use Doxport\Action\Base\QueryAction;
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
     * @return void
     */
    protected function configureAction(Action $action, InputInterface $input)
    {
        $action->setLogger($this->logger);

        if ($action instanceof QueryAction
            && $input->hasArgument('column')
            && $input->hasArgument('value')
        ) {
            $action->addRootCriteria(
                $input->getArgument('column'),
                $input->getArgument('value')
            );
        }

        if ($action instanceof FileActionTrait) {
            if ($input->hasOption('data-dir')) {
                $action->setFilePath($input->getOption('data-dir'));
            }

            $action->createFilePath();
        }
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
    }
}
