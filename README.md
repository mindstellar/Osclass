[![CodeFactor](https://www.codefactor.io/repository/github/navjottomer/osclass/badge)](https://www.codefactor.io/repository/github/navjottomer/osclass)
[![Build Status](https://travis-ci.com/navjottomer/Osclass.svg?branch=master)](https://travis-ci.com/navjottomer/Osclass)
# Osclass

**This repo is a fork of official [Osclass][original-code] repository.**
## Why this new fork?
Since Osclass project was effectively shut down in September 2019, this project's goal is to continue its development, to adapt new features, get rid of deprecated code and set road for new goals.

## What is Osclass?
Osclass is a free and open script to create your advertisement or listings site. Best features: Plugins,
themes, multi-language, CAPTCHA, dashboard, SEO friendly.

## Support
For any support related query, please visit our official support forum.

* [Osclass Discourse][support-forum]

## Develop

Clone the repository and the submodules.

```
$> git clone --recursive git@github.com:navjottomer/Osclass.git
```

In case you don't have a Database running in your laptop, you can use the following to start a mysql locally via docker:
```
$> docker-compose up -d
```

To run a basic web server locally (disconsider if you already use MAMP, XAMP, or other web servers)
In the project root, run:
```
$> php -S localhost:8000 -t .
```
Now just open your browser http://localhost:8000

## Pull Request
Want to help create a pull request from you clone, just make sure of few things

* Never target master-branch
* Target develop branch if you wan't to merge your fixes.
* Request a feature branch if your pull request change the functionality of our project.
* Create a new issue before making any pull request.  

## Project info

* Documentation: [Documentation][documentation]
* License: [Apache License V2.0][license]


## Installation
* Visit our documentation : https://osclass.gitbook.io/osclass-docs/beginners/install

[documentation]: https://osclass.gitbook.io/osclass-docs/
[support-forum]: https://osclass.discourse.group
[original-code]: https://github.com/osclass/Osclass
[code]: https://github.com/navjottomer/Osclass
[license]: http://www.apache.org/licenses/LICENSE-2.0
