<?php

/*
 * The MIT License
 *
 * Copyright 2016 Alejandro Peña Florentín (alejandropenaflorentin@gmail.com).
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Tight\Modules\Localize;

/**
 * Localize module for translations
 *
 * @author Alejandro Peña Florentín (alejandropenaflorentin@gmail.com)
 */
class Localize extends \Tight\Modules\AbstractModule
{

    /**
     * @var \Tight\Modules\Localize\LocalizeConfig Class configuration
     */
    private $config;

    /**
     * @var string Resource file type
     */
    private $resourceFileType;

    /**
     * @var string Current locale
     */
    private $locale;

    /**
     * @var array Associative array for the current locale
     */
    private $values;

    /**
     * Constructor
     * 
     * @param array|\Tight\Modules\Localize\LocalizeConfig $config
     * @throws \InvalidArgumentException If $config is not an array or an object
     * of \Tight\Modules\Localize\LocalizeConfig class
     */
    public function __construct($config = []) {
        if (is_array($config)) {
            $config = new \Tight\Modules\Localize\LocalizeConfig($config);
        } else if (!$config instanceof \Tight\Modules\Localize\LocalizeConfig) {
            throw new \InvalidArgumentException("Argument 1 passed to " . get_class($this) . " must be an array or an instance of Tight\Modules\Localize\LocalizeConfig");
        }
        parent::__construct();
        $this->setConfig($config);
        $this->setResourceFileType($this->config->resourceFileType);
        $this->checkDependences();
        $this->setLocale($this->config->defaultLocale);
    }

    /**
     * Sets the resource file type
     * @param string $resourceFileType Resource file type
     * @return \Tight\Modules\Localize\Localize Fluent setter
     */
    public function setResourceFileType($resourceFileType) {
        $this->resourceFileType = $resourceFileType;
        return $this;
    }

    /**
     * Gets all the defined values for the current locale
     * @return array Associative array of values
     */
    public function getValues() {
        return $this->values;
    }

    /**
     * Reloads the class with the new locale
     */
    public function reloadConfig() {
        $this->locale = $this->config->defaultLocale;
    }

    /**
     * Checks the dependences for the class
     * @throws \Tight\Modules\ModuleException If resource directory cant be 
     * found
     */
    private function checkDependences() {
        if (!is_dir($this->config->resourceFolder)) {
            throw new \Tight\Modules\ModuleException("Resource directory not found");
        }
    }

    /**
     * Sets the class configuration
     * @param \Tight\Modules\Localize\LocalizeConfig $config Class configuration
     */
    public function setConfig(LocalizeConfig $config) {
        $this->config = $config;
        $this->reloadConfig();
    }

    /**
     * Sets a new locale
     * @param string $locale New defined locale
     * @throws \Tight\Modules\ModuleException If resource default resource file
     * cant be found
     */
    public function setLocale($locale) {
        $this->locale = $locale;
        $folder = \Tight\Utils::addTrailingSlash($this->config->resourceFolder);
        $fileName = $this->config->resourceFileName . $this->config->langSeparator . $locale . "." . $this->config->resourceFileType;
        $file = $folder . $fileName;
        if (is_file($file)) {
            $this->values = json_decode(file_get_contents($file), JSON_FORCE_OBJECT);
        } else {
            $fileName = $this->config->resourceFileName . "." . $this->config->resourceFileType;
            $file = $folder . $fileName;
            if (is_file($file)) {
                $this->values = json_decode(file_get_contents($file), JSON_FORCE_OBJECT);
            } else {
                throw new \Tight\Modules\ModuleException("Resource file <strong>" . $file . "</strong> not found");
            }
        }
    }

    /**
     * Gets the module configuration
     * @return \Tight\Modules\Localize\LocalizeConfig Class configuration
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Gets the available locales
     * @return array Available locales
     */
    public function getLocales() {
        $output = [];
        $directory = $this->config->resourceFolder;
        $fileName = $this->config->resourceFileName;
        $dir = opendir($directory);
        $files = [];
        while ($entry = readdir($dir)) {
            if (strpos($entry, $fileName) !== false) {
                $files[] = $entry;
            }
        }
        foreach ($files as $element) {
            $file = \Tight\Utils::getSlicedFile($directory . $element);
            //Removes extension
            $name = $file["name"];
            $explode = explode($this->config->langSeparator, $name);
            // Get the locale of the defined file type
            if ($file["ext"] == $this->config->resourceFileType) {
                if (count($explode) > 1) {
                    $output[] = $explode[count($explode) - 1];
                } else {
                    $output[] = $this->config->defaultLocale;
                }
            }
        }
        return $output;
    }

    /**
     * Gets a value from a defined key
     * @param string $key Key
     * @return string Value defined for the key $key or an empty string if the
     * key is not defined
     */
    public function get($key) {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        } else {
            return "";
        }
    }

}