<?php

namespace MailService\MailService\Core;

use Exception;

/**
 * Class for create HTML template
 */
class HTMLMessage
{

    private const TEMPLATES_DIR = ROOT . "/templates";
    /**
     * @var string|null
     */
    private ?string $template;
    /**
     * @var
     */
    private $fileTemplate;

    /**
     * @param Mail $mail
     */
    public function __construct(Mail $mail)
    {
        $this->init($mail);
    }


    /**
     * @param Mail $mail
     * @return void
     * @throws Exception
     */
    private function init(Mail $mail)
    {
        $this->template = $mail->getTemplate();
        $this->getFileTemplate();
    }

    /**
     * @param array $message
     * @return mixed
     * @throws Exception
     */
    public function getTemplate(array $message)
    {
        if (!is_array($message)) {
            throw new Exception("Message is not an array");
        }
        $this->parseFile($message);
        return $this->fileTemplate;
    }

    /**
     * @param array $message
     * @return void
     * @throws Exception
     */
    private function parseFile(array $message)
    {
        foreach ($message as $key => $value) {
            if (!is_string($value) && !is_numeric($value)) {
                throw new Exception("Value to replace is not a string or value");
            }
            $pattern = '/{{' . $key . '}}/';
            $this->fileTemplate = preg_replace($pattern, $value, $this->fileTemplate);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function getFileTemplate()
    {
        $filename = 'mail';
        if (!empty($this->template)) {
            $filename = $this->template;
        }
        $this->fileTemplate = file_get_contents(self::TEMPLATES_DIR . '/' . $filename . '.html');
        if (empty($this->fileTemplate)) {
            throw new Exception("Cannot find file template");
        }

    }
}