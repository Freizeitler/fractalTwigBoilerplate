webArtig Common Release Tools
==========

Automating common release processes. 

Features
---------

- Automatically version bumping for Composer
- Version checks
- Git tagging, committing, pushing
- Simplest flows: release, hotfix and publish

Install
-------

First add this private repository to your composer.json 

```
…
"repositories": [
    {
      "type": "git",
      "url":  "git@bitbucket.org:webartig/common-releasetools.git"
    }
]
…
```

then require it via composer

```
$ composer require --dev webartig/releasetools 
```

### Usage and how to including flows in your project

Simply add your desired flow as a composer script ([more about composer scripts](https://getcomposer.org/doc/articles/scripts.md)):

        "scripts": {
            "release": "webartig\\common\\releasetools\\flows\\Release::execute",
            "hotfix": "webartig\\common\\releasetools\\flows\\Hotfix::execute",
            "publish": "webartig\\common\\releasetools\\flows\\Publish::execute"
         }   

or implement your own flow by using one of the many release services. 


Usage
-------

After setting up your composer.json you can use your cli like
 
```
composer release
```

or with arguments

```
composer release -- --level=major
```

### Release (composer release -- [--init] [--level=major|minor|patch] [--skip-version-bump])

Start a new release branch and push it to remote. Paves the way for a new release of the project.
This command is later - when the release is ready followed by `publish`

#### Options

##### --init

Argument: `void`

Mark as initial release. Otherwise the first release will error because of negative validation. Default 0.1.0 or your current composer version.

##### --level (release)

Argument: `string`<br>
Default: `hotfix`

- major
- minor
- hotfix

Bump current version by level

##### --skip-version-bump (release)

Argument: `void`

Skip version bumping 

### Hotfix (composer hotfix)

Start a new hotfix branch and push it to remote. Paves the way for a new hotfix release.
This command is later - when the hotfix is ready - followed by `publish`

### Publish (composer publish -- [--init])

When your branch is ready to be release you can run the command `publish`. The current release/hotfix branch is merged into master, tagged, signed and push to remote. Afterwards it is gemerged into the develop branch.

#### Options

##### --init

Argument: `void`

Mark as initial release. Otherwise the first release will error because of negative validation.

Contributing
-------

1. Clone/Fork
2. Add your improvment
3. Create a Pull Request

Ressources
-------

This module is heavily inspired by

- https://github.com/c9s/PHPRelease
- https://github.com/nvie/gitflow