# [KwaMoja - Accounting and Business Administration ERP System](http://https://www.kwamoja.org/)
[![Download Zen Cart&reg;](https://img.shields.io/sourceforge/dm/KwaMoja.svg)](https://github.com/timschofield/KwaMoja/archive/release_20.03.zip) [![Download Zen Cart&reg;](https://img.shields.io/sourceforge/dt/KwaMoja.svg)](https://github.com/timschofield/KwaMoja/archive/release_20.03.zip) ![GitHub last commit (branch)](https://img.shields.io/github/last-commit/KwaMoja-team/KwaMoja/master.svg) ![GitHub pull requests](https://img.shields.io/github/issues-pr-raw/KwaMoja-team/KwaMoja.svg)
## Introduction
KwaMoja is a free open-source ERP system, providing best practise, multi-user business administration and accounting tools over the web. For further information and for a full list of features, please visit the website at: https://www.kwamoja.org/Features.php

## Demo
A live demo of the latest release is available on the KwaMoja support site, where you can login and experiment with all the KwaMoja features: https://demo.kwamoja.org/

## Download
The latest stable version is currently 20.03, and can be downloaded from GitHub.
[![Download Latest Official Release](https://github.com/timschofield/KwaMoja/archive/release_20.03.zip)
Download the latest official KwaMoja release](https://github.com/timschofield/KwaMoja/archive/release_20.03.zip)

## Requirements
- A web server - KwaMoja has been tested on Apache, NGINX, lighthttpd, and Hiawatha
- PHP version 5.1 and above
- MySQL version 4.3 and above, or MariaDB version 5.1 and above
- A web browser with HTML5 compatibility

Further information about hardware and software requirements is available in the [documentation](https://www.kwamoja.org/ManualContents.php?PageName=Manual%2FManualRequirements.html).

## Installation
### New installation
1. [Download the latest official KwaMoja release.](https://github.com/timschofield/KwaMoja/archive/release_20.03.zip)
2. Unzip the downloaded file.
3. Create an empty database, taking note of your username, password, hostname, and database name.
4. Everything inside the folder you unzipped needs to be uploaded/copied to your webserver, for example, into your `public_html` or `www` or `html` folder (the folder will already exist on your webserver).
5. In your browser, enter the address to your site, such as: www.example.com (or if you uploaded it into another subdirectory such as foldername use www.example.com/foldername)
6. Follow the instructions that appear in your browser for installation.

### Upgrading
1. [Download the latest official KwaMoja release.](https://github.com/timschofield/KwaMoja/archive/release_20.03.zip)
2. Unzip the downloaded file.
3. Backup the `config.php` script and `companies/` directory from your previous installation.
3. Everything inside the folder you unzipped needs to be uploaded/copied to your webserver, overwriting your previous installation.
4. Verify that the `config.php` script and `companies/` directory are intact, and if not, restore them from your backup.
5. In your browser, enter the address to your site, such as: www.example.com (or if you uploaded it into another subdirectory such as foldername use www.example.com/foldername).
6. After you log-in, if any database upgrades are required, you will be prompted to install them.

Further information about installation and upgrading is available in the [documentation](https://www.kwamoja.org/ManualContents.php?PageName=Manual%2FManualGettingStarted.html).

## Documentation
The KwaMoja documentation is included in every installation, and can accessed by clicking on the `Manual` button on the top menu bar. The documentation is also available within the [live demo.](https://www.kwamoja.org/ManualContents.php?PageName=Manual%2FManualIntroduction.html)

## Support
Free support is available 24/7, provided by our enthusiastic community of actual KwaMoja users, integrators, and the developers themselves.
The primary means of support is through the forum at: https://discussions.kwamoja.org/

## Contribute to the KwaMoja project
Contributions of code and documententation including How-Tos with screen-shots etc are very much appreciated. If your business has done such training materials for your own team this will no doubt be useful to many others and a productive way that you could contribute. Contributions in the form of bug reports or other feedback through the forums or mailing lists above also help to improve the project.

Guidelines for contributing code can be found at: https://www.kwamoja.org/CodingGuidelines.php

Developers interested in contributing should read this document carefully and follow the guidelines therein. Standards and conventions used in the code are rigorously applied in the interests of consistency and readability.

## Legal
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

A copy of the GNU General Public License is included in the doc directory along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
