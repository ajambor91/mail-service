<?php

namespace MailService\MailService\Core;

/**
 * Simple router for checking path and method
 */
class Router
{
    /**
     * @const string
     */
    private const METHOD_FOR_MAIL = 'POST';
    /**
     * @const string
     */
    private const PATH_FOR_MAIL = '/send';

    /**
     * @const host path
     */
    private const HOST_PATH = '/';

    /**
     * Check route and methods, when find route for sending message return true, else return false for showing homepage
     * @return bool
     */
    public function checkPathForMail(array $server)
    {
        $requestUri = $server['REQUEST_URI'];
        $path = parse_url($requestUri, PHP_URL_PATH);
        $method = $server['REQUEST_METHOD'];
        if ($path !== self::HOST_PATH && $path !== self::PATH_FOR_MAIL) {
            header("Location: " . self::HOST_PATH);
            exit();
        }
        if ($path === self::PATH_FOR_MAIL && $method === self::METHOD_FOR_MAIL) {
            return true;
        }
        return false;
    }
}