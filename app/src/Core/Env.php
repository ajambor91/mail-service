<?php

namespace MailService\MailService\Core;

use Dotenv\Dotenv;
use Exception;
use MailService\MailService\Enums\SSLEnum;

/**
 * Helper for getting and parsing data from .env
 */
class Env
{
    /**
     * @var Env|null
     */
    private static ?Env $instance = null;
    /**
     * @var Dotenv
     */
    private Dotenv $dotEnv;

    /**
     *
     */
    private function __construct()
    {

        $this->dotEnv = Dotenv::createImmutable(ROOT);
        $this->dotEnv->load();
    }

    /**
     * @return Env
     */
    public static function getInstance(): Env
    {
        if (!Env::$instance) {
            Env::$instance = new Env();
        }
        return Env::$instance;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $_ENV["SECRET"];
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $_ENV["HOST"];
    }

    /**
     * @return bool
     */
    public function getIsDebug(): bool
    {
        $isDebug = false;
        if ($_ENV['IS_DEBUG'] === true || strtolower($_ENV['IS_DEBUG']) === 'true') {
            $isDebug = true;
        }
        return $isDebug;
    }

    /**
     * @return bool
     */
    public function getIsSMTP(): bool
    {
        $isSMTP = $_ENV['SMTP'];
        if ($isSMTP === false || strtolower($isSMTP) === 'false') {
            return false;
        }
        return true;
    }

    /**
     * @retutn bool
     */
    public function getSSL(): SSLEnum
    {

        return SSLEnum::fromConfigValue($_ENV['SSL'] ?? false);
    }

    /**
     * @return string
     */
    public function getDebugLevel(): string
    {
        $debugLevel = $_ENV['DEBUG_LEVEL'] ?? 'info';
        return $debugLevel;
    }

    /**
     * @return bool
     */
    public function getSSLVerifyServerCert(): bool
    {
        $verifyCertServer = $_ENV['SSL_VERIFY_SERVER_CERT'];
        if ($verifyCertServer === false || strtolower($verifyCertServer) === 'false') {
            return false;
        }
        return  true;
    }

    /**
     * @return bool
     */
    public function getSSLVerifyServerName(): bool
    {
        $sslVerifyServerName = $_ENV['SSL_VERIFY_SERVER_NAME'];
        if ($sslVerifyServerName === false || strtolower($sslVerifyServerName) === 'false') {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function getAllowingSelfSignCert() : bool
    {
        $allowSelfSignedSSLCerts = $_ENV['SSL_ALLOW_SELF_SIGNED'];
        if ($allowSelfSignedSSLCerts === true || strtolower($allowSelfSignedSSLCerts) === 'true') {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $_ENV["PASSWORD"] ?? null;
    }

    /**
     * @return string
     */
    public function getUsername(): ?string
    {
        return $_ENV['USERNAME'] ?? null;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return (int)$_ENV["PORT"];
    }

    /**
     * @return string
     */
    public function getSenderEmail(): string
    {
        $senderEmail = $_ENV['SENDER_EMAIL'];
        if (empty($senderEmail) || !Helper::checkEmailValidity($senderEmail)) {
            throw new Exception("Invalid sender email in .env");
        }
        return $senderEmail;
    }

    /**
     * @return string
     */
    public function getDefaultTile(): string
    {
        return $_ENV['DEFAULT_TITLE'];
    }

    /**
     * @return string|array
     */
    public function getRecipientEmail(): string|array
    {

        $recipientMail = $_ENV['RECIPIENT_MAIL'];
        $recipientMail = Helper::parseEnvArray($recipientMail);
        if (empty($recipientMail)) {
            $recipientMail = $_ENV['ADMIN_EMAIL'];
        } elseif (count($recipientMail) === 1) {
            $recipientMail = $recipientMail[0];
        }
        if (!empty($recipientMail) && !Helper::checkEmailValidity($recipientMail)) {
            throw new Exception("Invalid recipient email in .env");
        }
        return $recipientMail;
    }

    /**
     * @return bool
     */
    public function isPHPMailerDebugMode()
    {
        $phpMailerDebug = $_ENV['PHP_MAILER_DEBUG'] ?? null;
        if (!empty($phpMailerDebug) && strtolower($phpMailerDebug) === 'true') {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getBCCMail(): mixed
    {
        $bccMail = $_ENV['BCC_MAIL'] ?? null;
        if (!$bccMail) {
            return  null;
        }
        $bccMail = Helper::parseEnvArray($bccMail);
        if (!Helper::checkEmailValidity($bccMail)) {
            throw new Exception("Invalid bcc email in .env");

        }
        return count($bccMail) === 1 ? $bccMail[0] : $bccMail;
    }

    /**
     * @return mixed
     */
    public function getCCMail(): mixed
    {
        $ccMail = $_ENV['CC_MAIL'] ?? null;
        if (!$ccMail) {
            return  null;
        }
        $ccMail = Helper::parseEnvArray($ccMail);

        if (!Helper::checkEmailValidity($ccMail)) {
            throw new Exception("Invalid cc email in .env");
        }
        return count($ccMail) === 1 ? $ccMail[0] : $ccMail;
    }

    /**
     * @return array|null
     */
    public function getAllowedDomains(): array|null
    {
        $allowedDomains = $_ENV['ALLOWED_DOMAINS'];
        $allowedDomains = preg_replace('/[\[\'\]]/', '', $allowedDomains);
        $allowedDomains = explode(',', $allowedDomains);
        return !empty($allowedDomains) ? $allowedDomains : null;
    }

    /**
     * @return bool
     */
    public function getIsHTML()
    {
        $isHTML = $_ENV['IS_HTML'];
        if ($isHTML === true || strtolower($isHTML) === 'true') {
            return true;
        }
        return false;
    }

}