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
 * Testcases for local_lytix subplugin
 *
 * @package    lytix_activity
 * @category   test
 * @author     Günther Moser <moser@tugraz.at>
 * @author     Viktoria Wieser <viktoria.wieser@tugraz.at>
 * @copyright  2021 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lytix_activity;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use cache_store;
use context_course;
use external_api;
use lytix_activity\task\refresh_activity_cache;
use lytix_helper\dummy;
use stdClass;
use cache_definition;

global $CFG;
require_once($CFG->dirroot . '/lib/externallib.php');

/**
 * Class activities_cache_test
 * @group learners_corner
 * @coversDefaultClass \lytix_activity\activity_graph_lib
 */
class activities_cache_test extends advanced_testcase {
    /**
     * Variable for course
     * @var null
     */
    private $course = null;

    /**
     * Setup called before any test case.
     */
    public function setUp(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();
    }

    /**
     * Refresh cache.
     * @throws \dml_exception
     */
    public function execute_task() {
        $task = new refresh_activity_cache();
        $task->execute();

    }

    /**
     * Execute activity_logs_get.
     * @param int $userid
     * @return array|array[]
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public function execute($userid) {
        $context = context_course::instance($this->course->id);
        return activity_graph_lib::activity_logs_get($userid, $this->course->id, $context->id);

    }

    /**
     * Creates fake data for course.
     * @param array $students
     */
    public function fill_activities($students) {
        $context = context_course::instance($this->course->id);
        foreach ($students as $student) {
            if ($student->id) {

                $date = new \DateTime('5 months ago');
                date_add($date, date_interval_create_from_date_string('6 hours'));
                $today = new \DateTime('today midnight');
                date_add($today, date_interval_create_from_date_string('6 hours'));

                dummy::create_fake_data_for_course($date, $today, $student, $this->course->id, $context);
            }
        }
    }

