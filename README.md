This fork just removes all Osclass Market connections, including auto-upgrade of the script as well as plugins, themes and languages update functions.

## Status report

- Fully successful script installation (removed location insert, instead showing message to download and import manually; removed "connect with Market").
- No errors when opening admin panel (removed Market from menus and header, removed upgrade from menus).
- No errors when importing, installing and enabling a plugin (tested with Job Attributes).
- No errors when importing, installing and enabling a theme (tested with Bender Red).
- No errors when importing, installing and enabling a language (tested with Czech).
- No errors when importing, installing and enabling a location (tested with Croatia) - location page should point to tools -> import (not sure yet).
- No errors when an automatic or manual cron is triggered (removed auto-upgrade).

## Forked from 
https://github.com/navjottomer/Osclass/
