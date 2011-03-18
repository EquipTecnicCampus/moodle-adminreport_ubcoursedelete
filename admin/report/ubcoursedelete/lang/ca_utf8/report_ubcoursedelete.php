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

$string['ubcoursedelete'] = 'UB Cursos esborrats';

$string['actions'] = 'Accions';
$string['addtolog'] = 'Afegeix al registre';
$string['alreadyquarantinedcourse'] = 'Aquest curs ja està en quarantena.';
$string['backuperror'] = 'La còpia de seguretat no s\'ha completat satisfactòriament.';
$string['backupinactive'] = 'Les còpies de seguretat dels cursos en quarantena no estan actives.';
$string['cannotbackupcourse'] = 'No teniu permís per fer una copia de seguretat d\'aquest curs.';
$string['cannotquarantinecourse'] = 'No teniu permís per esborrar aquest curs.';
$string['cannotquarantinemetacourse'] = 'Els metacursos i els seus cursos fills no es poden esborrar utilitzant aquesta aplicació. Poseu-vos en contacte amb l\'equip de suport a través del \'Servei de consultes docència 24x7 (CRAI)\'';
$string['cannotrecovercourse'] = 'No teniu permís per recuperar aquest curs.';
$string['checkingparams'] = 'Comprovant i calculant els paràmetres.';
$string['configbackupdestination'] = 'Camí complet del directori on voleu desar els fitxers de còpies de seguretat. Deixeu en blanc si no voleu mantenir una còpia de seguretat dels cursos esborrats. El directori per defecte és el directori backupdata dels fitxers del lloc.';
$string['configexecuteat'] = 'Trieu a quina hora s\'haurien de realitzar les tasques automàtiques de quarantena cada dia.';
$string['configloglifedays'] = 'Nombre de dies que voleu mantenir les còpies de seguretat i els registres dels cursos esborrats. Els registres i les còpies de seguretat més antics que aquesta edat s\'eliminen automàticament. Deixeu en blanc (0 dies) si no voleu esborrar la informació dels cursos eliminats de la quarantena.';
$string['confignotifyrole'] = 'Aquest paràmetre us permet controlar qui rep la notificació de quarantena. Els usuaris que tinguin almenys un d\'aquests rols en un curs (o assignats en un context superior), rebran la notificació d\'aquest curs.';
$string['configquarantinerole'] = 'Aquest paràmetre us permet controlar qui es posa en quarantena i es desinscriu del curs. Els usuaris amb aquests rols en un curs (i només per al context del curs) es desinscriuran d\'aquest curs. La informació (usuari i rol) es guarda en quarantena per a poder recuperar-la. <br />
        Els rols per defecte són els que poden veure els cursos ocults; si no es posen en quarantena s\'ha d\'afegir una excepció als permisos en la categoria de quarantena.';
$string['configquarantinecategory'] = 'Categoria on romandran els cursos en quarantena. Ha d\'estar oculta perquè els usuaris no vegin els cursos.';
$string['configquarantinedays'] = 'Nombre de dies que romanen els cursos en quarantena. Deixeu en blanc (0 dies) si no voleu eliminar els cursos de la quarantena.';
$string['configurereport'] = 'Configura informe';
$string['cronconfig'] = 'Configuració del cron';
$string['deleted'] = 'Esborrat';
$string['deletingoldbackup'] = 'Esborrant els fitxers antics de les còpies de seguretat';
$string['executingbackup'] = 'Executant la còpia de seguretat.';
$string['executingcopying'] = 'Executant i copiant.';
$string['executingdelete'] = 'Executant el procés d\'esborrat';
$string['loglifedays'] =  'Manté els registres i les còpies de seguretat per';
$string['notifyrole'] = 'Rol de notificació';
$string['notinquarantine'] = 'Aquest curs no està en quarantena.';
$string['quarantinecategory'] = 'Categoria de quarantena';
$string['quarantinecheck'] = 'Esborrar {$a}?';
$string['quarantineconfig'] = 'Configuració de la quarantena';
$string['quarantinecourse'] = 'Esborra aquest curs';
$string['quarantinecoursecheck'] = 'Esteu absolutament segur que voleu suprimir aquest curs i totes les dades que conté?';
$string['quarantined'] = 'En quarantena';
$string['quarantinedays'] = 'Dies de quarantena';
$string['quarantinedby'] = 'En quarantena per';
$string['quarantinedcourse'] = 'S\'ha suprimit {$a}';
$string['quarantinenotify'] = 'Notificació de curs esborrat';
$string['quarantinenotifyemail'] = 'L\'usuari {$a->user} ha esborrat el {$a->time} el curs \'{$a->course}\'. <br/>{$a->link}';
$string['quarantinerole'] = 'Rol per posar en quarantena';
$string['quarantiningcourse'] = 'Esborrant {$a}';
$string['recovercheck'] = 'Recupera {$a}?';
$string['recovercourse'] = 'Recupera el curs';
$string['recovercoursecheck'] = 'Voleu recuperar de la quarantena el curs i els seus usuaris?';
$string['recovered'] = 'Recuperat';
$string['recoveredcourse'] = 'S\'ha recuperat {$a}';
$string['recovererror'] = 'La recuperació no s\'ha completat satisfactòriament.';
$string['recoveringcourse'] = 'Recuperant {$a}';
$string['status'] = 'Estat';
$string['showstatus'] = 'Mostra estat';
$string['statusall'] = 'Tots els estats';
$string['teachers'] = 'Usuari/s en quarantena';
$string['ubcoursedelete:backup'] = 'Fer còpies de seguretat de cursos en quarantena';
$string['ubcoursedelete:config'] = 'Configura informe de cursos esborrats';
$string['ubcoursedelete:delete'] = 'Esborrar cursos en quarantena';
$string['ubcoursedelete:quarantine'] = 'Posar en quarantena cursos';
$string['ubcoursedelete:recover'] = 'Recuperar els cursos en quarantena';
$string['ubcoursedelete:view'] = 'Veure informe de cursos esborrats';
$string['updatingparams'] = 'Actualitzant els paràmetres';
$string['viewlogs'] = 'Veure logs';
$string['viewreport'] = 'Veure informe';

?>