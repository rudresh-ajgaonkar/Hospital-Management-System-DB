  <?php
  /* Include the `fusioncharts.php` file that contains functions  to embed the charts. */
  include("./includes/fusioncharts.php");
  /*
  The following 4 code lines contain the database connection information.
  Alternatively, you can move these code lines to a separate
  file and include the file here.
  You can also modify this code based on your database connection.
  */
  if (isset($_POST['adc']) && isset($_POST['gender']) && isset($_POST['age_range']) ){
    $adc = $_POST['adc'];
    $sex = $_POST['gender'];
    $age = $_POST['age_range'];

    if($_POST['adc']==''){
      die();
    }

    if($age == ''){
    $age = 'novalue';
    }

    // echo "<script type='text/javascript'>alert('$adc');</script>";
  $hostdb = "localhost";  // MySQl host
  $userdb = "root";  // MySQL username
  $passdb = "";  // MySQL password
  $namedb = "database_final";  // MySQL database name

  // Establish a connection to the database

  $dbhandle = new mysqli($hostdb, $userdb, $passdb, $namedb);
  /*
  Render an error message,
  to avoid abrupt failure,
  if the database connection parameters are incorrect
  */

  if ($dbhandle->connect_error) {
  exit("There was an error with your connection: ".$dbhandle->connect_error);
  }


  $sql1 = "SELECT diagnosis_code FROM diagnosis_code_mapping where short_description LIKE '%".$_POST['adc']."%'";
  $result1 = mysqli_query($dbhandle,$sql1);

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

  // $adcb = "national_provider_id";
  // $sexb = "Avg(total_charges) AS avg_total_charges";
  // $ageb = "Count(national_provider_id) AS total_count";

  $finalWhere = " WHERE ";
  //$AQB = array($adc , $sex , $age);
  $ABQF = array('a.admitting_diagnosis_code' => "'".$adc."'", 'sex' => "'".$abc."'", 'age' => "'".$age."'" );
  $count = 3;
  $andCheck = FALSE;
  $aCounter = 0;

    //while (current($ABQF)!== FALSE) {
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

//$sql = "SELECT $adcb, $sexb, $ageb from finale_patient_data " . $finalWhere."GROUP BY national_provider_id ORDER by avg_total_charges ASC";

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
ORDER  BY avg_total_charges ASC limit 20";

  // Form the SQL query that returns the top 10 most populous countries

  //$strQuery = "SELECT national_provider_id, Avg(total_charges) AS avg_total_charges FROM finale_patient_data group BY national_provider_id DESC";

  // Execute the query, or else return the error message.

  $result = $dbhandle->query($sqlF) or exit("Error code ({$dbhandle->errno}): {$dbhandle->error}");

  // If the query returns a valid response, prepare the JSON strin

  if ($result) {

  // The `$arrData` array holds the chart attributes and data

  $arrData = array(
  "chart" => array
  (
   "caption" => "NATIONAL_PROVIDER_ID Vs AVG_TOTAL_COST",
   "paletteColors" => "#0075c2",
   "bgColor" => "#ffffff",
   "borderAlpha"=> "20",
   "canvasBorderAlpha"=> "0",
   "usePlotGradientColor"=> "0",
   "plotBorderAlpha"=> "10",
   "showXAxisLine"=> "1",
   "xAxisLineColor" => "#999999",
   "showValues" => "0",
   "divlineColor" => "#999999",
   "divLineIsDashed" => "1",
   "showAlternateHGridColor" => "0"
  )
  );

  $arrData["data"] = array();

  // Push the data into the array

  while($row = mysqli_fetch_array($result)) {
  array_push($arrData["data"], array(
  "label" => $row["national_provider_id"],
  "value" => $row["avg_total_charges"]
  )
  );
  }}
  /*JSON Encode the data to retrieve the string containing the JSON representation of the data in the array. */

  $jsonEncodedData = json_encode($arrData);
  /*
  Create an object for the column chart using the FusionCharts PHP class constructor.
  Syntax for the constructor is
  `FusionCharts("type of chart", "unique chart id", width of the chart, height of the chart, "div id to render the chart", "data format", "data source")`.
  Because we are using JSON data to render the chart, the data format will be `json`.
  The variable `$jsonEncodeData` holds all the JSON data for the chart,
  and will be passed as the value for the data source parameter of the constructor.
  */
  $columnChart = new FusionCharts("column2D", "myFirstChart" , 600, 300, "chart-2", "json", $jsonEncodedData);
  // Render the chart
  $columnChart->render();
  // Close the database connection
  $dbhandle->close();
}
  ?>
