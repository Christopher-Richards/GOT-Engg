<?php
/**
 * GOT-engg : Capstone project for SSE  (https://www.gotengg.org)
 * Copyright (c) Christopher Richards, Jordan Cook
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Christopher Richards, Jordan Cook
 * @link      https://www.gotengg.org SSE Capstone Project
 * @since     Year: 2016
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

if ((include '/var/www/html/GOTengg/src/Controller/Controller.php') == TRUE) {
}
/*if ((include '/var/www/html/GOTengg/src/Model.php') == TRUE) {
}*/

/*ini_set( 'error_reporting', E_ALL );
 ini_set( 'display_errors', true );
 print_r (error_get_last());*/

switch ($_POST ["functionname"]) {
	
	case 'listSchedules' :
		$schedule = new ScheduleController ();
		$schedule->listSchedules ();
		break;
	case 'printSchedule' :
		$schedule = new ScheduleController ();
		$schedule->printSchedule ();
		break;

}




?>