<?php
/**
 * File containing the class {@see \AppUtils\OutputBuffering}.
 *
 * @package Application Utils
 * @subpackage Output Buffering
 * @see \AppUtils\OutputBuffering
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Wrapper around the native PHP output buffering methods,
 * using exceptions to avoid having to check the return
 * values of the methods.
 *
 * It is limited to a typical output buffering usage:
 * starting a buffer, and retrieving or flushing its contents,
 * stopping the buffer in the process. Any more complex
 * buffer usage will have to use the PHP functions.
 *
 * @package Application Utils
 * @subpackage Output Buffering
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class OutputBuffering
{
    const ERROR_CANNOT_START = 91501;
    const ERROR_CANNOT_GET_BUFFER = 91502;
    const ERROR_BUFFER_NOT_STARTED = 91503;
    const ERROR_CANNOT_STOP_BUFFER = 91504;
    const ERROR_CANNOT_FLUSH_BUFFER = 91505;

    /**
     * @var int[]
     */
    private static $stack = array();

    /**
     * @var int
     */
    private static $baseLevel = 0;

    /**
     * Checks whether any level of output buffering is currently active.
     *
     * NOTE: Assumes by default that 0 is the inactive buffer level.
     * If this is not the case, see the {@see OutputBuffering::setBaseBufferLevel()}
     * method to adjust it.
     *
     * @return bool
     * @see OutputBuffering::setBaseBufferLevel()
     */
    public static function isActive() : bool
    {
        return ob_get_level() > self::$baseLevel;
    }

    /**
     * Starts a new output buffer.
     *
     * @throws OutputBuffering_Exception
     * @see OutputBuffering::ERROR_CANNOT_START
     */
    public static function start() : void
    {
        self::$stack[] = 1;

        if(ob_start() === true) {
            return;
        }

        throw new OutputBuffering_Exception(
            'Cannot start output buffering.',
            'ob_start returned false.',
            self::ERROR_CANNOT_START
        );
    }

    /**
     * Stops the output buffer, discarding the captured content.
     *
     * @throws OutputBuffering_Exception
     * @see OutputBuffering::ERROR_BUFFER_NOT_STARTED
     * @see OutputBuffering::ERROR_CANNOT_STOP_BUFFER
     */
    public static function stop() : void
    {
        self::_stop();

        if(ob_end_clean() !== false)
        {
            return;
        }

        throw new OutputBuffering_Exception(
            'Cannot stop the output buffer.',
            'The ob_end_clean call returned false.',
            self::ERROR_CANNOT_STOP_BUFFER
        );
    }

    /**
     * @throws OutputBuffering_Exception
     */
    private static function _stop() : void
    {
        if(empty(self::$stack))
        {
            throw new OutputBuffering_Exception(
                'Output buffering is not active',
                'Tried to get the output buffer, but none was active.',
                self::ERROR_BUFFER_NOT_STARTED
            );
        }

        array_pop(self::$stack);
    }

    /**
     * Flushes the captured output buffer to standard output.
     *
     * @throws OutputBuffering_Exception
     * @see OutputBuffering::ERROR_BUFFER_NOT_STARTED
     * @see OutputBuffering::ERROR_CANNOT_FLUSH_BUFFER
     */
    public static function flush() : void
    {
        self::_stop();

        if(ob_end_flush() !== false)
        {
            return;
        }

        throw new OutputBuffering_Exception(
            'Cannot flush the output buffer.',
            'ob_end_flush returned false.',
            self::ERROR_CANNOT_FLUSH_BUFFER
        );
    }

    /**
     * Retrieves the current output buffer's contents,
     * and stops the output buffer.
     *
     * @return string
     *
     * @throws OutputBuffering_Exception
     * @see OutputBuffering::ERROR_BUFFER_NOT_STARTED
     * @see OutputBuffering::ERROR_CANNOT_GET_BUFFER
     */
    public static function get() : string
    {
        self::_stop();

        $content = ob_get_clean();

        if($content !== false)
        {
            return $content;
        }

        throw new OutputBuffering_Exception(
            'Cannot stop the output buffer.',
            'ob_get_contents returned false.',
            self::ERROR_CANNOT_GET_BUFFER
        );
    }

    /**
     * Configure the output buffer to work in unit testing
     * mode, which assumes that the testing framework does
     * some output buffering of its own. This skews the
     * {@see OutputBuffering::isActive()} method, since it
     * assumes the buffer level must be 0 to be inactive.
     */
    public static function enableUnitTesting() : void
    {
        self::setBaseBufferLevel(1);
    }

    /**
     * Sets the base output buffering level. Any level
     * above this value is considered an active output
     * buffering level.
     *
     * Use this in case scripts outside your control
     * already have output buffering active. For example,
     * if your script always runs with 1 output buffering
     * level already being active, set the level to 1.
     *
     * @param int $level
     */
    public static function setBaseBufferLevel(int $level) : void
    {
        self::$baseLevel = $level;
    }
}
