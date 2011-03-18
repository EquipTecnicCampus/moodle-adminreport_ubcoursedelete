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
 * Settings for the UB Deleted courses report
 *
 * @package   adminreport_ubcoursedelete
 * @copyright 2010 Yolanda Ordonez
 * @author    Yolanda Ordonez <yordonez@ub.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version   1.0.1
 *
 */

// Category for the plugin
$ADMIN->add('reports', new admin_category('ubcoursedelete', get_string('ubcoursedelete', 'report_ubcoursedelete')));
//The view report page
$ADMIN->add('ubcoursedelete', new admin_externalpage('reportubcoursedelete',
                                                     get_string('viewreport', 'report_ubcoursedelete'),
                                                     "$CFG->wwwroot/$CFG->admin/report/ubcoursedelete/index.php",
                                                     'report/ubcoursedelete:view'));
// The report settingpage
$temp = new admin_settingpage('configubcoursedelete', get_string('configurereport', 'report_ubcoursedelete'),
                              'report/ubcoursedelete:config');
// The quarantine settings
$temp->add(new admin_setting_heading('report_ubcoursedelete_quarantine_heading',
                                     get_string('quarantineconfig', 'report_ubcoursedelete'),
                                     ''));
$temp->add(new admin_settings_coursecat_select('report_ubcoursedelete_quarantinecategory',
                                               get_string('quarantinecategory', 'report_ubcoursedelete'),
                                               get_string('configquarantinecategory', 'report_ubcoursedelete'), 1));
//Which roles to quarantine and unenrole from the course
$choices = array();
$roles = get_records('role','','','sortorder');
foreach($roles as $role) {
    $choices[$role->id] = format_string($role->name);
}
                                               $defaultsetting = array();
if ($teacherroles = get_roles_with_capability('moodle/course:viewhiddencourses', CAP_ALLOW)) {
    foreach ($teacherroles as $teacherrole) {
        $defaultsetting[$teacherrole->id] = $teacherrole->id;
    }
}
$temp->add(new admin_setting_configmulticheckbox('report_ubcoursedelete_quarantinerole',
                                                  get_string('quarantinerole', 'report_ubcoursedelete'),
                                                  get_string('configquarantinerole', 'report_ubcoursedelete'),
                                                  $defaultsetting, $choices));
//Which roles to send the notification for the quarantine course
$defaultsetting = array();
if ($teacherroles = get_roles_with_capability('moodle/legacy:editingteacher', CAP_ALLOW)) {
    foreach ($teacherroles as $teacherrole) {
        $defaultsetting[$teacherrole->id] = $teacherrole->id;
    }
}
$temp->add(new admin_setting_configmulticheckbox('report_ubcoursedelete_notifyrole',
                                                  get_string('notifyrole', 'report_ubcoursedelete'),
                                                  get_string('confignotifyrole', 'report_ubcoursedelete'),
                                                  $defaultsetting, $choices));
// The cron settings
$temp->add(new admin_setting_heading('report_ubcoursedelete_cron_heading',
                                     get_string('cronconfig', 'report_ubcoursedelete'),
                                     ''));
$temp->add(new admin_setting_configtime('report_ubcoursedelete_schehour', 'report_ubcoursedelete_scheminute',
                                        get_string('executeat'), get_string('configexecuteat', 'report_ubcoursedelete'),
                                        array('h' => 0, 'm' => 0)));
$temp->add(new admin_setting_configtext('report_ubcoursedelete_quarantinedays',
                                        get_string('quarantinedays', 'report_ubcoursedelete'),
                                        get_string('configquarantinedays', 'report_ubcoursedelete'), 90, PARAM_INT));
$defaultsetting = $CFG->dataroot.'/'.SITEID.'/backupdata';
$temp->add(new admin_setting_configdirectory('report_ubcoursedelete_backupdestination',
                                            get_string('saveto'),
                                            get_string('configbackupdestination', 'report_ubcoursedelete'),
                                            $defaultsetting));
$temp->add(new admin_setting_configtext('report_ubcoursedelete_loglifedays',
                                        get_string('loglifedays', 'report_ubcoursedelete'),
                                        get_string('configloglifedays', 'report_ubcoursedelete'), 180, PARAM_INT));

$ADMIN->add('ubcoursedelete', $temp);

?>