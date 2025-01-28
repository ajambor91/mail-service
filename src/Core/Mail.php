<?php

namespace MailService\MailService\Core;
use MailService\MailService\Exceptions\InvalidPayload;

/**
 * Mail class contains all message data
 */
class Mail implements IMail
{
    /**
     * @var string
     */
    private string $title;
    /**
     * @var string
     */
    private string $message;
    /**
     * @var string|array
     */
    private string | array $recipientMail;

    /**
     * @var string|array
     */
    private string | array $ccMail;

    /**
     * @var string|array
     */
    private string | array $bccMail;

    /**
     * @var string
     */
    private string $senderMail;
    /**
     * @var Env
     */
    private Env $env;

    /**
     * @throws InvalidPayload
     */
    public function __construct()
    {
        $this->env = Env::getInstance();
        $this->initMail();
    }

    /**
     * @return string|array
     */
    public function getCCMail(): string | array
    {
        return $this->ccMail;
    }

    /**
     * @return string|array
     */
    public function getBccMail(): string | array
    {
        return $this->bccMail;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string|array
     */

    public function getRecipientMail(): string | array
    {
        return $this->recipientMail;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Initializing e-mail object method and validiting request payload
     * @return void
     * @throws InvalidPayload
     */
    private function initMail(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (empty($payload)) {
            throw new InvalidPayload("No payload found");
        }

        if (empty($payload['message'])) {
            throw new InvalidPayload("No message found");

        }
        $this->message = $payload['message'];



        $recipientMail = $payload['recipientMail'] ?? $this->env->getRecipientEmail();
        if (empty($recipientMail)) {
            throw new InvalidPayload("No one recipient found");
        }
        if (!Helper::checkEmailValidity($recipientMail)) {
            throw new InvalidPayload("Invalid recipient email address");
        }
        $this->recipientMail = $recipientMail;

        $ccMail = $payload['ccMail'] ?? $this->env->getCCMail();
        if (!empty($ccMail)) {
            if (!Helper::checkEmailValidity($ccMail)) {
                throw new InvalidPayload("Invalid cc email email address");

            }
            $this->ccMail = $ccMail;
        }
        $bccMail = $payload['bccMail'] ?? $this->env->getBCCMail();
        if (!empty($bccMail)) {
            if (!Helper::checkEmailValidity($bccMail)) {
                throw new InvalidPayload("Invalid bcc email address");
            }
            $this->bccMail = $bccMail;
        }

        $title = $payload['title'] ?? $this->env->getDefaultTile();
        $this->title = $title;

    }

}