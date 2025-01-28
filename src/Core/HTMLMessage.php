<?php
namespace MailService\MailService\Core;

class HTMLMessage
{
    private const TEMPLATES_DIR = ROOT . "/templates";
    private ?string $template;
    private $fileTemplate;

    public function __construct(Mail $mail)
    {
        $this->init($mail);
    }


    private function init(Mail $mail)
    {
        $this->template = $mail->getTemplate();
        $this->getFileTemplate();
    }
    public function getTemplate(array $message)
    {
        if (!is_array($message)) {
            throw new \Exception("Message is not an array");
        }
        $this->parseFile($message);
        return $this->fileTemplate;
    }

    private function getFileTemplate()
    {
        $filename = 'mail';
        if (!empty($this->template)) {
            $filename = $this->template;
        }
        $this->fileTemplate = file_get_contents(self::TEMPLATES_DIR . '/' . $filename . '.html');
        if (empty($this->fileTemplate)) {
            throw new \Exception("Cannot find file template");
        }

    }

    private function parseFile(array $message)
    {
        foreach ($message as $key => $value) {
            if (!is_string($value) && !is_numeric($value)) {
                throw new \Exception("Value to replace is not a string or value");
            }
            $pattern = '/{{'. $key . '}}/';
            $this->fileTemplate = preg_replace($pattern, $value, $this->fileTemplate);
        }
    }
}