<?php

namespace MailService\MailService\Enums;

use MailService\MailService\Exceptions\InvalidSSLType;
use PHPMailer\PHPMailer\PHPMailer;

enum SSLEnum: string
{
    case NONE = '';
    case SMTPS = PHPMailer::ENCRYPTION_SMTPS;
    case STARTTLS = PHPMailer::ENCRYPTION_STARTTLS;


    public static function fromConfigValue(mixed $configValue): self
    {
        $lowerConfigValue = strtolower((string)$configValue);

        return match ($lowerConfigValue) {
            'false', '', 'null' => self::NONE,
            'smtps' => self::SMTPS,
            'starttls' => self::STARTTLS,
            default => throw new InvalidSSLType("Invalid SSL configuration: " . (is_scalar($configValue) ? (string)$configValue : gettype($configValue))),
        };

    }
}
