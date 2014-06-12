<?php

namespace Doxport;

use Doctrine\ORM\EntityManager;
use Doxport\Action\Base\Action;
use Doxport\File\Factory;
use Doxport\Metadata\Driver;
use Doxport\Pass\ClearPass;
use Doxport\Pass\ConstraintPass;
use Doxport\Pass\JoinPass;
use Doxport\Util\Chunk;
use Fhaculty\Graph\Set\Vertices;
use LogicException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

/**
 * Top level Doxport class
 *
 * Used to hold dependencies of the overall process, so Doxport can be used within
 * other frameworks/codebases as a library, rather than solely as a command line tool.
 *
 * Used by the classed in the Command namespace to do the actual work
 */
class Doxport implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var File\Factory
     */
    protected $fileFactory;

    /**
     * @var Action
     */
    protected $action;

    /**
     * @var string
     */
    protected $entity;

    /**
     * @var array
     */
    protected $options = [
        'root'  => false,
        'image' => false
    ];

    /**
     * Constructor
     *
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->entityManager = $manager;

        // Defaults, can be injected?
        $this->driver      = new Driver($manager);
        $this->fileFactory = new Factory();
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param string $option
     * @param mixed  $value
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param Driver $driver
     */
    public function setMetadataDriver(Driver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return Driver
     */
    public function getMetadataDriver()
    {
        return $this->driver;
    }

    /**
     * @param Factory $factory
     */
    public function setFileFactory(Factory $factory)
    {
        $this->fileFactory = $factory;
    }

    /**
     * @return Factory
     */
    public function getFileFactory()
    {
        return $this->fileFactory;
    }

    /**
     * @throws LogicException
     * @return EntityGraph
     */
    public function getEntityGraph()
    {
        if (!$this->entity) {
            throw new LogicException('Specify an entity type first, with setEntity()');
        }

        $this->logger->log(LogLevel::NOTICE, 'Creating entity graph for {entity}', [
            'entity' => $this->entity
        ]);

        return new EntityGraph($this->entity);
    }

    /**
     * @param string $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param Action $action
     */
    public function setAction(Action $action)
    {
        $this->action = $action;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param array $options An array of options
     *   - boolean image, default false   Whether to output a constraints image
     *   - boolean root, default false    Whether to include the root entity
     * @return ConstraintPass
     */
    public function getConstraintPass(array $options = [])
    {
        $this->checkAction();
        $options = array_merge($this->options, $options);

        $this->logger->log(LogLevel::NOTICE, 'Creating constraint pass');

        $pass = new ConstraintPass(
            $this->getMetadataDriver(),
            $this->getEntityGraph(),
            $this->action
        );

        $pass->setExportGraph($options['image']);
        $pass->setIncludeRoot($options['root']);

        $pass->setLogger($this->logger);
        $pass->setFileFactory($this->fileFactory);

        return $pass;
    }

    /**
     * @param Vertices $vertices
     * @param array $options
     * @return JoinPass
     */
    public function getJoinPass(Vertices $vertices, array $options = [])
    {
        $this->checkAction();
        $options = array_merge($this->options, $options);

        $this->logger->log(LogLevel::NOTICE, 'Creating join pass');

        $pass = new JoinPass(
            $this->getMetadataDriver(),
            $this->getEntityGraph(),
            $vertices,
            $this->action
        );

        $pass->setIncludeRoot($options['root']);
        $pass->setLogger($this->logger);
        $pass->setFileFactory($this->fileFactory);

        return $pass;
    }

    /**
     * @param Vertices $vertices
     * @param array $options
     * @return ClearPass
     */
    public function getClearPass(Vertices $vertices, array $options = [])
    {
        $this->checkAction();
        $options = array_merge($this->options, $options);

        $this->logger->log(LogLevel::NOTICE, 'Creating join pass');

        $pass = new ClearPass(
            $this->getMetadataDriver(),
            $this->getEntityGraph(),
            $vertices,
            $this->action
        );

        $pass->setIncludeRoot($options['root']);
        $pass->setLogger($this->logger);
        $pass->setFileFactory($this->fileFactory);

        return $pass;
    }

    /**
     * @throws LogicException
     */
    protected function checkAction()
    {
        if (!$this->action) {
            throw new LogicException('You must provide an action before obtaining pass objects');
        }
    }
}
