<?php

namespace Doxport\Action\Base;

use Doctrine\ORM\EntityManager;
use Doxport\File\Factory;
use Doxport\Metadata\Driver;
use Chunky\Chunk;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class Action implements LoggerAwareInterface
{
    use LoggerAwareTrait {
        setLogger as setParentLogger;
    }

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var Factory
     */
    protected $fileFactory;

    /**
     * @var Chunk
     */
    protected $chunk;

    /**
     * @var array<string,mixed>
     */
    protected $options = [];

    /**
     * @param EntityManager $em
     * @param array<string,mixed> $options
     */
    public function __construct(EntityManager $em, array $options = [])
    {
        $this->logger = new NullLogger();
        $this->em     = $em;

        $this->options = array_merge([
            'verbose' => false
        ], $options);

        // 500 rows initial estimate and max, target perform in 0.2 seconds
        $this->chunk = new Chunk(500, 0.2, [
            'max'     => 500,
            'min'     => 5,
            'verbose' => $this->options['verbose']
        ]);
    }

    /**
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->chunk->setLogger($logger);
        $this->setParentLogger($logger);
    }

    /**
     * @param string $class
     * @return mixed
     */
    protected function getClassName($class)
    {
        $parts = explode('\\', $class);
        return $parts[count($parts) - 1];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return lcfirst($this->getClassName(get_class($this)));
    }

    /**
     * @param Factory $fileFactory
     */
    public function setFileFactory(Factory $fileFactory)
    {
        $this->fileFactory = $fileFactory;
    }

    /**
     * @param Driver $driver
     */
    public function setMetadataDriver(Driver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param Chunk $chunk
     */
    public function setChunk(Chunk $chunk)
    {
        $this->chunk = $chunk;
    }

    /**
     * Outputs memory information to the logger
     *
     * @return void
     */
    protected function debugMemory()
    {
        if (!$this->options['verbose']) {
            return;
        }

        $a = [
            memory_get_usage(),
            memory_get_peak_usage(),
            round(100 * memory_get_usage() / memory_get_peak_usage()),
            round(100 * memory_get_peak_usage() / 160000000),
        ];

        $s = '';
        foreach ($a as $v) {
            $s .= str_pad(round($v), 15, ' ', STR_PAD_LEFT);
        }

        $this->logger->notice('Memory: ' . $s);
    }
}
