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
 * Backups a course from the quarantine and keeps the backup file in a directory.
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
$id     = required_param('id', PARAM_INT);  // course id

// Check the parameters and more permissions
$linkback = 'index.php';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $linkback = $_SERVER['HTTP_REFERER'];
    $linkback = str_replace('&', '&amp;', $linkback); // make it valid XHTML
}

if (!ubcoursedelete_is_backup_active()) {
    print_error('backupinactive', 'report_ubcoursedelete', $linkback);
}

if (!$course = get_record('course', 'id', $id)) {
    print_error('invalidcourseid', 'error', '', $id);
}

if (!ubcoursedelete_can_backup_course($course->id)) {
    print_error('cannotbackupcourse', 'report_ubcoursedelete', $linkback);
}
if (!ubcoursedelete_in_quarantined($course->id)) {
    print_error('notinquarantine', 'report_ubcoursedelete', $linkback);
}

// OK checks done, backup the course now.
$site = get_site();
$navlinks = array();

$linkcourseview = $CFG->wwwroot.'/course/view.php?id='.$course->id;

$strcoursebackup = get_string('coursebackup');
$stradministration = get_string('administration');
$strbackupfinished = get_string('backupfinished');

$navlinks[] = array('name' => $stradministration,
                    'link' => "$CFG->wwwroot/$CFG->admin/index.php",
                    'type' => 'misc');
$navlinks[] = array('name' => format_string($course->shortname),
                    'link' => $linkcourseview,
                    'type' => 'misc');
$navlinks[] = array('name' => $strcoursebackup,
                    'link' => null,
                    'type' => 'misc');
$navigation = build_navigation($navlinks);

print_header("$site->shortname: $strcoursebackup", $site->fullname, $navigation);

print_heading(format_string("$strcoursebackup: $course->fullname ($course->shortname)"));

//Adjust some php variables to the execution of this script
@ini_set('max_execution_time','3000');
raise_memory_limit('512M');

$status = ubcoursedelete_backup_course($course);

if (!$status) {
    print_error('backuperror', 'report_ubcoursedelete', $linkback);
}

print_heading($strbackupfinished);

print_continue($linkback);

//Print the footer
print_footer();

?>