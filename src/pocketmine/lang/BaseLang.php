<?php
/**
 * src/pocketmine/lang/BaseLang.php
 *
 * @package default
 */


/*
 *
 *  _                       _           _ __  __ _
 * (_)                     (_)         | |  \/  (_)
 *  _ _ __ ___   __ _  __ _ _  ___ __ _| | \  / |_ _ __   ___
 * | | '_ ` _ \ / _` |/ _` | |/ __/ _` | | |\/| | | '_ \ / _ \
 * | | | | | | | (_| | (_| | | (_| (_| | | |  | | | | | |  __/
 * |_|_| |_| |_|\__,_|\__, |_|\___\__,_|_|_|  |_|_|_| |_|\___|
 *                     __/ |
 *                    |___/
 *
 * This program is a third party build by ImagicalMine.
 *
 * PocketMine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ImagicalMine Team
 * @link http://forums.imagicalcorp.ml/
 *
 *
*/

namespace pocketmine\lang;

use pocketmine\event\TextContainer;
use pocketmine\event\TranslationContainer;

class BaseLang
{

    const FALLBACK_LANGUAGE = "eng";

    protected $langName;

    protected $lang = [];
    protected $fallbackLang = [];

    /**
     *
     * @param unknown $lang
     * @param unknown $path     (optional)
     * @param unknown $fallback (optional)
     */
    public function __construct($lang, $path = null, $fallback = self::FALLBACK_LANGUAGE)
    {
        $this->langName = strtolower($lang);

        if ($path === null) {
            $path = \pocketmine\PATH . "src/pocketmine/lang/locale/";
        }

        $this->loadLang($path . $this->langName . ".ini", $this->lang);
        $this->loadLang($path . $fallback . ".ini", $this->fallbackLang);
    }


    /**
     *
     * @return unknown
     */
    public function getName()
    {
        return $this->get("language.name");
    }


    /**
     *
     * @return unknown
     */
    public function getLang()
    {
        return $this->langName;
    }


    /**
     *
     * @param unknown $path
     * @param array   $d    (reference)
     */
    protected function loadLang($path, array &$d)
    {
        if (file_exists($path) and strlen($content = file_get_contents($path)) > 0) {
            foreach (explode("\n", $content) as $line) {
                $line = trim($line);
                if ($line === "" or $line{0} === "#") {
                    continue;
                }

                $t = explode("=", $line, 2);
                if (count($t) < 2) {
                    continue;
                }

                $key = trim($t[0]);
                $value = trim($t[1]);

                if ($value === "") {
                    continue;
                }

                $d[$key] = $value;
            }
        }
    }


    /**
     *
     * @param string   $str
     * @param string[] $params
     * @param unknown  $onlyPrefix (optional)
     * @return string
     */
    public function translateString($str, array $params = [], $onlyPrefix = null)
    {
        $baseText = $this->get($str);
        $baseText = $this->parseTranslation(($baseText !== null and ($onlyPrefix === null or strpos($str, $onlyPrefix) === 0)) ? $baseText : $str, $onlyPrefix);

        foreach ($params as $i => $p) {
            $baseText = str_replace("{%$i}", $this->parseTranslation((string) $p), $baseText, $onlyPrefix);
        }

        return $baseText;
    }


    /**
     *
     * @param TextContainer $c
     * @return unknown
     */
    public function translate(TextContainer $c)
    {
        if ($c instanceof TranslationContainer) {
            $baseText = $this->internalGet($c->getText());
            $baseText = $this->parseTranslation($baseText !== null ? $baseText : $c->getText());

            foreach ($c->getParameters() as $i => $p) {
                $baseText = str_replace("{%$i}", $this->parseTranslation($p), $baseText);
            }
        } else {
            $baseText = $this->parseTranslation($c->getText());
        }

        return $baseText;
    }


    /**
     *
     * @param unknown $id
     * @return unknown
     */
    public function internalGet($id)
    {
        if (isset($this->lang[$id])) {
            return $this->lang[$id];
        } elseif (isset($this->fallbackLang[$id])) {
            return $this->fallbackLang[$id];
        }

        return null;
    }


    /**
     *
     * @param unknown $id
     * @return unknown
     */
    public function get($id)
    {
        if (isset($this->lang[$id])) {
            return $this->lang[$id];
        } elseif (isset($this->fallbackLang[$id])) {
            return $this->fallbackLang[$id];
        }

        return $id;
    }


    /**
     *
     * @param unknown $text
     * @param unknown $onlyPrefix (optional)
     * @return unknown
     */
    protected function parseTranslation($text, $onlyPrefix = null)
    {
        $newString = "";

        $replaceString = null;

        $len = strlen($text);
        for ($i = 0; $i < $len; ++$i) {
            $c = $text{$i};
            if ($replaceString !== null) {
                if ((ord($c) >= 0x30 and ord($c) <= 0x39) or (ord($c) >= 0x41 and ord($c) <= 0x5a) or (ord($c) >= 0x61 and ord($c) <= 0x7a) or $c === ".") {
                    $replaceString .= $c;
                } else {
                    if (($t = $this->internalGet(substr($replaceString, 1))) !== null and ($onlyPrefix === null or strpos($replaceString, $onlyPrefix) === 1)) {
                        $newString .= $t;
                    } else {
                        $newString .= $replaceString;
                    }
                    $replaceString = null;

                    if ($c === "%") {
                        $replaceString = $c;
                    } else {
                        $newString .= $c;
                    }
                }
            } elseif ($c === "%") {
                $replaceString = $c;
            } else {
                $newString .= $c;
            }
        }

        if ($replaceString !== null) {
            if (($t = $this->internalGet(substr($replaceString, 1))) !== null and ($onlyPrefix === null or strpos($replaceString, $onlyPrefix) === 1)) {
                $newString .= $t;
            } else {
                $newString .= $replaceString;
            }
        }

        return $newString;
    }
}
