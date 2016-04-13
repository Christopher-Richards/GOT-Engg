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

if ((include '/var/www/html/GOTengg/src/Model.php') == TRUE) {
	//echo "correct model </br>";
}
class HomeController{
	private $transcript;
	protected $schedule;
	public function __construct($text, $schedule) {
		$this->setTranscript ( $text );
		$this->schedule = $schedule;
	}

	/**
	 * *************************************************************
	 * This function will check the text of the transcript to search for a student id
	 * If found , it will return the value, if not it will return null
	 */
	public function getStudentId() {
		preg_match ( '/\d{9}/', $this->transcript, $ID ); // this will get the SID of the student

		if (count ( $ID ) > 0) {
				
			// $key= mt_rand ( MIN_KEY, MAX_KEY ); // will append a random key to the SI
			return $ID [0]; // . strval($key);
		} else
			return null;
	}

	/**
	 * **************************************************************
	 * This Function determins if the transcript inserted was an admin or a student transcript
	 * The key difference is that the admin transcript contains the word "Term Code" within it
	 *
	 * Note curently there is no function to check if the rest of the info is contained
	 */
	public function determineParser($si) {
		$userProg = new ProgressController($si,$this->schedule );
		if (strpos ( $this->transcript, "Term Code" )) {
			$admParse = new AdminParse ( $this->transcript, $si );
			$admParse->parse ();
			$userProg->userProgressInsert();
			$admParse->redirect ( "refresh:1;url=Classes.php" );
		} else {
				
			$stuParse = new StudentParse ( $this->transcript, $si );
			$stuParse->studentParse ();
			$userProg->userProgressInsert();
			$stuParse->redirect ( "refresh:1;url=Classes.php" );
		}
	}
	
	private function setTranscript($text) {
		$this->transcript = $text;
	}
}
class ProgressController extends GotEnggGeneric{
	private $schedule;
	public function __construct($si, $schedule) {
		$this->schedule=$schedule;
		$this->setSI ( $si );
		$this->getDb();
	}
	public function userProgressInsert(){
		$sql = "SELECT * FROM Schedule WHERE Schedule.id ='$this->schedule'";
		$result = mysqli_query ( $this->db, $sql );
		$row = mysqli_fetch_assoc ( $result );
		
		$sem1 = $row ['semester1'];
		$sem2 = $row ['semester2'];
		$sem3 = $row ['semester3'];
		$sem4 = $row ['semester4'];
		$sem5 = $row ['semester5'];
		$sem6 = $row ['semester6'];
		$sem7 = $row ['semester7'];
		$sem8 = $row ['semester8'];
		$sem9 = $row ['semester9'];
		$electives = $row ['electives'];
		mysqli_free_result ( $result );
		$stuProg = new StudentProgress ( $this->SI );
		$stuProg->parseSemester ( $sem1, 1, $this->db );
		$stuProg->parseSemester ( $sem2, 2, $this->db );
		$stuProg->parseSemester ( $sem3, 3, $this->db );
		$stuProg->parseSemester ( $sem4, 4, $this->db );
		$stuProg->parseSemester ( $sem5, 5, $this->db );
		$stuProg->parseSemester ( $sem6, 6, $this->db );
		$stuProg->parseSemester ( $sem7, 7, $this->db );
		$stuProg->parseSemester ( $sem8, 8, $this->db );
		$stuProg->parseSemester ( $sem9, 9, $this->db );
		$stuProg->parseElectives ( $electives, $this->db );
		$stuProg->updateClassesCompleted ( $this->db );
		$stuProg->joinElectives ( $this->db );
		$stuProg->updateElectiveProgress($this->db);
		$stuProg->updateRequired ( $this->db );
	}
	public function setUpNewprogress() {
		$stuProg = new StudentProgress ( $this->SI );
		$notInResult = $stuProg->getClassNotInProgress ( $this->db);
		return $notInResult;
	}
	public function returnUserProgress(){
		$sql = "SELECT * FROM userProgress WHERE userProgress.SI = '$this->SI'";
		$result = mysqli_query ( $this->db, $sql );
		return $result;
	}
}
class ClassesController extends GotEnggGeneric{

