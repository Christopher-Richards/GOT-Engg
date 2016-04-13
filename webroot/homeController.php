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
/*
 * define ( 'MAX_KEY', 999 ); // can't because all data will be unique // if back button pressed, new key will be generated = problem
 * define ( 'MIN_KEY', 0 );
 */

/*
 * ini_set( 'error_reporting', E_ALL );
 * ini_set( 'display_errors', true );
 * print_r (error_get_last());
 */

if ((include '/var/www/html/GOTengg/src/Controller/Controller.php') == TRUE) {
}
// if ((include 'ScheduleController.php') == TRUE) {}
$text = $_POST ["studenttext"];
// $_SESSION['schedule']=$_POST['schedule'];

if (isset ( $_SESSION ['SI'] )) {
	$logoutCurrent = new Logout ( $_SESSION ['SI'] );
	$logoutCurrent->logout ();
}

session_unset (); // unsets any previously set session variables /**********NEED TO CLEAR DB FIRST**************/
$_SESSION ['schedule'] = $_POST ['schedule'];
$_SESSION ['created'] = time();
$hController = new HomeController ( $text, $_SESSION ['schedule']);

if ($_SESSION ['SI'] = $hController->getStudentId ()) {
	echo "Loading please wait... </br>";
	
	$hController->determineParser ( $_SESSION ['SI'] );
} else {
	echo "Missing SI number";
	sleep ( 2 );
	header ( "refresh:1;url=index.html" );
}



?>