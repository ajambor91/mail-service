<?php

namespace MailService\MailService\Core;

use MailService\MailService\Exceptions\InvalidContentType;
use MailService\MailService\Exceptions\InvalidHost;
use MailService\MailService\Exceptions\InvalidSecret;

/**
 * Class for getting request headers
 */
class Headers implements IHeaders
{


    private const HTTP_SECRET = 'HTTP_X_APP_SECRET';

    private const HTTP_HOST = 'HTTP_HOST';

    private const CONTENT_TYPE = 'CONTENT_TYPE';
    /**
     * @var string|null
     */
    private ?string $secret = null;
    /**
     * @var string|null
     */
    private ?string $host = null;
    /**
     * @var string|null
     */
    private ?string $contentType = null;


    public function __construct(array $headers)
    {
        $this->parseHeaders($headers);
    }

    /**
     * Parse request headers and set these for thic class
     * @param array $headers
     * @return void
     */
    private function parseHeaders(array $headers): void
    {

        foreach ($headers as $key => $header) {
            if (self::CONTENT_TYPE === $key) {
                $this->contentType = $header;
            }
            if (self::HTTP_HOST === $key) {
                $this->host = $header;
            }
            if (self::HTTP_SECRET === $key) {
                $this->secret = $header;
            }
        }
        if (empty($this->contentType)) {
            throw new InvalidContentType('No content type found');
        }

        if (empty($this->host)) {
            throw new InvalidHost('No host found');
        }

        if (empty($this->secret)) {
            throw new InvalidSecret("No secret found");
        }
    }

    /**
     * Get Content-Type request header
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Get secret from request header
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Get host from request header
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }
}

