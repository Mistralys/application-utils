<?php
/**
 * File containing the class {@see \AppUtils\FileHelper\CLICommandChecker}.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @see \AppUtils\FileHelper\CLICommandChecker
 */

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

/**
 * Tool that can be used to check if a specific command can
 * be executed on the command line.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class CLICommandChecker
{
    /**
     * @var array<string,bool>
     */
    private static array $checked = array();

    /**
     * Commands to use to search for available commands
     * on the target OS.
     *
     * @var array<string,string>
     */
    private static array $osCommands = array(
        'windows' => 'where',
        'linux' => 'which'
    );

    public static function factory() : CLICommandChecker
    {
        return new self();
    }

    public function getOS() : string
    {
        return strtolower(PHP_OS_FAMILY);
    }

    /**
     * @return string
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_UNSUPPORTED_OS_CLI_COMMAND
     */
    public function getWhereCommand() : string
    {
        $os = $this->getOS();

        if(isset(self::$osCommands[$os]))
        {
            return self::$osCommands[$os];
        }

        throw new FileHelper_Exception(
            'Unsupported OS for CLI commands',
            sprintf(
                'The command to search for available CLI commands is not known for the OS [%s].',
                $os
            ),
            FileHelper::ERROR_UNSUPPORTED_OS_CLI_COMMAND
        );
    }

    /**
     * @param string $command
     * @return bool
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_UNSUPPORTED_OS_CLI_COMMAND
     */
    public function exists(string $command) : bool
    {
        if(isset(self::$checked[$command]))
        {
            return self::$checked[$command];
        }

        $result = $this->catchOutput($command) !== '';

        self::$checked[$command] = $result;

        return $result;
    }

    /**
     * @param string $command
     * @return string
     * @throws FileHelper_Exception
     */
    private function catchOutput(string $command) : string
    {
        $pipes = array();

        $process = proc_open(
            $this->getWhereCommand().' '.$command,
            array(
                0 => array("pipe", "r"), //STDIN
                1 => array("pipe", "w"), //STDOUT
                2 => array("pipe", "w"), //STDERR
            ),
            $pipes
        );

        if($process === false)
        {
            return '';
        }

        $stdout = stream_get_contents($pipes[1]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        proc_close($process);

        if($stdout === false)
        {
            return '';
        }

        return $stdout;
    }
}
