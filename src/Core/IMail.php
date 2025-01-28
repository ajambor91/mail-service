<?php

namespace MailService\MailService\Core;
/**
 * Interface for mail objects
 */
interface IMail
{

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @return string|array
     */
    public function getCCMail(): string | array;

    /**
     * @return string|array
     */
    public function getBccMail(): string | array;

    /**
     * @return string|array
     */
    public function getRecipientMail(): string | array;

    /**
     * @return string
     */
    public function getTitle(): string;

}