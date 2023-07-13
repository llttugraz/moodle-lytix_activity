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
 * A course overview and filter plugin
 *
 * @package    lytix_activity
 * @category   cache
 * @author     Günther Moser <moser@tugraz.at>
 * @author     Viktoria Wieser <viktoria.wieser@tugraz.at>
 * @copyright  2021 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lytix_activity;

use cache_definition;
use context_course;
use lytix_helper\course_settings;
use PhpOffice\PhpSpreadsheet\Calculation\DateTime;
use lytix_planner\notification_settings;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/badgeslib.php');

/**
 * Class activities
 */
class activities_cache implements \cache_data_source {

    /** @var instance holds the singleton instance for this cache. */
    protected static $instance = null;

    /**
     * mandatory function
     *
     * @param cache_definition $definition
     * @return activities_cache|object|null
     */
    public static function get_instance_for_cache(cache_definition $definition) {
        if (is_null(self::$instance)) {
            self::$instance = new activities_cache();
        }
        return self::$instance;
    }

    /**
     * mandatory function
     *
     * @param int $key
     * @return array|mixed
     */
    public function load_for_cache($key) {
        return self::get_activities($key);
    }

    /**
     * mandatory function
     *
     * @param array $keys
     * @return array
     */
    public function load_many_for_cache(array $keys) {
        $courses = [];
        foreach ($keys as $key) {
            if ($course = $this->load_for_cache((int) $key)) {
                $courses[(int) $key] = $course;
            }
        }
        return $courses;
    }

    /**
     * load activities
     *
     * @param int $key
     * @return bool|float|int|mixed|string
     * @throws \coding_exception
     */
    public static function load_activities($key) {
        $cache = \cache::make('lytix_activity', 'activities_cache');
        return $cache->get($key);
    }

    /**
     * create activities
     *
     * @param int $courseid
     * @return array
     * @throws \dml_exception
     */
    private static function get_activities($courseid) {
        global $DB;

        // Use bigger date.
        $fetchstart = (new \DateTime())->setTimestamp(notification_settings::getcoursestartdate(($courseid)));
        $fetchstart->setTime(0, 0);

        $fetchend = new \DateTime('today');
        $end   = (new \DateTime())->setTimestamp(notification_settings::getcourseenddate(($courseid)));
        if ($end->getTimestamp() < $fetchend->getTimestamp()) {
            $fetchend = $end;
            $fetchend->setTime(0, 0);
        }

        $data = [];

        $params1['courseid']  = $courseid;
        $params1['fetchstart'] = $fetchstart->getTimestamp();
        $params1['fetchend']    = $fetchend->getTimestamp();

        $sqlviews = "SELECT * FROM {lytix_helper_dly_mdl_acty} " .
                    "WHERE courseid = :courseid AND timestamp >= :fetchstart AND timestamp <= :fetchend";

        if (!$DB->record_exists_sql($sqlviews, $params1)) {
            $data[] = [
                    'all_core'       => 0,
                    'all_forum'      => 0,
                    'all_grade'      => 0,
                    'all_submission' => 0,
                    'all_resource'   => 0,
                    'all_quiz'       => 0,
                    'all_bbb'        => 0,
                    'date'           => $fetchstart->format('Ymd')
            ];
        } else {
            $sql = "SELECT DISTINCT dly_logs.id, dly_logs.* " .
                   "FROM {lytix_helper_dly_mdl_acty} dly_logs " .
                   "WHERE dly_logs.courseid = :courseid AND dly_logs.timestamp >= :fetchstart AND dly_logs.timestamp <= :fetchperiod " .
                   "ORDER BY dly_logs.timestamp ASC";

            while ($fetchstart->getTimestamp() < $fetchend->getTimestamp()) {
                $fetchperiod = clone $fetchstart;
                $fetchperiod->setTime(23,59,59);

                $params['courseid']  = $courseid;
                $params['fetchstart'] = $fetchstart->getTimestamp();
                $params['fetchperiod']    = $fetchperiod->getTimestamp();

                $records = $DB->get_records_sql($sql, $params);

                if (count($records)) {

                    $allcore       = 0;
                    $allforum      = 0;
                    $allgrade      = 0;
                    $allsubmission = 0;
                    $allresource   = 0;
                    $allquiz       = 0;
                    $allbbb        = 0;

                    foreach ($records as $key => $record) {
                            $allcore       += $record->core_time;
                            $allforum      += $record->forum_time;
                            $allgrade      += $record->grade_time;
                            $allsubmission += $record->submission_time;
                            $allresource   += $record->resource_time;
                            $allquiz       += $record->quiz_time;
                            $allbbb        += $record->bbb_time;
                            // Remove this element from array.
                            //unset($records[$key]);
                    }
                    // Write sums for this day into array.
                    $data[] = [
                        'all_core'       => $allcore,
                        'all_forum'      => $allforum,
                        'all_grade'      => $allgrade,
                        'all_submission' => $allsubmission,
                        'all_resource'   => $allresource,
                        'all_quiz'       => $allquiz,
                        'all_bbb'        => $allbbb,
                        'date'           => date('Ymd', $fetchstart->getTimestamp())
                    ];
                } else { // No records at all for this day.
                    $data[] = [
                            'all_core'       => 0,
                            'all_forum'      => 0,
                            'all_grade'      => 0,
                            'all_submission' => 0,
                            'all_resource'   => 0,
                            'all_quiz'       => 0,
                            'all_bbb'        => 0,
                            'date'           => $fetchstart->format('Ymd')
                    ];
                }
                $fetchstart->modify('+1 day');
            }
        }
        return $data;
    }
}
