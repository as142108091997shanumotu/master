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
 * This file contains the Activity modules block.
 *
 * @package    block_activity_modules
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');

class block_course_activities_list extends block_list {
    public function init() {
        $this->title = get_string('pluginname', 'block_activity_status');
    }

    public function get_content() {
        global $CFG, $DB, $OUTPUT, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $course = $this->page->course;

        require_once($CFG->dirroot.'/course/lib.php');
        $course_module = $DB->get_records('course_modules',array('course' => $course->id));
        $archetypes = array();
        foreach ($course_module as $cm) {
            if (!$cm->visible) {
                continue;
            }

            $activity = $DB->get_record('modules',array('id' => $cm->module));
            $module_name = $DB->get_record($activity->name,array('id' =>$cm->instance));
            if (!array_key_exists($module_name->name, $archetypes)) {
                $archetypes[$module_name->name] = plugin_supports('mod', $activity->name, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
            }

            $modwithlink = '<a href="'.$CFG->wwwroot.'/mod/'.$activity->name.'/view.php?id='.$cm->id.'">'.$module_name->name.'</a>';
            $modstatus = $DB->record_exists('course_modules_completion', array('coursemoduleid' => $cm->id,
                                'userid' => $USER->id, 'completionstate' => 1));
            $modcompletionstatus = $modstatus ? '- Completed' : '';
            $this->content->items[] =$cm->id.' - '.$modwithlink.' - '.date('d-M-Y', $cm->added).' '.$modcompletionstatus;
        }
        return $this->content;
    }

    public function applicable_formats() {
        return array('course-view' => true);
    }
}


