 <?php

$text="";

if ($_SERVER["REQUEST_METHOD"] == "POST"){

   $text=$_POST['text'];        //gets the scheduel that was pasted
   $text=str_replace ("'" , "''" , $text); //replace ' so that sql can query
   
   $schedule=array();
   $schedule=preg_split('/(136.0 Total)/',$text); // splits the string into required classes and suggested electives
   					      // pos 0 are the required courses and pos 1 are the electives 

   $major=array();
   preg_match('/(BASc).*\n?.*(Engineering)/' ,$text, $major); //extract major from the scheduel
   echo $major[0] . "<br>";
  $year=$_POST['year'];   // gets the start year
  //preg_match('/(\d{4})/', $text, $startYear);
  echo $startYear . "<br>";

  $db = mysqli_connect("localhost","root","ense400", "StudentInfo" )
   or die("could not connect to the database: Error " . mysqli_error($db));

         $sql="INSERT INTO Schedule (year,program,electives)  VALUES('$year','$major[0]','$schedule[1]')";
         mysqli_query($db, $sql);
       
	 $id = mysqli_insert_id($db);   // gets id of table
	 mysqli_close($db);

  

   $Semester=array();
   preg_match_all('/(Semester)\s\d/', $schedule[0], $Semester); // seperates classes into semesters


   $SemesterString=preg_split('/(Semester)\s\d/', $schedule[0]);
   //print_r ($SemesterString);


   //This will loop through the semester array to extract the courses in each semester ( the first position in the array is useless)
  for ($i=0;$i<count($SemesterString);$i++)
  {
	if($i !=0){
	//$S_name=array();
	//$S_name=preg_split('/.*\n*.*(\))/', $SemesterString[$i]);

        $coursesArray=array();   // gets the courses for the given semester
	$coursesArray=preg_split('/(\d\.0)/', $SemesterString[$i]);
        //preg_match_all('/\w{2,4}\s\d{3}(?!\d)/', $SemesterString[$i], $coursesArray);
        //print_r ($coursesArray);
       	echo  "<br>".$Semester[0][$i-1];
        echo "</br>";
        
	$coursesString = implode(",",$coursesArray);       //turns the elements of the array into a string
	$Sem_name="Sem".$i."_name";
	$Sem_name=(string)$Sem_name;
	$semesterN="semester".$i;
	$semesterN=(string)$semesterN;
	$thisSem=$Semester[0][$i-1];
	$thisSem=(string)$thisSem;
	
	echo $coursesString . "<br>";
	
		$db = mysqli_connect("localhost","root","ense400", "StudentInfo" )
   		or die("could not connect to the database: Error " . mysqli_error($db));
		   $sql="UPDATE Schedule SET $Sem_name ='$thisSem', $semesterN='$coursesString' WHERE id='$id';";
         	   //$sql="INSERT INTO Schedule ('$Sem_name','$semesterN')  VALUES('$thisSem','$coursesString') WHERE id='$id'";
         	   mysqli_query($db, $sql);
		   //echo "<br>".$sql;	
         mysqli_close($db);        
	
        }
	}

echo "<br>electives". $schedule[1];

echo "The schedule has been saved <br>";
echo '<a href="index.html">Home</a>';
}

?>