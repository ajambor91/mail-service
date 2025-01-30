<?php
namespace MailService\MailService\Core;

/**
 * Class for logging
 */
class Logger
{
    /**
     *
     */
    private const LOG_DIR = ROOT . '/logs';
    /**
     *
     */
    private const LOG_FILENAME = '/log';
    /**
     * @var Logger|null
     */
    private static ?Logger $instance = null;

    /**
     * Private constructor to prevent create object from outside
     */
    private function __construct()
    {
        $this->initialize();
    }

    /**
     * @return Logger|null
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }


    /**
     * Method to logging
     * @param string $uuid
     * @param string $message
     * @return void
     */
    public function log(string $uuid, string $message)
    {
        $now = new \DateTime();
        $formettedDate = $now->format('Y-m-d h:i:s');
        $logFile = fopen( self::LOG_DIR . self::LOG_FILENAME, 'a');
        fwrite($logFile, $formettedDate . PHP_EOL . 'UUID - '. $uuid . PHP_EOL  .  $message . PHP_EOL . PHP_EOL );
        fclose($logFile);
    }

    /**
     * Initializing method, creating dir if isn't exist
     * @return void
     */
    private function initialize()
    {
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR);
        }

    }
}