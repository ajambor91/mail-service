<?php

namespace MailService\MailService\Core;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;

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
    public function __construct(Mail $mail)
    {
        $this->init($mail);
    }

    /**
     * Initializing Mailer
     * @param Mail $mail
     * @return void
     * @throws Exception
     */
    private function init(Mail $mail): void
    {
        if (empty($mail)) {
            throw new Exception("Mail cannot be empty");
        }
        $this->mail = $mail;
        $this->env = Env::getInstance();
        $this->phpMailer = new PHPMailer(true);
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
        $password = $this->env->getPassword();
        if (empty($password)) {
            throw new Exception("No password found");
        }
        $this->password = $password;
        $username = $this->env->getUsername();

        if (empty($username)) {
            throw new Exception("No username found");
        }


        $this->username = $username;
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
            $this->phpMailer->SMTPAuth = true;
            $this->phpMailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        }
        $this->phpMailer->Host = $this->hostServer;
        $this->phpMailer->Username = $this->username;
        $this->phpMailer->Password = $this->password;
        $this->phpMailer->Port = $this->port;
        return $this;
    }

    /**
     * Method for preparing message
     * @return $this
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
        $bcc = $this->mail->getBccMail();
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