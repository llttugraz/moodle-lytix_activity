# lytix\_activity

This plugin is a subplugin of [local_lytix](https://github.com/llttugraz/moodle-local_lytix).  
This module was designed specifically for students. Its primary goal is to offer students a comprehensive overview of the time they've spent in the course. This module is accompanied by a counterpart for lecturers: lytix_timeoverview.
Students can view their own and of their collegues time spent in a bar chart.

## Installation

1. Download the plugin and extract the files.
2. Move the extracted folder to your `moodle/local/lytix/modules` directory.
3. Log in as an admin in Moodle and navigate to `Site Administration > Plugins > Install plugins`.
4. Follow the on-screen instructions to complete the installation.

## Requirements

- Supported Moodle Version: 4.1 - 4.5
- Supported PHP Version:    7.4 - 8.2
- Supported Databases:      MariaDB, PostgreSQL
- Supported Moodle Themes:  Boost

This plugin has only been tested under the above system requirements against the specified versions.
However, it may work under other system requirements and versions as well.

## Features

The time data is aggregated per Moodle activity. Anything not related to a specific activity (like quizzes, H5P videos, or BigBlueButton sessions) is categorized as "Course Time". The line chart offers a more granular view, allowing students to see individual activity times on a day-to-day basis. Additionally, students have the option to view the median time spent by their peers.
The data is aggregated using a scheduled task. This task retrieves all student interactions (clicks) for the course from a log table in the database.

## Configuration

No settings for the subplugin are available.


## Usage

The provided widget of this subplugin is part of the LYTIX operation mode `Learner's Corner` for students. We refer to [local_lytix](https://github.com/llttugraz/moodle-local_lytix) for the configuration of this operation mode. If the mode `Learner's Corner` is active  and if a course is in the list of supported courses for this mode, then for students this widget is displayed when clicking on `Learner's Corner` in the main course view.

## API Documentation

No API.

## Privacy

The following personal data are stored:

| Entry         | Description                                                                    |
|---------------|--------------------------------------------------------------------------------|
| userid        | The ID of the user who viewed the activity widget                              |
| courseid      | The ID of the corresponding course                                             |
| show_others   | The option to show the median of activities in the course of other users       |


## Known Issues

There are no known issues related to this plugin.


## Dependencies

- [local_lytix](https://github.com/llttugraz/moodle-local_lytix)
- [lytix_logs](https://github.com/llttugraz/moodle-lytix_logs)
- [lytix_helper](https://github.com/llttugraz/moodle-lytix_helper)

Note: In order that the provided widget is displayed in `Learner's Corner` the **subplugin** [lytix_config](https://github.com/llttugraz/moodle-lytix_config) **is required**.

## License

This plugin is licensed under the [GNU GPL v3](https://github.com/llttugraz/moodle-lytix_activity?tab=GPL-3.0-1-ov-file).

## Contributors

- **Günther Moser** - Developer - [GitHub](https://github.com/ghinta)
- **Alex Kremser** - Developer
