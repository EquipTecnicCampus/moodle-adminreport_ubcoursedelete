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
 * Deletes a course from the quarantine it keeps the backup file in a directory.
 *
 * @package   adminreport_ubcoursedelete
 * @copyright 2010 Yolanda Ordonez
 * @author    Yolanda Ordonez <yordonez@ub.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version   1.0
 *
 */

require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/backuplib.php');

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
$linkback = 'index.php';
// Don't use de referer for the confirmation form
if (!$delete and !empty($_SERVER['HTTP_REFERER'])) {
    $linkback = $_SERVER['HTTP_REFERER'];
    $linkback = str_replace('&', '&amp;', $linkback); // make it valid XHTML
}

if (!ubcoursedelete_can_delete_course($course->id)) {
    print_error('cannotdeletecourse', 'report_ubcoursedelete', $linkback);
}

if (!ubcoursedelete_in_quarantined($course->id)) {
    print_error('notinquarantine', 'report_ubcoursedelete', $linkback);
}

//Display a form to confirm
$site = get_site();
$stradministration = get_string('administration');

$navlinks = array();
$navlinks[] = array('name' => $stradministration,
                    'link' => "$CFG->wwwroot/$CFG->admin/index.php",
                    'type' => 'misc');
$navlinks[] = array('name' => format_string($course->shortname),
                    'link' => $linkcourseview,
                    'type' => 'misc');
if (!$delete) {
    $strdeletecheck = get_string('deletecheck', '', $course->shortname);
    $strdeletecoursecheck = get_string('deletecoursecheck');

    $navlinks[] = array('name' => $strdeletecheck,
                        'link' => null,
                        'type' => 'misc');
    $navigation = build_navigation($navlinks);

    print_header("$site->shortname: $strdeletecheck", $site->fullname, $navigation);

    $backupinactive = '';
    if (!ubcoursedelete_is_backup_active()) {
        $backupinactive = get_string('backupinactive', 'report_ubcoursedelete');
    }

    notice_yesno("$strdeletecoursecheck<br /><br />$backupinactive<br /><br />".format_string($course->fullname).
                 " (" . format_string($course->shortname) . ")",
                 "delete.php?id=$course->id&amp;delete=".md5($course->timemodified)."&amp;sesskey=$USER->sesskey",
                 $linkback);

    print_footer($course);
    exit;
}

if ($delete != md5($course->timemodified)) {
    print_error('invalidmd5');
}

if (!confirm_sesskey()) {
    print_error('confirmsesskeybad', 'error');
}

// OK checks done, delete the course now.
$strdeletingcourse = get_string('deletingcourse', '', format_string($course->shortname));
$strdeletedcourse = get_string('deletedcourse', '', format_string($course->shortname));

$navlinks[] = array('name' => $strdeletingcourse,
                    'link' => null,
                    'type' => 'misc');
$navigation = build_navigation($navlinks);

print_header("$site->shortname: $strdeletingcourse", $site->fullname, $navigation);

print_heading(format_string("$strdeletingcourse: $course->fullname ($course->shortname)"));

//Adjust some php variables to the execution of this script
@ini_set('max_execution_time','3000');
raise_memory_limit('512M');

$status = ubcoursedelete_delete_course($course);

if ($status) {
    print_heading($strdeletedcourse);
}

print_continue($linkback);

//Print the footer
print_footer();
?>