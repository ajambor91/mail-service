<?php

namespace MailService\MailService\Core;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Mailer class, responsible for building PHPMailer object and sending messages
 */
class Mailer
{

    /**
     * @var string
     */
    private string $sender;
    /**
     * @var string
     */
    private string $username;
    /**
     * @var string
     */
    private string $hostServer;
    /**
     * @var string
     */
    private string $password;
    /**
     * @var int
     */
    private int $port;
    /**
     * @var bool
     */
    private bool $isSMTP;
    /**
     * @var Env
     */
    private Env $env;

    /**
     * @var PHPMailer
     */
    private PHPMailer $phpMailer;
    /**
     * @var Mail
     */
    private IMail $mail;

    /**
     * @param Mail $mail
     * @throws Exception
     */
    public function __construct(Mail $mail, Env $env, PHPMailer $phpMailer)
    {
        $this->phpMailer = $phpMailer;
        $this->env = $env;
        $this->mail = $mail;
        $this->init();
    }

    /**
     * Initializing Mailer
     * @param Mail $mail
     * @return void
     * @throws Exception
     */
    private function init(): void
    {

        if (empty($this->mail)) {
            throw new Exception("Mail cannot be empty");
        }
        $host = $this->env->getHost();
        if (empty($host)) {
            throw new Exception("No host found");
        }
        $this->hostServer = $host;

        $port = $this->env->getPort();
        if (empty($port)) {
            throw new Exception("No port found");
        }
        $this->port = $port;
        if (!empty($this->env->getPassword())) {
            $this->password = $this->env->getPassword();
        }
        if (!empty($this->env->getUsername())) {
            $this->username = $this->env->getUsername();
        }
        $sender = $this->env->getSenderEmail();
        if (empty($sender)) {
            throw new Exception("Sender email cannot be null");
        }
        $this->sender = $sender;
        $this->isSMTP = $this->env->getIsSMTP();

    }

    /**
     * Method for sending message, should be call after setup and prepare
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendMessage(): void
    {
        $this->phpMailer->send();
    }

    /**
     * Method for setup PHPMailer data
     * @return $this
     */
    public function setup(): self
    {
        if ($this->isSMTP) {
            $this->phpMailer->isSMTP();
            (new MailerSecuritySetup($this->phpMailer, $this->env))->setupSSL();
            $this->phpMailer->Host = $this->hostServer;
            $this->phpMailer->Port = $this->port;
        } else {
            $this->phpMailer->isMail();
            $this->phpMailer->SMTPAuth = false;
        }
        if ($this->env->isPHPMailerDebugMode()) {
            $this->phpMailer->SMTPDebug = SMTP::DEBUG_CONNECTION;
        }
        return $this;
    }

    /**
     * Method for preparing message
     * @return $this
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function prepare(): self
    {
        $this->addRecipients();
        $this->addCC();
        $this->addBCC();
        $this->addMessageData();
        return $this;
    }

    /**
     * Adding recipients to message
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function addRecipients(): void
    {
        $recipients = $this->mail->getRecipientMail();
        if (is_array($recipients)) {
            foreach ($recipients as $recipient) {
                $this->phpMailer->addAddress($recipient);
            }
        } else {
            $this->phpMailer->addAddress($recipients);
        }
    }

    /**
     * Adding cc to message
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function addCC(): void
    {
        $cc = $this->mail->getCCMail();
        if (empty($cc)) {
            return;
        }

        if (is_array($cc)) {
            foreach ($cc as $item) {
                $this->phpMailer->addCC($item);
            }
        } else {
            $this->phpMailer->addCC($cc);
        }
    }

    /**
     * Adding bcc to message
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function addBCC(): void
    {
        $bcc = $this->mail->getBCCMail();
        if (empty($bcc)) {
            return;
        }
        if (is_array($bcc)) {
            foreach ($bcc as $item) {
                $this->phpMailer->addBCC($item);
            }
        } else {
            $this->phpMailer->addBCC($bcc);
        }
    }

    /**
     * Adding message content
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function addMessageData(): void
    {
        $isHtml = false;
        if (!empty($this->mail->getIsHTML()) && $this->mail->getIsHTML() === true) {
            $isHtml = true;
        }
        if ($isHtml === false && $this->env->getIsHTML() === true) {
            $isHtml = true;
        }
        $this->phpMailer->isHTML($isHtml);
        if ($this->mail->getTitle()) {
            $this->phpMailer->Subject = $this->mail->getTitle();
        }
        $message = null;

        if ($isHtml) {
            if (!is_array($this->mail->getMessage())) {
                throw new Exception("Message for html e-mail must be an array");
            }
            $htmlParser = new HTMLMessage($this->mail);
            $message = $htmlParser->getTemplate($this->mail->getMessage());
        } elseif (is_string($this->mail->getMessage())) {
            $message = $this->mail->getMessage();
        }
        $this->phpMailer->Body = $message;
        $this->phpMailer->setFrom($this->sender);
    }
}