<?php
//$filename= $_FILES['classes']['name'];
  //      echo $filename ."<br/>";

if ( isset($_POST['submit'])) {
   if ( isset($_FILES['classes'])){

   
      // incase of an error while uploading
      if($_FILES['file']['error'] >0){
      	echo"Error code " . $_FILES['file']['error'] . "<br/>";
	}

	else if (file_exists($_FILES['file']['name'])){
	     unlink($_FILES['file']['name']);
	      }

	$uploaddir = "/var/www/uploads/";      
	$uploadfile = $uploaddir . basename($_FILES['classes']['name']);	      
	echo $uploadfile "<br/>";
	
	if (move_uploaded_file($_FILES['classes']['tmp_name'], $uploadfile)) {
    	   echo "File is valid, and was successfully uploaded.\n";
	   } 
	   else {
    	   echo "Possible file upload attack!\n";
	   }
	}

echo "Here is some more debugging info: ";
print_r($_FILES); 
}

error_reporting(E_ALL ^ E_NOTICE);
require_once 'excel_reader2.php';
$data = new Spreadsheet_Excel_Reader($filename);
?>
<html>
<head>
<style>
table.excel {
	    border-style:ridge;
	    border-width:1;
	    border-collapse:collapse;
	    font-family:sans-serif;
	    font-size:12px;
}
table.excel thead th, table.excel tbody th {
	    background:#CCCCCC;
	    border-style:ridge;
	    border-width:1;
	    text-align: center;
	    vertical-align:bottom;
}
table.excel tbody th {
	    text-align:center;
	    width:20px;
}
table.excel tbody td {
	    vertical-align:bottom;
}
table.excel tbody td {
    padding: 0 3px;
    border: 1px solid #EEEEEE;
}
</style>
</head>

<body>
<?php echo $data->dump(true,true); ?>
</body>
</html>