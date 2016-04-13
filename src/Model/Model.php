<?php
//include '/var/www/html/GOTengg/config/dbConnection.php';
class dbConnection
{
    protected $db;
    public function connect()
    {
        $dataB = mysqli_connect("localhost", "root", "ense400", "StudentInfo") or die("could not connect to the database: Error " . mysqli_error($db));
        $this->setDb($dataB);
    }
    private function setDb($dataB)
    {
        $this->db = $dataB;
    }
    public function getDb()
    {
        return $this->db;
    }
    public function test()
    {
        $t = 9;
        return $t;
        
    }
}
class AdminParse
{
    protected $text;
    function __construct($conText)
    {
        echo "in constructor";
        $this->setText($conText);
    }
    function parse()
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

class StudentProgress
{
    function parseSemester($semester, $semNum, $db)
    {
        $pos          = strpos($semester, ")");
        $semesterArea = substr($semester, 0, $pos + 1);
        $semesterArea = trim($semesterArea);
        $sem          = "Semester " . $semNum . " " . $semesterArea;
        $semester     = substr($semester, $pos + 2, strlen($semester));
        if ($pos = strpos($semester, "(")) {
            echo "another bracket found in semester " . $semNum . " at position " . $pos;
            $specialCase = true;
            $semester    = $this->electiveCase($sem, $semester, $db);
        }
         while (($pos = strpos($semester, ","))!== false) {
            $class = substr($semester, 0, $pos);
            $class = trim($class);
            $semester = substr($semester, $pos + 1, strlen($semester));
            if (empty($class)) {
                continue;
            } else {
                $userProg = "INSERT INTO userProgress (semester, class, grade) VALUES ('$sem', '$class', '0')";
                if (mysqli_query($db, $userProg)) {
                } else {
                    echo "Error: " . $userProg . mysqli_error($db) . "<br>";
                }
                
            }
        }
        $class    = trim($semester);
 	//echo $class . "<br>";
        $userProg = "INSERT INTO userProgress (semester, class, grade) VALUES ('$sem', '$class', '0')";
        if (mysqli_query($db, $userProg)) {
        } else {
            echo "Error: " . $userProg . mysqli_error($db) . "<br>";
        }
        
        // echo "<br>";
    }
    function electiveCase($sem, $semester, $db)
    {
        $posComma = strpos($semester, ",");
        $posStart = strpos($semester, "(");
        $posEnd   = strpos($semester, ")");
        if ($posComma === false) {
            echo "false";
            //start string from 0 to posEnd;
            $class = substr($semester, 0, $posEnd);
            $class = trim($class);
            echo "<br>";
            echo "class = " . $class . "<br>";
            //$semester = substr($semester, $posEnd + 1, strlen($semester));
            $semester = substr_replace($semester, "", 0, $posEnd + 1);
            $userProg = "INSERT INTO userProgress (semester, class, grade) VALUES ('$sem', '$class', '0')";
            if (mysqli_query($db, $userProg)) {
            } else {
                echo "Error: " . $userProg . mysqli_error($db) . "<br>";
            }
        } else {
            //echo "true";
            $class    = substr($semester, $posComma + 1, $posEnd);
            $class    = trim($class);
            //echo "<br>";
            //echo "class = " . $class . "<br>";
            //$semester = substr($semester, $posEnd + 1, strlen($semester));
            $semester = substr_replace($semester, "", $posComma, $posEnd + 1);
            $posComma = strpos($semester, ",");
            $semester = substr_replace($semester, "", $posComma - 1, $posComma);
            $userProg = "INSERT INTO userProgress (semester, class, grade) VALUES ('$sem', '$class', '0')";
            //echo $userProg;
            if (mysqli_query($db, $userProg)) {
            } else {
                echo "Error: " . $userProg . mysqli_error($db) . "<br>";
            }
        }
        //echo "sem = " . $semester . "<br>";
        
        return $semester;
    }
    function updateClassesCompleted($db)
    {
        $joinUpdate = "UPDATE userProgress\n" . "INNER JOIN CourseInfo\n" . "on CourseInfo.courseTitle = userProgress.class\n" . "SET userProgress.grade = CourseInfo.grade";
        if (mysqli_query($db, $joinUpdate)) {
        } else {
            echo "Error: " . $joinUpdate . mysqli_error($db) . "<br>";
        }
    }
    function parseElectives($electives, $db){
	//echo $electives;
	//\w{2,4}\s\d{3}
        $parseText = array(); // gets the start year  
       if(preg_match_all('/\w{2,4}\s\d{3}/', $electives, $parseText)){

}else{
	echo "false";
}
	 $deleteEle = "DELETE FROM electivesTaken";
            mysqli_query($db, $deleteEle);
        for ($i = 0; $i < count($parseText[0]); $i++) {
            $line = $parseText[0][$i];
	echo $line . ", ";
	//$update = "UPDATE userProgress WGST
	if ((strpos($line, 'PHIL') !== false) || (strpos($line, 'ENGL') !== false) || (strpos($line, 'RLST') !== false) || (strpos($line, 'WGST') !== false)) {
    $type = "Humanities Elective";
	}else{
$type = "Approved Elective";
}
	 $electives = "INSERT INTO electivesTaken (class, grade, type) VALUES ('$line', '0', '$type')";
                if (mysqli_query($db, $electives)) {
                } else {
                    echo "Error: " .$electives . mysqli_error($db) . "<br>";
                }

}
}

    function joinElectives($db){
	$joinUpdate = "UPDATE electivesTaken\n" . "INNER JOIN CourseInfo\n" . "on CourseInfo.courseTitle = electivesTaken.class\n" . "SET electivesTaken.grade = CourseInfo.grade";
        if (mysqli_query($db, $joinUpdate)) {
        } else {
            echo "Error: " . $joinUpdate . mysqli_error($db) . "<br>";
        }
	$2ndJoin = "UPDATE userProgress\n" . "INNER JOIN electivesTaken\n" . "on userProgress.class LIKE '%Humanities%' AND userProgress.class LIKE '%Approved%'\n" . "SET userProgress.grade = electivesTaken.grade";
if (mysqli_query($db, $2ndJoin)) {
        } else {
            echo "Error: " . $2ndJoin . mysqli_error($db) . "<br>";
        }


}
}
?>