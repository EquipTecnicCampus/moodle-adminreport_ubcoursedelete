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
 * Cron functions for UB Deleted courses report
 *
 * @package   adminreport_ubcoursedelete
 * @copyright 2010 Yolanda Ordonez
 * @author    Yolanda Ordonez <yordonez@ub.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version   1.0
 *
 */

require_once(dirname(__FILE__).'/backuplib.php');
require_once($CFG->libdir.'/filelib.php');

/**
 * Function to be run periodically according to the moodle cron
 * It prepares all the info and execute functions as necessary
 *
 * @return  bool true if all the actions succeeded.
 **/
function report_ubcoursedelete_cron() {
    $status = true;

    mtrace('    Checking cron status','...');
    if (ubcoursedelete_cron_needed()){
        //Mark sche_running
        $status = set_config('report_ubcoursedelete_scherunning', '1');

        //delete everything from ubcoursedelete tables related to deleted courses
        if ($status) {
            mtrace('    Deleting orphan courses');
            $status = ubcoursedelete_delete_orphan_courses();
        }

        //delete everything from ubcoursedelete tables related to deleted courses
        if ($status) {
            mtrace('    Deleting old data');
            $status = ubcoursedelete_delete_old_data();
        }

        //Deletes old backup files that are no longer recorded on logs table
        if ($status) {
            /// don't run every time it adds load to the server and shouldn't happen
            /// we'll use a random number to randomly choose the percentage of times we should run these jobs.
            $random100 = rand(0,100);
            if ($random100 < 20) {     // Approximately 20% of the time.
                $status = ubcoursedelete_delete_old_files();
            }
        }

        //Now we get a list of courses in quarantine
        if ($status) {
            mtrace('    Checking courses');
            $quarantinedays = get_config(null,'report_ubcoursedelete_quarantinedays');
            if (!empty($quarantinedays)) {
                $select = 'status = '.UBCOURSEDELETE_STATUS_QUARANTINED.
                        ' AND quarantinetime + '.($quarantinedays*86400).' < '.time();
                $quarantines = get_records_select('ubcoursedelete', $select);
                if (!empty($quarantines)) {
                    foreach ($quarantines as $quarantine) {
                        $status = ubcoursedelete_delete_course($quarantine->courseid, false);
                    }
                }
            }
        }
    }
    if ($status){
        $now = time();
        $nextschetime = ubcoursedelete_cron_next_execution($now);
        $status = set_config('report_ubcoursedelete_nextschetime', $nextschetime);
    }
    $status = set_config('report_ubcoursedelete_scherunning', '0');

    return $status;
}

/**
 * Check if we need the cron functions to be run.
 * This function checks in the report's settings the schedule time to execute the cron functions
 * and detect that there isn't another cron running. See {@link schedule_backup_cron()}
 *
 * @return  bool true if the cron is needed
 **/
function ubcoursedelete_cron_needed() {
    global $CFG;

    $needed = false;

    //First of all, we have to see if the scheduled is active and detect
    //that there isn't another cron running
    $scherunning = get_config(null, 'report_ubcoursedelete_scherunning');
    if(empty($CFG->report_ubcoursedelete_quarantinedays)) {
        mtrace("INACTIVE");
        return false;
    } else if (!empty($scherunning)) {
        mtrace("RUNNING");
        //Now check if it's a really running task or something very old looking
        //for info in ubcoursedelete_log to unlock status as necessary
        $timetosee = 1800;   //Half an hour looking for activity
        $timeafter = time() - $timetosee;
        $select = "action = 'cron' AND time > $timeafter";
        $numofrec = count_records_select('ubcoursedelete_log', $select);
        if (!$numofrec) {
            $timetoseemin = $timetosee/60;
            mtrace("    No activity in last ".$timetoseemin." minutes. Unlocking status");
        } else {
            mtrace("    Scheduled quarantine seems to be running. Execution delayed");
            return false;
        }
    } else {
        mtrace("OK");
    }

    //Get now
    $now = time();
    //Get scheduled time
    $nextschetime = get_config(null, 'report_ubcoursedelete_nextschetime');
    if (!$needed = $now > $nextschetime) {
        mtrace("    No cron required");
    }

    return $needed;
}


/**
 *
 * This function returns the next future GMT time to execute
 * the cron actions based in the configuration of the report
 *
 * @param   string  $now the timestamp to process
 * @return  string the timestamp for the next cron execution
 **/
