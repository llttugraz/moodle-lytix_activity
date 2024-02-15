# lytix\_activity

This plugin is a subplugin of [local_lytix](https://github.com/llttugraz/moodle-local_lytix).
This module was designed specifically for students. Its primary goal is to offer students a comprehensive overview of the time they've spent in the course. This module is accompanied by a counterpart for lecturers: lytix_timeoverview.
Students can view their own and of their collegues time spent in a Bar Chart

## Table of Contents

- [Installation](#installation)
- [Requirements](#requirements)
- [Features](#features)
- [Configuration](#configuration)
- [Usage](#usage)
- [Dependencies](#dependencies)
- [API Documentation](#api-documentation)
- [Subplugins](#subplugins)
- [Privacy](#privacy)
- [FAQ](#faq)
- [Known Issues](#known-issues)
- [Changelog](#changelog)
- [License](#license)
- [Contributors](#contributors)

## Installation

1. Download the plugin and extract the files.
2. Move the extracted folder to your `moodle/local/lytix/modules` directory.
3. Log in as an admin in Moodle and navigate to `Site Administration > Plugins > Install plugins`.
4. Follow the on-screen instructions to complete the installation.

## Requirements

- Moodle Version: 4.1+
- PHP Version: 7.4+
- Supported Databases: MariaDB, PostgreSQL
- Supported Moodle Themes: Boost

## Features

The time data is aggregated per Moodle activity. Anything not related to a specific activity (like quizzes, H5P videos, or BigBlueButton sessions) is categorized as "Course Time". The line chart offers a more granular view, allowing students to see individual activity times on a day-to-day basis. Additionally, students have the option to view the median time spent by their peers.
The data is aggregated using a scheduled task. This task retrieves all student interactions (clicks) for the course from a log table in the database.

## Configuration

Describe here how to configure the plugin after installation, including available settings and how to adjust them.

## Usage

Explain how to use the plugin with step-by-step instructions and provide screenshots if they help clarify the process.

## Dependencies

- [local_lytix](https://github.com/llttugraz/moodle-local_lytix).
- [lytix_config](https://github.com/llttugraz/moodle-lytix_config).
- [lytix_logs](https://github.com/llttugraz/moodle-lytix_logs).

## API Documentation

If your plugin offers API endpoints, document what they return and how to use them here.

## Subplugins

If there are any subplugins, provide links and descriptions for each.

## Privacy

Detail what personal data the plugin stores and how it handles this data.

## FAQ

**Q:** Frequently asked question here?
**A:** Answer to the question here.

**Q:** Another frequently asked question?
**A:** Answer to the question here.

## Known Issues

- Issue 1: Solution or workaround for the issue.
- Issue 2: Solution or workaround for the issue.

## License

This plugin is licensed under the [GNU GPL v3](https://github.com/llttugraz/moodle-lytix_activity?tab=GPL-3.0-1-ov-file).

## Contributors

- **Günther Moser** - Developer - [GitHub](https://github.com/ghinta)
- **Alex Kremser** - Developer - [GitHub](https://github.com/llt-tuggy)
