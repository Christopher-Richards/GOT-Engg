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
define ( 'MAX_CLASS_COUNT', '5' );

class dbConnection {
	protected $db;
	public function connect() {
		$dataB = mysqli_connect ( "localhost", "root", "ense400", "StudentInfo" ) or die ( "could not connect to the database: Error " . mysqli_error ( $db ) );
		$this->setDb ( $dataB );
	}
	private function setDb($dataB) {
		$this->db = $dataB;
	}
	public function getDb() {
		return $this->db;
	}
}
class StudentParse extends Parser {
	private $major;
	// calls the parent constructor containing the given parameters
	public function __construct($conText, $si) {
		// echo "in constructor";
		$args = func_get_args ();
		call_user_func_array ( array (
				$this,
				'parent::__construct' 
		), $args );
	}
	public function studentParse() {
		
		// Already info in there, need to delete (probably caused by someone pressing the back button
		$sql = "SELECT * FROM StuInfo  WHERE SI = " . $this->SI;
		$result = mysqli_query ( $this->db, $sql );
		$numRows = mysqli_num_rows ( $result );
		
		if ($numRows > 0) {
			
			while ( $row = mysqli_fetch_assoc ( $result ) ) {
				
				$deleteData = new Logout ( $row ['SI'] );
				$deleteData->logout ();
			}
		}
		
		$this->text = ( string ) $this->text;
		
		mysql_real_escape_string ( $this->db, $this->text ); // escape characters
		                                                     // $this->text = str_replace ( "'", "''", $this->text ); // replace ' so that sql can query
		
		$ID = array ();
		preg_match ( '/\d{9}/', $this->text, $ID ); // this will get the SID of the student
		
		$name = array ();
		preg_match ( '/Name:(.*?)\n/', $this->text, $name ); // extract name from transcript
		                                                     // $name[1]=str_replace (" " , "" , $name[1]); //removes spaces and comma's
		                                                     // $name[1]=str_replace ("," , "" , $name[1]);
		
		$major = array ();
		preg_match ( '/Major.{7}(.*?)Year/', $this->text, $major ); // extract major from transcript
		if (! empty ( $major [1] )) {
			$this->setMajor ( $major [1] );
		}
		
		$startYear = array (); // gets the start year , Loops through the classes on the transcript to find the earliest date
		preg_match_all ( '/(\d{4})\s(Fall|Winter|Spring & Summer)/', $this->text, $startYear );
		$year = 9999;
		for($i = 0; $i < count ( $startYear [1] ); $i ++) {
			if ($startYear [1] [$i] < $year) {
				$year = $startYear [1] [$i];
			}
		}
		
		// adds the information to the database if the data is good
		$sql = "INSERT INTO StuInfo (SI, name, startYear, major)  VALUES('$ID[0]','$name[1]','$year','$major[1]')";
		mysqli_query ( $this->db, $sql );
		
		$TermString = array ();
		$TermString = preg_split ( '/(\d{4})\s(Fall|Winter|Spring & Summer)/', $this->text );
		
		for($i = 0; $i < count ( $startYear [0] ); $i ++) {
			preg_match_all ( '/(\w{2,4}\t\d{1,3})\t\d{3}(.*?)(Registered|\d{1,3}|\s[W]\s|\s[P]\s|\s[F]\s)/', $TermString [$i], $classes ); // creates an array of classes
			
			if (! empty ( $classes [0] )) {
				
				for($j = 0; $j < count ( $classes [0] ); $j ++) {
					$courseID = $classes [1] [$j];
					$courseID = strval ( $courseID );
					$courseTitle = $classes [2] [$j];
					$courseTitle = strval ( $courseTitle );
					$grade = $classes [3] [$j];
					
					if ($grade == "Registered") // this is to convert the string to the same formate as the admin transcript since they use the words "Not Completed instead

					{
						$grade = "Currently Registered";
					}
					$grade = strval ( $grade );
					
					// This will convert the string containing the semester and year into the term number exp :201610 for winter 2016
					if ($startYear [2] [$i - 1] == "Winter") {
						$term = $startYear [1] [$i - 1] . "10";
					} else if ($startYear [2] [$i - 1] == "Fall") {
						$term = $startYear [1] [$i - 1] . "30";
					} else {
						$term = $startYear [1] [$i - 1] . "20";
					}
					
					$courseID = preg_replace ( '/\t/', ' ', $courseID );
					
					// only inserts classes that have been passed by the student
					if ($this->checkPass ( $grade )) {
						
						$sql = "INSERT INTO CourseInfo (SI,courseTitle, term, grade)  VALUES('" . $this->SI . "','" . $courseID . "','" . $term . "','" . $grade . "')";
						mysqli_query ( $this->db, $sql );
					}
				}
			}
		}
	}
	
	// sets the major
	private function setMajor($major) {
		$this->major = $major;
	}
	// gets the major
	public function getMajor() {
		return $this->major;
	}
}
class AdminParse extends Parser {
	public function __construct($conText, $si) {
		// echo "in constructor";
		$args = func_get_args ();
		call_user_func_array ( array (
				$this,
				'parent::__construct' 
		), $args );
	}
	public function parse() {
		
		// Already info in there, need to delete (probably caused by someone pressing the back button
		$sql = "SELECT * FROM StuInfo  WHERE SI = " . $this->SI;
		$result = mysqli_query ( $this->db, $sql );
		$numRows = mysqli_num_rows ( $result );
		
		if ($numRows > 0) {
			
			while ( $row = mysqli_fetch_assoc ( $result ) ) {
				
				$deleteData = new Logout ( $row ['SI'] );
				$deleteData->logout ();
			}
		} else {
		}
		
		// $text = $_POST["studenttext"];
		$parseTextName = array (); // gets the start year
		preg_match_all ( '/(\d{9}.*)/', $this->text, $parseTextName );
		$temp = $parseTextName [0] [0];
		$pos = strpos ( $temp, "\t" );
		$id = substr ( $temp, 0, $pos );
		$id = trim ( $id );
		
		$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
		
		$pos = strpos ( $temp, "\t" );
		
		$name = substr ( $temp, 0, $pos );
		$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
		
		$pos = strpos ( $temp, "\t" );
		
		$campus = substr ( $temp, 0, $pos );
		$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
		$pos = strpos ( $temp, "\t" );
		
		$college = substr ( $temp, 0, $pos );
		$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
		
		$pos = strpos ( $temp, "\t" );
		
		$degree = substr ( $temp, 0, $pos );
		$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
		
		$pos = strpos ( $temp, "\t" );
		
		$major = substr ( $temp, 0, $pos );
		$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
		
		$level = $temp;
		
		$UserInfo = "INSERT INTO StuInfo (SI,name,startYear,major) VALUES ( '$this->SI', '$name','0000','$major')";
		// echo $UserInfo . "<br>";
		if (mysqli_query ( $this->db, $UserInfo )) {
		} else {
			echo "Error: " . $UserInfo . "<br>" . mysqli_error ( $this->db );
		}
		
		$temp = array ();
		$temp = preg_split ( '/Transfer Courses/', $this->text ); // splits the string if there are transfer credits
		$this->text = $temp [0]; // the normal classes form this university will always be before the TC's
		
		if (! empty ( $temp [1] )) { // calls the transfer credit function if there are TC's
			$TransferString = $temp [1];
			$this->transferCredits ( $TransferString );
		}
		
		$parseText = array (); // gets the start year
		preg_match_all ( '/(\d{6}(.*?)\t\w{2,4}\s*\d{1,3}).*/', $this->text, $parseText );
		for($i = 0; $i < count ( $parseText [1] ); $i ++) {
			$line = $parseText [0] [$i];
			if (strpos ( $line, 'Registered' ) !== false) {
				// Currently taking
				$temp = $line;
				$pos = strpos ( $temp, "\t" );
				$code = substr ( $temp, 0, $pos );
				$code = trim ( $code );
				$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
				$pos = strpos ( $temp, "\t" );
				$subject = substr ( $temp, 0, $pos );
				$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
				$pos = strpos ( $temp, "\t" );
				$number = substr ( $temp, 0, $pos );
				$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
				$pos = strpos ( $temp, "\t" );
				$title = substr ( $temp, 0, $pos );
				$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
				$pos = strpos ( $temp, "\t" );
				$status = substr ( $temp, 0, $pos );
				$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
				$pos = strpos ( $temp, "\t" );
				$campus = substr ( $temp, 0, $pos );
				$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
				$cHours = $temp;
				$subject = trim ( $subject );
				$number = trim ( $number );
				$courseTitle = $subject . " " . $number;
				
				

				$CourseInfo = "INSERT INTO CourseInfo (courseTitle, term,grade, SI) VALUES ('$courseTitle', '$code','Currently Registered', '$this->SI')";
				// $CourseInfo = "INSERT INTO CourseInfo (SI, courseTitle, term,grade) VALUES ('$this->SI', '$courseTitle', '$code','Not Completed')";
				if (mysqli_query ( $this->db, $CourseInfo )) {
				} else {
					echo "Error: " . $CourseInfo . "<br>" . mysqli_error ( $this->db );
				}
			
			} else {
				$temp = $line;
				$pos = strpos ( $temp, "\t" );
				$code = substr ( $temp, 0, $pos );
				$code = trim ( $code );
				$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
				$pos = strpos ( $temp, "\t" );
				$subject = substr ( $temp, 0, $pos );
				$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
				$pos = strpos ( $temp, "\t" );
				$number = substr ( $temp, 0, $pos );
				$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
				$pos = strpos ( $temp, "\t" );
				$title = substr ( $temp, 0, $pos );
				$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
				$pos = strpos ( $temp, "\t" );
				$cHours = substr ( $temp, 0, $pos );
				$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
				$grade = $temp;
				$subject = trim ( $subject );
				$number = trim ( $number );
				$courseTitle = $subject . " " . $number;
				
				
				
				if ($this->checkPass ( $grade )) { // only insert the class if the student passed it
				// $CourseInfo = "INSERT INTO CourseInfo (SI, courseTitle, term,grade) VALUES ('$this->SI', '$courseTitle', '$code','$grade')";
				$CourseInfo = "INSERT INTO CourseInfo (courseTitle, term,grade, SI) VALUES ('$courseTitle', '$code','$grade', '$this->SI')";
				if (mysqli_query ( $this->db, $CourseInfo )) {
				} else {
					echo "Error: " . $CourseInfo . "<br>" . mysqli_error ( $this->db );
				}
				}
			}
		}
		// mysqli_close ( $db );
	}
	
