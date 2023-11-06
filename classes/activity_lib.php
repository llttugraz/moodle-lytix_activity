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
 * This is a one-line short description of the file.
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    lytix_activity
 * @author     Günther Moser <moser@tugraz.at>
 * @author     Viktoria Wieser <viktoria.wieser@tugraz.at>
 * @copyright  2021 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lytix_activity;

use context_course;
use lytix_helper\calculation_helper;
use lytix_helper\course_settings;

/**
 * Class activity_lib
 */
class activity_lib extends \external_api {

    /**
     * Checks parameters.
     * @return \external_function_parameters
     */
    public static function activity_get_parameters() {
        return new \external_function_parameters(
            [
                'userid' => new \external_value(PARAM_INT, 'User Id', VALUE_REQUIRED),
                'courseid' => new \external_value(PARAM_INT, 'Course Id', VALUE_REQUIRED),
                'contextid' => new \external_value(PARAM_INT, 'Context Id', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Checks return values.
     * @return \external_single_structure
     */
    public static function activity_get_returns() {
        return new \external_single_structure(
            [
                'Times' => new \external_multiple_structure(
                    new \external_single_structure(
                        [
                            'Type'       => new \external_value(PARAM_TEXT, 'Type fo target (Name)',
                                VALUE_REQUIRED),
                            'Me' => new \external_value(PARAM_FLOAT,
                                'Duration of time spend for this target (median) (User)',
                                VALUE_REQUIRED),
                            'Others' => new \external_value(PARAM_FLOAT,
                                'Duration of time spend for this target (median) (Others)',
                                VALUE_REQUIRED),
                        ], 'desc', VALUE_OPTIONAL
                    ), 'desc', VALUE_OPTIONAL
                ),
                'ShowOthers' => new \external_value(PARAM_BOOL, 'Show the times of other students', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Gets activity logs.
     * @param int $userid
     * @param int $courseid
     * @param int $contextid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public static function activity_get($userid, $courseid, $contextid) {

        $params  = self::validate_parameters(self::activity_get_parameters(), [
            'userid' => $userid,
            'courseid' => $courseid,
            'contextid' => $contextid
        ]);

        // We always must call validate_context in a webservice.
        $context = \context::instance_by_id($params['contextid'], MUST_EXIST);
        self::validate_context($context);

        $start = course_settings::getcoursestartdate($courseid);

        $courseend = course_settings::getcourseenddate($courseid);
        $today = new \DateTime('today midnight');
        $end = null;

        // Special case for iMooX. There is no Semesterend or a old one.
        if ($courseend && $courseend->getTimestamp() <= $today->getTimestamp()) {
            $end = $today;
        } else {
            $end = $courseend;
        }

        $all = calculation_helper::get_activity_aggregation($courseid, $start->getTimestamp(), $end->getTimestamp());
        $sumall = $all['time']['core'] + $all['time']['forum'] + $all['time']['grade'] +
            $all['time']['submission'] + $all['time']['resource'] + $all['time']['quiz'] +
            $all['time']['video'] + $all['time']['feedback'];

        $me = calculation_helper::get_activity_aggregation($courseid, $start->getTimestamp(), $end->getTimestamp(), $userid);
        $summe = $me['time']['core'] + $me['time']['forum'] + $me['time']['grade'] +
            $me['time']['submission'] + $me['time']['resource'] + $me['time']['quiz'] +
            $me['time']['video'] + $me['time']['feedback'];

        // Store into return array.
        $times[] = ['Type' => 'Navigation',
            'Me' => calculation_helper::div($me['time']['core'], $summe),
            'Others' => calculation_helper::div($all['time']['core'], $sumall)];
        $times[] = ['Type' => 'Forum',
            'Me' => calculation_helper::div($me['time']['forum'], $summe),
            'Others' => calculation_helper::div($all['time']['forum'], $sumall)];
        $times[] = ['Type' => 'Grade',
            'Me' => calculation_helper::div($me['time']['grade'], $summe),
            'Others' => calculation_helper::div($all['time']['grade'], $sumall)];
        $times[] = ['Type' => 'Submission',
            'Me' => calculation_helper::div($me['time']['submission'], $summe),
            'Others' => calculation_helper::div($all['time']['submission'], $sumall)];
        $times[] = ['Type' => 'Resource',
            'Me' => calculation_helper::div($me['time']['resource'], $summe),
            'Others' => calculation_helper::div($all['time']['resource'], $sumall)];
        $times[] = ['Type' => 'Quiz',
            'Me' => calculation_helper::div($me['time']['quiz'], $summe),
            'Others' => calculation_helper::div($all['time']['quiz'], $sumall)];
        $times[] = ['Type' => 'Video',
            'Me' => calculation_helper::div($me['time']['video'], $summe),
            'Others' => calculation_helper::div($all['time']['video'], $sumall)];

        $customization = activity_helper::test_and_set_customization($courseid, $userid, true);

        return [
            'Times' => $times,
            'ShowOthers' => (bool)$customization->show_others,
        ];
    }

    /**
     * Toggles the ShowOthers flag in the DB.
     * @return \external_function_parameters
     */
    public static function activity_toggle_others_parameters() {
        return new \external_function_parameters(
            [
                'userid' => new \external_value(PARAM_INT, 'User Id', VALUE_REQUIRED),
                'courseid' => new \external_value(PARAM_INT, 'Course Id', VALUE_REQUIRED),
                'contextid' => new \external_value(PARAM_INT, 'Context Id', VALUE_REQUIRED),
                'showothers' => new \external_value(PARAM_BOOL, 'ShowOthers flag', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Return function.
     * @return \external_single_structure
     */
    public static function activity_toggle_others_returns() {
        return new \external_single_structure(
            [
                'success' => new \external_value(PARAM_BOOL, 'Alwasy true', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Toggles the ShowOthers flag in the DB
     * @param int $userid
     * @param int $courseid
     * @param int $contextid
     * @param int|bool $showothers
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public static function activity_toggle_others($userid, $courseid, $contextid, $showothers) {

        $params  = self::validate_parameters(self::activity_toggle_others_parameters(), [
            'userid' => $userid,
            'courseid' => $courseid,
            'contextid' => $contextid,
            'showothers' => $showothers
        ]);

        // We always must call validate_context in a webservice.
        $context = \context::instance_by_id($params['contextid']);
        self::validate_context($context);

        $customization = activity_helper::test_and_set_customization($courseid, $userid, false, (int)$showothers);

        return ['success' => ($customization->show_others == $showothers)];
    }

}
