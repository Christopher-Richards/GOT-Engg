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
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GOTengg</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
    <style type="text/css">
        .carousel-inner > .item > img,
        .carousel-inner > .item > a > img {
            width: 75%;
            margin: auto;
        }
    </style>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">GOT-Engg</a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        					<li><a href="index.html">Home</a></li>
					<li class="active"><a href="#">Classes</a></li>
					<li><a href="Progress.php">Progress through Program</a>
					<li><a href="TPlanner.php"> Schedule Planner </a></li>
					<li><a href="SearchClasses.php"> Search Classes </a>
					<li> <a href="Schedules.php">Schedules</a></li>
					
				
                    </ul>

                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
	<br />
    <br />
<br />
 <br />
<div class='container-fluid'>
<div class='row'>
<div class='col-md-12'>
<div class='jumbotron'>

<?php
session_start ();


if (session_status () != 2  ){
	echo "The session has expired";
	sleep (5);
	header ( "refresh:1;url=index.html" );
	
}

/*ini_set ( 'error_reporting', E_ALL );
ini_set ( 'display_errors', true );
print_r ( error_get_last () );*/

if ((include '/var/www/html/GOTengg/src/Model.php') == TRUE) {
}

$logout = new Logout ( $_SESSION ['SI'] ); // logout for the current session

if ($logout->logout ()) {
	echo "You have been successfully logout";
	
	setcookie ( session_name (), '', 100 );
	session_unset ();
	session_destroy ();
	$_SESSION = array ();
	sleep (2);
	header ( "refresh:1;url=index.html" );
	
	
} else {
	echo "There was an error with the logout process";
}

?>
</div>
</div>
</div>
</div>
 </body>
  </html>