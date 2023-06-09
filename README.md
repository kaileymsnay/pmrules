# PM Rules

Allows administrators to set a limit on the number of posts a user must have before allowing PMs. Users may still PM team members.

## Installation

1. Download the extension
2. Copy the whole archive content to /ext/kaileymsnay/pmrules
3. Go to your phpBB board > Administration Control Panel > Customise > Manage extensions > PM Rules: enable

## Update instructions

1. Go to your phpBB board > Administration Control Panel > Customise > Manage extensions > PM Rules: disable
2. Delete all files of the extension from /ext/kaileymsnay/pmrules
3. Upload all the new files to the same locations
4. Go to your phpBB board > Administration Control Panel > Customise > Manage extensions > PM Rules: enable
5. Purge the board cache

## Automated testing

We use automated unit tests to prevent regressions. Check out our build below:

master: [![Build Status](https://github.com/kaileymsnay/pmrules/workflows/Tests/badge.svg)](https://github.com/kaileymsnay/pmrules/actions)

## License

[GNU General Public License v2](license.txt)
