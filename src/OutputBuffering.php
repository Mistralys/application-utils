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
    public const ERROR_CANNOT_START = 91501;
    public const ERROR_CANNOT_GET_BUFFER = 91502;
    public const ERROR_BUFFER_NOT_STARTED = 91503;
    public const ERROR_CANNOT_STOP_BUFFER = 91504;
    public const ERROR_CANNOT_FLUSH_BUFFER = 91505;

    /**
     * @var int[]
     */
    private static $stack = array();

    /**
     * Checks whether any level of output buffering is currently active,
     * but only the helper's output buffering. The native PHP buffering
     * is ignored.
     *
     * @return bool
     * @see OutputBuffering::setBaseBufferLevel()
     */
    public static function isActive() : bool
    {
        return self::getLevel() > 0;
    }

    /**
     * @return int
     */
    public static function getLevel() : int
    {
        return count(self::$stack);
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
}
