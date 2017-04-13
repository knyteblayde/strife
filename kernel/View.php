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
     * The default file type to be used
     * on all template files.
     * (defined on config/application.php)
     */
    private static $postfix = TEMPLATE_TYPE;

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
     * custom tags by equivalent code in PHP
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

        if (preg_match('/(.*)\.php/i', $template)) {
            $filename = '../views/' . $template;
        } else {
            $filename = '../views/' . ltrim($template, '/') . self::$postfix;
        }


        if (!file_exists($filename)) {
            trigger_error("File does not exist '{$filename}'", E_USER_ERROR);
        }
        
        $___ = file_get_contents($filename);
        $___ = preg_replace('/\{\{(.*)\}\}$/', '<?php echo htmlentities($1) ?>', $___);
        $___ = preg_replace('/\{\{/', '<?php echo htmlentities($1', $___);
        $___ = preg_replace('/\}\}/', ') ?>', $___);
        $___ = preg_replace('/\{\!(.*)\!\}$/', '<?php echo $1 ?>', $___);
        $___ = preg_replace('/\{\!/', '<?php echo $1', $___);
        $___ = preg_replace('/\!\}/', ' ?>', $___);
        $___ = preg_replace('/\\\{\\\{/', '{{', $___);
        $___ = preg_replace('/\\\}\\\}/', '}}', $___);
        $___ = preg_replace('/\{if (.*)\}/', '<?php if ($1) : ?>', $___);
        $___ = preg_replace('/\{else\}/', '<?php else : ?>', $___);
        $___ = preg_replace('/\{endif\}/', '<?php endif ?>', $___);
        $___ = preg_replace('/\{for (.*)\}/', '<?php for ($1) : ?>', $___);
        $___ = preg_replace('/\{endfor\}/', '<?php endfor ?>', $___);
        $___ = preg_replace('/\{while (.*)\}/', '<?php while ($1) : ?>', $___);
        $___ = preg_replace('/\{endwhile\}/', '<?php endwhile ?>', $___);
        $___ = preg_replace('/\{foreach (.*)\}/', '<?php foreach ($1) : ?>', $___);
        $___ = preg_replace('/\{endforeach\}/', '<?php endforeach ?>', $___);

        return eval(' ?>' . $___ . '<?php ');
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
        $filename = '../views/' . $template . self::$postfix;

        if (!file_exists($filename)) {
            trigger_error("File does not exist '{$filename}'", E_USER_ERROR);
        }

        return include ("$filename");
    }

}