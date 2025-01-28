<?php

namespace MailService\MailService\Core;

/**
 * Interface for header object
 */
interface IHeaders
{
    /**
     * @return string
     */
    public function getContentType(): string;

    /**
     * @return string
     */
    public function getSecret(): string;

    /**
     * @return string
     */
    public function getHost(): string;
}