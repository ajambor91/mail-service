<?php

namespace MailService\MailService\Core;

/**
 * Helper class for parse arrays from .env
 */
class Helper
{
    /**
     * Helper method to parsing array from .env file
     * @param string $envArgument
     * @return array
     */
    public static function parseEnvArray(string $envArgument): array
    {
        $envArgument = preg_replace('/[\[\'\]]/','',$envArgument);
        $envArgument = explode(',', $envArgument);
        return $envArgument;
    }

    /**
     * Helper method for email address validation
     * @param string|array $email
     * @return bool
     */
    public static function checkEmailValidity(string | array $email): bool
    {
        if (is_array($email)) {
            foreach ($email as $item) {
                if (!filter_var($item, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
            }
            return true;
        } else {
            return !!filter_var($email, FILTER_VALIDATE_EMAIL);
        }
    }
}