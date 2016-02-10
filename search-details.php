<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "database_final";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (isset($_POST['adc']) && isset($_POST['gender']) && isset($_POST['age_range']) ){
  // Decode our JSON into PHP objects we can use
      // $points = json_decode($_POST["all_data"]);
      // Access our object's data and array values.
      // echo "Data is: " . $points->data . "<br>";
      // echo "Point 1: " . $points->value[0]->first. ", " . $points->arPoints[0]->sec;
  if($_POST['adc']==''){
    die();
  }

  $sex = $_POST['gender'];
  $age = $_POST['age_range'];



if($age == ''){
$age = 'novalue';
}

  $sql1 = "SELECT diagnosis_code FROM diagnosis_code_mapping where short_description LIKE '%".$_POST['adc']."%'";
  $result1 = mysqli_query($conn,$sql1);

  if($sex =="Male"){
    $abc = 1;
  }else if ($sex =="Female"){
    $abc = 2;
  }else{
    $abc = 'novalue';
  }

//  $row1 = $result1->fetch_assoc();
  $row1 = mysqli_fetch_array($result1);
  $adc = $row1[0];

  // $adcb = "a.national_provider_id";
  // $sexb = "a.Avg(total_charges) AS avg_total_charges";
  // $ageb = "Count(national_provider_id) AS total_count";

  $finalWhere = " WHERE ";
  //$AQB = array($adc , $sex , $age);
  $ABQF = array('a.admitting_diagnosis_code' => "'".$adc."'", 'sex' => "'".$abc."'", 'age' => "'".$age."'" );
  $count = 3;
  $andCheck = FALSE;
  $aCounter = 0;


      while(list($key,$val) = each($ABQF)){
  //      echo "$key => $val\n";
     if ($val != "'novalue'"){
          if($andCheck){
            $finalWhere = $finalWhere . " AND ". $key. " = " .$val;
          }else{
            $finalWhere = $finalWhere . $key. " = ".$val;
                    $andCheck = TRUE;
              }
        }
}

// Check connection



$sqlF = "SELECT a.national_provider_id,
       Avg(a.total_charges)
       AS avg_total_charges,
       Avg(a.length_of_stay)
       AS avg_length_of_stay,
       ( b.total_death_count_per_diagno_code / b.total_count_per_diagno_code )
       AS
       mortality_ratio
FROM   finale_patient_data AS a
       LEFT JOIN total_count_mortality_count_per_npi_per_diagno_code AS b
              ON a.national_provider_id = b. national_provider_id
                 AND a.admitting_diagnosis_code = b.admitting_diagnosis_code
$finalWhere
GROUP  BY a.national_provider_id
ORDER  BY avg_total_charges ASC";
//echo $sqlF;




//$sql = "SELECT $adcb, $sexb, $ageb from finale_patient_data " . $finalWhere."GROUP BY national_provider_id";





// echo $sql = "SELECT $adcb, $sexb, $ageb from finale_patient_data  admitting_diagnosis_code = (SELECT diagnosis_code FROM diagnosis_code_mapping where short_description LIKE '%".$_POST['adc']."%')";
  // $sql = "SELECT $adcb, $sexb, $ageb FROM `finale_patient_data` WHERE admitting_diagnosis_code = (SELECT diagnosis_code FROM diagnosis_code_mapping where short_description LIKE '%".$_POST['adc']."%') AND age= $age AND sex = $sex GROUP BY national_provider_id";
//$sql = "SELECT * from finale_patient_data";
$result = mysqli_query($conn,$sqlF);
$count = mysqli_num_rows($result);
if ($count > 0) {
	echo "<table class='table'>
			<thead>
				<tr>
					<th>NATIONAL PROVIDER ID</th>
					<th>AVERAGE TOTAL CHARGES</th>
					<th>AVERAGE LENGTH OF STAY</th>
          <th>OVERALL MORTALITY RATIO</th>
				</tr>
			</thead>
			<tbody>";
    // output data of each row
    while($row = $result->fetch_assoc()) {
      if(is_null($row["mortality_ratio"])){
        $row["mortality_ratio"] = 0.0;
      }
        echo "<tr><td>" . $row["national_provider_id"]. "</td><td>" . $row["avg_total_charges"]. "</td><td>" . $row["avg_length_of_stay"]."</td><td>". $row["mortality_ratio"]. "</td></tr>";
    }
    echo "	</tbody></table>";


} else {
    echo "0 results";
}
$conn->close();
}
// header("location:DB_LoadData.php?query=".$sql);
?>
