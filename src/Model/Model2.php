<?php
class Parse
{
	protected $text;
	function __construct($conText)
	{
		echo "in constructor";
		$this->setText($conText);
	}
	
	// the major difference between the admin and the student trans is the words "Term Code" are only found in the admni transcript
	function determineParser()
	{
		preg_match_all ('/Term Code/',$this->text,$matches);   
		if ( empty ($matches)){
			stuParse();
			
		}
		else {
			adminParse();
		}
	}
	
	private function stuParse()
	{
		
		$connection = new dbConnection();
		$connection->connect();
		$db = $connection->getDb();
		
		$sql = "SELECT * FROM StuInfo WHERE SI = 200306794"; // selects the info where the SI is the same *******************
		$result = mysqli_query($db, $sql);
		$numRows = mysqli_num_rows($result );
		
		if($numRows > 0 ){
			//Already info in there, need to delete (probably caused by someone pressing the back button
			$deleteStu = "DELETE FROM StuInfo WHERE SI = 200306794"; //*****************************
			mysqli_query($db, $deleteStu );
			$deleteCo = "DELETE FROM CourseInfo WHERE courseID = 200306794";  //***************************
			mysqli_query($db, $deleteCo);
		}
		
			$text = mysql_real_escape_string($text);// escape characters
			$text=str_replace ("'" , "''" , $text); //replace ' so that sql can query
			 
			$ID = array();
			preg_match('/\d{9}/', $text, $ID);     // this will get the SID of the student
			 
			$name=array();
			preg_match('/Name:(.*?)\n/' , $text, $name);  //extract name from transcript
			$name[1]=str_replace (" " , "" , $name[1]);  //removes spaces and comma's
			$name[1]=str_replace ("," , "" , $name[1]);
			 
			$major=array();
			preg_match('/Major.{7}(.*?)Year/' ,$text, $major); //extract major from transcript
			 
			$startYear=array();   // gets the start year , Loops through the classes on the transcript to find the earliest date
			preg_match_all('/(\d{4})\s(Fall|Winter|Spring & Summer)/', $text, $startYear);
			$year=9999;
			for ($i=0;$i<count($startYear[1]);$i++)
			{
				if( $startYear[1][$i]<$year)
				{
					$year=$startYear[1][$i];
				}
			}
		
			//adds the information to the database if the data is good
			$sql="INSERT INTO StuInfo (SI, name, startYear, major)  VALUES('$ID[0]','$name[1]','$year','$major[1]')";
			mysqli_query($db, $sql);
			
			$TermString=array();
			$TermString=preg_split('/(\d{4})\s(Fall|Winter|Spring & Summer)/', $text);
			
			for($i=0; $i<count($startYear[0]); $i++)
			{
				preg_match_all('/(\w{2,4}\t\d{1,3})\t\d{3}(.*?)(Registered|\d{1,3}|\s[W]\s|\s[P]\s|\s[F]\s)/', $TermString[$i] ,$classes); // creates an array of classes
		
				if(!empty($classes[0])){
		
					for($j=0;$j<count($classes[0]);$j++)
					{
						$courseID= (string)$classes[1][$j];
						$courseTitle= (string)$classes[2][$j];
						$grade= (string)$classes[3][$j];
		
						if($grade =="Registered")     // this is to convert the string to the same formate as the admin transcript since they use the words "Not Completed instead
						{
							$grade ="Not Completed";
						}
							
						// This will convert the string containing the semester and year into the term number exp :201610 for winter 2016
						if ($startYear[2][$i-1] == "Winter"){
							$term =(string)$startYear[1][$i-1] . "10";
						}
						else if ($startYear[2][$i-1] == "Fall"){
							$term =(string)$startYear[1][$i-1] . "30";
						}
						else {
							$term =(string)$startYear[1][$i-1] . "20";
						}
		
		
		
						$sql="INSERT INTO CourseInfo (courseID,courseTitle, term, grade)  VALUES('". $ID[0]. "','".$courseID."','" .$term ."','". $grade ."')";
						mysqli_query($db, $sql);
		
					}
				}
			}
		
			mysqli_close($db);
		
		}
	
