<?php

namespace Doxport;

use Doctrine\ORM\EntityManager;
use Doxport\Action\Base\Action;
use Doxport\Exception\Exception;
use Doxport\File\Factory as FileFactory;
use Doxport\Pass\Factory as PassFactory;
use Doxport\Metadata\Driver;
use Doxport\Pass\ClearPass;
use Doxport\Pass\ConstraintPass;
use Doxport\Pass\JoinPass;
use Fhaculty\Graph\Set\Vertices;
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
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var PassFactory
     */
    protected $passFactory;

    /**
     * @var Action
     */
    protected $action;

    /**
     * @var string
     */
    protected $entity;

    /**
     * @var array<string,mixed>
     */
    protected $options = [
        'root'    => false,
        'image'   => false,
        'verbose' => false
    ];

    /**
     * Constructor
     *
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->entityManager = $manager;

        // Defaults, can be injected with setter injection
        $this->driver      = new Driver($manager);
        $this->fileFactory = new FileFactory();
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
     * @return FileFactory
     */
    public function getFileFactory()
    {
        return $this->fileFactory;
    }

    /**
     * @param PassFactory $factory
     */
    public function setPassFactory(PassFactory $factory)
    {
        $this->passFactory = $factory;
    }

    /**
     * Don't locally memoize the pass factory that's instantiated here, otherwise
     * the graph isn't cleared between passes
     *
     * @return PassFactory
     * @throws Exception
     */
    protected function getPassFactory()
    {
        if ($this->passFactory) {
            return $this->passFactory;
        }

        if (!isset($this->action)) {
            throw new Exception('Unable to create pass: tell the Doxport instance what action to run first');
        }

        $factory = new PassFactory(
            $this->driver,
            $this->getEntityGraph(),
            $this->action,
            $this->fileFactory
        );

        $factory->setLogger($this->getLogger());

        return $factory;
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
     * @throws Exception
     * @return EntityGraph
     */
    public function getEntityGraph()
    {
        if (!$this->entity) {
            throw new Exception('Specify an entity type first, with setEntity()');
        }

        if ($this->options['verbose']) {
            $this->logger->log(LogLevel::NOTICE, 'Creating entity graph for {entity}', [
                'entity' => $this->entity
            ]);
        }

        return new EntityGraph($this->entity);
    }

    /**
     * @param array $options
     * @return ConstraintPass
     */
    public function getConstraintPass(array $options = [])
    {
        return $this->getPassFactory()->get('constraint', null, array_merge(
            $this->options,
            $options
        ));
    }

    /**
     * @param Vertices $vertices
     * @param array $options
     * @return JoinPass
     */
    public function getJoinPass(Vertices $vertices, array $options = [])
    {
        return $this->getPassFactory()->get('join', $vertices, array_merge(
            $this->options,
            $options
        ));
    }

    /**
     * @param Vertices $vertices
     * @param array $options
     * @return ClearPass
     */
    public function getClearPass(Vertices $vertices, array $options = [])
    {
        return $this->getPassFactory()->get('clear', $vertices, array_merge(
            $this->options,
            $options
        ));
    }
}
