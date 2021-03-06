<?php

namespace Doxport\Console\Base;

use Doxport\Action\Base\QueryAction;
use Doxport\Console\Base\ActionCommand;
use InvalidArgumentException;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @property QueryAction $action
 */
abstract class QueryActionCommand extends ActionCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->addArgument('entity', InputArgument::REQUIRED, 'The entity to begin deleting from', null)
            ->addArgument('column', InputArgument::REQUIRED, 'A column to limit deleting', null)
            ->addArgument('value', InputArgument::REQUIRED, 'The value to limit by', null)
            ->addOption('include-root', 'r', InputOption::VALUE_NONE, 'Whether to include the root entity in the action')
            ->addOption('graph', 'g', InputOption::VALUE_NONE, 'Whether to output the constraints graph that was used');
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

        $pass = $this->doxport->getConstraintPass();
        $vertices = $pass->run();

        $pass = $this->doxport->getClearPass($vertices);
        $pass->run();

        $pass = $this->doxport->getJoinPass($vertices);
        $pass->run();

        $this->logger->notice('All done.');
    }

    protected function configureDoxport(InputInterface $input)
    {
        $doxport = parent::configureDoxport($input);

        $doxport->setEntity($input->getArgument('entity'));
        $doxport->setOption('image', $input->getOption('graph'));
        $doxport->setOption('root', $input->getOption('include-root'));

        return $doxport;
    }

    /**
     * @inheritDoc
     */
    protected function configureFileFactory(InputInterface $input)
    {
        $factory = parent::configureFileFactory($input);

        $factory->join($this->doxport->getAction()->getName());

        if ($input->hasArgument('column') && $input->hasArgument('value')) {
            $factory->join(sprintf(
                '%s_%s',
                $input->getArgument('column'),
                $input->getArgument('value')
            ));
        }

        $path   = $factory->getPath();
        $suffix = 2;

        while ($factory->pathExists()) {
            $factory->setPath($path . '_' . $suffix);
            $suffix++;
        }

        $factory->createPath();
        $this->logger->log(LogLevel::NOTICE, 'Output directory: {dir}', ['dir' => $factory->getPath()]);

        return $factory;
    }

    /**
     * @inheritDoc
     * @return QueryAction
     */
    protected function configureAction(InputInterface $input)
    {
        $action = parent::configureAction($input);

        if (!$action instanceof QueryAction) {
            throw new InvalidArgumentException('Query action command is for detailing with query actions only');
        }

        if ($input->hasArgument('column') && $input->hasArgument('value')) {
            $action->addRootCriteria(
                $input->getArgument('column'),
                $input->getArgument('value')
            );
        }

        return $action;
    }

    protected function validateInput(InputInterface $input)
    {
        $this->doxport->getMetadataDriver()->getEntityMetadata($input->getArgument('entity'));
    }
}
