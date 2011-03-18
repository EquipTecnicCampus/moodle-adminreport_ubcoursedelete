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
 * Library of functions and constants for UB Deleted courses report
 *
 * @package   adminreport_ubcoursedelete
 * @copyright 2010 Yolanda Ordonez
 * @author    Yolanda Ordonez <yordonez@ub.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version   1.0.1
 *
 */

define('UBCOURSEDELETE_STATUS_QUARANTINED', 1);
define('UBCOURSEDELETE_STATUS_DELETED', 2);
define('UBCOURSEDELETE_STATUS_RECOVERED', 3);

$statusoptions = array(
        UBCOURSEDELETE_STATUS_QUARANTINED => get_string('quarantined', 'report_ubcoursedelete'),
        UBCOURSEDELETE_STATUS_DELETED => get_string('deleted', 'report_ubcoursedelete'),
        UBCOURSEDELETE_STATUS_RECOVERED => get_string('recovered', 'report_ubcoursedelete')
);

/**
 * Can the current user quarantine this course?
 *
 * @param   int   $courseid The id of the course
 * @return  bool true if the user can quarantine the course
 */

function  ubcoursedelete_can_quarantine_course($courseid) {

    $context = get_context_instance(CONTEXT_COURSE, $courseid);
    $canquarantine = has_capability('report/ubcoursedelete:quarantine', $context);

    return $canquarantine;
}

/**
 * Can the current user recover this course?
 *
 * @param   int   $courseid The id of the course
 * @return  bool true if the user can recover the course
 */
function  ubcoursedelete_can_recover_course($courseid) {

    $context = get_context_instance(CONTEXT_COURSE, $courseid);
    $canrecover = has_capability('report/ubcoursedelete:recover', $context);

    return $canrecover;
}

/**
 * Can the current user backup this course?
 *
 * @param   int   $courseid The id of the course
 * @return  bool true if the user can recover the course
 */
function  ubcoursedelete_can_backup_course($courseid) {

    $context = get_context_instance(CONTEXT_COURSE, $courseid);
    $canbackup = has_capability('report/ubcoursedelete:backup', $context);

    return $canbackup;
}

/**
 * Can the current user delete this course?
 *
 * @param   int   $courseid The id of the course
 * @return  bool true if the user can delete the course
 */
function  ubcoursedelete_can_delete_course($courseid) {

    $context = get_context_instance(CONTEXT_COURSE, $courseid);
    $candelete = has_capability('report/ubcoursedelete:delete', $context);

    return $candelete;
}

/**
 * Checks if a course is already in quarantine.
 * It checks out if the course is in the database with the QUARANTINED status,
 * a recovered course will stay in the table with other status.
 *
 * @param   int   $courseid The id of the course.
 * @return  bool true if the course is already quarantine
 */

function ubcoursedelete_in_quarantined($courseid) {
    $result = record_exists('ubcoursedelete', 'courseid', $courseid, 'status', UBCOURSEDELETE_STATUS_QUARANTINED);
    return $result;
}


/**
 * Checks if a course is a metacourse or a metacourse child.
 *
 * @param   mixed   $courseorid The id of the course or course object.
 * @return  bool true if the course is a metacourse or a metacourse child
 */

function ubcoursedelete_in_metacourse($courseorid) {
    $result = false;

    if (is_object($courseorid)) {
        $courseid = $courseorid->id;
        $course   = $courseorid;
    } else {
        $courseid = $courseorid;
        if (!$course = get_record('course', 'id', $courseid)) {
            return false;
        }
    }
    //The course is a parent
    if ($course->metacourse) {
        return true;
    }
    //The course is a child course
    $result = record_exists('course_meta', 'child_course', $courseid);

    return $result;
}

/**
 * Quarantine a course.
 *
 * @param   mixed   $courseorid The id of the course or course object to delete.
 * @return  bool true if all the actions succeeded.
 */

