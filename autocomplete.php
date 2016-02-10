<?php
	$q=$_GET['q'];
	$my_data=mysql_real_escape_string($q);
	 $host = "localhost"; // Host name
    $username = "root"; // Mysql username
    $password = ""; // Mysql password
    $db_name = "database_final"; // Database name
    $tbl_name = "finale_patient_data"; // Table name
    $CBOUNCE = NULL;
    // Connect to server and select databse.
    mysql_connect("$host", "$username", "$password") or die("cannot connect");
    mysql_select_db("$db_name") or die("cannot select DB");
	$sql="SELECT DISTINCT admitting_diagnosis_code FROM finale_patient_data WHERE admitting_diagnosis_code LIKE '%$my_data%' ORDER BY admitting_diagnosis_code";
	$result = mysql_query($sql) or die(mysql_error());

	if($result)
	{
		while($row=mysql_fetch_array($result))
		{
			echo $row['admitting_diagnosis_code']."\n";
		}
	}
?>