	/*
	 * This function will take a string containing transfer credits
	 * It will parse the string and insert the classes into the db
	 */
	private function transferCredits($tcredit_string) {
		$parseText = array (); // gets the start year
		preg_match_all ( '/(\w{2,4}(.*?)\t\w{2,4}\s*\d{1,3}).*/', $tcredit_string, $parseText );
		
		for($i = 0; $i < count ( $parseText [1] ); $i ++) {
			$line = $parseText [0] [$i];
			
			$temp = $line;
			$pos = strpos ( $temp, "\t" );
			
			$temp = substr ( $temp, 0, strlen ( $temp ) );
			$pos = strpos ( $temp, "\t" );
			$subject = substr ( $temp, 0, $pos );
			$temp = substr ( $temp, $pos + 1, strlen ( $temp ) );
			$pos = strpos ( $temp, "\t" );
			$number = substr ( $temp, 0, $pos );
			
			$code = "Transfer Credit"; // not concerned with when they took it
			$grade = "P";
			$courseTitle = trim ( $subject ) . " " . trim ( $number );
			
			if ($this->checkPass ( $grade )) { // only insert the class if the student passed it
				$CourseInfo = "INSERT INTO CourseInfo (courseTitle, term,grade, SI) VALUES ('$courseTitle', '$code','$grade', '$this->SI')";
				
				if (mysqli_query ( $this->db, $CourseInfo )) {
				} else {
					echo "Error: " . $CourseInfo . "<br>" . mysqli_error ( $this->db );
				}
			
		}
		}
	}
}
class StudentProgress extends GotEnggGeneric {
	// protected $SI;
	public function __construct($si) {
		$this->setSI ( $si );
		$this->getDb ();
	}
	public function parseSemester($semester, $semNum, $db) {
		$pos = strpos ( $semester, ")" );
		$semesterArea = substr ( $semester, 0, $pos + 1 );
		$semesterArea = trim ( $semesterArea );
		$sem = "Semester " . $semNum . " " . $semesterArea;
		$semester = substr ( $semester, $pos + 2, strlen ( $semester ) );
		if ($pos = strpos ( $semester, "(" )) {
			//echo "another bracket found in semester " . $semNum . " at position " . $pos;
			$specialCase = true;
			$semester = $this->electiveCase ( $sem, $semester, $db );
		}
		while ( ($pos = strpos ( $semester, "," )) !== false ) {
			$class = substr ( $semester, 0, $pos );
			$class = trim ( $class );
			$semester = substr ( $semester, $pos + 1, strlen ( $semester ) );
			if (empty ( $class )) {
				continue;
			} else {
				$userProg = "INSERT INTO userProgress (SI,semester, class, grade, required) VALUES ('$this->SI','$sem', '$class', '0', 'true')";
				if (mysqli_query ( $this->db, $userProg )) {
				} else {
					echo "Error: " . $userProg . mysqli_error ( $this->db ) . "<br>";
				}
			}
		}
		$class = trim ( $semester );
		// echo $class . "<br>";
		$userProg = "INSERT INTO userProgress (SI,semester, class, grade, required) VALUES ('$this->SI','$sem', '$class', '0', 'true')";
		if (mysqli_query ( $this->db, $userProg )) {
		} else {
			echo "Error: " . $userProg . mysqli_error ( $this->db ) . "<br>";
		}
		
		// echo "<br>";
	}
	function electiveCase($sem, $semester, $db) {
		$posComma = strpos ( $semester, "," );
		$posStart = strpos ( $semester, "(" );
		$posEnd = strpos ( $semester, ")" );
		if ($posComma === false) {
			echo "false";
			// start string from 0 to posEnd;
			$class = substr ( $semester, 0, $posEnd );
			$class = trim ( $class );
			echo "<br>";
			echo "class = " . $class . "<br>";
			// $semester = substr($semester, $posEnd + 1, strlen($semester));
			$semester = substr_replace ( $semester, "", 0, $posEnd + 1 );
			$userProg = "INSERT INTO userProgress (SI,semester, class, grade, required) VALUES ('$this->SI','$sem', '$class', '0', 'true')";
			if (mysqli_query ( $this->db, $userProg )) {
			} else {
				echo "Error: " . $userProg . mysqli_error ( $this->db ) . "<br>";
			}
		} else {
			// echo "true";
			$class = substr ( $semester, $posComma + 1, $posEnd );
			$class = trim ( $class );
			// echo "<br>";
			// echo "class = " . $class . "<br>";
			// $semester = substr($semester, $posEnd + 1, strlen($semester));
			$semester = substr_replace ( $semester, "", $posComma, $posEnd + 1 );
			$posComma = strpos ( $semester, "," );
			$semester = substr_replace ( $semester, "", $posComma - 1, $posComma );
			$userProg = "INSERT INTO userProgress (SI,semester, class, grade, required) VALUES ('$this->SI','$sem', '$class', '0','true')";
			// echo $userProg;
			if (mysqli_query ( $this->db, $userProg )) {
			} else {
				echo "Error: " . $userProg . mysqli_error ( $this->db ) . "<br>";
			}
		}
		// echo "sem = " . $semester . "<br>";
		
		return $semester;
	}
	function updateClassesCompleted($db) {
		$joinUpdate = "UPDATE userProgress\n" . "INNER JOIN CourseInfo\n" . "on CourseInfo.courseTitle = userProgress.class and CourseInfo.SI = '$this->SI'\n" . "SET userProgress.grade = CourseInfo.grade";
		// echo "</br></br></br></br>" . $joinUpdate;
		if (mysqli_query ( $this->db, $joinUpdate )) {
		} else {
			echo "Error: " . $joinUpdate . mysqli_error ( $this->db ) . "<br>";
		}
	}
	function parseElectives($electives, $db) {
		// echo $electives;
		// \w{2,4}\s\d{3}
		$parseText = array (); // gets the start year
		if (preg_match_all ( '/\w{2,4}\s\d{3}/', $electives, $parseText )) {
		} else {
			//echo "false";
		}
		$deleteEle = "DELETE FROM electivesTaken WHERE SI = " . $this->SI;
		mysqli_query ( $this->db, $deleteEle );
		for($i = 0; $i < count ( $parseText [0] ); $i ++) {
			$line = $parseText [0] [$i];
			// $update = "UPDATE userProgress WGST
			if ((strpos ( $line, 'PHIL' ) !== false) || (strpos ( $line, 'ENGL' ) !== false) || (strpos ( $line, 'RLST' ) !== false) || (strpos ( $line, 'WGST' ) !== false)) {
				$type = "Humanities Elective";
			} else {
				$type = "Approved Elective";
			}
			
			$electives = "INSERT INTO electivesTaken (SI,class, grade, type) VALUES ('$this->SI','$line', '0', '$type')";
			//echo "<br><br><br><br>" . $electives;
			if (mysqli_query ( $this->db, $electives )) {
			} else {
				echo "Error: " . $electives . mysqli_error ( $this->db ) . "<br>";
			}
		}
	}
	function joinElectives($db) {
		$joinUpdate = "UPDATE electivesTaken\n" . "INNER JOIN CourseInfo\n" . "on CourseInfo.courseTitle = electivesTaken.class and CourseInfo.SI = electivesTaken.SI\n" . "SET electivesTaken.grade = CourseInfo.grade";
		if (mysqli_query ( $this->db, $joinUpdate )) {
		} else {
			echo "Error: " . $joinUpdate . mysqli_error ( $this->db ) . "<br>";
		}
	}
	public function updateElectiveProgress($db){
		$sql = "CREATE TEMPORARY TABLE IF NOT EXISTS table2 AS (SELECT * from electivesTaken where electivesTaken.SI = '$this->SI')";
		//$result = mysqli_query ( $this->db, $getNotIn );
		if (mysqli_query ( $db, $sql )) {
		} else {
			echo "Error: " . $sql. mysqli_error ( $db ) . "<br>";
		}
		$sql = "CREATE TEMPORARY TABLE IF NOT EXISTS tempTable AS (SELECT * from userProgress where userProgress.SI = '$this->SI')";
		
		//$result = mysqli_query ( $this->db, $getNotIn );
		if (mysqli_query ( $db, $sql )) {
		} else {
			echo "Error: " . $sql. mysqli_error ( $db ) . "<br>";
		}
		$sql = "UPDATE userProgress uP,(
		  SELECT AAA.id, AAA.semester, AAA.class, AAA.grade, BBB.class as class1, BBB.grade as grade1, BBB.type FROM
		
		  (select id, semester, class, grade,SI,
		  ( CASE class WHEN @curType THEN @curRow := @curRow + 1 ELSE @curRow := 1 AND @curType := class END ) + 1 AS rank
		  from tempTable, (SELECT @curRow := 0, @curType := '') r ORDER BY class) AAA
		
		  INNER JOIN (
		  select electivesTakenId, class, grade, type,SI, (
		  CASE type
		  WHEN @curType
		  THEN @curRow := @curRow + 1
		  ELSE @curRow := 1 AND @curType := type END
		  ) + 1 AS rank from table2 ,( SELECT @curRow := 0, @curType := '') r
		  where grade <> '0'
		  ORDER BY type
		  )
		  BBB ON AAA.class = BBB.type AND AAA.RANK = BBB.RANK)i SET uP.grade = i.grade1, uP.class = CONCAT(i.class1, ' - ', i.type) where uP.id = i.id";
		mysqli_query ( $db , $sql );
		
	}
	public function getClassNotInProgress($db) {
		$getNotIn = "SELECT CourseInfo.courseTitle from CourseInfo LEFT JOIN userProgress ON userProgress.class LIKE CONCAT('%', CourseInfo.courseTitle, '%') WHERE userProgress.class IS NULL AND CourseInfo.courseTitle NOT LIKE '%ENGG 0%' AND CourseInfo.SI = " . $this->SI;
		
		$result = mysqli_query ( $this->db, $getNotIn );
		return $result;
	}
	public function updateRequired($db) {
		$uReq = "UPDATE userProgress SET userProgress.required = 'false' where userProgress.class LIKE '%Elective%' ";
		if (mysqli_query ( $this->db, $uReq )) {
		} else {
			echo "Error: " . $uReq . mysqli_error ( $this->db ) . "<br>";
		}
	}
	/*
	 * private function setSI($si) {
	 * $this->SI = $si;
	 * }
	 */
}
class AvailibleSemesters extends GotEnggGeneric {
	protected $checkSemesters;