	public function __construct($si) {
		$this->setSI ( $si );
		$this->getDb();
	}
	public function getName(){
		$sql = "SELECT * FROM StuInfo where SI = '$this->SI'";
		$result = mysqli_query($this->db, $sql);
		return $result;
	}
public function getClasses(){
	$sql = "SELECT * FROM CourseInfo where SI = '$this->SI'";
	$result = mysqli_query($this->db, $sql);
	return $result;
}
}
class NextSemesterController {
}

class SchedulePlanner extends GotEnggGeneric {
	public function __construct($si) {
		$this->setSI ( $si );
		$this->getDb();
	}
	public function getSchedule(){
		$sql = "select *,
    (
    CASE
        WHEN SuggestedSchedule.inProgress = 0 AND SuggestedSchedule.pass = 0 THEN 'Not Completed'
        WHEN SuggestedSchedule.inProgress = 1 AND SuggestedSchedule.pass = 0 THEN 'Currently Enrolled'
        WHEN SuggestedSchedule.inProgress = 0 AND SuggestedSchedule.pass = 1 THEN 'Completed'
    END) AS Status
 from SuggestedSchedule where SuggestedSchedule.unAccounted = 0 AND SuggestedSchedule.SI = '$this->SI' order by SuggestedSchedule.term desc";
		$result = mysqli_query ( $this->db, $sql );
		return $result;
	}
	public function getUnAccounted(){
		$sql = "SELECT *, ( CASE WHEN SuggestedSchedule.inProgress = 0 AND SuggestedSchedule.pass = 0 THEN 'Not Completed' WHEN SuggestedSchedule.inProgress = 1 AND SuggestedSchedule.pass = 0 THEN 'Currently Enrolled' WHEN SuggestedSchedule.inProgress = 0 AND SuggestedSchedule.pass = 1 THEN 'Completed' END) AS Status FROM SuggestedSchedule where SuggestedSchedule.unAccounted = 1 AND SuggestedSchedule.courseTitle NOT LIKE '%ENGG 0%' AND SuggestedSchedule.SI = '$this->SI'";
		$result = mysqli_query ( $this->db, $sql );
		return $result;
	}
}


class ScheduleController extends GotEnggGeneric {

	public function __construct() {
		$this->getDb ();
	}

	/*
	 * This function will print the selescted schedule
	 */
	public function printSchedule() {
		if ($_SERVER ["REQUEST_METHOD"] == "POST") {

			$search = htmlspecialchars ( $_POST ["schedule"] );
			$search = htmlentities ( $search );

			$sql = "SELECT * FROM Schedule WHERE id LIKE '%" . $search . "%' LIMIT 20"; // search the DB for matches
//			mysqli_free_result ( $result );
			$result = mysqli_query ( $this->db, $sql );

			if ($result) { // If query successfull

				echo "	<thead>
							<tr>
								<th>Program</th>

							</tr>
							<tr></tr>
						</thead>
						<tbody>";

				while ( $row = mysqli_fetch_assoc ( $result ) ) { // Creates a loop to loop through results

					echo "<tr><td>" . $row ['program'] . "</td></tr>";
					echo "<tr><td>" . $row ['Sem1_name'] . " " . $row ['semester1'] . "</td></tr>";
					echo "<tr><td>" . $row ['Sem2_name'] . " " . $row ['semester2'] . "</td></tr>";
					echo "<tr><td>" . $row ['Sem3_name'] . " " . $row ['semester3'] . "</td></tr>";
					echo "<tr><td>" . $row ['Sem4_name'] . " " . $row ['semester4'] . "</td></tr>";
					echo "<tr><td>" . $row ['Sem5_name'] . " " . $row ['semester5'] . "</td></tr>";
					echo "<tr><td>" . $row ['Sem6_name'] . " " . $row ['semester6'] . "</td></tr>";
					echo "<tr><td>" . $row ['Sem7_name'] . " " . $row ['semester7'] . "</td></tr>";
					echo "<tr><td>" . $row ['Sem8_name'] . " " . $row ['semester8'] . "</td></tr>";
					echo "<tr><td>" . $row ['Sem9_name'] . " " . $row ['semester9'] . "</td></tr>";
					echo "<tr><td>" . $row ['electives'] . "</td></tr>";
					echo "<tbody>";
				}
			} else {
				echo "No Results Found";
			}
		}
	}

