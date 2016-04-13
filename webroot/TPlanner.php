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
if ((include '/var/www/html/GOTengg/src/Controller/Controller.php') == TRUE) {
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"
	class="cufon-active cufon-ready">
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8">
	<meta http-equiv="Content-Style-Type" content="text/css">
		<script
			src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
		<link rel="stylesheet"
			href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"
			integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ=="
			crossorigin="anonymous">
			<style type="text/css" media="screen">
span.col1 {
	display: inline-block;
	width: 40%;
}

span.col2 {
	display: inline-block;
	width: 30%;
}

span.col3 {
	display: inline-block;
	width: 25%;
}

body.dragging, body.dragging * {
	cursor: move !important;
}

.dragged {
	position: absolute;
	opacity: 0.5;
	z-index: 2000;
}

ol.nested_with_switch li, ol.simple_with_animation li, ol.serialization li,
	ol.default li {
	cursor: pointer;
}

/* line 51, /Users/jonasvonandrian/jquery-sortable/source/css/application.css.sass */
li {
	list-style-type: none;
}

ol {
	list-style-type: none;
	max-height: 400px;
	overflow: auto;
}
/* line 34, /Users/jonasvonandrian/jquery-sortable/source/css/application.css.sass */
ol i.icon-move {
	cursor: pointer;
}
/* line 36, /Users/jonasvonandrian/jquery-sortable/source/css/application.css.sass */
ol.vertical {
	margin: 0 0 9px 0;
	min-height: 10px;
}
/* line 13, /Users/jonasvonandrian/jquery-sortable/source/css/jquery-sortable.css.sass */
ol.vertical li {
	display: block;
	margin: 5px;
	padding: 5px;
	border: 1px solid #cccccc;
}
/* line 20, /Users/jonasvonandrian/jquery-sortable/source/css/jquery-sortable.css.sass */
ol.vertical li.placeholder {
	position: relative;
	margin: 0;
	padding: 0;
	border: none;
}
/* line 25, /Users/jonasvonandrian/jquery-sortable/source/css/jquery-sortable.css.sass */
ol.vertical li.placeholder:before {
	position: absolute;
	content: "";
	width: 0;
	height: 0;
	margin-top: -5px;
	left: -5px;
	top: -4px;
	border: 5px solid transparent;
	border-left-color: red;
	border-right: none;
}
</style>
			<title>GOT-Engg</title>

</head>
<script>
var adjustment;

$("ol.simple_with_animation").sortable({
  group: 'simple_with_animation',
  pullPlaceholder: false,
  // animation on drop
  onDrop: function  ($item, container, _super) {
    var $clonedItem = $('<li/>').css({height: 0});
    $item.before($clonedItem);
    $clonedItem.animate({'height': $item.height()});

    $item.animate($clonedItem.position(), function  () {
      $clonedItem.detach();
      _super($item, container);
    });
  },

  // set $item relative to cursor position
  onDragStart: function ($item, container, _super) {
    var offset = $item.offset(),
        pointer = container.rootGroup.pointer;

    adjustment = {
      left: pointer.left - offset.left,
      top: pointer.top - offset.top
    };

    _super($item, container);
  },
  onDrag: function ($item, position) {
    $item.css({
      left: position.left - adjustment.left,
      top: position.top - adjustment.top
    });
  }
});
</script>
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
				<li><a href="Progress.php">Progress through Program</a>
					<li class="active"><a href="TPlanner.php"> Schedule Planner </a></li>
					<li><a href="SearchClasses.php"> Search Classes </a>
						<li><a href="Schedules.php">Schedules</a></li>
						<li><a href="Logout.php">Logout</a></li>
			
			</ul>

		</div>
		<!-- /.navbar-collapse -->
	</div>
	<!-- /.container-fluid --> </nav>
	<br />
	<br />
	<br />
	<div class='container-fluid'>
		<div class='row'>
			<div class='col-md-12'>
				<div class='jumbotron'>
					<h2>Plan Your Schedule</h2>

				</div>
			</div>
		</div>
	</div>
	<br> <br>

<?php
if (isset ( $_SESSION ['SI'] )) {
	$future_schedule = new FutureSchedule ( $_SESSION ['SI'] );
	
	$future_schedule->printOptimalSchedule ();
	$schedControll = new SchedulePlannerController ( $_SESSION ['SI'] );
	
	// echo "</br></br></br> hello";
	// $planner->printTerm(201530);
}
$result = $schedControll->getSchedule ();

echo "<div class='row'>";
echo "<div class='col-md-1'>";
echo "</div>";
echo "<div class='col-md-5'>";
echo "<h2>";
echo "Planned Schedule";
echo "</h2>";
echo "<div style='height:50%;'>";
echo "<ol class='simple_with_animation vertical'>";
while ( $row = mysqli_fetch_assoc ( $result ) ) { // Creates a loop to loop through results
	$status = $row ['Status'];
	if ($status == "Completed") {
		echo "<li>" . "<span class='col1'>" . $row ['courseTitle'] . "</span>" . "<span class='col2'>" . $row ['term'] . "</span>" . "<span class='col3'>" . "<span class='glyphicon glyphicon-ok text-success'></span>" . "</span>" . "</li>";
	} else if ($status  == "Not Completed") {
		echo "<li>" . "<span class='col1'>" . $row ['courseTitle'] . "</span>" . "<span class='col2'>" . $row ['term'] . "</span>" . "<span class='col3'>" . "<span class='glyphicon glyphicon-exclamation-sign text-danger'></span>" . "</span>" . "</li>";
	}else{
		echo "<li>" . "<span class='col1'>" . $row ['courseTitle'] . "</span>" . "<span class='col2'>" . $row ['term'] . "</span>" . "<span class='col3'>" . "<span class='glyphicon glyphicon-minus-sign text-warning'></span>" . "</span>" . "</li>";
	}
}
echo "</ol>";
echo "</div>";
echo "</div>";
$result = $schedControll->getUnAccounted ();
echo "<div class='col-md-5'>";
echo "<h2>";
echo "Unaccounted for classes";
echo "</h2>";
echo "<div>";
echo "<ol class='simple_with_animation vertical'>";
while ( $row = mysqli_fetch_assoc ( $result ) ) { // Creates a loop to loop through results
	$status = $row ['Status'];
	if ($status == "Completed") {
		echo "<li>" . "<span class='col1'>" . $row ['courseTitle'] . "</span>" . "<span class='col2'>" . $row ['term'] . "</span>" . "<span class='col3'>" . "<span class='glyphicon glyphicon-ok text-success'></span>" . "</span>" . "</li>";
	} else if ($status  == "Not Completed") {
		echo "<li>" . "<span class='col1'>" . $row ['courseTitle'] . "</span>" . "<span class='col2'>" . $row ['term'] . "</span>" . "<span class='col3'>" . "<span class='glyphicon glyphicon-exclamation-sign text-danger'></span>" . "</span>" . "</li>";
	}else{
		echo "<li>" . "<span class='col1'>" . $row ['courseTitle'] . "</span>" . "<span class='col2'>" . $row ['term'] . "</span>" . "<span class='col3'>" . "<span class='glyphicon glyphicon-minus-sign text-warning'></span>" . "</span>" . "</li>";
	}

}
echo "</ol>";
echo "</div>";
echo "</div>";
?> 
<script src='jquery-sortable.js'></script>
			</table>

</body>
</html>