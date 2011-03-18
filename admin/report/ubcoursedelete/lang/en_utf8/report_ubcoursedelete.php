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
 * @package   adminreport_ubcoursedelete
 * @copyright 2010 Yolanda Ordonez
 * @author    Yolanda Ordonez <yordonez@ub.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version   1.0
 *
 */

$string['ubcoursedelete'] = 'UB Deleted courses';

$string['actions'] = 'Actions';
$string['addtolog'] = 'Add to log';
$string['alreadyquarantinedcourse'] = 'This course is already quarantined.';
$string['backuperror'] = 'The backup did not complete successfully.';
$string['backupinactive'] = 'The backup process for quarantined courses in not active.';
$string['cannotbackupcourse'] = 'You do not have the permission to backup this course.';
$string['cannotquarantinecourse'] = 'You do not have the permission to delete this course.';
$string['cannotquarantinemetacourse'] = 'The metacourses and their child courses can not be deleted using this application. Please contact the support team through the \'Servei de consultes docència 24x7 (CRAI)\'';
$string['cannotrecovercourse'] = 'You do not have the permission to recover this course.';
$string['checkingparams'] = 'Checking and calculating parameters';
$string['configbackupdestination'] = 'Full path to the directory where you want to save the backup files. Leave it blank if you don\'t want to keep a backup for the deleted courses. Default is the backupdata directory from the site files.';
$string['configexecuteat'] = 'Choose what time automated quarantine cron jobs should run at every day.';
$string['configloglifedays'] = 'Number of days you want to keep backups and logs about deleted courses.  Logs and backup files that are older than this age are automatically deleted. Leave it blank (0 days) if you don\'t want to lose any information for deleted courses in quarantine.';
$string['confignotifyrole'] = 'This setting allows you to control who receives the quarantine notification. Users need to have at least one of these roles in the course (assigned in higher context too), to receive the notification for that course.';
$string['configquarantinerole'] = 'This setting allows you to control who is quarantined and unenroled from the course. Users with these roles in the course (and only in the course context) will be unrenroled from that course. The information (user and role) will be kept in quarantine so it can be recovered. <br />
        The default roles are the ones who can see hidden courses; so if you don\'t quarantine that role you should override permissions in the quarantine category.';
$string['configquarantinecategory'] = 'Category to keep the quarantined courses. It should be hidden so users won\'t see the courses';
$string['configquarantinedays'] = 'Number of days any course is kept in quarantine. Leave it blank (0 days) if you don\'t want to delete courses from quarantine.';
$string['configurereport'] = 'Configure report';
$string['cronconfig'] = 'Cron configuration';
$string['deleted'] = 'Deleted';
$string['deletingoldbackup'] = 'Deleting old backup files';
$string['executingbackup'] = 'Executing backup';
$string['executingcopying'] = 'Executing and copying';
$string['executingdelete'] = 'Executing delete';
$string['loglifedays'] = 'Keep logs and backups for';
$string['notifyrole'] = 'Notify role';
$string['notinquarantine'] = 'This course is not in quarantine.';
$string['quarantinecategory'] = 'Quarantine category';
$string['quarantinecheck'] = 'Delete {$a}?';
$string['quarantineconfig'] = 'Quarantine configuration';
$string['quarantinecourse'] = 'Delete this course';
$string['quarantinecoursecheck'] = 'Are you absolutely sure you want to delete this course and all the data it contains?';
$string['quarantined'] = 'Quarantined';
$string['quarantinedays'] = 'Quarantine days';
$string['quarantinedby'] = 'Quarantined by';
$string['quarantinedcourse'] = '{$a} has been deleted';
$string['quarantinenotify'] = 'Deleted course notification';
$string['quarantinenotifyemail'] = 'The user {$a->user} deleted at {$a->time} the course \'{$a->course}\'. <br/>{$a->link}';
$string['quarantinerole'] = 'Role to quarantine';
$string['quarantiningcourse'] = 'Deleting {$a}';
$string['recovercheck'] = 'Recover {$a}?';
$string['recovercourse'] = 'Recover course';
$string['recovercoursecheck'] = 'Do you want to recover the quarantined course and users?';
$string['recovered'] = 'Recovered';
$string['recoveredcourse'] = '{$a} has been recovered';
$string['recovererror'] = 'The recover did not complete successfully.';
$string['recoveringcourse'] = 'Recovering {$a}';
$string['status'] = 'Status';
$string['statusall'] = 'All status';
$string['showstatus'] = 'Show status';
$string['teachers'] = 'User/s quarantined';
$string['ubcoursedelete:backup'] = 'Backup quarantined courses';
$string['ubcoursedelete:config'] = 'Configure deleted courses report';
$string['ubcoursedelete:delete'] = 'Delete quarantined courses';
$string['ubcoursedelete:quarantine'] = 'Quarantine courses';
$string['ubcoursedelete:recover'] = 'Recover quarantined courses';
$string['ubcoursedelete:view'] = 'View deleted courses report';
$string['updatingparams'] = 'Updating parameters';
$string['viewlogs'] = 'View logs';
$string['viewreport'] = 'View report';

?>