	/**
	 * ****************************************************
	 * This function will query the db to get the names of all the schedules that are currently in the db
	 * It will return an array containing a list of availible schedules in the DB
	 */
	public function listSchedules() {

		$schduleList = array ();

		$sql = "SELECT * FROM Schedule";
		$result = mysqli_query ( $this->db, $sql );

		// loops through all the queries
		$i = 0;
		while ( $row = mysqli_fetch_assoc ( $result ) ) {

			$schduleList [$i] = $row ['year'] . " " . $row ['program'];
			// echo $schduleList [$i] . "</br>";
			echo "<option value='" . $row ['id'] . "'>" . $schduleList [$i] . "</option>";
			$i ++;
		}

		return;
		// return $scheduleList;
	}
}
class SchedulePlannerController extends GotEnggGeneric {
	public function __construct($si) {
		$this->setSI ( $si );
		$this->getDb ();
	}
	public function getSchedule() {
		$sql = "select *,
    (
    CASE
        WHEN SuggestedSchedule.inProgress = 0 AND SuggestedSchedule.pass = 0 THEN 'Not Completed'
        WHEN SuggestedSchedule.inProgress = 1 AND SuggestedSchedule.pass = 0 THEN 'Currently Enrolled'
        WHEN SuggestedSchedule.inProgress = 0 AND SuggestedSchedule.pass = 1 THEN 'Completed'
    END) AS Status
 from SuggestedSchedule where SuggestedSchedule.unAccounted = 0 AND SuggestedSchedule.SI = '$this->SI' order by SuggestedSchedule.term desc";
		$result = mysqli_query ( $this->db, $sql );
		return $result;
	}
	public function getUnAccounted() {
		$sql = "SELECT *, ( CASE WHEN SuggestedSchedule.inProgress = 0 AND SuggestedSchedule.pass = 0 THEN 'Not Completed' WHEN SuggestedSchedule.inProgress = 1 AND SuggestedSchedule.pass = 0 THEN 'Currently Enrolled' WHEN SuggestedSchedule.inProgress = 0 AND SuggestedSchedule.pass = 1 THEN 'Completed' END) AS Status FROM SuggestedSchedule where SuggestedSchedule.unAccounted = 1 AND SuggestedSchedule.courseTitle NOT LIKE '%ENGG 0%' AND SuggestedSchedule.SI = '$this->SI'";
		$result = mysqli_query ( $this->db, $sql );
		return $result;
	}
}
/****
 * This class will search the DB for a matching class name
 *
 *
 */
class SearchClassesController extends GotEnggGeneric {


	public function __construct(){

		$this->getDb();
	}


	/*
	 * this function take a class name as the parameter
	 * Prints the results after a query to the DB
	 */
	public function searchClass($class){


		// this will add a space if one is missing inbetween the course letters and numbers
		// if a space already exists then nothing will be changed
		preg_match('/(\w{1,4})\s?(\d{0,3})/', $class, $Sarray);

		if ($Sarray[2] !=""){
			$class= $Sarray[1] . " " . $Sarray[2];
		}
		else {
			$class=$Sarray[1];
		}

		$sql = "SELECT * FROM CoursesOffered WHERE courseName LIKE '%". $class ."%' LIMIT 20";   //search the DB for matches
		mysqli_free_result($result);
		$result = mysqli_query($this->db,$sql);

		// prints the results
		if($result){//If query successfull

			echo	"	<thead>
							<tr class = 'results'>
								<th>Course Name</th>
								<th>Course Title</th>
								<th>Prerequisites</th>
								<th>Semester Offered</th>

						</thead>
						<tbody class='connectedSortable'>";
				
			while ($row = mysqli_fetch_assoc($result)) { //Creates a loop to loop through results
					
				echo "<tr class='results'><td>" . $row ['courseName'] . "</td><td>" . $row ['courseTitle'] . "</td>" . "<td>" . $row ['pre_reqs'] . "</td>" . "<td>" . $row ['semester_offered'] . "</td></tr>";
			}
		}
		else{
			echo "No Results Found";
		}

	}
}


?>