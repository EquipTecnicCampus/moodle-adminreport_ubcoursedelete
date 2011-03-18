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
 * UB Deleted courses report list all the courses that have been deleted or quarantine
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
$chooselog = optional_param('chooselog', 0, PARAM_INT);
$timefrom  = optional_param('timefrom', 0, PARAM_INT);                  // how far back to look...
$status    = optional_param('status', 0, PARAM_INT);                    // a status as recorded in the quarantine table
$page      = optional_param('page', 0, PARAM_INT);                      // which page to show
$perpage   = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);   // how many per page

// Print the header.
admin_externalpage_setup('reportubcoursedelete');
admin_externalpage_print_header();

//Display a form to control the report.
print_heading(get_string('chooselogs') .':');
ubcoursedelete_print_log_selector_form($status, $timefrom);

//If the URL contains all the parameters needed to generate the report,
//get some data out of the database and display the report
if (!empty($chooselog)) {
    $baseurl =  $CFG->wwwroot.'/admin/report/ubcoursedelete/index.php?chooselog=1&amp;status='.
                $status.'&amp;timefrom='.$timefrom.'&amp;perpage='.$perpage;

    $strcourse = get_string('course');
    $strtime = get_string('time');
    $strquarantinedby =  get_string('quarantinedby', 'report_ubcoursedelete');
    $strcategory = get_string('category');
    $strteachers = get_string('teachers', 'report_ubcoursedelete');
    $strstatus = get_string('status', 'report_ubcoursedelete');
    $stractions = get_string('actions', 'report_ubcoursedelete');

    $table = new flexible_table('quarantine-report-'.$status.$timefrom);

    $table->define_columns(array('coursename', 'quarantinetime', 'quarantineby', 'cc.name',
                                'teachers', 'status', 'actions'));
    $table->define_headers(array($strcourse, $strtime, $strquarantinedby, $strcategory,
                                $strteachers, $strstatus, $stractions));
    $table->define_baseurl($baseurl);

    $table->set_attribute('cellpadding','5');
    $table->set_attribute('class', 'generaltable generalbox reporttable');

    $table->sortable(true, 'quarantinetime', SORT_DESC);
    $table->no_sorting('teachers', 'actions');

    $table->set_control_variables(array(
                                        TABLE_VAR_SORT    => 'ssort',
                                        TABLE_VAR_HIDE    => 'shide',
                                        TABLE_VAR_SHOW    => 'sshow',
                                        TABLE_VAR_IFIRST  => 'sifirst',
                                        TABLE_VAR_ILAST   => 'silast',
                                        TABLE_VAR_PAGE    => 'spage'
                                        ));
    $table->setup();

    $statussql = '';
    if (!empty($status)){
        $statussql = ' AND q.status = '.$status;
    }

    $sql = "SELECT q.id, q.courseid, q.coursename, q.oldcategoryid, cc.name, q.oldteachers, q.status,
                   q.quarantineby, u.firstname, u.lastname, q.quarantinetime, q.timemodified
                  FROM {$CFG->prefix}ubcoursedelete q
                       JOIN {$CFG->prefix}user u    ON u.id = q.quarantineby
                       LEFT OUTER JOIN {$CFG->prefix}course_categories cc   ON cc.id = q.oldcategoryid
            WHERE q.quarantinetime > ".$timefrom.$statussql;

    if ($table->get_sql_where()) {
        $sql .= ' AND '.$table->get_sql_where(); //initial bar
    }
    if ($table->get_sql_sort()) {
        $sql .= ' ORDER BY '.$table->get_sql_sort();
    }

    $countsql = "SELECT COUNT(DISTINCT(q.id))
                    FROM {$CFG->prefix}ubcoursedelete q
                WHERE q.quarantinetime > ".$timefrom.$statussql;

    $totalcount = count_records_sql($countsql);

    if ($table->get_sql_where()) {
        $matchcount = count_records_sql($countsql.' AND '.$table->get_sql_where());
    } else {
        $matchcount = $totalcount;
    }

    $table->pagesize($perpage, $matchcount);

    echo '<div id="ubcoursedeletereport">';

    if (!$quarantines = get_records_sql($sql, $table->get_page_start(), $table->get_page_size())) {
        $quarantines = array(); // tablelib will handle saying 'Nothing to display' for us.
    }

    $data = array();

    $a->count = $totalcount;
    if ($totalcount == 1){
        $a->items = get_string('course');
    } else {
        $a->items = get_string('courses');
    }

    if ($matchcount != $totalcount) {
        $a->count = $matchcount.'/'.$a->count;
    }

    echo '<h3>'.get_string('counteditems', '', $a).'</h3>';

    $canrecover = has_capability('report/ubcoursedelete:recover', $sitecontext);
    $canbackup = has_capability('report/ubcoursedelete:backup', $sitecontext);
    $candelete = has_capability('report/ubcoursedelete:delete', $sitecontext);
    $canviewdetails = has_capability('moodle/user:viewdetails', $sitecontext);
    $strrecover = get_string('recovercourse', 'report_ubcoursedelete');
    $strviewlogs = get_string('viewlogs', 'report_ubcoursedelete');
    $strbackup = get_string('coursebackup');
    $strdelete = get_string('deletecourse');

    foreach ($quarantines as $q) {
        $loglink = '<a href="'.$CFG->wwwroot.'/admin/report/ubcoursedelete/courselog.php?id='.$q->id.'">'.
                '<img src="'.$CFG->pixpath.'/i/log.gif" class="icon" alt="'.$strviewlogs.'" title="'.
                $strviewlogs.'" /></a>';
        $recoverlink = '';
        $backuplink = '';
        $deletelink = '';
        if ($q->status == UBCOURSEDELETE_STATUS_QUARANTINED) {
            if ($canrecover) {
                $recoverlink = '<a href="'.$CFG->wwwroot.'/admin/report/ubcoursedelete/recover.php?id='.$q->courseid.
                        '"><img src="'.$CFG->pixpath.'/i/restore.gif" class="icon" alt="'.$strrecover.'" title="'.
                        $strrecover.'"/></a>';
            }
            if ($canbackup) {
                $backuplink = '<a href="'.$CFG->wwwroot.'/admin/report/ubcoursedelete/backup.php?id='.$q->courseid.
                        '"><img src="'.$CFG->pixpath.'/i/backup.gif" class="icon" alt="'.$strbackup.'" title="'.
                        $strbackup.'"/></a>';
            }
            if ($candelete) {
                $deletelink = '<a href="'.$CFG->wwwroot.'/admin/report/ubcoursedelete/delete.php?id='.$q->courseid.
                        '"><img src="'.$CFG->pixpath.'/t/delete.gif" class="icon" alt="'.$strdelete.'" title="'.
                        $strdelete.'"/></a>';
            }
        }
        $actions = $loglink.$recoverlink.$backuplink.$deletelink;
        if ($q->status == UBCOURSEDELETE_STATUS_DELETED) {
            $course = $q->coursename;
        } else {
            $course = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$q->courseid.'">'.$q->coursename.'</a>';
        }
        if ($canviewdetails) {
            $quarantineby = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$q->quarantineby.'">'.fullname($q).'</a>';
        } else {
            $quarantineby = fullname($q);
        }
        $category =  '<a href="'.$CFG->wwwroot.'/course/category.php?id='.$q->oldcategoryid.'">'.$q->name.'</a>';
        $teachers = ubcoursedelete_get_teachers($q->oldteachers);
        $data = array($course, userdate($q->quarantinetime), $quarantineby, $category,
                      ubcoursedelete_get_teachers_name($teachers, true),
                      ubcoursedelete_get_status_name($q->status), $actions);
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

}

//Print the footer
admin_externalpage_print_footer();

?>