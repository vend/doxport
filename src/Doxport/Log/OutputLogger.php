<?php

namespace Doxport\Log;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class OutputLogger extends Logger
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $verbosity = $this->output->getVerbosity();

        if ($verbosity == OutputInterface::VERBOSITY_QUIET) {
            return;
        }

        if (($verbosity >= OutputInterface::VERBOSITY_NORMAL && $level >= LogLevel::NOTICE)
            || ($verbosity >= OutputInterface::VERBOSITY_VERBOSE && $level >= LogLevel::DEBUG)
            || ($verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE)
        ) {
            $this->doLog($message, $context);
        }
    }

    /**
     * Actually logs the message to the output interface
     *
     * @param string $message
     * @param array  $context
     * @return void
     */
    protected function doLog($message, array $context)
    {
        $this->output->writeln($this->interpolate($message, $context));
    }
}
