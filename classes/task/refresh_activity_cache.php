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
 * Refresh the cache for the activity plugin
 *
 * @package    lytix_activity
 * @category   task
 * @author     Günther Moser <moser@tugraz.at>
 * @author     Viktoria Wieser <viktoria.wieser@tugraz.at>
 * @copyright  2021 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lytix_activity\task;
// Important to get libraries here, else we get a conflict with the unit-tests.
// Note that we do NOT need to use global $CFG.
use lytix_activity\activities_cache;
use lytix_timeoverview\timeoverview;
use lytix_helper\cache_reset;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');

/**
 * Class refresh_lc_tc_cache
 */
class refresh_activity_cache extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     * @return string
     */
    public function get_name() {
        return get_string('cron_refresh_lytix_activity_cache', 'lytix_activity');
    }

    /**
     * Executes Task.
     * @throws \dml_exception
     */
    public function execute() {
        if (get_config('local_lytix', 'platform') == 'learners_corner') {

            $courseids = explode(',', get_config('local_lytix', 'course_list'));
            foreach ($courseids as $courseid) {
                if (!$courseid) {
                    continue;
                }
                // Reset Activities.
                $cache = \cache::make('lytix_activity', 'activities_cache');
                $cache->delete((int) $courseid);
                // Build Activities.
                $activities = activities_cache::load_activities((int) $courseid);

                if ($activities == false) {
                    echo "There was an error creating the caches for course $courseid.";
                }
            }
        }
    }
}
