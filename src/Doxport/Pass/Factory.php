<?php

namespace Doxport\Pass;

use Doxport\Action\Base\Action;
use Doxport\Exception\Exception;
use Doxport\Exception\InvalidArgumentException;
use Fhaculty\Graph\Set\Vertices;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Doxport\Metadata\Driver;
use Doxport\EntityGraph;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Doxport\File\Factory as FileFactory;

class Factory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Type mapping for passes
     *
     * See setPassType() to add a custom pass type
     *
     * @var array<string,string>
     */
    protected $types = [
        'constraint' => 'Doxport\Pass\ConstraintPass',
        'join'       => 'Doxport\Pass\JoinPass',
        'clear'      => 'Doxport\Pass\ClearPass'
    ];

    /**
     * @var \Doxport\Metadata\Driver
     */
    protected $driver;

    /**
     * @var \Doxport\EntityGraph
     */
    protected $graph;

    /**
     * @var \Doxport\Action\Base\Action
     */
    protected $action;

    /**
     * @var \Doxport\File\Factory
     */
    protected $fileFactory;

    /**
     * Constructor
     *
     * @param Driver $driver
     * @param EntityGraph $graph
     * @param Action $action
     * @param FileFactory $factory
     */
    public function __construct(
        Driver $driver,
        EntityGraph $graph,
        Action $action,
        FileFactory $factory
    ) {
        $this->driver   = $driver;
        $this->graph    = $graph;
        $this->action   = $action;
        $this->fileFactory = $factory;
        $this->logger   = new NullLogger();
    }

    /**
     * @param string $type
     * @param Vertices $vertices
     * @param array $options
     * @return Pass
     * @throws Exception
     */
    public function get($type, Vertices $vertices = null, array $options = [])
    {
        if (!isset($this->types[$type])) {
            throw new InvalidArgumentException('Cannot create pass type; type not found');
        }

        $class = $this->types[$type];

        /* @var Pass $pass */
        $pass = new $class(
            $this->driver,
            $this->graph,
            $this->action,
            $vertices,
            $options
        );

        if ($options['verbose']) {
            $this->logger->log(LogLevel::NOTICE, 'Configuring pass: ' . get_class($pass));
        }

        $pass->setIncludeRoot($options['root']);
        $pass->setExportGraph($options['image']);
        $pass->setFileFactory($this->fileFactory);

        if ($options['verbose']) {
            $pass->setLogger($this->logger);
        }

        return $pass;
    }
}
