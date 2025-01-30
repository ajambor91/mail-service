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
     * @return string|array
     */
    public function getCCMail(): string|array;

    /**
     * @return string|array
     */
    public function getBccMail(): string|array;

    /**
     * @return string|array
     */
    public function getRecipientMail(): string|array;

    /**
     * @return string
     */
    public function getTitle(): string;

}