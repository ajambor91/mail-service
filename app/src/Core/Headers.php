<?php

namespace MailService\MailService\Core;

use Exception;
use MailService\MailService\Exceptions\InvalidContentType;
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
        $this->getHeaders($headers);
    }

    /**
     * Parse request headers and set these for thic class
     * @param array $headers
     * @return void
     */
    private function getHeaders(array $headers): void
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
    }

    /**
     * Get Content-Type request header
     * @return string
     * @throws InvalidContentType
     */
    public function getContentType(): string
    {
        if (empty($this->contentType)) {
            throw new InvalidContentType('No content type found');
        }
        return $this->contentType;
    }

    /**
     * Get secret from request header
     * @return string
     * @throws InvalidSecret
     */
    public function getSecret(): string
    {
        if (empty($this->secret)) {
            throw new InvalidSecret("No secret found");
        }
        return $this->secret;
    }

    /**
     * Get host from request header
     * @return string
     * @throws Exception
     */
    public function getHost(): string
    {
        if (empty($this->host)) {
            throw new Exception("No host found");
        }
        return $this->host;
    }
}