function ubcoursedelete_quarantine_course($courseorid) {
    global $CFG, $USER;

    $success = true;

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

    $oldcategory = $course->category;
    $teachers = ubcoursedelete_get_course_teachers($courseid);

    //new quarantine object
    $quarantine = (object) null;
    $quarantine->courseid = $courseid;
    $quarantine->coursename = $course->fullname;
    $quarantine->oldcategoryid = $course->category;
    $quarantine->oldteachers = ubcoursedelete_serialize_teachers($teachers);
    $quarantine->status = UBCOURSEDELETE_STATUS_QUARANTINED;
    $quarantine->quarantineby = $USER->id;
    $quarantine->quarantinetime =  time();

    //If the record already exist we update the data
    if ($oldquarantine = get_record('ubcoursedelete', 'courseid', $courseid)) {
        debugging('course already in quarantine we update the data', DEBUG_DEVELOPER);
        $quarantine->timemodified = time();
        $quarantine->id = $oldquarantine->id;
        $success = update_record('ubcoursedelete', addslashes_recursive($quarantine));
    } else {
        $quarantineid = insert_record('ubcoursedelete', addslashes_recursive($quarantine));
        $quarantine->id = $quarantineid;
    }

    //update course parameters
    debugging('updating course parameters', DEBUG_DEVELOPER);
    $course->category = $CFG->report_ubcoursedelete_quarantinecategory;
    $course->visible = 0;
    $course->timemodified = time();
    $success = update_record('course', addslashes_recursive($course));
    // Update the course context
    $context   = get_context_instance(CONTEXT_COURSE, $course->id);
    $newparent = get_context_instance(CONTEXT_COURSECAT, $course->category);
    context_moved($context, $newparent);

    //unenrole teachers
    if ($success) {
        debugging('unenrole teachers', DEBUG_DEVELOPER);
        $success = ubcoursedelete_unenrole_teachers($teachers, $courseid);
    }
    //send notification
    if ($success) {
        debugging('sending notification', DEBUG_DEVELOPER);
        $success = ubcoursedelete_send_notification($quarantine, $teachers);
    }
    //log the action on the report log
    if ($success) {
        debugging('log the action on the report log', DEBUG_DEVELOPER);
        $success = ubcoursedelete_add_log($quarantine, 'quarantine');
    }

    //update course count and order in categories
    fix_course_sortorder($oldcategory);
    fix_course_sortorder($course->category);

    return $success;
}


/**
 * Send quarantine notification
 *
 * @param   mixed   $quarantine object
 * @param   mixed   $teachers object
 * @return  bool true if all the actions succeeded.
 */

function ubcoursedelete_send_notification($quarantine, $teachers) {
    global $CFG;

    $success = true;

    if (!empty($CFG->report_ubcoursedelete_notifyrole)) {
        $context = get_context_instance(CONTEXT_COURSE, $quarantine->courseid);

        $to = array();
        $roles = split(',', $CFG->report_ubcoursedelete_notifyrole);

        //check if we have to send the notification to any of the former teachers
        if (is_array($teachers) && count($teachers)) {
            foreach ($teachers as $teacher) {
                if (in_array($teacher->roleid, $roles)) {
                    $to[$teacher->id] = $teacher;
                }
            }
        }

        $rusers = get_role_users($roles, $context, true);
        if (is_array($rusers) && count($rusers)) {
            foreach ($rusers as $user) {
                $to[$user->id] = $user;
            }
        }

        if (count($to)) {
            $from = get_record('user', 'id', $quarantine->quarantineby);
            $subject = get_string('quarantinenotify', 'report_ubcoursedelete');
            $a = new object();
            $a->user = fullname($from);
            $a->course = $quarantine->coursename;
            $a->time = userdate($quarantine->quarantinetime);
            $a->link = '';
            foreach ($to as $user) {
                if (has_capability('moodle/course:view', $context, $user->id)){
                    $a->link = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$quarantine->courseid.'">'.
                                $CFG->wwwroot.'/course/view.php?id='.$quarantine->courseid.'</a>';
                }
                $messagehtml = get_string('quarantinenotifyemail', 'report_ubcoursedelete', $a);
                $messagetext = strip_tags($messagehtml);

                if (email_to_user($user, $from, $subject, $messagetext, $messagehtml)) {
                    debugging('Notification sent to: '. $user->id.'<br />Subject: '.$subject.'<br />message: '.$messagehtml, DEBUG_DEVELOPER);
                } else {
                    debugging('Error sending notification to: '. $user->id.'<br />Subject: '.$subject.'<br />message: '.$messagehtml, DEBUG_DEVELOPER);
                    $success = false;
                }
            }
        }
    }
    return $success;
}


