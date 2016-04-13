<?php
session_start();
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

// checks if the user has a session

// add redirection to main page from js
if (  !isset ($_SESSION['SI'])){ //session_status() == 2 || $_SESSION['SI'] == ""){
	//echo "YESEXPR";
	//header ( "Location:https://www.gotengg.org/index.html" );
	echo "Missing SI";
	//break;
}
//if ((include 'NextTerm.php') == TRUE) {}
else {
/*ini_set( 'error_reporting', E_ALL );
ini_set( 'display_errors', true );
print_r (error_get_last());
*/
require(__DIR__  . '/NextTerm.php');


switch ($_POST ["functionname"]) {

	case 'getAvailibleSemesters' :

		$semesters = new AvailibleSemesters();
		$semesters->getAvailibleSemesters();
		break;

	case '' :
		$semester = $_POST ["semester"];
		$Nsemester = new NextSemester ( $semester,$_SESSION['SI'] );
		$Nsemester->optimalSemester ();
		break;
}
}
?>
