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
 * Show all the actions done to a course in quarantine
 *
 * @package   adminreport_ubcoursedelete
 * @copyright 2010 Yolanda Ordonez
 * @author    Yolanda Ordonez <yordonez@ub.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version   1.0
 *
 */

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once(dirname(__FILE__).'/lib.php');

define('DEFAULT_PAGE_SIZE', 20);
define('SHOW_ALL_PAGE_SIZE', 5000);

// Check permissions.
require_login();
$sitecontext = get_context_instance(CONTEXT_SYSTEM);
require_capability('report/ubcoursedelete:view', $sitecontext);

// Get URL parameters.
$id        = required_param('id', PARAM_INT);              // quarantine id
$page      = optional_param('page', 0, PARAM_INT);                      // which page to show
$perpage   = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);   // how many per page

// Print the header.
admin_externalpage_setup('reportubcoursedelete');
admin_externalpage_print_header();

// Check the parameters and more permissions
$linkback = 'index.php';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $linkback = $_SERVER['HTTP_REFERER'];
    $linkback = str_replace('&', '&amp;', $linkback); // make it valid XHTML
}
if (!$quarantine = get_record('ubcoursedelete', 'id', $id)) {
    print_error('notinquarantine', 'report_ubcoursedelete', $linkback);
}

//If the URL contains all the parameters needed to generate the report,
//get some data out of the database and display the report

$baseurl =  $CFG->wwwroot.'/admin/report/ubcoursedelete/courselog.php?id='.$quarantine->id.
            '&amp;perpage='.$perpage;

$strcourse = get_string('course');
$strtime = get_string('time');
$strfullname = get_string('fullname');
$straction = get_string('action');
$strinfo = get_string('info');

$table = new flexible_table('quarantine-report-courselog'.$quarantine->id);

$table->define_columns(array('coursename', 'time', 'userid', 'action', 'info'));
$table->define_headers(array($strcourse, $strtime, $strfullname, $straction, $strinfo));
$table->define_baseurl($baseurl);

$table->set_attribute('cellpadding','5');
$table->set_attribute('class', 'generaltable generalbox reporttable');

$table->sortable(true, 'time', SORT_DESC);
$table->no_sorting('info');

$table->set_control_variables(array(
                                    TABLE_VAR_SORT    => 'ssort',
                                    TABLE_VAR_HIDE    => 'shide',
                                    TABLE_VAR_SHOW    => 'sshow',
                                    TABLE_VAR_IFIRST  => 'sifirst',
                                    TABLE_VAR_ILAST   => 'silast',
                                    TABLE_VAR_PAGE    => 'spage'
                                    ));
$table->setup();

$sql = "SELECT l.id, q.courseid, q.coursename, q.status, l.userid, u.firstname, u.lastname, l.time, l.action, l.info
        FROM {$CFG->prefix}ubcoursedelete_log l
            JOIN {$CFG->prefix}ubcoursedelete q    ON l.quarantineid = q.id
            JOIN {$CFG->prefix}user u    ON u.id = l.userid
        WHERE l.quarantineid = ".$quarantine->id;

if ($table->get_sql_where()) {
    $sql .= ' AND '.$table->get_sql_where(); //initial bar
}
if ($table->get_sql_sort()) {
    $sql .= ' ORDER BY '.$table->get_sql_sort();
}

$countsql = "SELECT COUNT(DISTINCT(l.id))
                FROM {$CFG->prefix}ubcoursedelete_log l
            WHERE l.quarantineid = ".$quarantine->id;

$totalcount = count_records_sql($countsql);

if ($table->get_sql_where()) {
    $matchcount = count_records_sql($countsql.' AND '.$table->get_sql_where());
} else {
    $matchcount = $totalcount;
}

$table->pagesize($perpage, $matchcount);

echo '<div id="ubcoursedeletecourselog">';

if (!$quarantinelogs = get_records_sql($sql, $table->get_page_start(), $table->get_page_size())) {
    $quarantinelogs = array(); // tablelib will handle saying 'Nothing to display' for us.
}

$data = array();

echo '<h3>'.get_string('displayingrecords', '', $totalcount).'</h3>';

$canviewdetails = has_capability('moodle/user:viewdetails', $sitecontext);
foreach ($quarantinelogs as $q) {
    if ($q->status == UBCOURSEDELETE_STATUS_DELETED) {
        $course = $q->coursename;
    } else {
        $course = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$q->courseid.'">'.$q->coursename.'</a>';
    }
    if ($canviewdetails) {
        $user = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$q->userid.'">'.fullname($q).'</a>';
    } else {
        $user = fullname($q);
    }
    $info = ubcoursedelete_format_info($q);
    $data = array($course, userdate($q->time), $user, $q->action, $info);
    $table->add_data($data);
}

$table->print_html();

if ($perpage == SHOW_ALL_PAGE_SIZE) {
    echo '<div id="showall"><a href="'.$baseurl.'&amp;perpage='.DEFAULT_PAGE_SIZE.'">'.
        get_string('showperpage', '', DEFAULT_PAGE_SIZE).'</a></div>';
}
else if ($matchcount > 0 && $perpage < $matchcount) {
    echo '<div id="showall"><a href="'.$baseurl.'&amp;perpage='.SHOW_ALL_PAGE_SIZE.'">'.
        get_string('showall', '', $matchcount).'</a></div>';
}

echo '</div>';

//Print the footer
admin_externalpage_print_footer();

?>