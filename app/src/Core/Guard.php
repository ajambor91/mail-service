<?php

namespace MailService\MailService\Core;

use MailService\MailService\Exceptions\InvalidContentType;
use MailService\MailService\Exceptions\InvalidDomain;
use MailService\MailService\Exceptions\InvalidSecret;

/**
 * Class for checking access
 */
class Guard
{
    /**
     *
     */
    private const CONTENT_TYPE = 'application/json';
    /**
     * @var Env
     */
    private Env $env;
    /**
     * @var array|null
     */
    private array|null $allowedDomains;
    /**
     * @var string
     */
    private string $secret;
    /**
     * @var IHeaders
     */
    private IHeaders $headers;

    /**
     * @var Response
     */
    private Response $response;

    /**
     * @param IHeaders $headers
     * @param Env $env
     * @param Response $response
     */
    public function __construct(IHeaders $headers, Env $env, Response $response)
    {
        $this->response = $response;
        $this->env = $env;
        $this->headers = $headers;
        $this->init();
    }

    /**
     * Initializing object
     * @return void
     */
    private function init(): void
    {
        $this->allowedDomains = $this->env->getAllowedDomains();
        $this->secret = $this->env->getSecret();
    }

    /**
     * Checking domain, secret type and valid Content-Type
     * @return bool
     * @throws InvalidContentType
     * @throws InvalidSecret
     * @throws InvalidDomain
     */
    public function checkAccess(): bool
    {
        $isAllowedDomain = $this->checkDomains();
        $isRightSecret = $this->checkSecret();
        $isAppJson = $this->checkContentType();
        return $isAppJson && $isAllowedDomain && $isRightSecret;

    }

    /**
     * Checking domain
     * @return bool
     */
    private function checkDomains(): bool
    {

        if ($this->allowedDomains == null) {
            return true;
        } elseif ($this->iterateOverDomains()) {
            return true;
        }

        throw new InvalidDomain("Invalid domain");

    }

    /**
     * Method iterating over .env domains and compare it to request's host
     * @return bool
     */
    private function iterateOverDomains(): bool
    {

        foreach ($this->allowedDomains as $allowedDomain) {
            if ($allowedDomain === $this->headers->getHost()) {
                $this->response->setAllowedDomain($allowedDomain);
                return true;
            }
        }
        return false;
    }

    /**
     * Checking secret
     * @return bool
     * @throws InvalidSecret
     */
    private function checkSecret(): bool
    {
        if (empty($this->secret) || $this->secret !== $this->headers->getSecret()) {
            throw new InvalidSecret("Invalid secret");

        }
        return true;
    }

    /**
     * Checking content type
     * @return bool
     * @throws InvalidContentType
     */
    private function checkContentType(): bool
    {
        if (self::CONTENT_TYPE !== $this->headers->getContentType()) {
            throw new InvalidContentType("Invalid content type");
        }
        return true;
    }
}