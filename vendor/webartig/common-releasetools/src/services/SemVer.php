<?php

namespace webartig\common\releasetools\services;

use vierbergenlars\SemVer\version;
use webartig\common\releasetools\helper\ComposerIO;

class SemVer
{
    const LEVEL_MAJOR = 'major';
    const LEVEL_MINOR = 'minor';
    const LEVEL_PATCH = 'patch';

    /**
     * @param string $version
     * @return bool is version upgrade
     */
    public static function validate($version)
    {
        return self::checkSemVer($version) && self::checkIsGreatThanLatestTag($version);
    }


    protected static function checkSemVer($version)
    {
        $semVer = new Version($version);
        return $semVer->valid();
    }
    /**
     * @param string $version
     * @return bool
     */
    protected static function checkIsGreatThanLatestTag($version)
    {
        $latestTagVersion = Git::latestTagVersion();
        if ($latestTagVersion == null)
            return false;

        $validation = version::gt($version, $latestTagVersion);

        if (!$validation)
            Logger::error("Your release version $version is smaller or equals latest released version with tag $latestTagVersion. Only upgrade are supported");

        return $validation;
    }

    /**
     * @param string $level
     * @param bool $dry without writing to composer.json
     * @return string version
     */
    public static function bumpVersion($level, $dry = false)
    {
        if ($level == null)
            $level = SemVer::LEVEL_PATCH;

        $newVersionString = self::increase(ComposerIO::readVersion(), $level);

        if ($dry === false)
            return ComposerIO::writeVersion($newVersionString);

        return $newVersionString;
    }

    /**
     * @param string $version
     * @param int $level semVer
     * @return string version
     * @throws \Exception
     */
    protected static function increase($version, $level)
    {
        if (!self::checkSemVer($version))
            throw new \Exception('Current composer-version is no valid SemVer format');

        if (!in_array($level, ['major', 'minor', 'patch']))
            throw new \InvalidArgumentException();

        $currentSemVer = new version($version);
        $semVer = $currentSemVer->inc($level);
        return $semVer->getVersion();

    }

}
