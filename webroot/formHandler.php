<?php

include '/var/www/html/GOTengg/config/dbConnection.php';

$connection = new dbConnection();

$connection->connect();
//$connection->connect();
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


if ($_SERVER["REQUEST_METHOD"] == "POST"){

   $text=$_POST['studenttext'];        //gets the transcript that was pasted
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

  $TermString=array();
  $TermString=preg_split('/(\d{4})\s(Fall|Winter|Spring & Summer)/', $text);
  
  

   /*$db = mysqli_connect("localhost","root","ense400", "StudentInfo" )
   or die("could not connect to the database: Error " . mysqli_error($db));*/

                         //adds the information to the database if the data is good
                         $sql="INSERT INTO StuInfo (SI, name, startYear, major)  VALUES('$ID[0]','$name[1]','$year','$major[1]')";
                         echo $sql;
                         mysqli_query($db, $sql);
                         //mysqli_close($db);
  
   /*$db = mysqli_connect("localhost","root","ense400", "StudentInfo" )
   or die("could not connect to the database: Error " . mysqli_error($db));  

      $sql="CREATE TABLE $name[1] (id INT(6) UNSIGNED AUTO_INCREMENT,
                   courseID VARCHAR(60) NOT NULL,
                   courseTitle VARCHAR(100) NOT NULL,
                   term VARCHAR(50) NOT NULL,
                   grade VARCHAR(50),
                   PRIMARY KEY (id)
                    )";
		   mysqli_query($db, $sql);
                   mysqli_close($db);*/
                         
    				/*$db = mysqli_connect("localhost","root","ense400", "StudentInfo" )
                        	or die("could not connect to the database: Error " . mysqli_error($db));   */
    				
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
$message ="This info has been added to the database";
echo $message;

header( "refresh:1;url=Classes.php" );

}

?>