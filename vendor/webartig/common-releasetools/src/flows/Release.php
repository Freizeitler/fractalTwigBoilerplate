<?php

namespace webartig\common\releasetools\flows;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;
use webartig\common\releasetools\helper\ComposerIO;
use webartig\common\releasetools\services\Git;
use webartig\common\releasetools\services\Logger;
use webartig\common\releasetools\services\SemVer;

class Release
{
    /**
     * Start a new release by executing this flow.
     *
     * - Bump current patch version by one, or major/minor version.
     * - Create a new branch with the new version
     * - Commit all changes
     * - Push this branch to the default remote (origin)
     *
     * @param Event $event
     */
    public static function execute(Event $event)
    {
        if (!Git::isWDClean()) {
            Logger::error("You have uncommitted changes in your working directory. Please start with a fully committed branch.");
            return;
        }

        $args = $event->getArguments();

        $incLevel = self::getLevel($args);
        $skipVersionBump = self::getSkipVersionBump($args);
        $isInit = self::getIsInit($args);
        $releaseVersion = !$skipVersionBump && !$isInit ? SemVer::bumpVersion($incLevel, true) : ComposerIO::readVersion();

        $noVersionInfo = false;
        if ($releaseVersion == null) {
            $releaseVersion = "0.1.0";
            $noVersionInfo = true;
        }

        $nextReleaseBranch = Git::releaseBranchName($releaseVersion);

        $newRelease = false;

        //Still on a release branch
        if (Git::currentBranchIsReleaseBranch()) {
            $releaseVersion = ComposerIO::readVersion();
            if (!SemVer::validate($releaseVersion)) {
                Logger::warn("Is this a new project? If so please use --init");
                Logger::error("Your current version does not use correct semVer syntax or is missing.");
                return;
            }

            $nextReleaseBranch = Git::releaseBranchName($releaseVersion);
            Logger::info("Your are still preparing " . Git::currentReleaseBranchName());

            if ($incLevel != null)
                Logger::warn("It seems your are trying to change the version level $incLevel. But since you already are on a release branch this is ignored");

        } //Start a new release branch
        else if (Git::currentBranch() != $nextReleaseBranch) {
            if (!SemVer::validate($releaseVersion) && !$isInit) {

                if ($noVersionInfo)
                    Logger::warn('Is this a new project? There is so release information so far.');

                Logger::error('If this is your first release please rerun this command with flag --init');
                return;
            }

            if (!Git::createReleaseBranch($releaseVersion))
                return;

            if ($isInit) {
                Logger::info("This is a new project. Your version will be $releaseVersion");
                ComposerIO::writeVersion($releaseVersion);
                Git::commit();
            } else if (!$skipVersionBump) {
                SemVer::bumpVersion($incLevel);
                Git::commit();
                Logger::info("Bump version to $releaseVersion [$incLevel]");
            }
            $newRelease = true;
        }

        if (Git::currentBranchIsReleaseBranch() && Git::push())
            Logger::success("Successfully " . ($newRelease ? 'started' : 'enhanced') . " $nextReleaseBranch");
    }

    /**
     * @param $args
     * @return int|string SemVer bump-level
     */
    protected static function getLevel($args)
    {
        $level = null;
        if (($keys = preg_grep('/--level=/', $args)) !== false) {
            if (count($keys) > 0) {
                reset($keys);
                $level = $args[key($keys)];
                if (($start = strpos($level, '=')) !== false)
                    $level = substr($level, $start + 1);
            }
        }

        if (!in_array($level, [SemVer::LEVEL_MAJOR, SemVer::LEVEL_MINOR, SemVer::LEVEL_PATCH]))
            $level = SemVer::LEVEL_PATCH;

        return $level;
    }

    /**
     * @param $args
     * @return bool
     */
    protected static function getSkipVersionBump($args)
    {
        return (count(preg_grep('/--skip-version-bump/', $args)) == 1);
    }

    /**
     * @param $args
     * @return bool
     */
    protected static function getIsInit($args)
    {
        return (count(preg_grep('/--init/', $args)) == 1);
    }
}