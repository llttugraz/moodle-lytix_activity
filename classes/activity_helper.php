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

/**
 * Helper class for lytix_activity.
 */
class activity_helper {
    /**
     * Function to customize the activity widget.
     * @param int $courseid
     * @param int $userid
     * @param int $getshowothers
     * @param int $showothers
     * @return false|mixed|\stdClass
     * @throws \dml_exception
     */
    public static function test_and_set_customization($courseid, $userid, $getshowothers, $showothers = 0) {
        global $DB;
        $table    = 'lytix_activity_customization';
        $customization = $DB->get_record($table, ['courseid' => $courseid, 'userid' => $userid]);

        if (!$customization) {
            $customization              = new \stdClass();
            $customization->courseid    = $courseid;
            $customization->userid      = $userid;

            $customization->show_others = $showothers;

            $customization->future      = '';

            $customization->id          = $DB->insert_record($table, $customization);
        }

        if (!$getshowothers) {
            if ($customization->show_others != $showothers) {
                $customization->show_others = $showothers;
                $DB->update_record($table, $customization);
            }
        }

        return $customization;
    }

}
