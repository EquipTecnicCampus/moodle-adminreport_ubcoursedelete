<?PHP
/// This file is part of Moodle - http://moodle.org/
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
 * Library of functions related to the backups for UB Deleted courses report
 *
 * @package   adminreport_ubcoursedelete
 * @copyright 2010 Yolanda Ordonez
 * @author    Yolanda Ordonez <yordonez@ub.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version   1.0
 *
 */
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/backup/backup_scheduled.php');
require_once ($CFG->dirroot.'/backup/lib.php');
require_once ($CFG->dirroot.'/backup/backuplib.php');

/**
 * Is the backup active?
 * If the backup is not active the quarantined courses will be completely deleted
 *
 * @return  bool true if the backup is active
 */

function ubcoursedelete_is_backup_active() {
    global $CFG;

    $scheactive = false;

    $backudir = get_config(null, 'report_ubcoursedelete_backupdestination');
    $backup_config =  backup_get_config();
    if (isset($backup_config->backup_sche_active)) {
        $scheactive = $backup_config->backup_sche_active;
    }

    return (!empty($backudir) and !empty($scheactive));
}

/**
 * Backup a course from the quarantine and keeps the backup file in a directory.
 * It follows the same schema as: {@link schedule_backup_launch_backup()}
 *
 * @param   mixed   $courseorid The id of the course or course object to delete.
 * @param   bool    $showfeedback show all the informative messages nicely formated
 * @return  bool true if all the actions succeeded.
 */

function ubcoursedelete_backup_course($courseorid, $showfeedback = true) {
    global $CFG;

    if (!ubcoursedelete_is_backup_active()) {
        $strbackupinactive = get_string('backupinactive', 'report_ubcoursedelete');
        if ($showfeedback) {
            notify($strbackupinactive);
        } else {
            mtrace($strbackupinactive);
        }
        return false;
    }

    if (is_object($courseorid)) {
        $courseid = $courseorid->id;
        $course   = $courseorid;
    } else {
        $courseid = $courseorid;
        if (!$course = get_record('course', 'id', $courseid)) {
            return false;
        }
    }

    // frontpage course can not be deleted!!
    if ($courseid == SITEID) {
        return false;
    }
    if (!$quarantine = get_record('ubcoursedelete', 'courseid', $courseid)) {
        return false;
    }

    //Launch backup
    $preferences = false;
    $status = false;
    $strexecutingbackup = get_string('executingbackup', 'report_ubcoursedelete');
    $strcheckingparams = get_string('checkingparams', 'report_ubcoursedelete');
    $strexecutingcopying = get_string('executingcopying', 'report_ubcoursedelete');
    $strdeletingoldbackup = get_string('deletingoldbackup', 'report_ubcoursedelete');
    $strbackupfinished = get_string('backupfinished');
    $strbackuperror = get_string('backuperror', 'report_ubcoursedelete');
    $straddtolog =  get_string('addtolog', 'report_ubcoursedelete');

    if ($showfeedback) {
        notify($strexecutingbackup, 'notifysuccess', 'left');
        notify($strcheckingparams, 'notifysuccess', 'left');
    } else {
        mtrace('    '.$strexecutingbackup);
        mtrace('        '.$strcheckingparams);
    }

    $preferences = schedule_backup_course_configure($course);
    // We change the backup destination to our own directory
    if (!empty($CFG->report_ubcoursedelete_backupdestination)) {
        $preferences->backup_destination = $CFG->report_ubcoursedelete_backupdestination;
    }

    if ($preferences) {
        if ($showfeedback) {
            notify($strexecutingcopying, 'notifysuccess', 'left');
        } else {
            mtrace('        '.$strexecutingcopying);
        }
        $status = schedule_backup_course_execute($preferences);
    }
    if ($status && $preferences) {
        //Only if the backup_sche_keep is set
        if ($preferences->backup_keep) {
            if ($showfeedback) {
                notify($strdeletingoldbackup, 'notifysuccess', 'left');
            } else {
                mtrace('        '.$strdeletingoldbackup);
            }
            $status = schedule_backup_course_delete_old_files($preferences);
        }
    }
    if ($status && $preferences) {
        //log the action on the report log
        if ($showfeedback) {
            notify($straddtolog, 'notifysuccess', 'left');
        } else {
            mtrace('        '.$straddtolog);
        }
        $status = ubcoursedelete_add_log($quarantine, 'backup', $preferences->backup_name);
    }
    if ($status && $preferences) {
        if ($showfeedback) {
            notify($strbackupfinished, 'notifysuccess', 'left');
        } else {
            mtrace('        '.$strbackupfinished);
        }
    } else {
        if ($showfeedback) {
            notify($strbackuperror, 'notifyproblem', 'left');
        } else {
            mtrace('        '.$strbackuperror);
        }
    }

    return $status && $preferences;
}

/**
 * Deletes a course from the quarantine keeping a backup file in a directory.
 *
 * @param   mixed   $courseorid The id of the course or course object to delete.
 * @param   bool    $showfeedback show all the informative messages nicely formated
 * @return  bool true if all the actions succeeded.
 */

function ubcoursedelete_delete_course($courseorid, $showfeedback = true) {
    global $CFG;

    $status = false;

    if (is_object($courseorid)) {
        $courseid = $courseorid->id;
        $course   = $courseorid;
    } else {
        $courseid = $courseorid;
        if (!$course = get_record('course', 'id', $courseid)) {
            return false;
        }
    }
    // frontpage course can not be deleted!!
    if ($courseid == SITEID) {
        return false;
    }
    if (!$quarantine = get_record('ubcoursedelete', 'courseid', $courseid)) {
        return false;
    }

    if (ubcoursedelete_is_backup_active()) {
        //Launch backup
        $status = ubcoursedelete_backup_course($course, $showfeedback);
        if (!$status) {
            return false;
        }
    } else {
        $strbackupinactive = get_string('backupinactive', 'report_ubcoursedelete');
        if ($showfeedback) {
            notify($strbackupinactive, 'notifyproblem', 'left');
        } else {
            mtrace($strbackupinactive);
        }
    }

    //Launch delete
    $strexecutingdelete = get_string('executingdelete', 'report_ubcoursedelete');
    $strupdatingparams = get_string('updatingparams', 'report_ubcoursedelete');
    $straddtolog =  get_string('addtolog', 'report_ubcoursedelete');

    if ($showfeedback) {
        notify($strexecutingdelete, 'notifysuccess', 'left');
    } else {
        mtrace('    '.$strexecutingdelete);
    }

    $status = delete_course($course, $showfeedback);
    fix_course_sortorder($course->category); //update course count in categories

    //update quarantine parameters
    if ($status) {
        if ($showfeedback) {
            notify($strupdatingparams, 'notifysuccess', 'left');
        } else {
            mtrace('        '.$strupdatingparams);
        }
        $quarantine->timemodified = time();
        $quarantine->status = UBCOURSEDELETE_STATUS_DELETED;
        $status = update_record('ubcoursedelete', addslashes_recursive($quarantine));
    }
    //log the action on the report log and in the mdl_log
    if ($status) {
        if ($showfeedback) {
            notify($straddtolog, 'notifysuccess', 'left');
        } else {
            mtrace('        '.$straddtolog);
        }
        $status = ubcoursedelete_add_log($quarantine, 'delete');
        add_to_log(SITEID, 'course', 'delete', 'view.php?id='.$course->id, "$course->fullname (ID $course->id)");
    }
    return $status;
}
?>