	private function adminParse()
	{
		echo "THIS->TEXT:  " . $this->text;
		$connection = new dbConnection();

		$connection->connect();
		//$connection->connect();
		$db = $connection->getDb();
		//echo $db2;

		//$db = mysqli_connect("localhost", "root", "ense400", "StudentInfo") or die("could not connect to the database: Error " . mysqli_error($db));

		$sql     = "SELECT * FROM StuInfo";
		$result  = mysqli_query($db, $sql);
		$numRows = mysqli_num_rows($result);

		if ($numRows > 0) {
			//Already info in there, need to delete (probably caused by someone pressing the back button
			$deleteStu = "DELETE FROM StuInfo";
			mysqli_query($db, $deleteStu);
			$deleteCo = "DELETE FROM CourseInfo";
			mysqli_query($db, $deleteCo);
		} else {

		}



		//$text          = $_POST["studenttext"];
		$parseTextName = array(); // gets the start year
		preg_match_all('/(\d{9}.*)/', $this->text, $parseTextName);
		$temp = $parseTextName[0][0];
		$pos  = strpos($temp, "\t");
		$id   = substr($temp, 0, $pos);
		$id   = trim($id);

		$temp = substr($temp, $pos + 1, strlen($temp));

		$pos = strpos($temp, "\t");

		$name = substr($temp, 0, $pos);
		$temp = substr($temp, $pos + 1, strlen($temp));

		$pos = strpos($temp, "\t");

		$campus = substr($temp, 0, $pos);
		$temp   = substr($temp, $pos + 1, strlen($temp));
		$pos    = strpos($temp, "\t");

		$college = substr($temp, 0, $pos);
		$temp    = substr($temp, $pos + 1, strlen($temp));

		$pos = strpos($temp, "\t");

		$degree = substr($temp, 0, $pos);
		$temp   = substr($temp, $pos + 1, strlen($temp));

		$pos = strpos($temp, "\t");

		$major = substr($temp, 0, $pos);
		$temp  = substr($temp, $pos + 1, strlen($temp));


		$level = $temp;

		$UserInfo = "INSERT INTO StuInfo (SI, StudentString, name,startYear,major) VALUES ('$id', 'NA', '$name','0000','$major')";
		if (mysqli_query($db, $UserInfo)) {
		} else {
			echo "Error: " . $UserInfo . "<br>" . mysqli_error($conn);
		}
		$parseText = array(); // gets the start year
		preg_match_all('/(\d{6}(.*?)\t\w{2,4}\s*\d{1,3}).*/', $this->text, $parseText);
		for ($i = 0; $i < count($parseText[1]); $i++) {
			$line = $parseText[0][$i];
			if (strpos($line, 'Registered') !== false) {
				//Currently taking
				$temp        = $line;
				$pos         = strpos($temp, "\t");
				$code        = substr($temp, 0, $pos);
				$code        = trim($code);
				$temp        = substr($temp, $pos + 1, strlen($temp));
				$pos         = strpos($temp, "\t");
				$subject     = substr($temp, 0, $pos);
				$temp        = substr($temp, $pos + 1, strlen($temp));
				$pos         = strpos($temp, "\t");
				$number      = substr($temp, 0, $pos);
				$temp        = substr($temp, $pos + 1, strlen($temp));
				$pos         = strpos($temp, "\t");
				$title       = substr($temp, 0, $pos);
				$temp        = substr($temp, $pos + 1, strlen($temp));
				$pos         = strpos($temp, "\t");
				$status      = substr($temp, 0, $pos);
				$temp        = substr($temp, $pos + 1, strlen($temp));
				$pos         = strpos($temp, "\t");
				$campus      = substr($temp, 0, $pos);
				$temp        = substr($temp, $pos + 1, strlen($temp));
				$cHours      = $temp;
				$subject     = trim($subject);
				$number      = trim($number);
				$courseTitle = $subject . " " . $number;
				$CourseInfo  = "INSERT INTO CourseInfo (courseID, courseTitle, term,grade) VALUES ('$id', '$courseTitle', '$code','Not Completed')";
				if (mysqli_query($db, $CourseInfo)) {
				} else {
					echo "Error: " . $CourseInfo . "<br>" . mysqli_error($db);
				}

			} else {
				$temp        = $line;
				$pos         = strpos($temp, "\t");
				$code        = substr($temp, 0, $pos);
				$code        = trim($code);
				$temp        = substr($temp, $pos + 1, strlen($temp));
				$pos         = strpos($temp, "\t");
				$subject     = substr($temp, 0, $pos);
				$temp        = substr($temp, $pos + 1, strlen($temp));
				$pos         = strpos($temp, "\t");
				$number      = substr($temp, 0, $pos);
				$temp        = substr($temp, $pos + 1, strlen($temp));
				$pos         = strpos($temp, "\t");
				$title       = substr($temp, 0, $pos);
				$temp        = substr($temp, $pos + 1, strlen($temp));
				$pos         = strpos($temp, "\t");
				$cHours      = substr($temp, 0, $pos);
				$temp        = substr($temp, $pos + 1, strlen($temp));
				$grade       = $temp;
				$subject     = trim($subject);
				$number      = trim($number);
				$courseTitle = $subject . " " . $number;
				$CourseInfo  = "INSERT INTO CourseInfo (courseID, courseTitle, term,grade) VALUES ('$id', '$courseTitle', '$code','$grade')";
				if (mysqli_query($db, $CourseInfo)) {
				} else {
					echo "Error: " . $CourseInfo . "<br>" . mysqli_error($db);
				}

			}
		}
		mysqli_close($db);
	}
	function redirect()
	{
		header("refresh:1;url=Classes.php");
	}
	private function setText($conText)
	{
		$this->text = $conText;
	}
	public function getText()
	{
		return $this->text;
	}

}



?>