<?php

namespace jensostertag\Templify;

class Templify {
    private static array $config = [
        "TEMPLATE_BASE_DIR" => "/templates/"
    ];

    /**
     * Set a Config Value
     * @param string $key Config Key
     * @param mixed $value Config Value
     * @return void
     */
    public static function setConfig(string $key, mixed $value): void {
        self::$config[$key] = $value;
    }

    /**
     * Get a Config Value
     * @param string $key
     * @return mixed
     */
    public static function getConfig(string $key): mixed {
        if(isset(self::$config[$key])) {
            return self::$config[$key];
        }

        return null;
    }

    /**
     * Include a PHP Template File that contains the frontend Code
     * @param string $template Name of Template File within the frontend Directory
     * @param array|null $variables Variables that should be available within the Template File
     * @return void
     */
    public static function display(string $template, ?array $variables = null): void {
        $file = self::$config["TEMPLATE_BASE_DIR"] . (str_ends_with(self::$config["TEMPLATE_BASE_DIR"], "/") ? "" : "/") . $template;
        if(!(file_exists($file))) {
            error_log("Could not find Template File \"{$file}\".");
            return;
        }

        if(isset($variables) && $variables != null) {
            extract($variables);
        }

        include($file);
    }

    /**
     * Include a PHP Template File within another Template File
     * @param string $template Name of Template File within the frontend/includes Directory
     * @param array|null $variables Variables that should be available within the Template File
     * @return void
     */
    public static function include(string $template, ?array $variables = null): void {
        $file = self::$config["TEMPLATE_BASE_DIR"] . (str_ends_with(self::$config["TEMPLATE_BASE_DIR"], "/") ? "" : "/") . "includes/" . $template;
        if(!(file_exists($file))) {
            error_log("Could not find Template File \"{$file}\".");
            return;
        }

        if(isset($variables) && $variables != null) {
            extract($variables);
        }

        include($file);
    }

    /**
     * Fetch the HTML Code of a PHP Template File that contains the frontend Code
     * @param string $template
     * @param array|null $variables
     * @return string
     */
    public static function fetch(string $template, ?array $variables = null): string {
        ob_start();
        self::display($template, $variables);
        return ob_get_clean();
    }

    /**
     * Parses a Templify Template File to a generic PHP Template File, saves the Content to the Cache and returns the Path to the Cache File
     * @param string $file
     * @return string
     */
    private static function parseTemplateFile(string $file): string {
        if(!(file_exists($file))) {
            error_log("Could not find Component File \"{$file}\".");
            return "";
        }

        $templateContents = file_get_contents($file);

        $templifyParser = new TemplifyParser($templateContents);
        $parsedFileContents = $templifyParser->parse();

        // TODO: Generate cache key
        // TODO: Save file to cache

        return "";
    }
}
