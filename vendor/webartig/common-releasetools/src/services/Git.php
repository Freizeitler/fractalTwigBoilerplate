<?php

namespace webartig\common\releasetools\services;

use webartig\common\releasetools\helper\ComposerIO;

class Git
{

    public static function isWDClean()
    {
        return shell_exec("git status --porcelain") == null;
    }

    /**
     * Create a new branch `release-CURRENT_VERSION`
     * @param string $version
     * @return bool
     */
    public static function createReleaseBranch($version)
    {
        $releaseBranchName = self::releaseBranchName($version);
        if (self::tagExists($releaseBranchName)) {
            Logger::error("A release '$releaseBranchName' was already published!");
            return false;
        }

        return self::createBranch($releaseBranchName);
    }

    /**
     * @param string $version
     * @return string release branch name
     */
    public static function releaseBranchName($version)
    {
        return "release-$version";
    }

    /**
     * @param string $name tag
     * @param bool $checkRemote
     * @return bool tag exists
     */
    public static function tagExists($name, $checkRemote = false)
    {
        return shell_exec("git show-ref --tags | grep refs/tags/$name" . ($checkRemote ? " git ls-remote --tags origin | grep refs/tags/$name" : "")) != null;
    }

    /**
     * Create a new git branch with the name $name
     *
     * @param $name
     * @return bool
     */
    static public function createBranch($name)
    {
        if (self::branchExist($name)) {
            Logger::warn("Branch exist already!");
            return Git::checkout($name);
        }

        if (($exists = (shell_exec("git branch | grep $name")) == null))
            return shell_exec("git checkout -q -b $name --track") == null;

        return false;
    }

    /**
     * @param string $name branch name
     * @return bool does branch exist
     */
    public static function branchExist($name)
    {
        return shell_exec("git show-ref refs/heads/$name") != null;
    }

    /**
     * @param string $name branch name
     * @return bool
     */
    public static function checkout($name)
    {
        return shell_exec("git checkout -q $name") == null;
    }

    /**
     * Create a new branch `hotfix-VERSION`
     * @param string $version
     * @return bool
     */
    public static function createHotfixBranch($version)
    {
        $hotfixBranchName = self::hotfixBranchName($version);
        if (self::tagExists($hotfixBranchName)) {
            Logger::error("A hotfix '$hotfixBranchName' was already published!");
            return false;
        }

        return self::createBranch($hotfixBranchName);
    }

    /**
     * @param string $version
     * @return string hotfix branch name
     */
    public static function hotfixBranchName($version)
    {
        return "hotfix-$version";
    }

    /**
     * Commit all changes to git
     */
    static public function commit()
    {
        $version = ComposerIO::readVersion();
        $msg = "Checking in changes for release $version";
        return shell_exec("git commit -a -q -m '$msg'") == null;
    }

    /**
     * Push all commits to default remote (origin)
     * @param bool $tags Push tags to git
     * @return bool
     */
    static public function push($tags = false)
    {
        if (self::remotesExist()) {
            shell_exec("git push -q -u origin " . self::currentBranch() . ($tags ? " --tags" : ""));
            return true; //workaround because of Atlassian :( https://bitbucket.org/site/master/issues/11409/revert-modify-create-pull-request-message
        } else
            Logger::error("No remote exist!");

        return false;
    }

    /**
     * @return bool does remotes exist
     */
    public static function remotesExist()
    {
        return shell_exec("git remote -v") != null;
    }

    /**
     * @return string current git branch
     */
    static public function currentBranch()
    {
        return trim(shell_exec("git rev-parse --abbrev-ref HEAD"));
    }

    /**
     * Pull all commits from default remote (origin)
     * @return bool
     */
    static public function pull()
    {
        if (self::remotesExist()) {
            shell_exec("git pull -q origin " . self::currentBranch());
            return true; //workaround because of Atlassian :( https://bitbucket.org/site/master/issues/11409/revert-modify-create-pull-request-message
        } else
            Logger::error("No remote exist!");

        return false;
    }

    /**
     * @param string $source branch source
     * @return bool|string
     */
    public static function merge($source)
    {
        $head = self::currentBranch();
        $return = shell_exec("git merge -m \"Merge $source into $head\" -q --no-ff $source");

        if ($return != null) {
            return $return;
        }

        return true;
    }

    /**
     * @param string $name branch name
     * @param string $branch
     * @return bool is merged
     */
    public static function isMerged($name, $branch = 'master')
    {
        return shell_exec("git branch --merged $branch | grep $name") != null;
    }

    /**
     * @param string $name tag name
     * @param string $msg tag message
     */
    public static function tag($name, $msg)
    {
        shell_exec("git tag -s $name -m '$msg'");
    }

    /**
     * @return bool tag exists
     */
    public static function latestTagVersion()
    {
        return shell_exec("git tag --sort version:refname | tail -1");
    }

    /**
     * @param string $name branch name
     * @return bool
     */
    public static function removeBranch($name)
    {
        return shell_exec("git branch -q -d $name") == null;
    }

    /**
     * @param string $name branch name
     * @return bool
     */
    public static function removeRemoteBranch($name)
    {
        return shell_exec("git push origin -q --delete $name") == null;
    }

    /**
     * @return string current release branch name
     */
    static public function currentReleaseBranchName()
    {
        return self::releaseBranchName(ComposerIO::readVersion());
    }

    /**
     * @return string current hotfix branch name
     */
    static public function currentHotfixBranchName()
    {
        return self::hotfixBranchName(ComposerIO::readVersion());
    }

    /**
     * @return bool is branch a release branch
     */
    static public function currentBranchIsReleaseBranch()
    {
        return (preg_match('/^(release\-[0-9]\.[0-9]\.[0-9])((-(dev|alpha|beta|rc\d*))?)/', self::currentBranch()) > 0);
    }

    /**
     * @return bool is branch a hotfix branch
     */
    static public function currentBranchIsHotfixBranch()
    {
        return (preg_match('/^hotfix\-[A-Za-z0-9]*/', self::currentBranch()) > 0);
    }
}