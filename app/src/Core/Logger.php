<?php

namespace MailService\MailService\Core;

use DateTime;
use RuntimeException;

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
    private const LOG_FILENAME = 'application.log';

    /**
     *
     */
    private const LEVEL_MAP = [
        'emergency' => 600,
        'alert' => 550,
        'critical' => 500,
        'error' => 400,
        'warning' => 300,
        'notice' => 250,
        'info' => 200,
        'debug' => 100,
    ];


    /**
     * @var string
     */
    private string $minLogLevel = 'info';

    /**
     * @param Env $env
     */
    public function __construct(Env $env)
    {
        $this->initialize($env);
    }

    /**
     * @param Env $env
     * @return void
     */
    private function initialize(Env $env): void
    {

        $this->minLogLevel = $env->getDebugLevel();
        $logDir = self::LOG_DIR;

        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0775, true)) {
                error_log(sprintf("FATAL ERROR: Failed to create log directory: %s. Check permissions. UUID: %s", $logDir, $this->uuid ?? 'N/A'));
                throw new RuntimeException(sprintf("Log directory %s does not exist and could not be created.", $logDir));
            }
        }

        if (!is_writable($logDir)) {
            error_log(sprintf("FATAL ERROR: Log directory is not writable: %s. Check permissions. UUID: %s", $logDir, $this->uuid ?? 'N/A'));
            throw new RuntimeException(sprintf("Log directory %s is not writable.", $logDir));
        }
    }

    /**
     * @param string $uuid
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug(string $uuid, string $message, array $context = []): void
    {
        $this->logToFile($uuid, $message, 'debug', $context);
    }

    /**
     * @param string $uuid
     * @param string $message
     * @param string $level
     * @param array $context
     * @return void
     */
    private function logToFile(string $uuid, string $message, string $level, array $context = []): void
    {
        if (!$this->shouldLog($level)) {
            return;
        }

        $logFilePath = rtrim(self::LOG_DIR, '/') . '/' . ltrim(self::LOG_FILENAME, '/');

        $now = new DateTime();
        $formattedDate = $now->format('Y-m-d H:i:s');

        $logLine = sprintf("[%s] [%s] UUID: %s %s", $formattedDate, strtoupper($level), $uuid, $message);

        if (!empty($context)) {
            $logLine .= ' Context: ' . json_encode($context);
        }

        $logLine .= "\n";

        $result = file_put_contents($logFilePath, $logLine, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            error_log(sprintf("FATAL ERROR: Failed to write log entry to file %s for level %s. Check file permissions and disk space. Original message UUID: %s",
                $logFilePath, $level, $uuid));
        }
    }

    /**
     * @param string $level
     * @return bool
     */
    private function shouldLog(string $level): bool
    {
        $levelValue = self::LEVEL_MAP[$level] ?? 0;
        $minLevelValue = self::LEVEL_MAP[$this->minLogLevel] ?? 0;

        return $levelValue >= $minLevelValue || true;
    }

    /**
     * @param string $uuid
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info(string $uuid, string $message, array $context = []): void
    {
        $this->logToFile($uuid, $message, 'info', $context);
    }

    /**
     * @param string $uuid
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice(string $uuid, string $message, array $context = []): void
    {
        $this->logToFile($uuid, $message, 'notice', $context);
    }

    /**
     * @param string $uuid
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning(string $uuid, string $message, array $context = []): void
    {
        $this->logToFile($uuid, $message, 'warning', $context);
    }

    /**
     * @param string $uuid
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error(string $uuid, string $message, array $context = []): void
    {
        $this->logToFile($uuid, $message, 'error', $context);
    }

    /**
     * @param string $uuid
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical(string $uuid, string $message, array $context = []): void
    {
        $this->logToFile($uuid, $message, 'critical', $context);
    }

    /**
     * @param string $uuid
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert(string $uuid, string $message, array $context = []): void
    {
        $this->logToFile($uuid, $message, 'alert', $context);
    }

    /**
     * @param string $uuid
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency(string $uuid, string $message, array $context = []): void
    {
        $this->logToFile($uuid, $message, 'emergency', $context);
    }


}