	/*
	 * constructor
	 */
	public function __construct() {
		$this->getDb ();
	}

	/*
	 * This function checks if the semesetersGenerated appear in the database
	 */
	public function getAvailibleSemesters() {

		$this->generateSemesters ();

		for($i = 0; $i < count ( $this->checkSemesters ); $i ++) {
			for($j = 0; $j < 3; $j ++) {

				$sql = "SELECT * FROM CoursesOffered WHERE semester_offered LIKE '%" . $this->checkSemesters [$i] [$j] . "%'"; // search the DB for matches
				$result = mysqli_query ( $this->db, $sql );

				if (mysqli_num_rows ( $result ) != 0) {
						
					echo "<option value='" . $this->checkSemesters [$i] [$j] . "'>" . $this->checkSemesters [$i] [$j] . "</option>";
				}
			}
		}
	}

	/*
	 * This function takes the current year into account and generates a list of possible next semesters
	 */
	public function generateSemesters() {
		$date = date ( "Y/m/d" ); // gets the current date
		$dateArray = explode ( "/", $date ); // splits the date into year/day/month
		 
		// years that need to be checked
		$checkyears = array (
				(intval ( $dateArray [0] ) + 1),
				(intval ( $dateArray [0] )),
				(intval ( $dateArray [0] ) - 1)
		);

		for($i = 0; $i < 3; $i ++) {
				
			$this->checkSemesters [$i] = array (
					strval ( $checkyears [$i] . " Winter" ),
					strval ( $checkyears [$i] . " Fall" ),
					strval ( $checkyears [$i] . " Spring & Summer" )
			);
		}
	}
}

/**
 * **********************************************************************************************************************************
 */
class NextSemester extends GotEnggGeneric {

	protected $semester;
	protected $optimalClasses = array ();

	public function __construct($Nsemester, $si) {
		$this->setText ( $Nsemester );
		$this->setSI ( $si );
		$this->getDb ();
	}

	private function setText($Nsemester) {
		$this->semester = $Nsemester;
	}

	/**
	 * **************************************************************
	 */
	// this function will attempt to determin the optimal classes for a student to take in the followin semester
	// assumes that the userProgress table contains the classes taken by the student compared to the schedule
	public function optimalSemester() {
		$sql = "SELECT * FROM userProgress  WHERE SI = " . $this->SI;

		if (! $this->db) {
			die ( 'Not connected : ' . mysqli_error () );
		}

		$result = mysqli_query ( $this->db, $sql );
		$numRows = mysqli_num_rows ( $result );

		if ($numRows == 0) { // check to make sure that the userProgress query was effective
			echo "error: no user progress known";
		} else {
		}

		while ( $evalClass = mysqli_fetch_assoc ( $result ) ) { // Creates a loop to loop through results to check if the student passed the given course
				
			$evalClass ['pass'] = $this->checkPass ( $evalClass ['grade'] ); // checks if the class has been completed
				
			if ($evalClass ['pass'] != 1) {

				$evalClass ['semNum'] = $this->getSemNum ( $evalClass ['semester'] );

				$evalClass ['priority'] = 1; // sets the priority to the lowest level (int value)

				if (preg_match_all ( '/\w{2,4}\s\d{3}/', $evalClass ['class'], $className )) {
					$evalClass ['class'] = $className [0] [0];
				}

				$classInfo = array ();
				$this->getClassInfo ( $evalClass ['class'], $classInfo, $this->db );

				if (! empty ( $classInfo )) {
						
					// checks if the class is offered in the desired semester
					$evalClass ['offered'] = $this->checkOffered ( $classInfo ['semester_offered'] );
						
					// checks if the class is offered more than once per year
					// increments the priority by adding the return value of the function
					$evalClass ['priority'] = $evalClass ['priority'] + $this->checkFlexibility ( $classInfo ['semester_offered'] );
						
					// checks if the class is a pre_req for any other classes
					// adjusts the priority accordingly
					$evalClass ['priority'] = $evalClass ['priority'] + $this->checkPreReqs ( $evalClass ['class'], $this->db );
						
					// checks if the student has the pre-reqs reqired to take the class
					$evalClass ['has-reqs'] = $this->checkHasPreReqs ( $classInfo ['pre_reqs'], $this->db, "CourseInfo" );
						
				} else { // the class was not found in the DB, most likely an elective
					$evalClass ['priority'] = 1;
					$evalClass ['offered'] = "yes"; // possible elective string
					$evalClass ['has-reqs'] = "yes";
				}

				// this will determine if the student has passed the class
				if ($evalClass ['offered'] == "yes" && $evalClass ['has-reqs'] == "yes") {
						
					$this->addToList ( $evalClass ['class'], $evalClass ['priority'], $evalClass ['semNum'] );
				}

				mysqli_free_result ( $classInfo );
			}
		}
		$this->sort ();
		$this->printResults ();
	}

	/**
	 * *********************
	 * This function returns the semester number as indicated in the semester string
	 * It takes a strring as a parameter and finds the value
	 * @
	 */
	protected function getSemNum($semString) {
		preg_match ( '/\d/', $semString, $matches ); // gets the suggested semester number from schedule
		return $matches [0];
	}
	/**
	 * ********************************************
	 * This function takes a reference to an array and class name
	 * It will return the semesters string that the class is offered in along with the pre-reqs
	 */
	protected function getClassInfo($className, &$info, $db) {
		$sql = "SELECT * FROM CoursesOffered Where courseName Like '%" . $className . "%'";
		$info = mysqli_query ( $db, $sql );
		$info = mysqli_fetch_assoc ( $info );
	}

