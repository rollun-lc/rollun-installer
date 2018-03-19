<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 15.03.18
 * Time: 12:21 PM
 */
namespace rollun\installer;
use Composer\Config;
use Composer\IO\ConsoleIO;
use Composer\IO\IOInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
/**
 * Class ProxyConsoleIO
 * Fixed bug with select ConsoleIO method
 * @package rollun\installer
 */
class ProxyConsoleIO extends ConsoleIO
{
    use LoggerTrait;
    /** @var ConsoleIO */
    protected $consoleIO;
    /**
     * ProxyConsoleIO constructor.
     * @param ConsoleIO $consoleIO
     */
    public function __construct(ConsoleIO $consoleIO)
    {
        return $this->consoleIO = $consoleIO;
    }
    /**
     * Is this input means interactive?
     *
     * @return bool
     */
    public function isInteractive()
    {
        return $this->consoleIO->isInteractive();
    }
    /**
     * Is this output verbose?
     *
     * @return bool
     */
    public function isVerbose()
    {
        return $this->consoleIO->isVerbose();
    }
    /**
     * Is the output very verbose?
     *
     * @return bool
     */
    public function isVeryVerbose()
    {
        return $this->consoleIO->isVeryVerbose();
    }
    /**
     * Is the output in debug verbosity?
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->consoleIO->isDebug();
    }
    /**
     * Is this output decorated?
     *
     * @return bool
     */
    public function isDecorated()
    {
        return $this->consoleIO->isDecorated();
    }
    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool $newline Whether to add a newline or not
     * @param int $verbosity Verbosity level from the VERBOSITY_* constants
     */
    public function write($messages, $newline = true, $verbosity = self::NORMAL)
    {
        $this->consoleIO->write($messages, $newline, $verbosity);
    }
    /**
     * Writes a message to the error output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool $newline Whether to add a newline or not
     * @param int $verbosity Verbosity level from the VERBOSITY_* constants
     */
    public function writeError($messages, $newline = true, $verbosity = self::NORMAL)
    {
        $this->consoleIO->writeError($messages, $newline, $verbosity);
    }
    /**
     * Overwrites a previous message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool $newline Whether to add a newline or not
     * @param int $size The size of line
     * @param int $verbosity Verbosity level from the VERBOSITY_* constants
     */
    public function overwrite($messages, $newline = true, $size = null, $verbosity = self::NORMAL)
    {
        $this->consoleIO->overwrite($messages, $newline, $size, $verbosity);
    }
    /**
     * Overwrites a previous message to the error output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool $newline Whether to add a newline or not
     * @param int $size The size of line
     * @param int $verbosity Verbosity level from the VERBOSITY_* constants
     */
    public function overwriteError($messages, $newline = true, $size = null, $verbosity = self::NORMAL)
    {
        $this->consoleIO->overwriteError($messages, $newline, $size, $verbosity);
    }
    /**
     * Asks a question to the user.
     *
     * @param string|array $question The question to ask
     * @param string $default The default answer if none is given by the user
     *
     * @throws \RuntimeException If there is no data to read in the input stream
     * @return string            The user answer
     */
    public function ask($question, $default = null)
    {
        return $this->consoleIO->ask($question, $default);
    }
    /**
     * Asks a confirmation to the user.
     *
     * The question will be asked until the user answers by nothing, yes, or no.
     *
     * @param string|array $question The question to ask
     * @param bool $default The default answer if the user enters nothing
     *
     * @return bool true if the user has confirmed, false otherwise
     */
    public function askConfirmation($question, $default = true)
    {
        return $this->consoleIO->askConfirmation($question, $default);
    }
    /**
     * Asks for a value and validates the response.
     *
     * The validator receives the data to validate. It must return the
     * validated data when the data is valid and throw an exception
     * otherwise.
     *
     * @param string|array $question The question to ask
     * @param callable $validator A PHP callback
     * @param null|int $attempts Max number of times to ask before giving up (default of null means infinite)
     * @param mixed $default The default answer if none is given by the user
     *
     * @throws \Exception When any of the validators return an error
     * @return mixed
     */
    public function askAndValidate($question, $validator, $attempts = null, $default = null)
    {
        return $this->consoleIO->askAndValidate($question, $validator, $attempts, $default);
    }
    /**
     * Asks a question to the user and hide the answer.
     *
     * @param string $question The question to ask
     *
     * @return string The answer
     */
    public function askAndHideAnswer($question)
    {
        return $this->consoleIO->askAndHideAnswer($question);
    }
    /**
     * Asks the user to select a value.
     *
     * @param string|array $question The question to ask
     * @param array $choices List of choices to pick from
     * @param bool|string $default The default answer if the user enters nothing
     * @param bool|int $attempts Max number of times to ask before giving up (false by default, which means infinite)
     * @param string $errorMessage Message which will be shown if invalid value from choice list would be picked
     * @param bool $multiselect Select more than one value separated by comma
     *
     * @throws \InvalidArgumentException
     * @return int|string|array          The selected value or values (the key of the choices array)
     */
    public function select($question, $choices, $default, $attempts = false, $errorMessage = 'Value "%s" is invalid', $multiselect = false)
    {
        //Fixed bug with one select in compsoer
        if($multiselect){
            return $this->consoleIO->select($question,$choices,$default,$attempts,$errorMessage,$multiselect);
        } else {
            $result = $this->consoleIO->select($question,$choices,$default,$attempts,$errorMessage,true);
            if(count($result) > 1) {
                $this->consoleIO->writeError("Only one choices can be selected.");
                return $this->select($question,$choices,$default,$attempts,$errorMessage,$multiselect);
            }
            $index = current($result);//return index of choices.
            //todo: add check index.
            return $index;
        }
    }
    /**
     * Get all authentication information entered.
     *
     * @return array The map of authentication data
     */
    public function getAuthentications()
    {
        return $this->consoleIO->getAuthentications();
    }
    /**
     * Verify if the repository has a authentication information.
     *
     * @param string $repositoryName The unique name of repository
     *
     * @return bool
     */
    public function hasAuthentication($repositoryName)
    {
        return $this->consoleIO->hasAuthentication($repositoryName);
    }
    /**
     * Get the username and password of repository.
     *
     * @param string $repositoryName The unique name of repository
     *
     * @return array The 'username' and 'password'
     */
    public function getAuthentication($repositoryName)
    {
        return $this->consoleIO->getAuthentication($repositoryName);
    }
    /**
     * Set the authentication information for the repository.
     *
     * @param string $repositoryName The unique name of repository
     * @param string $username The username
     * @param string $password The password
     */
    public function setAuthentication($repositoryName, $username, $password = null)
    {
        $this->consoleIO->setAuthentication($repositoryName, $username, $password);
    }
    /**
     * Loads authentications from a config instance
     *
     * @param Config $config
     */
    public function loadConfiguration(Config $config)
    {
        return $this->consoleIO->loadConfiguration($config);
    }
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $this->consoleIO->log($level, $message, $context);
    }
}