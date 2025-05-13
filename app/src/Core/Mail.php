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
    private ?string $template = null;

    /**
     * @var bool
     */
    private bool $isHTML;

    /**
     * @var string
     */
    private string $title;
    /**
     * @var string | array
     */
    private string|array $message;
    /**
     * @var string|array
     */
    private string|array $recipientMail;

    /**
     * @var string|array
     */
    private mixed $ccMail;

    /**
     * @var mixed
     */
    private mixed $bccMail;

    /**
     * @var string
     */
    private string $senderMail;
    /**
     * @var Env
     */
    private Env $env;

    /**
     * @param array $payload
     * @throws InvalidPayload
     */
    public function __construct(array $payload, Env $env)
    {
        $this->env = $env;
        $this->initMail($payload);
    }

    /**
     * Initializing e-mail object method and validiting request payload
     * @param array $payload
     * @return void
     * @throws InvalidPayload
     */
    private function initMail(array $payload): void
    {
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

        $ccMail = $payload['ccMail'] ?? null;
        if (!empty($ccMail) && !Helper::checkEmailValidity($ccMail)) {

                throw new InvalidPayload("Invalid cc email email address");


        } else if (!empty($this->env->getCCMail())){
            $ccMail = $this->env->getCCMail();
        }
        $this->ccMail = $ccMail;

        $bccMail = $payload['bccMail'] ?? null;
        if (!empty($bccMail) && !Helper::checkEmailValidity($bccMail)) {
                throw new InvalidPayload("Invalid bcc email address");
        } else if (!empty($this->env->getBCCMail())) {
            $bccMail = $this->env->getBCCMail();
        }
        $this->bccMail = $bccMail;

        if (!empty($payload['isHTML']) && $payload['isHTML'] === true) {
            $this->isHTML = true;
            if (!empty($payload['template'])) {
                $this->template = $payload['template'];
            }
        } else {
            $this->isHTML = false;
        }


        $title = $payload['title'] ?? $this->env->getDefaultTile();
        $this->title = $title;

    }

    /**
     * @return mixed
     */
    public function getCCMail(): mixed
    {
        return $this->ccMail;
    }

    /**
     * @return mixed
     */
    public function getBCCMail(): mixed
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

    public function getRecipientMail(): string|array
    {
        return $this->recipientMail;
    }

    /**
     * @return string|array
     */
    public function getMessage(): string|array
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function getIsHTML(): bool
    {
        return $this->isHTML;
    }

    /**
     * @return string | null
     */
    public function getTemplate(): string|null
    {
        return $this->template;
    }

}