<?php
session_start ();

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

if ((include '/var/www/html/GOTengg/src/Model.php') == TRUE) {
}
if (isset ( $_SESSION ['SI'] ) && (time () - $_SESSION ['created'] > 1800)) {
	session_unset (); // unset $_SESSION variable for the run-time
	session_destroy (); // destroy session data in storage
	do_alert ();
} else {
	$timeOut = new Timeout ( $_SESSION ['SI'] );
	$timeOut->updateTimestamp ();
	$_SESSION ['created'] = time ();
}
function do_alert() {
	header ( "Location:SessionExpired.php" );
	exit ();
}
?>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Search Classes</title>
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

<script type="text/javascript"
	src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
	
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script type="text/javascript">

$(function() {
$("#search_button").click(function() {
	
	//var searchString    = $("#coursename").val();
	// forming the queryString
	var data = $( "#coursename").serialize();

    
	if(data) {
           									 // ajax call
            $.ajax({
                type: "POST",
                url: "SearchResults.php",
                data: data,
                //dataType: "string",
                beforeSend: function(html) { // this happens before actual call
                    $("#results").html(''); 
                    $("#searchresults").show();
               
               },
               success: function(html){ // this happens after we get results
                    $("#results").show();
                    $("#results").append(html);
             }
                });    
               }
               return false;
});
});

</script>


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
					<li> <a href="Progress.php">Progress through Program</a></li>
					<li><a href="TPlanner.php"> Schedule Planner </a></li>
					<li class="active"><a href="#">Search Classes</a></li>
					<li> <a href="Schedules.php">Schedules</a></li>
					<li><a href="Logout.php">Logout</a></li>
					
			
				
				</ul>

			</div>
			<!-- /.navbar-collapse -->
		</div>
		<!-- /.container-fluid -->
	</nav>



	<br />
	<br />
	<br />
	<br />
	<div class='container-fluid'>
		<div class='row'>
			<div class='col-md-12'>
				<div class='jumbotron'>
					<h2>Search for Classes</h2>

						<form role='form' action= "SearchResults.php" method ="post">
						<input type="text" name="search" id="coursename"/>
						<input class="btn btn-primary btn-large" type="submit" name="button"
							id="search_button" value="search"/>
						</form>
				</div>
			</div>
		</div>
	</div>


	<div id='searchresults'  >
		<div class='row'>
			<div class='col-md-2'></div>
			<div class='col-md-8'>
				<div style='overflow: auto; height: 100%;'>
					<table class='table' id='results'>
					
					</table>
				</div>
			</div>
		</div>
	</div>
</body>
</html>