<?php

namespace MailService\MailService\Core;

/**
 * Class for showing default page
 */
class View
{
    /**
     * @const VIEW_PATH
     */
    private const VIEW_PATH = ROOT . '/src/views/index.html';

    /**
     * Shows app homepage
     * @return void
     */
    public function showView()
    {
        $file = file_get_contents(self::VIEW_PATH);
        echo $file;
        exit();
    }

}