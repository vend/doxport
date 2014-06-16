<?php

namespace Doxport\Util;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Tracks chunk time for large segmented operations that target wallclock time
 *
 * The contract is:
 *  - Provide an initial estimate of the chunk size
 *  - Provide a target wallclock time for each chunk to take
 *  - Wrap the critical/timed section in begin/end calls
 *  - Call getEstimatedSize(), and perform the work in chunks that big
 *
 * The Chunk class will provide an exponentially weighted moving-average based
 * guess as to the correct chunk size to use.
 */
class Chunk implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The current estimated chunk size to use
     *
     * @var integer
     */
    protected $estimate;

    /**
     * A target amount of wallclock time for a chunk to take
     *
     * @var float Seconds
     */
    protected $target;

    /**
     * Exponentially moving average of the rate of processing with respect to time
     *
     * @var float
     */
    protected $average;

    /**
     * Beginning time of the most recent chunk
     *
     * @var float
     */
    protected $begin;

    /**
     * Ending time of the most recent chunk
     *
     * @var float
     */
    protected $end;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param int $estimate
     * @param float $target
     * @param array $options
     *   - int min         The minimum estimated size to ever return
     *   - int max         The maximum estimated size to ever return
     *   - float smoothing The exponential smoothing factor, 0 < s < 1
     */
    public function __construct($estimate, $target = 0.2, array $options = [])
    {
        $this->estimate = (int)$estimate;
        $this->target   = $target;
        $this->average  = $estimate / $target;
        $this->logger   = new NullLogger();

        $this->options  = array_merge([
            'min'       => (int)(0.01 * $this->estimate),
            'max'       => (int)(3    * $this->estimate),
            'smoothing' => 0.3,
            'verbose'   => false
        ], $options);
    }

    /**
     * Call this method immediately before the start of each chunk being processed
     *
     * @return void
     */
    public function begin()
    {
        $this->begin = microtime(true);
    }

    /**
     * Call this method immediately after the end of each chunk being processed
     *
     * @return void
     * @param int $processed The number of items that were actually processed
     */
    public function end($processed = null)
    {
        $this->end = microtime(true);
        $this->updateEstimate($processed);
    }

    /**
     * Alternative way to set observed time
     *
     * @param float $interval
     * @param int $processed The number of items that were actually processed
     */
    public function interval($interval, $processed = null)
    {
        $this->begin = 0;
        $this->end = $interval;
        $this->updateEstimate($processed);
    }

    /**
     * Returns an estimated chunk size to use to get the operation to perform within the target timeframe
     *
     * @return int
     */
    public function getEstimatedSize()
    {
        return $this->estimate;
    }

    /**
     * Updates the current estimate of the correct chunk size
     *
     * @param integer $processed
     * @return void
     */
    protected function updateEstimate($processed = null)
    {
        // The previous chunk size processed, from either the caller, or the last estimate
        $previous = $processed !== null ? $processed : $this->estimate;

        // dx/dt of last observation, per second rate
        $time     = ($this->end - $this->begin);
        $observed = $previous / $time;

        // Update the average
        $this->average = $this->updateExponentialAverage($this->average, $observed);

        // Calculate the new estimate
        $this->estimate = (int)$this->average * $this->target;

        $clamp = false;

        // Clamp
        if ($this->estimate > $this->options['max']) {
            $clamp = true;
            $this->estimate = (int)$this->options['max'];
        }

        if ($this->estimate < $this->options['min']) {
            $clamp = true;
            $this->estimate = (int)$this->options['min'];
        }

        if ($this->options['verbose']) {
            $this->logger->notice(
                'Chunk size update: {p}, {d}/{n}s, {r}/{nr} -> {e} {c}',
                [
                    'p'  => $previous,
                    'd'  => $time,
                    'n'  => $this->target,
                    'r'  => $observed,
                    'nr' => $previous / $this->target,
                    'e'  => $this->estimate,
                    'c'  => ($clamp ? '(clamped)' : '')
                ]
            );
        }

        $this->begin = null;
        $this->end   = null;
    }

    /**
     * @param float $previous  The previously arrived-at weighted average
     * @param float $new       Some new observation to update the average with
     * @return float The new exponentially smoothed average
     */
    protected function updateExponentialAverage($previous, $new)
    {
        return $this->options['smoothing'] * $new + (1 - $this->options['smoothing']) * $previous;
    }
}