/**
 * Log the action on the report log
 *
 * @param   mixed   $quarantine object
 * @param   string  $action The action to log
 * @param   string  $info   Additional description information
 * @param   string  $user   If log regards $user other than $USER
 * @return  mixed   return the "id" field or false if an error occurs
 */

function ubcoursedelete_add_log($quarantine, $action, $info = '', $user=0) {
    global $USER;

    $success = true;
    $userid = 0;

    if ($user) {
        $userid = $user;
    } else if (!empty($USER->id)) {
        $userid = $USER->id;
    }

    $log = (object) null;
    $log->quarantineid = $quarantine->id;
    $log->courseid = $quarantine->courseid;
    $log->userid = $userid;
    $log->time =  time();
    $log->action = $action;
    $log->info = addslashes($info);

    $success = insert_record('ubcoursedelete_log', $log);

    return $success;
}

/**
 * Recover a course.
 *
 * @param   mixed   $courseorid The id of the course or course object to recover.
 * @return  bool true if all the actions succeeded.
 */

function ubcoursedelete_recover_course($courseorid) {
    global $CFG, $USER;

    $success = true;

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
        debugging('course not found in quarantine table', DEBUG_DEVELOPER);
        return false;
    }

    $oldcategory = $course->category;
    $teachers = ubcoursedelete_get_teachers($quarantine->oldteachers);

    //update quarantine parameters
    if ($success) {
        debugging('updating quarantine parameters', DEBUG_DEVELOPER);
        $quarantine->timemodified = time();
        $quarantine->status = UBCOURSEDELETE_STATUS_RECOVERED;
        $success = update_record('ubcoursedelete', addslashes_recursive($quarantine));
    }
    //update course parameters
    if ($success) {
        debugging('updating course parameters', DEBUG_DEVELOPER);
        $course->category = $quarantine->oldcategoryid;
        $course->visible = 1;
        $course->timemodified = time();
        $success = update_record('course', addslashes_recursive($course));
        // Update the course context
        $context   = get_context_instance(CONTEXT_COURSE, $course->id);
        $newparent = get_context_instance(CONTEXT_COURSECAT, $course->category);
        context_moved($context, $newparent);

    }
    //enrole teachers
    if ($success) {
        debugging('enrole teachers', DEBUG_DEVELOPER);
        $success = ubcoursedelete_enrole_teachers($teachers, $courseid);
    }
    //log the action on the report log
    if ($success) {
        debugging('log the action on the report log', DEBUG_DEVELOPER);
        $success =  ubcoursedelete_add_log($quarantine, 'recovered');
    }

    //update course count and order in categories
    fix_course_sortorder($oldcategory);
    fix_course_sortorder($course->category);

    return $success;
}

//////// TEACHER FUNCTIONS


/**
 * Get the teachers from a course.
 * teachers are the users with the role defined by the parameter
 * $CFG->report_ubcoursedelete_quarantinerole in the course context
 * only, not in the parent contexts.
 * If the parameter is empty an empty array is returned
 *
 * @param   int   $courseid The id of the course.
 * @return  mixed array of course teachers
 */

function ubcoursedelete_get_course_teachers($courseid) {
    global $CFG;

    $teachers =  array();

    // no roles to quarantine and unenrole
    if (empty($CFG->report_ubcoursedelete_quarantinerole)) {
        return $teachers;
    }

    $context = get_context_instance(CONTEXT_COURSE, $courseid);
    $idroles = explode(',', $CFG->report_ubcoursedelete_quarantinerole);
    $fields = "ra.roleid || ':' || u.id as uniqueid,
                u.id, ra.roleid, u.confirmed, u.username, u.firstname, u.lastname, u.lang, u.timezone,
                u.maildisplay, u.mailformat, u.maildigest, u.email, u.emailstop, r.name as rolename";

    $teachers = get_role_users($idroles, $context, false, $fields);

    return $teachers;
}

