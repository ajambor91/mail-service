<?php

namespace MailService\MailService\Core;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class for set security options
 */
class MailerSecuritySetup
{
    /**
     * @var Env
     */
    private Env $env;
    /**
     * @var PHPMailer
     */
    private PHPMailer $phpMailer;

    /**
     * @param PHPMailer $phpMailer
     */
    public function __construct(PHPMailer $phpMailer, Env $env)
    {
        $this->phpMailer = $phpMailer;
        $this->env = $env;
    }

    /**
     * @return void
     */
    public function setupSSL(): void
    {
        $sslEncryption = $this->env->getSSL()->value;
        $this->phpMailer->SMTPSecure = $sslEncryption;

        if ($sslEncryption) {
            $this->setSSLOptions();
        }
        $this->setSMTPAuthorization();

    }

    /**
     * @return void
     */
    private function setSSLOptions()
    {

        $this->phpMailer->SMTPOptions = [
            'ssl' => [
                'verify_peer' => $this->env->getSSLVerifyServerCert(),
                'verify_peer_name' => $this->env->getSSLVerifyServerName(),
                'allow_self_signed' => $this->env->getAllowingSelfSignCert()
            ]
        ];
    }

    /**
     * @return void
     */
    private function setSMTPAuthorization()
    {
        if (!empty($this->env->getUsername()) && !empty($this->env->getPassword())) {
            $this->phpMailer->SMTPAuth = true;
            $this->phpMailer->Username = $this->env->getUsername();
            $this->phpMailer->Password = $this->env->getPassword();
        } else {
            $this->phpMailer->SMTPAuth = false;
        }
    }

}