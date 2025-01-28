<?php

namespace MailService\MailService\Core;

use Dotenv\Dotenv;

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
    public function getIsSMTP(): bool {
        $isSMTP = $_ENV['SMTP'];
        if ($isSMTP === false || strtolower($isSMTP) === 'false') {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $_ENV["PASSWORD"];
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $_ENV['USERNAME'];
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
            throw new \Exception("Invalid sender email in .env");
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
    public function getRecipientEmail(): string | array
    {

        $recipientMail = Helper::parseEnvArray($recipientMail);
        if (empty($recipientMail)) {
            $recipientMail = $_ENV['ADMIN_EMAIL'];
        } elseif (count($recipientMail) === 1) {
            $recipientMail = $recipientMail[0];
        }
        if (!empty($recipientMail) && !Helper::checkEmailValidity($recipientMail)) {
            throw new \Exception("Invalid recipient email in .env");
        }
        return  $recipientMail;
    }

    /**
     * @return array|string
     */
    public function getBCCMail(): array | string
    {
        $bccMail = $_ENV['BCC_MAIL'];
        $bccMail = Helper::parseEnvArray($bccMail);
        if (!empty($bccMail) && !Helper::checkEmailValidity($bccMail)) {
            throw new \Exception("Invalid bcc email in .env");

        }
        return count($bccMail) === 1 ? $bccMail[0] : $bccMail;
    }

    /**
     * @return array|string
     */
    public function getCCMail(): array | string
    {
        $ccMail = $_ENV['CC_MAIL'];
        $ccMail = Helper::parseEnvArray($ccMail);
        if (!empty($ccMail) && !Helper::checkEmailValidity($ccMail)) {
            throw new \Exception("Invalid cc email in .env");
        }
        return count($ccMail) === 1 ? $ccMail[0] : $ccMail;
    }

    /**
     * @return array|null
     */
    public function getAllowedDomains(): array | null
    {
        $allowedDomains = $_ENV['ALLOWED_DOMAINS'];
        $allowedDomains = preg_replace('/[\[\'\]]/','',$allowedDomains);
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