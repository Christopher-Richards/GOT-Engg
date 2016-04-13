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
if ((include '/var/www/html/GOTengg/src/Controller/Controller.php') == TRUE) {
}
if (isset($_SESSION['SI']) && (time() - $_SESSION ['created'] > 1800)) {
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
do_alert();
}else{
	$timeOut = new Timeout($_SESSION['SI']);
	$timeOut->updateTimestamp();
	$_SESSION ['created'] = time();
}
function do_alert() 
    {
	header( "Location:SessionExpired.php" );
	exit();
    }
?>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>GOTengg</title>
<link rel="stylesheet"
	href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"
	integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ=="
	crossorigin="anonymous">
<style type="text/css">
.carousel-inner>.item>img, .carousel-inner>.item>a>img {
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
				<button type="button" class="navbar-toggle collapsed"
					data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span> <span
						class="icon-bar"></span> <span class="icon-bar"></span> <span
						class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">GOT-Engg</a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse"
				id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
				<li><a href="index.html">Home</a></li>
					<li><a href="Classes.php">Classes</a></li>
					<li class="active"><a href="#">Progress through Program</a>
					<li><a href="TPlanner.php"> Schedule Planner </a></li>
					<li><a href="SearchClasses.php"> Search Classes </a>
					<li> <a href="Schedules.php">Schedules</a></li>
					<li><a href="Logout.php">Logout</a></li>


				</ul>

			</div>
			<!-- /.navbar-collapse -->
		</div>
		<!-- /.container-fluid -->
	</nav>

<?php
/*echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";

ini_set( 'error_reporting', E_ALL );
ini_set( 'display_errors', true );
print_r (error_get_last());*/

$cCon = new ClassesController($_SESSION['SI']);

// include '/var/www/html/GOTengg/config/dbConnection.php';
// include '/var/www/html/GOTengg/src/Model.php';

$result = $cCon->getName();
echo "<br />";
echo "<br />";
echo "<br />";
echo "<br />";
echo "<div class='container-fluid'>";
echo "<div class='row'>";
echo "<div class='col-md-12'>";
echo "<div class='jumbotron'>";
echo "<h2>";
echo "Current Progress Through Program";
echo "</h2>";
echo "<p>";
while ( $row = mysqli_fetch_assoc ( $result ) ) { // Creates a loop to loop through results
	
	echo " " . $row ['SI'] . " - " . $row ['name'] . " - " . $row ['major'];
}

echo "</p>";
echo "<p>";
echo "<a class='btn btn-primary btn-large' href='#'>New Student?</a>";
echo "</p>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

mysqli_free_result ( $result );
/*
 * echo "<div class='tab-content'>";
 * echo "<div class='tab-pane' id='panel-553231'>";
 * echo "<p>";
 * echo "I'm in Section 1.";
 * echo "</p>";
 * echo "</div>";
 * echo "<div class='tab-pane active' id='panel-754113'>"
 * echo "<p>";
 * echo "Howdy, I'm in Section 2.";
 * echo "</p>";
 * echo "</div>";
 * echo "</div>";
 */

$progress = new ProgressController($_SESSION['SI'],$_SESSION['schedule']);
$notInResult = $progress->setUpNewprogress();

$result = $progress->returnUserProgress();
//echo "<div class='col-md-1'></div>";
echo "<div class='col-md-1'></div>";
echo "<div class='col-md-11'>";
echo "<h1>";
echo "Current Progress";
echo "</h1>";
echo "</div>";

echo "<div class='row'>";
echo "<div class='col-md-1'>";
echo "</div>";

echo "<div class='col-md-6'>";

echo "<div style='overflow:auto;height:100%;'>";
echo "<table class='table'>";
echo "<thead>";
echo "<tr >";
echo "<th>";
echo "Semester";
echo "</th>";
echo "<th>";
echo "Course";
echo "</th>";
echo "<th>";
echo "Grade";
echo "</th>";
echo "</thead>";
echo "<tbody>";
while ( $row = mysqli_fetch_assoc ( $result ) ) { // Creates a loop to loop through results
	$grade = $row ['grade'];
	if ($grade == "0") {
	echo "<tr>" . "<td>" . $row ['semester'] . "</td>" . "<td>" . $row ['class'] . "</td><td>" . "<span class='glyphicon glyphicon-exclamation-sign text-danger'></span>" . "</td>" . "</tr>";
	}else if($grade == "Currently Registered") {
	echo "<tr>" . "<td>" . $row ['semester'] . "</td>" . "<td>" . $row ['class'] . "</td><td>" . "<span class='glyphicon glyphicon-minus-sign text-warning'></span>" . "</td>" . "</tr>";
	}else{

	echo "<tr>" . "<td>" . $row ['semester'] . "</td>" . "<td>" . $row ['class'] . "</td><td>" ."<span class='glyphicon glyphicon-ok text-success'></span>" . " " . $row ['grade']  . "</td>" . "</tr>";
}
}
//echo "</tr>";
echo "</table>"; // Close the table in HTML
echo "</div>";
echo "</div>";
mysqli_free_result ( $result );
//echo "</br>";
echo "<div class='col-md-4'>";
echo "<div style='overflow:auto;height:100%;'>";
echo "<table class='table'>";
echo "<thead>";
echo "<tr >";
echo "<th>";
echo "Unaccounted for classes";
echo "</th>";
echo "</thead>";
echo "<tbody>";
while ( $row = mysqli_fetch_assoc ( $notInResult ) ) { // Creates a loop to loop through results
	echo "<tr>" . "<td>" . $row ['courseTitle'] . "</td>" . "</tr>";
}
//echo "</tr>";
echo "</table>"; // Close the table in HTML
echo "</div>";
echo "</div>";
mysqli_free_result ( $result );
?>
</body>
</html>
