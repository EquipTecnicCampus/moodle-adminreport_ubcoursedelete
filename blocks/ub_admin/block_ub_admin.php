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
 * Language strings for the UB Deleted courses report
 *
 * @package   block_ub_admin
 * @copyright 2010 Yolanda Ordonez
 * @author    Yolanda Ordonez <yordonez@ub.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version   1.0
 *
 */

class block_ub_admin extends block_list {
    function init() {
        $this->title = get_string('ub_admin', 'block_ub_admin');
        $this->version = 2010072301;
    }

    function get_content() {
        global $CFG, $SITE, $COURSE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->instance->pageid)) { // sticky
            if (!empty($COURSE)) {
                $this->instance->pageid = $COURSE->id;
            }
        }

        if (empty($this->instance)) {
            return $this->content = '';
        } else if ($this->instance->pageid == SITEID) {
            // return $this->content = '';
        }

        if (!empty($this->instance->pageid)) {
            $context = get_context_instance(CONTEXT_COURSE, $this->instance->pageid);
            if ($COURSE->id == $this->instance->pageid) {
                $course = $COURSE;
            } else {
                $course = get_record('course', 'id', $this->instance->pageid);
            }
        } else {
            $context = get_context_instance(CONTEXT_SYSTEM);
            $course = $SITE;
        }

        if (!has_capability('moodle/course:view', $context)) {  // Just return
            return $this->content;
        }

        if (empty($CFG->loginhttps)) {
            $securewwwroot = $CFG->wwwroot;
        } else {
            $securewwwroot = str_replace('http:','https:',$CFG->wwwroot);
        }

    /// Quarantine Course
        if ($course->id != SITEID and has_capability('report/ubcoursedelete:quarantine', $context)) {
            $this->content->items[]='<a href="'.$CFG->wwwroot.'/'.$CFG->admin.'/report/ubcoursedelete/quarantine.php?id='.$course->id.'">'.get_string('quarantinecourse', 'report_ubcoursedelete').'</a>';
            $this->content->icons[]='<img src="'.$CFG->pixpath.'/t/delete.gif" class="icon" alt="" />';
        }

        return $this->content;

    }

    function applicable_formats() {
        return array('course' => true);   // Not needed on site
    }


}

?>