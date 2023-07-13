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
 * Class activity_graph_lib
 */
class activity_graph_lib extends \external_api {
    /**
     * Calculates average.
     * @param int|float $count
     * @param int|float $sum
     * @return float|int
     */
    private static function get_average($count, $sum) {
        return ($count == 0) ? 0 : floor($sum / $count);
    }

    /**
     * Checks parameters.
     * @return \external_function_parameters
     */
    public static function activity_logs_get_parameters() {
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
    public static function activity_logs_get_returns() {
        return new \external_single_structure(
            [
                'data' => new \external_multiple_structure(
                    new \external_single_structure(
                        [
                            'average_all' => new \external_value(PARAM_INT, 'Type of event', VALUE_OPTIONAL),
                            'user_all' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),

                            'all_core' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),
                            'user_core' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),

                            'all_forum' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),
                            'user_forum' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),

                            'all_grade' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),
                            'user_grade' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),

                            'all_submission' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),
                            'user_submission' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),

                            'all_resource' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),
                            'user_resource' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),

                            'all_quiz' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),
                            'user_quiz' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),

                            'all_bbb' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),
                            'user_bbb' => new \external_value(PARAM_INT, 'Duration of time spend for this target (seconds)',
                                    VALUE_REQUIRED),
                            'date' => new \external_value(PARAM_TEXT, 'Date of activities', VALUE_REQUIRED),
                        ], '', false
                    )
                ),
                'MedianTimes' => new \external_multiple_structure(
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
                        ], 'desc', false
                    )
                ),
                'ShowOthers' => new \external_value(PARAM_BOOL, 'Show the times of other students', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Calculates Median Times.
     * @param array $mmcore
     * @param array $mmforum
     * @param array $mmgrade
     * @param array $mmsubmission
     * @param array $mmresource
     * @param array $mmquiz
     * @param array $mmvideo
     * @param array $omcore
     * @param array $omforum
     * @param array $omgrade
     * @param array $omsubmission
     * @param array $omresource
     * @param array $omquiz
     * @param array $omvideo
     * @return array
     */
    public static function calculate_median_times ($mmcore, $mmforum, $mmgrade, $mmsubmission, $mmresource, $mmquiz, $mmvideo,
                                                   $omcore, $omforum, $omgrade, $omsubmission, $omresource, $omquiz, $omvideo) {
        // Calculate me median times.
        $mmcore = calculation_helper::median($mmcore);
        $mmforum = calculation_helper::median($mmforum);
        $mmgrade = calculation_helper::median($mmgrade);
        $mmsubmission = calculation_helper::median($mmsubmission);
        $mmresource = calculation_helper::median($mmresource);
        $mmquiz = calculation_helper::median($mmquiz);
        $mmvideo = calculation_helper::median($mmvideo);

        $mmtime = $mmcore + $mmforum + $mmgrade + $mmsubmission +
                  $mmresource + $mmquiz + $mmvideo;

        $mmcoremt = calculation_helper::div($mmcore, $mmtime);
        $mmforummt = calculation_helper::div($mmforum, $mmtime);
        $mmgrademt = calculation_helper::div($mmgrade, $mmtime);
        $mmsubmissionmt = calculation_helper::div($mmsubmission, $mmtime);
        $mmresourcemt = calculation_helper::div($mmresource, $mmtime);
        $mmquizmt = calculation_helper::div($mmquiz, $mmtime);
        $mmvideomt = calculation_helper::div($mmvideo, $mmtime);

        // Caluclate others median times.
        $omcore = calculation_helper::median($omcore);
        $omforum = calculation_helper::median($omforum);
        $omgrade = calculation_helper::median($omgrade);
        $omsubmission = calculation_helper::median($omsubmission);
        $omresource = calculation_helper::median($omresource);
        $omquiz = calculation_helper::median($omquiz);
        $omvideo = calculation_helper::median($omvideo);

        $omtime = $omcore + $omforum + $omgrade + $omsubmission +
                  $omresource + $omquiz + $omvideo;

        $omcoremt = calculation_helper::div($omcore, $omtime);
        $omforummt = calculation_helper::div($omforum, $omtime);
        $omgrademt = calculation_helper::div($omgrade, $omtime);
        $omsubmissionmt = calculation_helper::div($omsubmission, $omtime);
        $omresourcemt = calculation_helper::div($omresource, $omtime);
        $omquizmt = calculation_helper::div($omquiz, $omtime);
        $omvideomt = calculation_helper::div($omvideo, $omtime);

        // Store into return array.
        $mediantimes[] = ['Type' => 'Navigation', 'Me' => $mmcoremt, 'Others' => $omcoremt];
        $mediantimes[] = ['Type' => 'Forum', 'Me' => $mmforummt, 'Others' => $omforummt];
        $mediantimes[] = ['Type' => 'Grade', 'Me' => $mmgrademt, 'Others' => $omgrademt];
        $mediantimes[] = ['Type' => 'Submission', 'Me' => $mmsubmissionmt, 'Others' => $omsubmissionmt];
        $mediantimes[] = ['Type' => 'Resource', 'Me' => $mmresourcemt, 'Others' => $omresourcemt];
        $mediantimes[] = ['Type' => 'Quiz', 'Me' => $mmquizmt, 'Others' => $omquizmt];
        $mediantimes[] = ['Type' => 'Video', 'Me' => $mmvideomt, 'Others' => $omvideomt];

        return $mediantimes;
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
    public static function activity_logs_get($userid, $courseid, $contextid) {
        global $DB;

        $params  = self::validate_parameters(self::activity_logs_get_parameters(), [
            'userid' => $userid,
            'courseid' => $courseid,
            'contextid' => $contextid
        ]);

        // We always must call validate_context in a webservice.
        $context = \context::instance_by_id($params['contextid'], MUST_EXIST);
        self::validate_context($context);
        $coursecontext = context_course::instance($courseid);

        $start = course_settings::getcoursestartdate($courseid);

        // Use smaller date.
        $courseend = course_settings::getcourseenddate($courseid);

        $today = new \DateTime('today');

        if ($today->getTimestamp() < $courseend->getTimestamp()) {
            $end = clone $today;
        } else {
            $end = clone $courseend;
        }

        $data = [];

        $params['courseid'] = $courseid;
        $params['contextid'] = $contextid;
        $params['userid'] = $userid;
        $params['semstart'] = $start->getTimestamp();
        $params['today'] = $end->getTimestamp();

        $sql = "SELECT *
            FROM {lytix_helper_dly_mdl_acty} logs
            WHERE logs.courseid = :courseid AND logs.contextid = :contextid AND logs.userid = :userid
            AND logs.timestamp >= :semstart AND logs.timestamp <= :today ORDER BY logs.core_time ASC";

        $records = $DB->get_records_sql($sql, $params);

        // Get all enrolled users.
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $enrolled = count(get_role_users($studentrole->id, $coursecontext));

        $all = activities_cache::load_activities((int)$courseid);

        $day = 0;
        // Iterate from semester start till today. days = max 120 days per semester.
        $mediantimes = [];
        // Users median times.
        $mmcore = [];
        $mmforum = [];
        $mmgrade = [];
        $mmsubmission = [];
        $mmresource = [];
        $mmquiz = [];
        $mmvideo = [];
        // Others median times.
        $omcore = [];
        $omforum = [];
        $omgrade = [];
        $omsubmission = [];
        $omresource = [];
        $omquiz = [];
        $omvideo = [];

        if (count($records)) {
            while ($start->getTimestamp() < $end->getTimestamp()) {
                $tmpday = clone $start;
                $tmpday->modify('+ 1 day');

                $usercore = 0;
                $userforum = 0;
                $usergrade = 0;
                $usersubmission = 0;
                $userresource = 0;
                $userquiz = 0;
                $userbbb = 0;

                // Iterate true each record on this day = users times.
                foreach ($records as $key => $record) {
                    if ($record->timestamp >= $start->getTimestamp() && $record->timestamp <= $tmpday->getTimestamp()) {
                        // Aggregate specific times for current user.
                        $usercore  += $record->core_time;
                        $userforum += $record->forum_time;
                        $usergrade += $record->grade_time;
                        $usersubmission += $record->submission_time;
                        $userresource += $record->resource_time;
                        $userquiz += $record->quiz_time;
                        $userbbb += $record->bbb_time;
                        unset($records[$key]);
                    } else {
                        break;
                    }
                }
                $allall = self::get_average($enrolled, $all[$day]['all_core'] + $all[$day]['all_forum'] + $all[$day]['all_grade']
                    + $all[$day]['all_submission'] + $all[$day]['all_resource'] + $all[$day]['all_quiz'] + $all[$day]['all_bbb']);
                $allcore = self::get_average($enrolled, $all[$day]['all_core']);
                $allforum = self::get_average($enrolled, $all[$day]['all_forum']);
                $allgrade = self::get_average($enrolled, $all[$day]['all_grade']);
                $allsubmission = self::get_average($enrolled, $all[$day]['all_submission']);
                $allresource = self::get_average($enrolled, $all[$day]['all_resource']);
                $allquiz = self::get_average($enrolled, $all[$day]['all_quiz']);
                $allbbb = self::get_average($enrolled, $all[$day]['all_bbb']);

                $userall = $usercore + $userforum + $usergrade + $usersubmission + $userresource + $userquiz + $userbbb;

                // Aggregate me average times.
                $mmcore[] = $usercore;
                $mmforum[] = $userforum;
                $mmgrade[] = $usergrade;
                $mmsubmission[] = $usersubmission;
                $mmresource[] = $userresource;
                $mmquiz[] = $userquiz;
                $mmvideo[] = $userbbb;

                // Aggregate others average times.
                $omcore[] = $allcore;
                $omforum[] = $allforum;
                $omgrade[] = $allgrade;
                $omsubmission[] = $allsubmission;
                $omresource[] = $allresource;
                $omquiz[] = $allquiz;
                $omvideo[] = $allbbb;

                $data[] = [
                    'average_all' => $allall,
                    'user_all' => $userall,

                    'all_core' => $allcore,
                    'user_core' => $usercore,

                    'all_forum' => $allforum,
                    'user_forum' => $userforum,

                    'all_grade' => $allgrade,
                    'user_grade' => $usergrade,

                    'all_submission' => $allsubmission,
                    'user_submission' => $usersubmission,

                    'all_resource' => $allresource,
                    'user_resource' => $userresource,

                    'all_quiz' => $allquiz,
                    'user_quiz' => $userquiz,

                    'all_bbb' => $allbbb,
                    'user_bbb' => $userbbb,

                    'date' => $start->format('Ymd')
                ];
                $day++;
                date_add($start, date_interval_create_from_date_string('1 day'));
            }
        } else {
            $data[] = [
                'average_all' => 0,
                'user_all' => 0,

                'all_core' => 0,
                'user_core' => 0,

                'all_forum' => 0,
                'user_forum' => 0,

                'all_grade' => 0,
                'user_grade' => 0,

                'all_submission' => 0,
                'user_submission' => 0,

                'all_resource' => 0,
                'user_resource' => 0,

                'all_quiz' => 0,
                'user_quiz' => 0,

                'all_bbb' => 0,
                'user_bbb' => 0,

                'date' => $start->format('Ymd')
            ];
        }

        $mediantimes = self::calculate_median_times($mmcore, $mmforum, $mmgrade, $mmsubmission, $mmresource, $mmquiz, $mmvideo,
                                                   $omcore, $omforum, $omgrade, $omsubmission, $omresource, $omquiz, $omvideo);

        $customization = activity_helper::test_and_set_customization($courseid, $userid, true);

        return [
            'data' => $data,
            'MedianTimes' => $mediantimes,
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
        $context = \context::instance_by_id($params['contextid'], MUST_EXIST);
        self::validate_context($context);

        $customization = activity_helper::test_and_set_customization($courseid, $userid, false, (int)$showothers);

        return ['success' => ($customization->show_others == $showothers)];
    }

}
