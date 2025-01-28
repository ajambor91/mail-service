<?php
namespace MailService\MailService\Core;

/**
 * Response class responsible 
 */
class Response
{

    /**
     * @var Response|null
     */
    private static ?Response $instance = null;
    /**
     * @var array
     */
    private array $headers = [
        'Content-type' => 'application/json',
        'Access-Control-Allow-Origin' => '',
        'Access-Control-Allow-Methods' => 'POST, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Secret',
        'Access-Control-Allow-Credentials' => false
    ];


    /**
     * @var array
     */
    private array $message;

    /**
     * @var int
     */
    private int $code;

    /**
     * Private constructor to prevent creating object of this class in outside object
     */
    private function __construct()
    {

    }

    /**
     * Returning self instance
     * @return Response
     */
    public static function getInstance(): Response {
        if (!Response::$instance) {
            Response::$instance = new Response();
        }
        return Response::$instance;
    }


    /**
     * Setting allowing domain
     * @param string $domain
     * @return void
     */
    public function setAllowedDomain(string $domain): void
    {
        $this->headers['Access-Control-Allow-Origin'] = $domain;
    }

    /**
     * Setting response message
     * @param array $message
     * @return void
     */
    public function setMessage(array $message): void
    {
        $this->message = $message;
    }

    /**
     * Setting HTTP response code
     * @param int $code
     * @return void
     */
    public function setCode(int $code): void {
        $this->code =$code;
    }

    /**
     * Returning HTTP response
     * @return void
     */
    public function returnResponse(): void {
        foreach ($this->headers as $key => $header) {
            header("$key: $header");
        }

        if (empty($this->message)) {
            http_response_code(500);
            echo json_encode(['message'=> "Message is empty"]);
            exit;
        }
        http_response_code($this->code);
        echo json_encode($this->message);
        exit;
    }


}