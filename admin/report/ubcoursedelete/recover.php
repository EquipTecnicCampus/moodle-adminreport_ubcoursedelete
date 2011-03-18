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
 * Recovers a course from the quarantine identified by its id.
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
$id     = required_param('id', PARAM_INT);                  // course id
$recover = optional_param('recover', '', PARAM_ALPHANUM);   // recover confirmation hash

// Check the parameters and more permissions
if (!$course = get_record('course', 'id', $id)) {
    print_error('invalidcourseid', 'error', '', $id);
}

$linkcourseview = $CFG->wwwroot.'/course/view.php?id='.$course->id;
$linkback = 'index.php';
// Don't use de referer for the confirmation form
if (!$recover and!empty($_SERVER['HTTP_REFERER'])) {
    $linkback = $_SERVER['HTTP_REFERER'];
    $linkback = str_replace('&', '&amp;', $linkback); // make it valid XHTML
}

if (!ubcoursedelete_can_recover_course($course->id)) {
    print_error('cannotrecovercourse', 'report_ubcoursedelete', $linkcourseview);
}

if (!ubcoursedelete_in_quarantined($course->id)) {
    print_error('notinquarantine', 'report_ubcoursedelete', $linkcourseview);
}

//Display a form to confirm
$site = get_site();
$stradministration = get_string("administration");

$navlinks = array();
$navlinks[] = array('name' => $stradministration,
                    'link' => "$CFG->wwwroot/$CFG->admin/index.php",
                    'type' => 'misc');
$navlinks[] = array('name' => format_string($course->shortname),
                    'link' => $linkcourseview,
                    'type' => 'misc');
if (! $recover) {
    $strrecovercheck = get_string('recovercheck', 'report_ubcoursedelete', $course->shortname);
    $strrecovercoursecheck = get_string('recovercoursecheck', 'report_ubcoursedelete');

    $navlinks[] = array('name' => $stradministration,
                        'link' => "$CFG->wwwroot/$CFG->admin/index.php",
                        'type' => 'misc');
    $navlinks[] = array('name' => format_string($course->shortname),
                        'link' => $linkcourseview,
                        'type' => 'misc');
    $navlinks[] = array('name' => $strrecovercheck,
                        'link' => null,
                        'type' => 'misc');
    $navigation = build_navigation($navlinks);

    print_header("$site->shortname: $strrecovercheck", $site->fullname, $navigation);

    notice_yesno($strrecovercoursecheck.'<br /><br />' . format_string($course->fullname) .
                 ' (' . format_string($course->shortname) . ')',
                 'recover.php?id='.$course->id.'&amp;recover='.md5($course->timemodified).'&amp;sesskey='.$USER->sesskey,
                 $linkback);

    print_footer($course);
    exit;
}

if ($recover != md5($course->timemodified)) {
    print_error('invalidmd5');
}

if (!confirm_sesskey()) {
    print_error('confirmsesskeybad', 'error');
}

// OK checks done, recover the course now.
$strrecoveringcourse  = get_string('recoveringcourse', 'report_ubcoursedelete', format_string($course->shortname));
$strrecoveredcourse = get_string('recoveredcourse', 'report_ubcoursedelete', format_string($course->shortname));

$navlinks[] = array('name' => $stradministration,
                    'link' => "$CFG->wwwroot/$CFG->admin/index.php",
                    'type' => 'misc');
$navlinks[] = array('name' => format_string($course->shortname),
                    'link' => $linkcourseview,
                    'type' => 'misc');
$navlinks[] = array('name' => $strrecoveringcourse,
                    'link' => null,
                    'type' => 'misc');
$navigation = build_navigation($navlinks);

print_header("$site->shortname: $strrecoveringcourse", $site->fullname, $navigation);

print_heading($strrecoveringcourse);


$status = ubcoursedelete_recover_course($course);
fix_course_sortorder(); //update course count in categories

if (!$status) {
    print_error('recovererror', 'report_ubcoursedelete', $linkback);
}

print_heading($strrecoveredcourse);

print_continue($linkcourseview);

//Print the footer
print_footer();
?>