	/**
	 * *************************************************************
	 * This function searches a string for matches with the semester
	 * It returns "yes" if there is a match and "no" if there isn't
	 */
	protected function checkOffered($semString) {
		if (strpos ( $semString, $this->semester ) !== FALSE) {
				
			$evalClass ['offered'] = "yes"; // boolean meaning the class is offered
			return "yes";
		} else {
			$evalClass ['offered'] = "no"; // class is not offered in the given semester
			return "no";
		}
	}

	/**
	 * *************************************************************
	 */
	// This function take in the value of the semester offered of a class
	// This function will check if a class is flexible
	// This means that teh class is offered more that once per year.
	// if it is then the return value is 1 and if it isn't, then the return value is 0
	protected function checkFlexibility($semString) {
		$frequency = 0; // this value will count the frequency of a course
		if (strpos ( $semString, "Winter" ) !== FALSE) {
			$frequency ++;
		}
		if (strpos ( $semString, "Spring" ) !== FALSE) {
			$frequency ++;
		}
		if (strpos ( $semString, "Fall" ) !== FALSE) {
			$frequency ++;
		}

		if ($frequency > 1) {
			return 0;
		} else {
			return 1;
		}
	}

	/**
	 * *************************************************************
	 */
	// This function will check if a class pre_req for any other classes.
	// This is done by querying the CoursesOffered table to look for matches in the pre_reqs colum
	// if no matches then the class is not a pre_req and function will return 0
	// if there are matches, the function must check to see if the class is required by the schedule
	// or if it is an elective. in the case that the class is a pre_req for an elective, the function returns 1
	// in the case it is a pre_req for a required class, then the function returns 2
	protected function checkPreReqs($class, $db) {
		$sql = "SELECT * FROM CoursesOffered WHERE pre_reqs LIKE '%" . $class . "%'";
		$result = mysqli_query ( $db, $sql );

		if (mysqli_num_rows ( $result ) == 0) {
			return 0; // the course is not a pre_req
		}

		else {
				
			while ( $required = mysqli_fetch_assoc ( $result ) ) { // gets a list of classes that this class is a pre_req for (These will be the required courses by the schedule)

				$sql = "SELECT * FROM userProgress WHERE class LIKE '%" . $required ['courseName'] . "%' AND SI ='" . $this->SI . "'";
				$RequiredResult = mysqli_query ( $db, $sql );

				if (mysqli_num_rows ( $RequiredResult ) > 0) {
					return 2; // the course is a pre-req for a required course
				}
			}
				
			return 1; // the class is a pre-req for an elective
		}
	}

	/**
	 * *************************************************************
	 */
	// This function checks if a student has passed the course based on their grade
	// returns "pass" if they have completed the course
	// returns "0" if they haven't signed up for the course
	// returns "Currently Registered if they have signed up for the course
	protected function checkPass($grade) {
		if ((intval ( $grade ) >= 50 && intval ( $grade ) <= 100) || strpos ( $grade, "P" ) !== False) {
			return 1;
		} else if ($grade == "Currently Registered") {
			return "Currently Registered"; // the student has registerd but not completed
		} else {
			return 0; // students hasn't registered
		}
	}

	/**
	 * ********************************************************
	 * This function will check if the user has the reqired pre-reqs to take the class at this time.
	 */

	protected function checkHasPreReqs($preReq, $db, $table) {

		if ($preReq == "" || preg_match ( '/\w{2,4}\s\d{3}/', $preReq ) == 0) {
				
			return "yes"; // this means that the class has no pre-reqs
		}

		else if (strpos ( $preReq, "concurrent" ) !== FALSE || strpos ( $preReq, "Concurrent" ) !== FALSE) { // this is temp until i make a functions to check the concurrency

			 
			return "yes";
		}

		else if (strpos ( $preReq, "or" ) !== FALSE || strpos ( $preReq, "One of" ) !== FALSE) {
			$dividedPreReq = preg_split ( '/\sor\s/', $preReq ); // special case is considered where the you can take one pre-req OR another
			$flag = "no"; // pre set flag meaning you don't have the pre reqs yet
				
			for($k = 0; $k < count ( $dividedPreReq ); $k ++) {


				if ($this->checkPreReqMatches ( strval ( $dividedPreReq [$k] ), $db, $table )) {
					return "yes"; // checks one substring == "yes" then the student can take the class
				}
			}
		} else {
			if ($this->checkPreReqMatches ( strval ( $preReq ), $db, $table )) {
				 
				return "yes";
			}
		}

		return "no";
	}

	/**
	 * *******************************************************
	 * Returns yes or no depending on whether the pre reqs are in the user progress table
	 * the function returns "no" if the student is missing a pre-req
	 * takes (string,db, table name(string))
	 */
	protected function checkPreReqMatches($preString, $db, $table) {
		$flag = TRUE; // we first assume the class can be taken until we prove otherwise


		$preString = str_replace ( " and ", " ", $preString );
		$l = preg_match_all ( '/\w{2,4}\s\d{3}/', $preString, $matches );

		if ($l != 0) {
				
			for($j = 0; $j < count ( $matches [0] ); $j ++) { // check user progress to see if the student has taken the class
				 
				if (! $db) {
					die ( 'Not connected : ' . mysqli_error () );
				}

				if ($matches [0] [$j] != "") {
					$sql = "SELECT * FROM " . $table . " WHERE courseTitle LIKE '%" . $matches [0] [$j] . "%' AND SI = '" . $this->SI . "'"; // ////add ID
					$RequiredResults = mysqli_query ( $db, $sql );
						
						
					if (mysqli_num_rows ( $RequiredResults ) != 0) { // check if a result has returned

						while ( $Required = mysqli_fetch_assoc ( $RequiredResults ) ) {

							// calls a function to check if the class was taken and passed or currently registered by the student
							if (strpos ( $Required ['courseTitle'], strval ( $matches [0] [$j] ) ) !== FALSE) {
								// do nothing because the flag will already be set
							} else {
									
								$flag = FALSE; // the student is missing a pre-req
							}
						}
					} else { // no result has been returned
						$flag = FALSE; // the student is missing a pre-req
					}
				}
			}
		} else {
			$flag = TRUE; // no class pre-reqs in this string thus the student can still take the class
		}

		return $flag;
	}

	/**
	 * *************************************************************
	 * This function will add the class to the array if it is a viable class
	 */
	protected function addToList($name, $priority, $semNum) {

		$this->optimalClasses [] = array (
				$name,
				$priority,
				$semNum
		);
	}

	/**
	 * ***************************************************
	 * This functions will print the results of a the array given
	 * It will also order the array from highest priority to lowest
	 * If there is conflict, the semester that the class is offered in will be
	 * taken into account as well
	 */
	protected function printResults() {
		echo "The classes that are optimal next semester are: </br></br> ";

		for($i = 0; $i < count ( $this->optimalClasses ); $i ++) {
			echo $this->optimalClasses [$i] [0] . "     Priority-value:  " . $this->optimalClasses [$i] [1] . "</br>";
		}
	}

	/**
	 * ************************************************
	 * this function will sort the array into prioriy value and semester number
	 */
	protected function sort() {
		for($j = count ( $this->optimalClasses ) - 1; $j > 0; $j --)
			for($i = 0; $i < $j; $i ++) {
				if (($this->optimalClasses [$i] [1] < $this->optimalClasses [$i + 1] [1]) || ($this->optimalClasses [$i] [1] == $this->optimalClasses [$i + 1] [1] && $this->optimalClasses [$i] [2] > $this->optimalClasses [$i + 1] [1])) {
						
					$this->swap ( $i, $i + 1 );
				}
			}

		return;
	}

	/**
	 * ************************************************
	 * swap function to re arrange the array
	 * swaps the position of the 2 keys
	 */
	protected function swap($key1, $key2) {
		$temp = array (
				$this->optimalClasses [$key1] [0],
				$this->optimalClasses [$key1] [1],
				$this->optimalClasses [$key1] [2]
		);

		$this->optimalClasses [$key1] [0] = $this->optimalClasses [$key2] [0];
		$this->optimalClasses [$key1] [1] = $this->optimalClasses [$key2] [1];
		$this->optimalClasses [$key1] [2] = $this->optimalClasses [$key2] [2];

		$this->optimalClasses [$key2] [0] = $temp [0];
		$this->optimalClasses [$key2] [1] = $temp [1];
		$this->optimalClasses [$key2] [2] = $temp [1];
	}
}

/**
 * ************************************************************************************************************************************
 * This Class will be used to determine optimal route for the student to complete their degree
 *
 */
class FutureSchedule extends NextSemester {
	private $printedClasses = array ();
	private $unPrintedClasses = array ();
	private $unAccountedClasses = array ();
	protected $semester = array (); // holds the year and the semester value (10,20,30) , both are int values

	public function __construct($si) {
		$this->setSI ( $si );
		$this->getDb ();
		$this->setNextSemester (); // sets the current next semester

		$sql = "DELETE FROM SuggestedSchedule WHERE SI = " . $this->SI;
		mysqli_query ( $this->db, $sql );
	}