function ubcoursedelete_cron_next_execution($now) {
    global $CFG;

    $result = -1;
    if (!$admin = get_admin()) {
        return $result;
    }
    $timezone = $admin->timezone;
    //Get today's midnight GMT
    $midnight = usergetmidnight($now, $timezone);
    //Calculate distance
    $dist = ($CFG->report_ubcoursedelete_schehour*3600) +      //Hours distance
            ($CFG->report_ubcoursedelete_scheminute*60);       //Minutes distance
    $result = $midnight + $dist;
    //If that time is past, call the function recursively to obtain the next valid day
    if ($result > 0 && $result < time()) {
        $result = ubcoursedelete_cron_next_execution($now + 86400);
    }
    return $result;
}


/**
 * Mark as deleted in ubcoursedelete tables the records related to deleted courses
 *
 * @return  bool true if all the actions succeeded.
 **/
function ubcoursedelete_delete_orphan_courses() {
    $status = true;

    $select = 'status <> '.UBCOURSEDELETE_STATUS_DELETED.' AND courseid not in (SELECT id FROM mdl_course)';
    $orphanquarantines = get_records_select('ubcoursedelete', $select);
    if (!empty($orphanquarantines)) {
        foreach ($orphanquarantines as $quarantine) {
            $quarantine->timemodified = time();
            $quarantine->status = UBCOURSEDELETE_STATUS_DELETED;
            update_record('ubcoursedelete', addslashes_recursive($quarantine));
            // Try to fing more info in the logs
            $select = "module = 'course' AND action= 'delete' AND url= 'view.php?id=$quarantine->courseid'";
            if ($log = get_record_select('log', $select)) {
                $userid = $log->userid;
                $time = $log->time;
                $date =  mktime(0, 0, 0, date('m',$time)  , date('d',$time), date('Y',$time));
                $info = html_entity_decode('/course/report/log/index.php?chooselog=1&user='.$userid.
                        '&date='.$date.'&modaction=delete&logformat=showashtml');
                if ($courselogid = ubcoursedelete_add_log($quarantine, 'delete', $info, $userid)) {
                    set_field('ubcoursedelete_log', 'time', $time, 'id', $courselogid);
                }
            }
        }
    }

    return $status;
}


/**
 * Deletes old data from ubcoursedelete tables and the its backup files
 *
 * @return  bool true if all the actions succeeded.
 **/
function ubcoursedelete_delete_old_data() {
    global $CFG;

    $status = true;

    if (empty($CFG->report_ubcoursedelete_loglifedays)){
        return true;
    }

    $now = time();
    $loglifetime =  86400 * $CFG->report_ubcoursedelete_loglifedays;
    $select = "status = '".UBCOURSEDELETE_STATUS_DELETED."' AND timemodified + $loglifetime < $now ";
    $oldquarantines = get_records_select('ubcoursedelete', $select);
    $count = 0;
    $path = get_config(null, 'report_ubcoursedelete_backupdestination');
    if (!empty($oldquarantines)) {
        foreach ($oldquarantines as $quarantine) {
            //Get the backup logs from the courselog
            $select = "quarantineid = $quarantine->id AND action= 'backup'";
            $quarantinelogs = get_records_select('ubcoursedelete_log', $select);
            if (!empty($quarantinelogs)) {
                foreach ($quarantinelogs as $quarantinelog) {
                    //Delete the backup file
                	$filename = $path.'/'.$quarantinelog->info;
                    fulldelete($filename);
                }
                //Delete the logs
                delete_records('ubcoursedelete_log', 'quarantineid', $quarantine->id, 'action', 'backup');
            }
            //Delete the quarantine record
            if (delete_records('ubcoursedelete', 'id', $quarantine->id)){
                $count++;
            }
        }
        //mtrace number of records deleted
        mtrace('        '.$count.' old quarantine records deleted');
    }

    return $status;
}

/**
 * Deletes old backup files that are no longer recorded on logs table
 *
 * @return  bool true if all the actions succeeded.
 **/
function ubcoursedelete_delete_old_files() {
    global $CFG;

    $status = true;
    $count = 0;

    //Get all the backup filenames from the course logs
    $backups = get_records_menu('ubcoursedelete_log', 'action', 'backup', 'info', 'id, info');

    //Get all the files from the quarantine backup folder
    $backupdir = get_config(null, 'report_ubcoursedelete_backupdestination');
    if (is_dir($backupdir)) {
        $list = list_directories_and_files($backupdir);
        if ($list) {
            //Iterate
            foreach ($list as $key=>$filename) {
                $file = $backupdir.'/'.$filename;
                if (!(is_array($backups) and in_array($filename, $backups))) {
                debugging('The file '.$filename.' is not on the database and needs to be deleted.', DEBUG_DEVELOPER);
                    if (is_file($file) and fulldelete($file)) {
                        $count++;
                    }
                }
            }
        }
    }

    if ($count > 0) {
        mtrace('        '.$count.' old backup files deleted');
    }

    return $status;
}
?>