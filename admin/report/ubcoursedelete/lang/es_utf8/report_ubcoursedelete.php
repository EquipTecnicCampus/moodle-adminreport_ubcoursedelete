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

$string['ubcoursedelete'] = 'UB Cursos borrados';

$string['actions'] = 'Acciones';
$string['addtolog'] = 'Añade al registro';
$string['alreadyquarantinedcourse'] = 'Este curso ya está en cuarentena.';
$string['backuperror'] = 'La copia de seguridad no se ha completado satisfactoriamente.';
$string['backupinactive'] = 'Las copias de seguridad de los cursos en cuarentena no están activadas.';
$string['cannotbackupcourse'] = 'No tiene permiso para hacer una copia de seguridad de este curso.';
$string['cannotquarantinecourse'] = 'No tiene permiso para borrar este curso.';
$string['cannotquarantinemetacourse'] = 'Los metacursos y sus cursos hijos no se pueden borrar utilizando esta aplicación. Póngase en contacto con el equipo de suporte a través del \'Servei de consultes docència 24x7 (CRAI)\'';
$string['cannotrecovercourse'] = 'No tiene permiso para recuperar este curso.';
$string['checkingparams'] = 'Comprobando y calculando los parámetros.';
$string['configbackupdestination'] = 'Ruta completa del directorio donde quiere guardar los archivos de copias de seguridad. Dejar vacío si no quiere mantener una copia de seguridad de los cursos borrados. El directorio por defecto es el directorio backupdata en los archivos del sitio.';
$string['configexecuteat'] = 'Escoja la hora para realizar las tareas automáticas de cuarentena diarias.';
$string['configloglifedays'] = 'Número de días en que se mantendrán las copias de seguridad y los registros de los cursos borrados. Los registros y las copias de seguridad más antiguos que esta edad se eliminan automáticamente. Dejar vacío (0 días) si no quiere borrar la información de los cursos eliminados de la cuarentena.';
$string['confignotifyrole'] = 'Este parámetro permite controlar quien recibe la notificación de cuarentena. Los usuarios con al menos uno de estos roles en un curso (o asignado en un contexto superior), recibirán la notificación de este curso.';
$string['configquarantinerole'] = 'Este parámetro permite controlar quien se guarda en cuarentena y se desmatricula del curso. Los usuarios con alguno de estos roles en un curso (y sólo en el contexto del curso) se desmatricularan de este curso. La información (usuario y rol) se guarda en cuarentena para poder recuperarla. <br />
        Los roles por defecto son aquellos que pueden ver los cursos ocultos; si no se ponen en cuarentena se ha de añadir una excepción de permisos en la categoría de cuarentena.';
$string['configquarantinecategory'] = 'Categoría para los cursos en cuarentena. Ha de estar oculta par que los usuarios no vean los cursos.';
$string['configquarantinedays'] = 'Número de días en que permanecen los cursos en cuarentena. Dejar vacío (0 días) para que no se eliminen los cursos de la cuarentena.';
$string['configurereport'] = 'Configura el informe';
$string['cronconfig'] = 'Configuración del cron';
$string['deleted'] = 'borrado';
$string['deletingoldbackup'] = 'Borrando los archivos antiguos de las copias de seguridad';
$string['executingbackup'] = 'Ejecutando la copia de seguridad.';
$string['executingcopying'] = 'Ejecutando y copiando.';
$string['executingdelete'] = 'Ejecutando el proceso de borrado';
$string['loglifedays'] =  'Mantener los registros y las copias de seguridad durante';
$string['notifyrole'] = 'Rol de notificación';
$string['notinquarantine'] = 'Este curso no está en cuarentena.';
$string['quarantinecategory'] = 'Categoría de cuarentena';
$string['quarantinecheck'] = 'Borrar {$a}?';
$string['quarantineconfig'] = 'Configuración de la cuarentena';
$string['quarantinecourse'] = 'Borra este curso';
$string['quarantinecoursecheck'] = 'Esta absolutamente seguro que quiere eliminar este curso y todos los datos que contiene?';
$string['quarantined'] = 'En cuarentena';
$string['quarantinedays'] = 'Días de cuarentena';
$string['quarantinedby'] = 'En cuarentena por';
$string['quarantinedcourse'] = 'Se ha eliminado {$a}';
$string['quarantinenotify'] = 'Notificación de curso borrado';
$string['quarantinenotifyemail'] = 'El usuario {$a->user} ha borrado el {$a->time} el curso \'{$a->course}\'. <br/>{$a->link}';
$string['quarantinerole'] = 'Rol para poner en cuarentena';
$string['quarantiningcourse'] = 'Borrando {$a}';
$string['recovercheck'] = 'Recupera {$a}?';
$string['recovercourse'] = 'Recupera el curso';
$string['recovercoursecheck'] = 'Quiere recuperar de la cuarentena el curso y sus usuarios?';
$string['recovered'] = 'Recuperado';
$string['recoveredcourse'] = 'Se ha recuperado {$a}';
$string['recovererror'] = 'La recuperación no se ha completado satisfactoriamente.';
$string['recoveringcourse'] = 'Recuperando {$a}';
$string['status'] = 'estado';
$string['showstatus'] = 'Mostrar estado';
$string['statusall'] = 'Todos los estados';
$string['teachers'] = 'usuario/s en cuarentena';
$string['ubcoursedelete:backup'] = 'Hacer copias de seguridad de los cursos en cuarentena';
$string['ubcoursedelete:config'] = 'Configura el informe de cursos borrados';
$string['ubcoursedelete:delete'] = 'Borrar cursos en cuarentena';
$string['ubcoursedelete:quarantine'] = 'Enviar a cuarentena cursos';
$string['ubcoursedelete:recover'] = 'Recuperar los cursos en cuarentena';
$string['ubcoursedelete:view'] = 'Ver informe de cursos borrados';
$string['updatingparams'] = 'Actualizando los parámetros';
$string['viewlogs'] = 'Ver logs';
$string['viewreport'] = 'Ver informe';

?>
