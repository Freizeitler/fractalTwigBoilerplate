<?php

namespace webartig\common\releasetools\flows;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;
use webartig\common\releasetools\services\Git;
use webartig\common\releasetools\services\Logger;
use webartig\common\releasetools\services\SemVer;

class Hotfix
{
    /**
     * Start a new hotfix by executing this flow.
     *
     * - Bump current patch version by one.
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

        $hotfixVersion = SemVer::bumpVersion(SemVer::LEVEL_PATCH, true);
        $newHotfixBranch = Git::hotfixBranchName($hotfixVersion);
        $newHotfix = false;

        //Still on a hotfixbranch
        if (Git::currentBranchIsHotfixBranch()) {
            $hotfixVersion = ComposerIO::readVersion();

            if (!self::validateSemVer($hotfixVersion))
                return;

            $newHotfixBranch = Git::releaseBranchName($hotfixVersion);
            Logger::info("Your are still preparing " . Git::currentHotfixBranchName());

        } //Start a new hotfix branch
        else if (Git::currentBranch() != $newHotfixBranch) {
            if (!self::validateSemVer($hotfixVersion))
                return;

            if (!Git::createHotfixBranch($hotfixVersion))
                return;

            $newHotfix = true;
            SemVer::bumpVersion(SemVer::LEVEL_PATCH);
            Git::commit();
            Logger::info("Bump version to $hotfixVersion [" . SemVer::LEVEL_PATCH . "]");
        }


        if (Git::currentBranchIsHotfixBranch() && Git::push())
            Logger::success("Successfully " . ($newHotfix ? 'started' : 'enhanced') . " $newHotfixBranch");
    }

    /**
     * @param string $hotfixVersion
     * @return bool
     */
    protected
    static function validateSemVer($hotfixVersion)
    {
        if (SemVer::validate($hotfixVersion))
            return true;

        Logger::warn("Is this a new project? If so please use the release flow with --init to start with a inital release");
        Logger::error("Your current version does not use correct semVer syntax or is missing.");
        return false;
    }
}