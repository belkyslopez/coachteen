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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_url_activity_task
 */

/**
 * Structure step to restore one bigbluebuttonbn activity
 */
class restore_bigbluebuttonbn_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('bigbluebuttonbn', '/activity/bigbluebuttonbn');
        if ($userinfo) {
            $paths[] = new restore_path_element('bigbluebuttonbn_logs', '/activity/bigbluebuttonbn/logs/log');
        }
        
        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_bigbluebuttonbn($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        // insert the bigbluebuttonbn record
        $newitemid = $DB->insert_record('bigbluebuttonbn', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_bigbluebuttonbn_logs($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->bigbluebuttonbnid = $this->get_new_parentid('bigbluebuttonbn');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->meetingid = $data->meetingid;
        $data->event = $data->event;
        $data->meta = $data->meta;

        $newitemid = $DB->insert_record('bigbluebuttonbn_logs', $data);
        $this->set_mapping('bigbluebuttonbn_logs', $oldid, $newitemid); // because of decode
    }

    protected function after_execute() {
        // Add bigbluebuttonbn related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_bigbluebuttonbn', 'intro', null);
    }
}