<?php

namespace MailService\MailService\Core;
/**
 * Interface for mail objects
 */
interface IMail
{

    /**
     * @return bool
     */
    public function getIsHTML(): bool;

    /**
     * @return string | null
     */
    public function getTemplate(): string|null;

    /**
     * @return string|array
     */
    public function getMessage(): string|array;

    /**
     * @return mixed
     */
    public function getCCMail(): mixed;

    /**
     * @return mixed
     */
    public function getBCCMail(): mixed;

    /**
     * @return string|array
     */
    public function getRecipientMail(): string|array;

    /**
     * @return string
     */
    public function getTitle(): string;

}