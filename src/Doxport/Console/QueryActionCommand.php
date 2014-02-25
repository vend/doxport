<?php

namespace Doxport\Console;

use Doxport\Action\Base\QueryAction;
use Doxport\EntityGraph;
use Doxport\Pass\ClearPass;
use Doxport\Pass\ConstraintPass;
use Doxport\Pass\JoinPass;
use Fhaculty\Graph\Set\Vertices;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @property QueryAction $action
 */
abstract class QueryActionCommand extends ActionCommand
{
    /**
     * The root entity
     *
     * @var string
     */
    protected $entity;

    /**
     * @var boolean
     */
    protected $includeRoot = false;

    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption('include-root', 'r', InputOption::VALUE_NONE, 'Whether to include the root entity in the action');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->fileFactory->createPath();

        $this->validateInput($input);

        $this->entity      = $input->getArgument('entity');
        $this->includeRoot = $input->getOption('include-root');
    }

    protected function validateInput(InputInterface $input)
    {
        $entity = $input->getArgument('entity');
        $this->getMetadataDriver()->getEntityMetadata($entity);
    }

    /**
     * @inheritDoc
     */
    protected function configureAction(InputInterface $input)
    {
        parent::configureAction($input);

        if ($input->hasArgument('column') && $input->hasArgument('value')) {
            $this->action->addRootCriteria(
                $input->getArgument('column'),
                $input->getArgument('value')
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function configureFileFactory(InputInterface $input)
    {
        parent::configureFileFactory($input);

        $this->fileFactory->join($this->action->getName());

        if ($input->hasArgument('column') && $input->hasArgument('value')) {
            $this->fileFactory->join(sprintf(
                '%s_%s',
                $input->getArgument('column'),
                $input->getArgument('value')
            ));
        }
    }

    protected function getEntityGraph()
    {
        $this->logger->log(LogLevel::NOTICE, 'Creating entity graph for {entity}', [
            'entity' => $this->entity
        ]);

        return new EntityGraph($this->entity);
    }

    /**
     * @return ConstraintPass
     */
    protected function getConstraintPass()
    {
        $this->logger->log(LogLevel::NOTICE, 'Creating constraint pass');

        $pass = new ConstraintPass(
            $this->getMetadataDriver(),
            $this->getEntityGraph(),
            $this->action
        );

        $pass->setIncludeRoot($this->includeRoot);
        $pass->setLogger($this->logger);
        $pass->setFileFactory($this->fileFactory);

        return $pass;
    }

    /**
     * @param Vertices $vertices
     * @return JoinPass
     */
    protected function getJoinPass(Vertices $vertices)
    {
        $this->logger->log(LogLevel::NOTICE, 'Creating join pass');

        $pass = new JoinPass(
            $this->getMetadataDriver(),
            $this->getEntityGraph(),
            $vertices,
            $this->action
        );

        $pass->setIncludeRoot($this->includeRoot);
        $pass->setLogger($this->logger);
        $pass->setFileFactory($this->fileFactory);

        return $pass;
    }

    /**
     * @param Vertices $vertices
     * @return ClearPass
     */
    public function getClearPass(Vertices $vertices)
    {
        $this->logger->log(LogLevel::NOTICE, 'Creating join pass');

        $pass = new ClearPass(
            $this->getMetadataDriver(),
            $this->getEntityGraph(),
            $vertices,
            $this->action
        );

        $pass->setIncludeRoot($this->includeRoot);
        $pass->setLogger($this->logger);
        $pass->setFileFactory($this->fileFactory);

        return $pass;
    }
}
