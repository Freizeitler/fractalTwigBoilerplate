<?php

namespace webartig\common\releasetools\helper;

class ComposerIO
{
    /**
     * @return string|null
     */
    public static function readVersion()
    {
        if (file_exists("composer.json")) {
            $composer = json_decode(file_get_contents("composer.json"), true);
            if (isset($composer['version'])) {
                return $composer['version'];
            }
        }

        return null;
    }

    /**
     * @param string $newVersion
     * @return int|null
     */
    public static function writeVersion($newVersion)
    {
        if (file_exists("composer.json")) {
            $composer = json_decode(file_get_contents("composer.json"), true);
            $composer['version'] = $newVersion;
            return file_put_contents("composer.json", json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        return null;
    }

}