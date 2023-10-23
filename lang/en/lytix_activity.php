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
$string['activity'] = 'Activity';
$string['error_text'] = '<div class="alert alert-danger">Something went wrong, please reload the page(F5). <br>
 If this error happens again please contact your administrator.</div>';
$string['overall_time'] = 'Overall Time';
$string['time_per_day'] = 'Time per Day';
$string['show_others'] = 'Show Others';
$string['average_all'] = "average time";
$string['user_all'] = "my time";
$string['all_core'] = "average in course";
$string['user_core'] = "me in course";
$string['all_forum'] = "average in forum";
$string['user_forum'] = "me in forum";
$string['all_grade'] = "average in grading";
$string['user_grade'] = "me in grading";
$string['all_submission'] = "average for submissons";
$string['user_submission'] = "me for submissions";
$string['all_resource'] = "average for resources";
$string['user_resource'] = "me for resources";
$string['all_quiz'] = "average for quizzes";
$string['user_quiz'] = "me for quizzes";
$string['all_bbb'] = "average in BBB";
$string['user_bbb'] = "me in BBB";
$string['no_activities_found'] = "No activities for this course found.";
$string['sum_user'] = "Sum User: ";
$string['sum_average'] = "Sum Average: ";
$string['core'] = "Course";
$string['forum'] = "Forum";
$string['grade'] = "Grade";
$string['submission'] = "Submission";
$string['resource'] = "Resource";
$string['quiz'] = "Quiz";
$string['video'] = "Video";
$string['bbb'] = "BigBlueButton";
$string['all'] = 'All';
$string['nodata'] = 'not enough data available';
$string['description_me'] = 'This shows the mean percentage of time you have spent on different activities.';
$string['description_others'] = 'Here you see how your colleagues have spent their time on average.';
$string['h'] = 'hours';
$string['m'] = 'minutes';
$string['s'] = 'seconds';
$string['title'] = 'Activity';
// Privacy.
$string['privacy:metadata:lytix_activity'] = "In order to track all activities of the users , we\
 need to save some user related data";
$string['privacy:metadata:lytix_activity:courseid'] = "The course ID will be saved for knowing to which course the\
 data belongs to";
$string['privacy:metadata:lytix_activity:userid'] = "The user ID will be saved for uniquely identifying the user";
$string['privacy:metadata:lytix_activity:show_others'] = "The option to show the median of other users in the course\
 is saved here";
$string['privacy:metadata:lytix_activity:future'] = "This value is a placeholder for future values";
