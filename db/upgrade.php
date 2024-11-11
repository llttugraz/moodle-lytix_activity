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
 * Upgrade changes between versions
 *
 * @package   lytix_activity
 * @author    Günther Moser <moser@tugraz.at>
 * @copyright 2021 Educational Technologies, Graz, University of Technology
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or laterB
 */

/**
 * Upgrade Activity Basic DB
 * @param int $oldversion
 * @return bool
 * @throws ddl_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_lytix_activity_upgrade($oldversion) {

    if ($oldversion < 2022092100) {
        // Basic savepoint reached.
        upgrade_plugin_savepoint(true, 2022092100, 'lytix', 'activity');
    }

    if ($oldversion < 2024110801) {
        global $DB;
        // Delete deleted users from table 'lytix_activity_customization'.
        $DB->delete_records_select('lytix_activity_customization',
                'userid IN (SELECT id FROM  {user} WHERE deleted = 1)');

        // Delete non-existing courses from table 'lytix_activity_customization'.
        $DB->delete_records_select('lytix_activity_customization',
                'courseid NOT IN (SELECT id FROM  {course})');

        // Coursepolicy savepoint reached.
        upgrade_plugin_savepoint(true, 2024110801, 'lytix', 'activity');
    }

    return true;
}
