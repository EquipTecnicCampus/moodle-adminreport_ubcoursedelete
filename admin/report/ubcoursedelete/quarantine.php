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
 * Puts into quarantine a course identified by its id.
 *
 * @package   adminreport_ubcoursedelete
 * @copyright 2010 Yolanda Ordonez
 * @author    Yolanda Ordonez <yordonez@ub.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version   1.0
 *
 */

require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/lib.php');

// Check permissions.
require_login();

// Get URL parameters.
$id     = required_param('id', PARAM_INT);              // course id
$delete = optional_param('delete', '', PARAM_ALPHANUM); // delete confirmation hash

// Check the parameters and more permissions
if (!$course = get_record('course', 'id', $id)) {
    print_error('invalidcourseid', 'error', '', $id);
}

$linkcourseview = $CFG->wwwroot.'/course/view.php?id='.$course->id;

if (!ubcoursedelete_can_quarantine_course($course->id)) {
    print_error('cannotquarantinecourse', 'report_ubcoursedelete', $linkcourseview);
}

if (ubcoursedelete_in_quarantined($course->id)) {
    print_error('alreadyquarantinedcourse', 'report_ubcoursedelete', $linkcourseview);
}

if (ubcoursedelete_in_metacourse($course)) {
    print_error('cannotquarantinemetacourse', 'report_ubcoursedelete', $linkcourseview);
}

//Display a form to confirm
$site = get_site();
$navlinks = array();

if (! $delete) {
    $strquarantinecheck = get_string('quarantinecheck', 'report_ubcoursedelete', $course->shortname);
    $strquarantinecoursecheck = get_string('quarantinecoursecheck', 'report_ubcoursedelete');

    $navlinks[] = array('name' => format_string($course->shortname),
                        'link' => $linkcourseview,
                        'type' => 'misc');
    $navlinks[] = array('name' => $strquarantinecheck,
                        'link' => null,
                        'type' => 'misc');
    $navigation = build_navigation($navlinks);

    print_header("$site->shortname: $strquarantinecheck", $site->fullname, $navigation);

    notice_yesno("$strquarantinecoursecheck<br /><br />" . format_string($course->fullname) .
                 " (" . format_string($course->shortname) . ")",
                 "quarantine.php?id=$course->id&amp;delete=".md5($course->timemodified)."&amp;sesskey=$USER->sesskey",
                 $linkcourseview);

    print_footer($course);
    exit;
}

if ($delete != md5($course->timemodified)) {
    print_error('invalidmd5');
}

if (!confirm_sesskey()) {
    print_error('confirmsesskeybad', 'error');
}

// OK checks done, quarantine the course now.
add_to_log(SITEID, 'course', 'quarantine', 'view.php?id='.$course->id, "$course->fullname (ID $course->id)");

$strquarantiningcourse  = get_string('quarantiningcourse', 'report_ubcoursedelete', format_string($course->shortname));
$strquarantinedcourse = get_string('quarantinedcourse', 'report_ubcoursedelete', format_string($course->shortname));

$navlinks[] = array('name' => format_string($course->shortname),
                    'link' => $linkcourseview,
                    'type' => 'misc');
$navlinks[] = array('name' => $strquarantiningcourse,
                    'link' => null,
                    'type' => 'misc');
$navigation = build_navigation($navlinks);

print_header("$site->shortname: $strquarantiningcourse", $site->fullname, $navigation);

print_heading($strquarantiningcourse);

ubcoursedelete_quarantine_course($course);

print_heading($strquarantinedcourse);

print_continue($CFG->wwwroot.'/');

//Print the footer
print_footer();

?>