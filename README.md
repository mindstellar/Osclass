This fork just removes all Osclass Market connections, including auto-upgrade of the script as well as plugins, themes and languages update functions.

# Status report

- Fully successful script installation (removed location insert, instead showing message to download and import manually; removed "connect with Market").
- No errors when opening admin panel (removed Market from menus and header, removed upgrade from menus).
- No errors when importing, installing and enabling a plugin (tested with Job Attributes).
- No errors when importing, installing and enabling a theme (tested with Bender Red).
- No errors when importing, installing and enabling a language (tested with Czech).
- No errors when importing, installing and enabling a location (tested with Croatia) - location page should point to tools -> import (not sure yet).
- No errors when an automatic or manual cron is triggered (removed auto-upgrade).

# Osclass

**This repo is a fork of official [Osclass][original-code] repository.**
## Why this new fork?
Transform osclass to adapt new features, get rid of deprecated code. And set the road for new goals.

## What is Osclass?
[Osclass] is a free
and open script to create your advertisement or listings site. Best features: Plugins,
themes, multi-language, CAPTCHA, dashboard, SEO friendly.

[Preview of Osclass][demo]

## Develop

Clone the repository and the submodules.

```
$> git clone --recursive git@github.com:navjottomer/Osclass.git
```

## Project info

* [Official website][osclass]
* ~~Plugins and themes~~
* [Official forums][forums]
* [Wiki & documentation][wiki]
* License: [Apache License V2.0][license]


## Installation

Go to [our site][installing] to get detailed information on installing Osclass.

[original-code]: https://github.com/osclass/Osclass
[osclass]: https://osclass.org/
[preview]: https://osclass.org/wp-content/uploads/2011/01/single_job_board-1024x729.png
[code]: https://github.com/navjottomer/Osclass
[market]: https://market.osclass.org/
[demo]: https://osclass.org/page/demo
[forums]: http://forums.osclass.org/
[wiki]: https://doc.osclass.org/Main_Page
[license]: http://www.apache.org/licenses/LICENSE-2.0
[installing]: https://osclass.org/installing-osclass/