/**
 * Serialize the teachers id
 * Get a string containing the teachers id from an array of teachers objects
 *
 * @param   mixed   $teachers An array of teachers objects
 * @return  string of course teachers
 */

function ubcoursedelete_serialize_teachers($teachers) {
    $teacherser = '';

    if (!empty($teachers)) {
        foreach ($teachers as $teacher) {
            $teacherser .= $teacher->roleid.':'.$teacher->id.',';
        }
        $teacherser = rtrim($teacherser, ', ');
    }

    return $teacherser;
}


/**
 * Get the array of teachers objects from the serialized teachers string
 *
 * @param   string  $teacherser the serialized theachers form the course
 * @return  mixed an array of teachers objects with the user and role data
 */

function ubcoursedelete_get_teachers($teacherser) {
    $teachers =  array();

    if (empty($teacherser)) {
        return $teachers;
    }

    $fields = 'id, confirmed, username, firstname, lastname, lang, timezone, '.
            'maildisplay, mailformat, maildigest, email, emailstop';
    $assigments = explode(',',$teacherser);
    foreach ($assigments as $assigment) {
        $aux = explode(':',$assigment);
        $roleid = $aux[0];
        $userid = $aux[1];
        $select = ' id = '.$userid;
        if ($teacher = get_record_select('user', $select, $fields)) { //we don't need all the user object
            $teacher->roleid = $roleid;
            $teachers[$assigment] = $teacher;
        }
    }

    return $teachers;
}


/**
 * Get the names of teachers from the array of teachers objects
 *
 * @param   mixed   $teachers an array of teachers objects
 * @return  string the name of the theachers
 */

function ubcoursedelete_get_teachers_name($teachers, $showlink = false) {
    global $CFG;

    $teachersname = '';
    $teachersnames = array();

    if (!empty($teachers)) {
        $context = get_context_instance(CONTEXT_SYSTEM);
        foreach ($teachers as $teacher) {
            //$teachersname .= fullname($teacher).', ';
            // only check the viewdetails capability the page will check what the user can actually view
            if ($showlink and has_capability('moodle/user:viewdetails', $context)) {
                $name = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$teacher->id.'">'.fullname($teacher).'</a>';
            } else {
                $name = fullname($teacher);
            }
            $teachersnames[$teacher->id] = $name;
        }
        //$teachersname = rtrim($teachersname, ', ');
        $teachersname = implode(', ', $teachersnames);
    }

    return $teachersname;

}

/**
 * Unenrole the teachers from a course
 *
 * @param   mixed   $teachers An array of teachers objects
 * @param   int     $courseorid The id of the course.
 * @return  bool true if all the actions succeeded.
 */

function ubcoursedelete_unenrole_teachers($teachers, $courseid) {
    $success = true;

    if (empty($teachers)) {
        return $success;
    }

    $context = get_context_instance(CONTEXT_COURSE, $courseid);
    foreach ($teachers as $teacher) {
        if (!role_unassign($teacher->roleid, $teacher->id, 0, $context->id)) {
           $success = false;
        }
    }

    return $success;
}


/**
 * Enrole the teachers in a course
 *
 * @param   mixed   $teachers An array of teachers objects, it must contains the roleid
 * @param   int     $courseorid The id of the course.
 * @return  bool true if all the actions succeeded.
 */

function ubcoursedelete_enrole_teachers($teachers, $courseid) {
    $success = true;

    if (empty($teachers)) {
        return $success;
    }

    $context = get_context_instance(CONTEXT_COURSE, $courseid);
    foreach ($teachers as $teacher) {
        if (!role_assign($teacher->roleid, $teacher->id, 0, $context->id)) {
           $success = false;
        }
    }

    return $success;
}


//////// REPORT FUNCTIONS


/**
 * Print the form to select the logs
 *
 * @param   int   $selectedstatus
 * @param   int   $selectedtime
  */