    /**
     * Creates and enroles student.
     * @param string $email
     * @return stdClass|null
     * @throws \dml_exception
     */
    private function create_enrol_student($email) {
        global $DB;
        $dg = $this->getDataGenerator();

        $role    = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);
        $student = $dg->create_user(array('email' => $email));
        if ($dg->enrol_user($student->id, $this->course->id, $role->id)) {
            return $student;
        } else {
            return null;
        }
    }

    /**
     * Set up semester start, semester end and adds course to course_list for the learnerscorner.
     * @param false|mixed|\stdClass $start
     * @param false|mixed|\stdClass $end
     */
    private function setup_semester($start, $end) {
        set_config('semester_start', $start->format('Y-m-d'), 'local_lytix');
        set_config('semester_end', $end->format('Y-m-d'), 'local_lytix');
        // Create course.
        $this->course = $this->getDataGenerator()->create_course(['startdate' => $start->getTimestamp()]);
        // Add course to config list.
        set_config('course_list', $this->course->id, 'local_lytix');
        set_config('platform', 'learners_corner', 'local_lytix');
    }

    /**
     * Creates fake logstore data for course.
     * @param array $students
     * @param int $courseid
     * @param int $semstart
     * @return void
     * @throws \dml_exception
     */
    private function fake_standard_logstore_entries($students, $courseid, $semstart) {
        global $DB;

        $context = context_course::instance($this->course->id);

        foreach ($students as $student) {
            if ($student->id) {
                $logstore = new stdClass();
                $logstore->userid    = $student->id;
                $logstore->courseid  = $courseid;
                $logstore->target = "course";
                $logstore->action = "viewed";
                $logstore->timecreated = $semstart->getTimestamp();
                $logstore->edulevel = 2;
                $logstore->contextid = $context->id;
                $logstore->contextlevel = 50;
                $logstore->contextinstanceid = 1;
                $DB->insert_record('logstore_standard_log', $logstore);
            }
        }
    }

    /**
     * Tests the cache data after one day, where no entries should exist yet.
     * @covers ::activity_logs_get
     * @covers ::activity_logs_get_parameters
     * @covers ::activity_logs_get_returns
     * @covers ::calculate_median_times
     * @covers \lytix_activity\activities_cache::load_activities
     * @covers \lytix_activity\activities_cache::get_activities
     * @covers \lytix_activity\activities_cache::load_for_cache
     * @covers \lytix_activity\activity_helper::test_and_set_customization
     * @covers \lytix_activity\activity_graph_lib::activity_logs_get
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public function test_case_1() {
        $today = new \DateTime('today');
        $today->setTime(0, 0);
        $in5months = new \DateTime('5 months');
        $this->setup_semester($today, $in5months);

        $this->execute_task();
        $today = new \DateTime('today');

        $user = $this->create_enrol_student('user@example.org');

        $result = $this->execute($user->id);

        try {
            external_api::clean_returnvalue(activity_graph_lib::activity_logs_get_returns(), $result);
        } catch (\invalid_response_exception $e) {
            if ($e) {
                self::assertFalse(true, "invalid_responce_exception thorwn.");
            }
        }

        self::assertEquals(3, count($result));
        self::assertEquals(1, count($result['data']));
        self::assertEquals(0, $result['data'][0]['average_all']);
        self::assertEquals(0, $result['data'][0]['user_all']);
        self::assertEquals(0, $result['data'][0]['all_core']);
        self::assertEquals(0, $result['data'][0]['user_core']);
        self::assertEquals(0, $result['data'][0]['all_forum']);
        self::assertEquals(0, $result['data'][0]['user_forum']);
        self::assertEquals(0, $result['data'][0]['all_grade']);
        self::assertEquals(0, $result['data'][0]['user_grade']);
        self::assertEquals(0, $result['data'][0]['all_submission']);
        self::assertEquals(0, $result['data'][0]['user_submission']);
        self::assertEquals(0, $result['data'][0]['all_resource']);
        self::assertEquals(0, $result['data'][0]['user_resource']);
        self::assertEquals(0, $result['data'][0]['all_quiz']);
        self::assertEquals(0, $result['data'][0]['user_quiz']);
        self::assertEquals(0, $result['data'][0]['all_bbb']);
        self::assertEquals(0, $result['data'][0]['user_bbb']);
        self::assertEquals($today->format('Ymd'), $result['data'][0]['date']);
    }

    /**
     * Tests the cache data if data exists.
     * @covers ::activity_logs_get
     * @covers ::activity_logs_get_parameters
     * @covers ::activity_logs_get_returns
     * @covers ::calculate_median_times
     * @covers \lytix_activity\activities_cache::load_activities
     * @covers \lytix_activity\activities_cache::get_activities
     * @covers \lytix_activity\activities_cache::load_for_cache
     * @covers \lytix_activity\activity_helper::test_and_set_customization
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public function test_case_2() {
        $fivemonthsago = new \DateTime('5 months ago');
        $fivemonthsago->setTime(0, 0);
        $tomorrow = new \DateTime('today midnight');
        date_add($tomorrow, date_interval_create_from_date_string('1 day'));

        $this->setup_semester($fivemonthsago, $tomorrow);

        $students = [];
        for ($i = 0; $i < 50; $i++) {
            $students[$i] = $this->create_enrol_student('teststudent' . $i . '@example.org');
        }

        $this->fill_activities($students);
        date_add($fivemonthsago, date_interval_create_from_date_string('1 day'));
        $this->fake_standard_logstore_entries($students, $this->course->id, $fivemonthsago);
        date_sub($fivemonthsago, date_interval_create_from_date_string('1 day'));

        $this->execute_task();

        $result = $this->execute($students[0]->id);

        $today    = new \DateTime('today');
        $interval = $fivemonthsago->diff($today);

        self::assertEquals(3, count($result));
        self::assertEquals($interval->format('%a'), count($result['data']));
        self::assertEquals(0, $result['ShowOthers']);
        self::assertEquals(7, count($result['MedianTimes']));

        // Show Others.
        activity_helper::test_and_set_customization($this->course->id, $students[0]->id, false, 1);

        $this->execute_task();

        $result = $this->execute($students[0]->id);

        self::assertEquals(1, $result['ShowOthers']);
    }

    /**
     * Tests the cache data if users joined later.
     * @covers ::activity_logs_get
     * @covers ::activity_logs_get_parameters
     * @covers ::activity_logs_get_returns
     * @covers ::calculate_median_times
     * @covers \lytix_activity\activities_cache::load_activities
     * @covers \lytix_activity\activities_cache::get_activities
     * @covers \lytix_activity\activities_cache::load_for_cache
     * @covers \lytix_activity\activity_helper::test_and_set_customization
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public function test_case_3() {
        global $DB;

        $fivemonthsago = new \DateTime('5 months ago');
        $fivemonthsago->setTime(0, 0);

        $tomorrow = new \DateTime('today midnight');
        date_add($tomorrow, date_interval_create_from_date_string('1 day'));

        $this->setup_semester($fivemonthsago, $tomorrow);

        $students = [];
        for ($i = 0; $i < 50; $i++) {
            $students[$i] = $this->create_enrol_student('teststudent' . $i . '@example.org');
        }

        $this->fill_activities($students);
        date_add($fivemonthsago, date_interval_create_from_date_string('1 day'));
        $this->fake_standard_logstore_entries($students, $this->course->id, $fivemonthsago);
        date_sub($fivemonthsago, date_interval_create_from_date_string('1 day'));

        // Delete first 10 days of 10 students.
        $after10days = new \DateTime('5 months ago');
        date_add($after10days, date_interval_create_from_date_string('10 days'));
        $whereclause = " userid = ? AND timestamp < ?";
        for ($i = 0; $i < 10; $i++) {
            $DB->delete_records_select('lytix_helper_dly_mdl_acty', $whereclause,
                                       array($students[$i]->id, $after10days->getTimestamp()));
        }

        $this->execute_task();

        $result = $this->execute($students[0]->id);

        $today    = new \DateTime('today');
        $interval = $fivemonthsago->diff($today);

        self::assertEquals(3, count($result));
        self::assertEquals($interval->format('%a'), count($result['data']));
    }

    /**
     * Tests the "ShowOthers" Button.
     * @covers ::activity_logs_get
     * @covers ::activity_logs_get_parameters
     * @covers ::activity_logs_get_returns
     * @covers ::calculate_median_times
     * @covers ::activity_toggle_others
     * @covers ::activity_toggle_others_parameters
     * @covers ::activity_toggle_others_returns
     * @covers \lytix_activity\activities_cache::load_activities
     * @covers \lytix_activity\activities_cache::get_activities
     * @covers \lytix_activity\activities_cache::load_for_cache
     * @covers \lytix_activity\activity_helper::test_and_set_customization
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public function test_case_4() {
        $fivemonthsago = new \DateTime('5 months ago');
        $fivemonthsago->setTime(0, 0);

        $tomorrowmidnight = new \DateTime('today midnight');
        date_add($tomorrowmidnight, date_interval_create_from_date_string('1 day'));

        $this->setup_semester($fivemonthsago, $tomorrowmidnight);

        $context = context_course::instance($this->course->id);

        $students = [];
        for ($i = 0; $i < 30; $i++) {
            $students[$i] = $this->create_enrol_student('teststudent' . $i . '@example.org');
        }

        $this->fill_activities($students);
        date_add($fivemonthsago, date_interval_create_from_date_string('1 day'));
        $this->fake_standard_logstore_entries($students, $this->course->id, $fivemonthsago);
        date_sub($fivemonthsago, date_interval_create_from_date_string('1 day'));

        $this->execute_task();

        // Toggle ShowOthers button.
        $success = activity_graph_lib::activity_toggle_others($students[0]->id, $this->course->id, $context->id, 1);
        try {
            external_api::clean_returnvalue(activity_graph_lib::activity_toggle_others_returns(), $success);
        } catch (\invalid_response_exception $e) {
            if ($e) {
                self::assertFalse(true, "invalid_responce_exception thorwn.");
            }
        }

        $result = $this->execute($students[0]->id);

        self::assertEquals(1, $result['ShowOthers']);

        activity_graph_lib::activity_toggle_others($students[0]->id, $this->course->id, $context->id, 0);

        $result = $this->execute($students[0]->id);

        self::assertEquals(0, $result['ShowOthers']);
    }

    /**
     * Tests the cache instance.
     * @covers \lytix_activity\activities_cache::get_instance_for_cache
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public function test_case_5() {
        $definition = cache_definition::load_adhoc(cache_store::MODE_APPLICATION, 'lytix_activity', 'phpunit_test');
        $instance = activities_cache::get_instance_for_cache($definition);
        self::assertNotNull($instance);
    }

    /**
     * Tests the get_many function of cache.
     * @covers \lytix_activity\activities_cache::load_many_for_cache
     * @return void
     * @throws \coding_exception
     */
    public function test_case_6() {
        $semstart = new \DateTime('5 months ago');
        $semstart->setTime(0, 0);

        $semend = new \DateTime('today midnight');
        date_add($semend, date_interval_create_from_date_string('1 day'));

        $this->setup_semester($semstart, $semend);

        $course2 = $this->getDataGenerator()->create_course(['startdate' => $semstart->getTimestamp()]);
        // Add course to config list.
        set_config('course_list', $course2->id, 'local_lytix');
        $courseids = array($this->course->id, $course2->id);
        $cache = \cache::make('lytix_activity', 'activities_cache');
        $cache->get_many($courseids);
    }

    /**
     * Tests if today's date is taken when the course_end/semester_end is smaller.
     * @covers ::activity_logs_get
     * @covers ::activity_logs_get_parameters
     * @covers ::activity_logs_get_returns
     * @covers ::calculate_median_times
     * @covers \lytix_activity\activities_cache::load_activities
     * @covers \lytix_activity\activities_cache::get_activities
     * @covers \lytix_activity\activities_cache::load_for_cache
     * @covers \lytix_activity\activity_helper::test_and_set_customization
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public function test_case_7() {
        $start = new \DateTime('5 months ago');
        $start->setTime(0, 0);

        $end = new \DateTime('today midnight');
        date_sub($end, date_interval_create_from_date_string('2 days'));

        $this->setup_semester($start, $end);
        $this->course->enddate = $end;

        $studentone = $this->create_enrol_student('teststudent@example.org');

        $this->execute_task();

        $result = $this->execute($studentone->id);
        self::assertNotNull($result);
    }
}