	/*
	 * This function is a public function that can be called to insert the optimal schedule into the Suggested Schedules DB
	 */
	public function printOptimalSchedule() {

		$sql = "SELECT * FROM userProgress WHERE SI = " . $this->SI;
		$result = mysqli_query ( $this->db, $sql );
		$numRows = mysqli_num_rows ( $result );

		if ($numRows == 0) { // check to make sure that the userProgress query was effective
			echo "error: no user progress known";
		} else {
		}

		while ( $evalClass = mysqli_fetch_assoc ( $result ) ) { // Creates a loop to loop through results to check if the student passed the given course
				
			$evalClass ['pass'] = $this->checkPass ( $evalClass ['grade'] );
				
			// updates the class name, if it was modified
			if (preg_match_all ( '/\w{2,4}\s\d{3}/', $evalClass ['class'], $className )) {
				$evalClass ['class'] = $className [0] [0];
			}
			$evalClass ['term'] = $this->getTerm ( $evalClass ['class'], $this->db );
				
			// send the passed classes to the printed array
			// insert into the DB
				
			if ($evalClass ['pass'] == 1) {

				// $this->insertToDB($class, $term, $priority, $pass, $unAccounted, $db)
				$this->insertToDB ( $evalClass ['class'], $evalClass ['term'], 0, 1, 0, 0, $this->db );
				$this->toPrintedClasses ( $evalClass ['class'], $evalClass ['term'], 0, true );
			}  // classes that are currently in progress..

			else if ($evalClass ['term'] != "") {

				$this->insertToDB ( $evalClass ['class'], $evalClass ['term'], 0, 0, 0, 1, $this->db );
				$this->toPrintedClasses ( $evalClass ['class'], $evalClass ['term'], 0, false );
			} else

			{

				$evalClass ['semNum'] = $this->getSemNum ( $evalClass ['semester'] );
				$evalClass ['priority'] = 1;

				$classInfo = array ();
				$this->getClassInfo ( $evalClass ['class'], $classInfo, $this->db );

				if (! empty ( $classInfo )) {
						
					$evalClass ['semester_offered'] = $this->getSemOffered ( $classInfo ['semester_offered'] );
						
					$evalClass ['priority'] = $evalClass ['priority'] + $this->checkFlexibility ( $evalClass ['semester_offered'] );
						
					$evalClass ['priority'] = $evalClass ['priority'] + $this->checkPreReqs ( $evalClass ['class'], $this->db );
						
					// checks if the student has the pre-reqs reqired to take the class
					$evalClass ['pre_reqs'] = $classInfo ['pre_reqs'];
						
				} else {
					$evalClass ['semester_offered'] = "/10/20/30";
					$evalClass ['pre_reqs'] = "";
				}

				$this->toUnPrintedClasses ( $evalClass ['class'], intval ( $evalClass ['priority'] ), intval ( $evalClass ['semNum'] ), $evalClass ['semester_offered'], $evalClass ['pre_reqs'] );
			}
		}

		$this->getClassNotInProgress ( $this->db );
		$this->iterateSemester ();
	}

	/**
	 * ********************************************************
	 * This function interates through the unprintedClasses to add them to the printedClass array
	 */
	public function iterateSemester() {

		// gets the current semester
		$termArray = $this->getSemester ();
		// print_r ( $termArray );

		$term = implode ( "", $termArray );
		$previousCount=0;

		// loop until all classes have been placed
		while ( count ( $this->unPrintedClasses ) > 0  && count ( $this->unPrintedClasses ) != $previousCount) { // watch for infinite loop case *******
			 
				
			$previousCount= count ( $this->unPrintedClasses ); // update previous count
				
			$leftOver = 0;
			// loop through current semester
			while ( $this->getSpacesLeft ( $term ) > 0 && $leftOver != $this->getSpacesLeft ( $term ) ) {

				$leftOver = $this->getSpacesLeft ( $term );
				$nextClass = null; // this will hold the key of the next value to b inserted

				// loops through unplaced classes
				for($n = 0; $n < count ( $this->unPrintedClasses ); $n ++) {
						
					$pre = $this->checkHasPreReqs ( strval ( $this->unPrintedClasses [$n] [4] ), $this->db, "SuggestedSchedule" );
					// checks pre-reqs
					if (strpos ( $this->unPrintedClasses [$n] [3], strval ( $termArray ['sem'] ) ) !== FALSE && $pre == "yes") {
						// check if the nextclass is empty
						if (empty ( $nextClass )) {
								
							$nextClass = array (
									$this->unPrintedClasses [$n] [0],
									$this->unPrintedClasses [$n] [1],
									$this->unPrintedClasses [$n] [2],
									$this->unPrintedClasses [$n] [4],
									FALSE
							);
							$key = $n;
								
						} else if (intval ( $nextClass [1] ) < intval ( $this->unPrintedClasses [$n] [1] )) {
								
							// change the next class to be printed
							$nextClass [0] = $this->unPrintedClasses [$n] [0]; // name
							$nextClass [1] = $this->unPrintedClasses [$n] [1]; // priority
							$nextClass [2] = $this->unPrintedClasses [$n] [2]; // sem num
							$nextClass [3] = $this->unPrintedClasses [$n] [4];
							$key = $n;
								
							// check if this class is optimal....
						}
							
					}
				}
				// adds the class to the current semester
				if (! empty ( $nextClass )) {
					$this->toPrintedClasses ( $nextClass [0], $term, $nextClass [1], false );
					$this->insertToDB ( $nextClass [0], $term, $nextClass [1], 0, 0, 0, $this->db );
					$this->removeRow ( $key );
					$leftOver ++;
				}


			}
			// increment the semester
			$this->incrementSemester ();
			$termArray = $this->getSemester ();
			$term = implode ( "", $termArray ); // gets the term in string format
			$term = intval ( $term );
		}
		if  (  count ( $this->unPrintedClasses ) >0){
			$this->printunknownTerm();
		}
	}

	/*
	 * this function prints the classes that were un able to be placed by the schedule planning algorithm
	 */
	protected function printunknownTerm(){


		while(count($this->unPrintedClasses)>0){
				
			$i=(count($this->unPrintedClasses))-1;  // last position in the array
				
			$this->toPrintedClasses ( $this->unPrintedClasses [$i] [0], "Undetermined", $this->unPrintedClasses [$i] [1], false );
			$this->insertToDB ( $this->unPrintedClasses [$i] [0], "Undetermined",$this->unPrintedClasses [$i] [1], 0, 0, 0, $this->db );
			$this->removeRow ( $i );
		}

	}

	/**
	 * **************************?
	 * This funciton is used to unset a row in the unPrinted Array
	 * it will set the data to be the last element in the array and it will then delete the last element
	 */
	protected function removeRow($key) {
		$length = count ( $this->unPrintedClasses );

		if (($length - 1) == $key) {
			unset ( $this->unPrintedClasses [$key] );
		} else {
			$this->unPrintedClasses [$key] [0] = $this->unPrintedClasses [$length - 1] [0];
			$this->unPrintedClasses [$key] [1] = $this->unPrintedClasses [$length - 1] [1];
			$this->unPrintedClasses [$key] [2] = $this->unPrintedClasses [$length - 1] [2];
			$this->unPrintedClasses [$key] [3] = $this->unPrintedClasses [$length - 1] [3];
			$this->unPrintedClasses [$key] [4] = $this->unPrintedClasses [$length - 1] [4];
			unset ( $this->unPrintedClasses [$length - 1] );
		}
	}

	/**
	 * ********************************
	 * This function inserts the classes into the DB
	 * (string,string,int,int,bool, bool,$db)
	 */
	protected function insertToDB($class, $term, $priority, $pass, $unAccounted, $inProgress, $db) {
		$sql = "INSERT INTO SuggestedSchedule (id, SI, courseTitle, term, pass, priority, semNum, unAccounted, inProgress)
				VALUES (NULL, '" . $this->SI . "', '" . $class . "', '" . strval ( $term ) . "', '" . $pass . "', '" . $priority . "', '', '" . $unAccounted . "','" . $inProgress . "')";
		mysqli_query ( $db, $sql );
	}

	/**
	 * *******************************************************
	 * This function gets the number of classes that are still able to be filled by the current semester
	 *
	 *
	 * ////// max Class count
	 */
	protected function getSpacesLeft($term) {
		$numOfClasses = MAX_CLASS_COUNT;
		for($i = 0; $i < count ( $this->printedClasses ); $i ++) {
			if ($this->printedClasses [$i] [1] == $term) {
				$numOfClasses --;
			}
		}
		return intval ( $numOfClasses );
	}

	/**
	 * ****************************************
	 * This function will take in a string containing a list of when the class is offere
	 * It will serach the String for "winter", "Fall", and "Spring"
	 * a value will be assigned for each accordingly
	 */
	protected function getSemOffered($semString) {
		$offeredIn = "";
		if (strpos ( $semString, "Winter" ) !== FALSE) {
			$offeredIn .= "/10";
		}
		if (strpos ( $semString, "Spring" ) !== FALSE) {
			$offeredIn .= "/20";
		}
		if (strpos ( $semString, "Fall" ) !== FALSE) {
			$offeredIn .= "/30";
		}
		return $offeredIn;
	}