function ubcoursedelete_print_log_selector_form($selectedstatus = 0, $selectedtime = 0) {
    global $CFG;

    // List of status options.
    global $statusoptions;

    //Prepare the time options
    // Note that we are keeping track of real (GMT) time and user time
    // User time is only used in displays - all calcs and passing is GMT
    $timeoptions = array();
    // get minimum log time
    $minlog = get_field_sql('SELECT min(quarantinetime) FROM '.$CFG->prefix.'ubcoursedelete ');

    $now = usergetmidnight(time());

    // Today
    $timeoptions[$now] = get_string("today");

    // days
    for ($i = 1; $i < 7; $i++) {
        if (strtotime('-'.$i.' days',$now) >= $minlog) {
            $timeoptions[strtotime('-'.$i.' days',$now)] = get_string('numdays','moodle',$i);
        }
    }
    // weeks
    for ($i = 1; $i < 10; $i++) {
        if (strtotime('-'.$i.' weeks',$now) >= $minlog) {
            $timeoptions[strtotime('-'.$i.' weeks',$now)] = get_string('numweeks','moodle',$i);
        }
    }
    // months
    for ($i = 2; $i < 12; $i++) {
        if (strtotime('-'.$i.' months',$now) >= $minlog) {
            $timeoptions[strtotime('-'.$i.' months',$now)] = get_string('nummonths','moodle',$i);
        }
    }
    // try a year
    if (strtotime('-1 year',$now) >= $minlog) {
        $timeoptions[strtotime('-1 year',$now)] = get_string('lastyear');
    }

    //The form
    echo "<form class=\"logselectform\" action=\"$CFG->wwwroot/admin/report/ubcoursedelete/index.php\" method=\"get\">\n";
    echo "<div>\n";
    echo "<input type=\"hidden\" name=\"chooselog\" value=\"1\" />\n";
    echo '<label for="menustatus">'.get_string('showstatus', 'report_ubcoursedelete').'</label>'."\n";
    choose_from_menu($statusoptions,'status',$selectedstatus,get_string('statusall', 'report_ubcoursedelete'));
    echo '<label for="menutimefrom">'.get_string('lookback').'</label>'."\n";
    choose_from_menu($timeoptions,'timefrom',$selectedtime, get_string("alldays"));
    echo '<input type="submit" value="'.get_string('gettheselogs').'" />';
    echo '</div>';
    echo '</form>';

}


/**
 * Return the status as a string
 * To be show in the reports.
 *
 * @param   int   $status the status id
 * @return  string the status as a string
 */

function ubcoursedelete_get_status_name($status) {
    global $statusoptions;
    $statusname = '';

    if (key_exists($status, $statusoptions)) {
       $statusname = $statusoptions[$status];
    }

    return $statusname;
}


/**
 * Return the info from a quarantine course as a string
 * To be show in the reports.
 *
 * @param   mixed   $quarantinelog object
 * @return  string the info nicely formated for the report
 */

function ubcoursedelete_format_info($quarantinelog) {
    global $CFG;

    $strinfo = '';

    switch ($quarantinelog->action) {
        case 'backup':
            $path = get_config(null, 'report_ubcoursedelete_backupdestination');
            $filename = $path.'/'.$quarantinelog->info;
            if (file_exists($filename)) {
                $count = 0;
                $dir = str_replace($CFG->dataroot . '/', '', $path, &$count);
                if ($count) {
                    $dirs = explode('/', $dir); /// Extract path parts
                    $courseid = $dirs[0];
                    if (record_exists('course', 'id', $courseid)) {
                        unset($dirs[0]);
                        $wdir = implode('/', $dirs);
                        $strinfo = '<a href="'.$CFG->wwwroot.'/files/index.php?id='.$courseid.'&amp;wdir='.$wdir.'">'.
                                    $quarantinelog->info.'</a>';
                    }
                }
                // The file exists but it cannot be reached
                if (empty($strinfo)) {
                    $strinfo = $filename;
                }
            } else {
                $strinfo = $quarantinelog->info;
            }
            break;

        case 'delete':
            if (!empty($quarantinelog->info)) {
                $strinfo = '<a href="'.$CFG->wwwroot.$quarantinelog->info.'">'.
                            get_string('viewlogs', 'report_ubcoursedelete').'</a>';
            }
            break;

        default:
    		$strinfo = $quarantinelog->info;
            break;
    }

    return $strinfo;
}
?>