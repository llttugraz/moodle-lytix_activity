<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Activity plugin for lytix
 *
 * @package    lytix_activity
 * @author     Günther Moser <moser@tugraz.at>
 * @author     Viktoria Wieser <viktoria.wieser@tugraz.at>
 * @copyright  2021 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Lytix Activity';
$string['privacy:metadata'] = 'This plugin does not store any data.';

// Activity.
$string['title'] = 'Activity';
$string['show_others'] = 'Show Others';
$string['core'] = 'Course';
$string['forum'] = 'Forum';
$string['grade'] = 'Grade';
$string['submission'] = 'Submission';
$string['resource'] = 'Resource';
$string['quiz'] = 'Quiz';
$string['video'] = 'Video';
$string['bbb'] = 'BigBlueButton';
$string['all'] = 'All';
$string['nodata'] = 'not enough data available';
$string['desc'] = 'This shows the mean percentage of time spent on different activities.';

// Privacy.
$string['privacy:metadata:lytix_activity'] = 'In order to track all activities of the users , we\
 need to save some user related data';
$string['privacy:metadata:lytix_activity:courseid'] = 'The course ID will be saved for knowing to which course the\
 data belongs to';
$string['privacy:metadata:lytix_activity:userid'] = 'The user ID will be saved for uniquely identifying the user';
$string['privacy:metadata:lytix_activity:show_others'] = 'The option to show the median of other users in the course\
 is saved here';
$string['privacy:metadata:lytix_activity:future'] = 'This value is a placeholder for future values';