	/**
	 * ****************************************************
	 * This function will add a class to the printedClasses array
	 * *Note the priority is only there for classes that have yet to be taken
	 * If the class has been take, the priority will be 0
	 * (string, string, int,bool)
	 */
	protected function toPrintedClasses($class, $term, $priority, $pass) {
		$this->printedClasses [] = array (
				$class,
				$term,
				$priority,
				$pass
		);
	}

	/**
	 * ****************************************************
	 * This function will add a class to the unPrintedClasses array
	 * (String, int,int,string,string)
	 */
	protected function toUnPrintedClasses($class, $priority, $sem_num, $sem_offered, $pre_reqs) { // $has_reqs) {
		$this->unPrintedClasses [] = array (
				$class,
				$priority,
				$sem_num,
				$sem_offered,
				$pre_reqs
		);

	}

	/**
	 * ****************************************************
	 * This function will get the term that the class was taken by the student
	 * Returns the value as a string or null if the class wasn't found.
	 */
	protected function getTerm($class, $db) {
		$sql = "SELECT term FROM CourseInfo WHERE courseTitle LIKE '%" . $class . "%' AND SI = '" . $this->SI . "'"; // // add SI
		$result = mysqli_query ( $db, $sql );
		$term = mysqli_fetch_assoc ( $result );
		$rows = mysqli_num_rows ( $result );
			

		if ($rows == 1) {
			return ( $term ['term'] );
		} else {
			return null;
		}
	}

	/*
	 * This function will return a string containing the value of the next semester
	 */
	protected function setNextSemester() {
		$date = date ( "Y/m/d" ); // gets the current date

		$dateArray = explode ( "/", $date ); // splits the date into year/day/month

		if (intval ( $dateArray [1] ) >= 8 && intval ( $dateArray [1] < 10 )) { // value for fall
			$this->semester ['year'] = intval ( $dateArray [0] );
			$this->semester ['sem'] = 30;
		} else if (intval ( $dateArray [1] ) >= 2 && intval ( $dateArray [1] < 8 )) { // value for spring
			$this->semester ['year'] = intval ( $dateArray [0] );
			$this->semester ['sem'] = 20;
		} else if (intval ( $dateArray [1] ) >= 10 && intval ( $dateArray [1] < 2 )) { // value for winter
			$this->semester ['year'] = intval ( $dateArray [0] );
			$this->semester ['sem'] = 10;
		}
	}

	/*
	 * This function increments the current semester
	 */
	protected function incrementSemester() {
		if ($this->semester ['sem'] == 10) {
			$this->semester ['sem'] = 20;
		} else if ($this->semester ['sem'] == 20) {
			$this->semester ['sem'] = 30;
		} else if ($this->semester ['sem'] == 30) {
			$this->semester ['year'] ++;
			$this->semester ['sem'] = 10;
		}
	}

	/*
	 * getter for next semester
	 */
	public function getSemester() {
		return $this->semester;
	}

	/**
	 * *************************************************************
	 * This function will print the the classes in the printedClass array with the given term.
	 * This function takes a term as a parameter
	 */
	public function printTerm($term) {
		for($i = 0; $i < count ( $this->printedClasses ); $i ++) {
			if ($this->printedClasses [$i] [1] == $term) {
				echo $this->printedClasses [$i] [0] . " " . $this->printedClasses [$i] [1] . " " . $this->printedClasses [$i] [2] . " </br>";
			}
		}
	}

	/**
	 * **************************
	 *
	 * gets the left over classes from userProgress
	 */
	public function getClassNotInProgress($db) {
		$sql = "SELECT CourseInfo.courseTitle, CourseInfo.term, CourseInfo.grade from CourseInfo LEFT JOIN userProgress ON userProgress.class LIKE CONCAT('%', CourseInfo.courseTitle, '%') WHERE userProgress.class IS NULL AND CourseInfo.SI = " . $this->SI;

		$result = mysqli_query ( $db, $sql );

		While ( $row = mysqli_fetch_assoc ( $result ) ) {
			$this->setUnAccountedClasses ( $row ['courseTitle'], $row ['term'], 0 );
				
			if ($this->checkPass ( $row ['grade'] )) {
				$this->insertToDB ( $row ['courseTitle'], $row ['term'], 0, 1, 1, 0, $db );
			}
				
		}
	}

	/**
	 * *****************************************************
	 * This function sets the un accounted for classes.
	 */
	protected function setUnAccountedClasses($class, $term, $priority) {
		$this->unAccountedClasses [] = array (
				$class,
				$term,
				$priority
		);
	}
}

/**************************************************************************************************/
class Logout extends GotEnggGeneric {
	public function __construct($si) {
		$this->setSI ( $si );
		$this->getDb ();
	}
	
	// this function will wipe all of the tables containing the SI
	public function logout() {
		if (! $this->db) {
			die ( 'Not connected : ' . mysqli_error () );
			return false;
		} else {
			$sql = "DELETE FROM StuInfo WHERE SI = " . $this->SI;
			mysqli_query ( $this->db, $sql );
			
			$sql = "DELETE FROM CourseInfo WHERE SI = " . $this->SI;
			mysqli_query ( $this->db, $sql );
			
			$sql = "DELETE FROM electivesTaken WHERE SI = " . $this->SI;
			mysqli_query ( $this->db, $sql );
			
			$sql = "DELETE FROM SuggestedSchedule WHERE SI = " . $this->SI;
			mysqli_query ( $this->db, $sql );
			
			$sql = "DELETE FROM userProgress WHERE SI = " . $this->SI;
			mysqli_query ( $this->db, $sql );
			
			return true;
		}
	}
}
class Timeout extends GotEnggGeneric {
	
	public function __construct($si) {
		$this->setSI ( $si );
		$this->getDb ();
	}
	public function updateTimestamp() {
		$timestamp = date('Y-m-d G:i:s');
		$update = "update StuInfo set StuInfo.timeIn = '$timestamp' where StuInfo.SI = '$this->SI'";
		//echo "<br><br><br>" . $update;
		mysqli_query ( $this->db, $update );
	}
}
// This is a genneric abstract class that will be inherited by all the classes that require a DB and an SI number
abstract class GotEnggGeneric {
	protected $db;
	protected $SI;
	protected function setSI($si) {
		$this->SI = $si;
	}
	// sets the instace of the db
	protected function getDb() {
		$connection = new dbConnection ();
		$connection->connect ();
		$this->db = $connection->getDb ();
	}
	public function __destruct() {
		mysqli_close ( $this->db );
	}
}

// this will be a generic class that the parsers will inherite
abstract class Parser extends GotEnggGeneric {
	protected $text;
	public function __construct($conText, $si) {
		$this->setSI ( $si );
		$this->setText ( $conText );
		$this->getDb ();
	}
	public function redirect($url) {
		
		// "refresh:1;url=Classes.php"
		header ( $url );
	}
	protected function setText($conText) {
		$this->text = $conText;
	}
	
