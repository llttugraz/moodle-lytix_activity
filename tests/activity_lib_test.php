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
 * Testcases for the activity library.
 *
 * @package    lytix_activity
 * @author     Günther Moser <moser@tugraz.at>
 * @copyright  2023 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lytix_activity;

defined('MOODLE_INTERNAL') || die();

use external_api;
use externallib_advanced_testcase;
use lytix_helper\dummy;

global $CFG;
require_once("{$CFG->dirroot}/webservice/tests/helpers.php");

/**
 * Class activity_lib_test.
 *
 * @runTestsInSeparateProcesses
 * @coversDefaultClass  \lytix_activity\activity_lib
 */
final class activity_lib_test extends externallib_advanced_testcase {
    /**
     * Variable for course.
     *
     * @var \stdClass|null
     */
    private $course = null;

    /**
     * Variable for the context
     *
     * @var bool|\context|\context_course|null
     */
    private $context = null;

    /**
     * Variable for the students
     *
     * @var array
     */
    private $students = [];

    /**
     * Setup called before any test case.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        global $CFG;

        require_once("{$CFG->libdir}/externallib.php");

        $course            = new \stdClass();
        $course->fullname  = 'Test Course';
        $course->shortname = 'activity_test_course';
        $course->category  = 1;

        $this->students = dummy::create_fake_students(10);
        $return         = dummy::create_course_and_enrol_users($course, $this->students);
        $this->course   = $return['course'];
        $this->context  = \context_course::instance($this->course->id);

        set_config('course_list', $this->course->id, 'local_lytix');
        // Set platform.
        set_config('platform', 'learners_corner', 'local_lytix');
        // Set start and end.
        $semstart = new \DateTime('4 months ago');
        $semstart->setTime(0, 0);
        set_config('semester_start', $semstart->format('Y-m-d'), 'local_lytix');
        $semend = new \DateTime('today midnight');
        set_config('semester_end', $semend->format('Y-m-d'), 'local_lytix');
    }

    /**
     * Tests timeoverview webservice.
     *
     * @covers ::activity_get
     * @covers ::activity_get_returns
     * @covers ::activity_get_parameters
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public function test_empty_activity(): void {
        global $DB;
        $return = activity_lib::activity_get($this->students[0]->id, $this->course->id, $this->context->id);
        external_api::clean_returnvalue(activity_lib::activity_get_returns(), $return);

        // Basic asserts.
        $this::assertEquals(2, count($return));

        $this->assertTrue(key_exists('Times', $return));
        $this->assertTrue(key_exists('ShowOthers', $return));

        $this::assertEquals(7, count($return['Times']));

        // Update 2024-11-05: This test is extended for cleanup (implemented in local_lytix).
        $this::assertEquals(1, $DB->count_records('lytix_activity_customization'));
        delete_user($this->students[0], false);
        $this::assertEquals(0, $DB->count_records('lytix_activity_customization'));
    }

    /**
     * Create activites and check timeoverview webservice.
     *
     * @covers ::activity_get
     * @covers ::activity_get_returns
     * @covers ::activity_get_parameters
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \restricted_context_exception
     */
    public function test_activity_get(): void {
        global $DB;
        $date = new \DateTime('3 months ago');
        $today = new \DateTime('today midnight');

        foreach ($this->students as $student) {
            dummy::create_fake_data_for_course($date, $today, $student, $this->course->id, $this->context);
        }

        $result = activity_lib::activity_get($this->students[0]->id, $this->course->id, $this->context->id);
        external_api::clean_returnvalue(activity_lib::activity_get_returns(), $result);

        $this->assertTrue(key_exists('Times', $result));
        $summe = 0;
        $sumothers = 0;
        for ($i = 0; $i < 7; $i++) {
            $summe += $result['Times'][$i]['Me'];
            $sumothers += $result['Times'][$i]['Others'];
        }
        // Sum should always be equal to 1.
        $this::assertEquals(1, round($summe));
        $this::assertEquals(1, round($sumothers));

        // Update 2024-11-05: This test is extended for cleanup (implemented in local_lytix).
        $this::assertEquals(1, $DB->count_records('lytix_activity_customization'));
        delete_course($this->course->id, false);
        $this::assertEquals(0, $DB->count_records('lytix_activity_customization'));

    }
}
