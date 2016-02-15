<?php

/**
 * Class View
 */
abstract class View
{
    /**
     * Holds the layout's container folder to be used
     * by end() method for including footer file.
     */
    private static $layoutFolderName = '';


    /**
     * Holds the layout's container folder to be used
     * by end() method for including footer file.
     */
    private static $postfix = '.php';

    /**
     * Extends a view
     *
     * @param $layoutFolderName
     * @param $var
     * @return self::render
     */
    public static function extend($layoutFolderName, $var = [])
    {
        self::$layoutFolderName = trim(str_replace('header', '', $layoutFolderName), '/');

        return self::render(self::$layoutFolderName . '/header', $var);
    }

    /**
     * Formatting the content by replacing any
     * custom tags.
     *
     * @param $template
     * @param $_
     * @return view
     */
    public static function render($template, $_ = null)
    {
        /**
         * Extract the $_ variable to create new variables
         * from array keys
         */
        if (!is_null($_)) {
            extract($_);
        }

        $filename = views_dir() . preg_replace('/\.php$/', '', ltrim($template, '/')) . self::$postfix;

        if (!file_exists($filename)) {
            trigger_error("File does not exist '{$filename}'", E_USER_ERROR);
        }

        $content = file_get_contents($filename);
        $content = preg_replace('/\{\{(.*)\}\}$/', '<?php echo htmlentities($1) ?>', $content);
        $content = preg_replace('/\{\{/', '<?php echo htmlentities($1', $content);
        $content = preg_replace('/\}\}/', ') ?>', $content);
        $content = preg_replace('/\{\!(.*)\!\}$/', '<?php echo $1 ?>', $content);
        $content = preg_replace('/\{\!/', '<?php echo $1', $content);
        $content = preg_replace('/\!\}/', ' ?>', $content);
        $content = preg_replace('/\\\{\\\{/', '{{', $content);
        $content = preg_replace('/\\\}\\\}/', '}}', $content);
        $content = preg_replace('/\{if (.*)\}/', '<?php if ($1) : ?>', $content);
        $content = preg_replace('/\{else\}/', '<?php else : ?>', $content);
        $content = preg_replace('/\{endif\}/', '<?php endif ?>', $content);
        $content = preg_replace('/\{for (.*)\}/', '<?php for ($1) : ?>', $content);
        $content = preg_replace('/\{endfor\}/', '<?php endfor ?>', $content);
        $content = preg_replace('/\{while (.*)\}/', '<?php while ($1) : ?>', $content);
        $content = preg_replace('/\{endwhile\}/', '<?php endwhile ?>', $content);
        $content = preg_replace('/\{foreach (.*)\}/', '<?php foreach ($1) : ?>', $content);
        $content = preg_replace('/\{endforeach\}/', '<?php endforeach ?>', $content);

        return eval(' ?>' . $content . '<?php ');
    }

    /**
     * Require a footer file.
     *
     * @param $var
     * @return self::render
     */
    public static function endExtend($var = [])
    {
        return self::render(self::$layoutFolderName . '/footer', $var);
    }


    /**
     * Include a file and render custom tags.
     *
     * @param $template
     * @param $var
     * @return self::render
     */
    public static function parse($template, $var = [])
    {
        return self::render($template, $var);
    }


    /**
     * Include a file.
     * you cannot use custom tags with this.
     *
     * @param $template
     * @return self::render
     */
    public static function get($template)
    {
        $filename = views_dir() . $template . self::$postfix;

        if (!file_exists($filename)) {
            trigger_error("File does not exist '{$filename}'", E_USER_ERROR);
        }

        return include ("$filename");
    }

}