	// This function checks if a student has passed the course based on their grade
	// returns "pass" if they have completed the course
	// returns "0" if they haven't signed up for the course
	// returns "Currently Registered if they have signed up for the course
	protected function checkPass($grade) {
		
		
		if ((intval ( $grade ) >= 50 && intval ( $grade ) <= 100) || strpos ( $grade, "P" ) !== False) {
			return 1;
		} else if ($grade == "Currently Registered" ||  strpos($grade,"Registered") !== False) {
			return "Currently Registered"; // the student has registerd but not completed
		} else {
			return 0; // students hasn't registered
		}
	}
}
class FutureClassesParser extends Parser{

	
	public function parseFutureClasses($text) {
		
		
		$text = str_replace ( "'", "''", $text ); // replace ' so that sql can query
		//echo $text;
		
		$text = strip_tags($text);
		preg_match_all ( '/(\w{2,4}\s\d{3})\s\-/', $text, $className ); // gets the semester that the class is offered in
		                                                            // pos 0 contains the year and semester extracted from the regex
		                                                            // print_r ($className);
		                                                            
		// extracts the name and title of the offered classes
		                                                            // this is a 2D array : column 0 contains both for each match, column 1 contains the class name and number, column 2 contains the classe title
		                                                            // the rows contain each different class found by the reg ex
		                                                            // print_r ($classes);
		
		$termName = array ();
		preg_match_all ( '/(\d{4})\s(Winter|Fall|Spring)/', $text, $termName );
		
		if (strpos ( $termName [0] [0], "Spring" ) !== FALSE) { // since i can't get a Regex to grab all of the sring summer
			$termName [0] [0] = $termName [0] [0] . " & Summer";
		}
		// echo $termName[0][0];
		
		// $termString = preg_split('/(\w{2,4}\s\d{3})\s\-/',$text); // splits the text into an array containing the text written in between each class
		// pos 0 is useless since it contains all the text before the first class occures
		// '/\*{2,3}.*\*{2,3}/'
		// pos 0 are the required courses and pos 1 are the electives
		
		for($i = 0; $i < count ( $className [1] ); $i ++) // loops through all the entries to find the pre-reqs for the the classes
{
			// echo count($className) . "</br>";
			// preg_match_all('/\d{4}\s(Winter|Fall|Spring & Summer)/', $termString[$i+1], $termName);
			// print_r($termName);
			echo $className [1] [$i];
			$db = mysqli_connect ( "localhost", "root", "ense400", "StudentInfo" ) or // connects to the DB
die ( "could not connect to the database: Error " . mysqli_error ( $db ) );
			
			// Checks if the class is already inserted into the DB
			$sql = "SELECT * FROM CoursesOffered WHERE courseName='" . $className [1] [$i] . "'"; // 'AND semester_offered LIKE '".$semesterOffered[0][0]."'";
			                                                                              // mysql_free_result($result);
			                                                                              // echo $sql ."<br/>";
			$result = mysqli_query ( $db, $sql );
			
			if (! $result) {
				die ( 'Query failed to execute for some reason' );
			}
			// printf ("%s (%s)\n", $row[0], $row[1]);
			
			$row = mysqli_fetch_array ( $result );
			// echo strpos($row['semester_offered'], $termName[0][0]) . "<br/>";
			// echo $row['semester_offered'];
			
			if (empty ( $row ['semester_offered'] )) {
				
				$sql = "UPDATE CoursesOffered SET semester_offered = concat( semester_offered,'" . $termName [0] [0] . "') WHERE courseName LIKE '%" . $className [1] [$i] . "%'";
				mysqli_query ( $db, $sql );
				mysqli_close ( $db );
				// echo $sql ."<br/>";
			} 

			else if (strpos ( $row ['semester_offered'], $termName [0] [0] ) !== FALSE) { // checks if the semeseter is different, if it is, then it is appended
				                                                                           // echo "hi <br/>";
			} else {
				$sql = "UPDATE CoursesOffered SET semester_offered = concat( semester_offered,', " . $termName [0] [0] . "') WHERE courseName LIKE '%" . $className [1] [$i] . "%'";
				// echo $sql;
				mysqli_query ( $db, $sql );
				mysqli_close ( $db );
			}
			
			// echo $classes[1][$i] . "<br/>";
			// echo $classes[2][$i] . "<br/>";
			// echo $pre_req[1][0];
			// echo"<br/><br/>";
		}
	}
}
class ParseOfferedClasses {
	public function parseOfferedClass($text) {
		$text = str_replace ( "'", "''", $text ); // replace ' so that sql can query
		$text = strip_tags($text);
		
		$semesterOffered = "";
		preg_match_all ( '/\d{4}\s(Winter|Fall|Spring & Summer|Spring\/Summer)/', $text, $semesterOffered ); // gets the semester that the class is offered in
		                                                                                                 // pos 0 contains the year and semester extracted from the regex
		                                                                                                 
		// print_r ($semesterOffered);
		                                                                                                 // echo "<br/>";
		                                                                                                 
		// extracts the name and title of the offered classes
		                                                                                                 // this is a 2D array : column 0 contains both for each match, column 1 contains the class name and number, column 2 contains the classe title
		                                                                                                 // the rows contain each different class found by the reg ex
		$classes = array ();
		preg_match_all ( '/(\w{2,4}\s\d{3})\s\-(.*)/', $text, $classes );
		// print_r ($classes);
		
		$pre_req_string = preg_split ( '/(\w{2,4}\s\d{3})\s\-(.*)/', $text ); // splits the text into an array containing the text written in between each class
		                                                                  // pos 0 is useless since it contains all the text before the first class occures
		                                                                  // '/\*{2,3}.*\*{2,3}/'
		                                                                  // pos 0 are the required courses and pos 1 are the electives
		
		for($i = 0; $i < count ( $pre_req_string ) - 1; $i ++) // loops through all the entries to find the pre-reqs for the the classes
{
			
			preg_match_all ( '/\*{2,3}\s?Prerequisite:\s?(.*)\*{3}/', $pre_req_string [$i + 1], $pre_req );
			
			$db = mysqli_connect ( "localhost", "root", "ense400", "StudentInfo" ) or // connects to the DB
die ( "could not connect to the database: Error " . mysqli_error ( $db ) );
			
			// Checks if the class is already inserted into the DB
			$sql = "SELECT * FROM CoursesOffered WHERE courseName='" . $classes [1] [$i] . "'"; // 'AND semester_offered LIKE '".$semesterOffered[0][0]."'";
			                                                                            // echo $sql ."<br/>";
			$result = mysqli_query ( $db, $sql );
			
			if (! $result) {
				die ( 'Query failed to execute for some reason' );
			}
			
			$row = mysqli_fetch_array ( $result, MYSQLI_NUM );
			// printf ("%s (%s)\n", $row[0], $row[1]);
			if (is_null ( $row )) {
				
				$sql = "INSERT INTO CoursesOffered (courseName,courseTitle,pre_reqs,semester_offered)  VALUES('" . $classes [1] [$i] . "','" . $classes [2] [$i] . "','" . $pre_req [1] [0] . "','" . $semesterOffered [0] [0] . "')";
				mysqli_query ( $db, $sql );
				mysqli_close ( $db );
				// echo $sql ."<br/>";
			} 

			else if (strpos ( $row ['semester_offered'], $semesterOffered [0] [0] )) { // checks if the semeseter is different, if it is, then it is appended
				
				$sql = "UPDATE CoursesOffered SET semester_offered = concat( semester_offered,' " . $semesterOffered [0] [0] . "') WHERE courseName LIKE '%" . $classes [1] [$i] . "%'";
				mysqli_query ( $db, $sql );
				mysqli_close ( $db );
			} else {
				
				//echo $row ['semester_offered'] . " </br>" . $semesterOffered [0] [0] . " </br>";
			}
			
			// echo $classes[1][$i] . "<br/>";
			// echo $classes[2][$i] . "<br/>";
			// echo $pre_req[1][0];
			// echo"<br/><br/>";
		}
		//echo '<a href="index.html">Home</a>';
	}
}
class CurrentClasses {
	protected $mostRecentSemester;
	function __construct() {
		echo "in construct";
		$recentSemester = $this->grabRecentSemester ();
		$this->setSem($recentSemester);
		//$this->loadClassSchedList ( $recentSemester );
		//$this->loadPreReqsList ( $recentSemester );
	}
	public function actualCurrentClasses($semester) {
		$data_fields = array (
				'p_term' => $semester,
				'p_calling_proc' => 'bwckschd.p_disp_dyn_sched' 
		);
		
		$curl_connection = curl_init ();
		curl_setopt ( $curl_connection, CURLOPT_CONNECTTIMEOUT, 30 );
		curl_setopt ( $curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)" );
		curl_setopt ( $curl_connection, CURLOPT_URL, 'https://banner.uregina.ca/prod/sct/bwckgens.p_proc_term_date' );
		curl_setopt ( $curl_connection, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl_connection, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $curl_connection, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $curl_connection, CURLOPT_POSTFIELDS, http_build_query ( $data_fields ) );
		
		// perform our request
		$result = curl_exec ( $curl_connection );
		
		// show information regarding the request
		print_r ( curl_getinfo ( $curl_connection ) );
		echo "<br>";
		echo curl_errno ( $curl_connection ) . '-' . curl_error ( $curl_connection );
		echo "<br>";
		// echo $result;
		
		echo "<br>";
		
		echo "BELOW IS ALL ENSE CLASSES FOR 201630";
		echo "<br>";
		$data_fields = array (
				'term_in' => '201630',
				'sel_to_cred' => '',
				'sel_title' => '',
				'sel_subj[0]' => '0',
				'sel_subj[1]' => '1',
				'sel_sess' => 'dummy',
				'sel_schd' => '%',
				'sel_schd' => 'dummy',
				'sel_ptrm' => '%',
				'sel_ptrm' => 'dummy',
				'sel_levl' => '%',
				'sel_levl' => 'dummy',
				'sel_instr' => '%',
				'sel_instr' => 'dummy',
				'sel_insm' => '%',
				'sel_insm' => 'dummy',
				'sel_from_cred' => '',
				'sel_day' => 'dummy',
				'sel_crse' => '',
				'sel_camp' => '%',
				'sel_camp' => 'dummy',
				'sel_attr' => 'dummy',
				'end_mi' => '0',
				'end_hh' => '0',
				'end_ap' => 'a',
				'begin_mi' => '0',
				'begin_hh' => '0',
				'begin_ap' => 'a' 
		);
		$data = "term_in=201630&sel_subj=dummy&sel_day=dummy&sel_schd=dummy&sel_insm=dummy&sel_camp=dummy&sel_levl=dummy&sel_sess=dummy&sel_instr=dummy&sel_ptrm=dummy&sel_attr=dummy&sel_subj=ENEL&sel_crse=&sel_title=&sel_schd=%25&sel_insm=%25&sel_from_cred=&sel_to_cred=&sel_camp=%25&sel_levl=%25&sel_ptrm=%25&sel_instr=%25&begin_hh=0&begin_mi=0&begin_ap=a&end_hh=0&end_mi=0&end_ap=a";
		curl_setopt ( $curl_connection, CURLOPT_URL, 'https://banner.uregina.ca/prod/sct/bwckschd.p_get_crse_unsec' ); // use the URL that shows up in your <form action="...url..."> tag
		curl_setopt ( $curl_connection, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl_connection, CURLOPT_POSTFIELDS, $data );
		$result = curl_exec ( $curl_connection );
		echo "<br>";
		echo $result;
		curl_close ( $ch );
		return $result;
	}
	public function clostCon($ch) {
		curl_close ( $ch );
	}
	public function currClassesAndReqs($semester) {
		$data_fields = array (
				'cat_term_in' => $semester,
				'call_proc_in' => 'bwckctlg.p_disp_dyn_ctlg' 
		);
		
		$curl_connection = curl_init ();
		curl_setopt ( $curl_connection, CURLOPT_CONNECTTIMEOUT, 30 );
		curl_setopt ( $curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)" );
		curl_setopt ( $curl_connection, CURLOPT_URL, 'https://banner.uregina.ca/prod/sct/bwckctlg.p_disp_cat_term_date' );
		curl_setopt ( $curl_connection, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl_connection, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $curl_connection, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $curl_connection, CURLOPT_POSTFIELDS, http_build_query ( $data_fields ) );
		
		// perform our request
		$result = curl_exec ( $curl_connection );
		
		// show information regarding the request
		print_r ( curl_getinfo ( $curl_connection ) );
		echo "<br>";
		echo curl_errno ( $curl_connection ) . '-' . curl_error ( $curl_connection );
		echo "<br>";
		// echo $result;
		
		echo "<br>";
		
		echo "BELOW IS ALL ENSE CLASSES FOR 201630";
		echo "<br>";
		$data_fields = array (
				'term_in' => '201630',
				'sel_to_cred' => '',
				'sel_title' => '',
				'sel_subj[0]' => '0',
				'sel_subj[1]' => '1',
				'sel_sess' => 'dummy',
				'sel_schd' => '%',
				'sel_schd' => 'dummy',
				'sel_ptrm' => '%',
				'sel_ptrm' => 'dummy',
				'sel_levl' => '%',
				'sel_levl' => 'dummy',
				'sel_instr' => '%',
				'sel_instr' => 'dummy',
				'sel_insm' => '%',
				'sel_insm' => 'dummy',
				'sel_from_cred' => '',
				'sel_day' => 'dummy',
				'sel_crse' => '',
				'sel_camp' => '%',
				'sel_camp' => 'dummy',
				'sel_attr' => 'dummy',
				'end_mi' => '0',
				'end_hh' => '0',
				'end_ap' => 'a',
				'begin_mi' => '0',
				'begin_hh' => '0',
				'begin_ap' => 'a' 
		);
		$data = "term_in=201630&call_proc_in=bwckctlg.p_disp_dyn_ctlg&sel_subj=dummy&sel_levl=dummy&sel_schd=dummy&sel_coll=dummy&sel_divs=dummy&sel_dept=dummy&sel_attr=dummy&sel_subj=ENEL&sel_crse_strt=&sel_crse_end=&sel_title=&sel_levl=%25&sel_schd=%25&sel_coll=%25&sel_divs=%25&sel_dept=%25&sel_from_cred=&sel_to_cred=&sel_attr=%25";
		curl_setopt ( $curl_connection, CURLOPT_URL, 'https://banner.uregina.ca/prod/sct/bwckctlg.p_display_courses' ); // use the URL that shows up in your <form action="...url..."> tag
		curl_setopt ( $curl_connection, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl_connection, CURLOPT_POSTFIELDS, $data );
		$result = curl_exec ( $curl_connection );
		echo "<br>";
		echo $result;
		curl_close ( $ch );
		return $result;
	}
	public function loadClassSchedList($semester) {
		echo "in";
		$classSchedForm = array ();
		$dataFields = array (
				'p_term' => $semester,
				'p_calling_proc' => 'bwckschd.p_disp_dyn_sched' 
		);
		$url = 'https://banner.uregina.ca/prod/sct/bwckgens.p_proc_term_date';
		$result = $this->listLoadingHelper ( $dataFields, $url );
		// echo $result;
		$options = $this->performDomRequest ( $result, 'subj_id', 'option' );
		foreach ( $options as $option ) {
			$value = $option->getAttribute ( 'value' );
			$text = $option->textContent;
			$classSchedForm [] = $value;
		}
		//print_r ( $classSchedForm );
		curl_close ( $curl_connection );
		return $classSchedForm;
	}
	public function grabRecentSemester() {
		$curl_connection = curl_init ();
		curl_setopt ( $curl_connection, CURLOPT_CONNECTTIMEOUT, 30 );
		curl_setopt ( $curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)" );
		curl_setopt ( $curl_connection, CURLOPT_URL, 'https://banner.uregina.ca/prod/sct/bwckschd.p_disp_dyn_sched' );
		curl_setopt ( $curl_connection, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl_connection, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $curl_connection, CURLOPT_FOLLOWLOCATION, 1 );
		// curl_setopt ( $curl_connection, CURLOPT_POSTFIELDS, http_build_query($data_fields) );
		
		// perform our request
		$result = curl_exec ( $curl_connection );
		// echo $result;
		$options = $this->performDomRequest ( $result, 'term_input_id', 'option' );
		echo "done options";
		$array = array ();
		foreach ( $options as $option ) {
			$value = $option->getAttribute ( 'value' );
			$text = $option->textContent;
			$array [] = $value;
		}
		curl_close ( $curl_connection );
		return $array [1];
	}
	public function loadPreReqsList($semester) {
		$preReqsForm = array ();
		$dataFields = array (
				'cat_term_in' => $semester,
				'call_proc_in' => 'bwckctlg.p_disp_dyn_ctlg' 
		);
		$url = 'https://banner.uregina.ca/prod/sct/bwckctlg.p_disp_cat_term_date';
		$result = $this->listLoadingHelper ( $dataFields, $url );
		// echo $result;
		$options = $this->performDomRequest ( $result, 'subj_id', 'option' );
		foreach ( $options as $option ) {
			$value = $option->getAttribute ( 'value' );
			$text = $option->textContent;
			$preReqsForm [] = $value;
		}
		echo "<br>";
		//print_r ( $preReqsForm );
		curl_close ( $curl_connection );
		return $preReqsForm;
	}
	public function listLoadingHelper($dataFields, $url) {
		$curl_connection = curl_init ();
		curl_setopt ( $curl_connection, CURLOPT_CONNECTTIMEOUT, 30 );
		curl_setopt ( $curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)" );
		curl_setopt ( $curl_connection, CURLOPT_URL, $url );
		curl_setopt ( $curl_connection, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl_connection, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $curl_connection, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $curl_connection, CURLOPT_POSTFIELDS, http_build_query ( $dataFields ) );
		
		// perform our request
		$result = curl_exec ( $curl_connection );
		
		return $result;
	}
	public function performDomRequest($resultPage, $idName, $tagName) {
		$dom = new DOMDocument ();
		if ($dom->loadHTML ( $resultPage )) {
		} else {
		}
		$select = $dom->getElementById ( $idName );
		if ($select == null) {
		} else {
		}
		$options = $select->getElementsByTagName ( $tagName );
		if ($options->length == 0) {
		} else {
		}
		return $options;
	}
	private function setSem($sem){
		$this->mostRecentSemester = $sem;
	}
	public function getSem(){
		return $this->mostRecentSemester;
	}
}
/**
 * ******************************
 *
 * This Class will be called by the script called checkServerDataController.php
 * The script runs every minute on the server
 * the script check the DB for out of date data and deletes it
 */
class CheckServerData extends GotEnggGeneric {
	public function __construct() {
		$this->getDb ();
	}
	public function deleteOld() {
		if (! $this->db) {
			die ( 'Not connected : ' . mysqli_error () );
		}
		
		// gets data that is older that 24 minutes and sends it to the logout
		$sql = "SELECT * FROM StudentInfo.StuInfo WHERE timeIn < DATE_ADD(NOW(), INTERVAL - 24 MINUTE)";
		$result = mysqli_query ( $this->db, $sql );
		
		$numRows = mysqli_num_rows ( $result );
		
		if ($numRows > 0) {
			
			while ( $row = mysqli_fetch_assoc ( $result ) ) {
				
				$deleteData = new Logout ( $row ['SI'] );
				$deleteData->logout ();
			}
		}
	}
}

?>










