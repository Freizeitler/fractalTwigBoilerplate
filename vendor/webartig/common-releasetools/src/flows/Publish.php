<?php

namespace webartig\common\releasetools\flows;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;
use webartig\common\releasetools\helper\ComposerIO;
use webartig\common\releasetools\services\Git;
use webartig\common\releasetools\services\Logger;
use webartig\common\releasetools\services\SemVer;

class Publish
{
    /**
     * Publish a existing release by executing this flow.
     *
     * - Merge release into master
     * - Tag with release version
     * - Push master with tags
     * - Merge release back into develop
     * - Remove release
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
        $currentVersion = ComposerIO::readVersion();
        $resumeFromMasterMerge = Git::currentBranch() == "master" && (Git::branchExist(Git::releaseBranchName($currentVersion)) || Git::branchExist(Git::hotfixBranchName($currentVersion)));
        $isInit = self::getIsInit($args);

        if (!Git::currentBranchIsReleaseBranch() && !Git::currentBranchIsHotfixBranch()) {
            Logger::error("Your are not on a release or hotfix branch. Switch your branch or start a new release/hotfix");
            return;
        }

        $currentBranch = $resumeFromMasterMerge ? Git::releaseBranchName($currentVersion) : Git::currentBranch();
        $currentVersionPlain = str_replace(['v', 'V'], "", $currentVersion);

        if (!SemVer::validate($currentVersion) && !$isInit) {
            Logger::warn("Is this a new project? If so please use --init");
            Logger::error("Your current version does not use correct semVer syntax or is missing.");
            return;
        }

        if ($resumeFromMasterMerge)
            Logger::info("Resume publishing after merging");
        else {
            Logger::info("Start publishing " . $currentBranch);
            if (!Git::checkout('master')) {
                if ($isInit) {
                    Git::createBranch('master');
                } else
                    Logger::error("Failed checking out to master branch.");
            }

            if (!Git::pull() && !$isInit) {
                Logger::warn("Pull from remote branch (origin master) failed! Please make sure your master branch has read rights to your remote branch.");
                return;
            }

            $masterMerge = Git::merge($currentBranch);

            if ($masterMerge !== true) {
                Logger::warn("Publishing failed! There are just some conflicts, so don't worry. Resolve the conflicts, checkout out your release/hotfix and start the publish flow again! ");
                Logger::info($masterMerge);
                return;
            }
        }

        if (Git::isMerged($currentBranch) || $resumeFromMasterMerge) {
            Git::tag("v$currentVersion", "Version $currentVersionPlain");
            Git::push(true);
            Logger::info("… merged $currentBranch into master, tagged with version $currentVersion and pushed to origin");
            Logger::info("");
            Logger::success("RELEASED $currentVersion");
            Logger::info("");

            Logger::info("Start cleaning");

            if (!Git::isMerged($currentBranch, 'develop')) {
                Git::checkout('develop');
                Git::merge($currentBranch);
                Git::push();
            }

            if (!Git::isMerged($currentBranch, 'develop'))
                Logger::warn("$currentBranch is not fully merged into develop. Aborting removing of branch. Please merge release branch manually and remove the release branch afterwards.");

            else {
                Logger::info("… merged $currentBranch into develop and pushed to origin");
                if (Git::removeBranch($currentBranch) && Git::removeRemoteBranch($currentBranch))
                    Logger::info("… removed local and origin branch $currentBranch");
                Logger::info("");
            }

            Logger::success("Successfully finished release of $currentVersion");
        }

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