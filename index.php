<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">


  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="main.css">
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="http://code.jquery.com/jquery-2.1.4.min.js"></script>
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script type="text/javascript" src="./js/fusioncharts.js"></script>
    <script type="text/javascript" src="./themes/fusioncharts.theme.ocean.js"></script>


  <script>
  $(function() {
    var availableTags = [
      <?php
   $host = "localhost"; // Host name
   $username = "root"; // Mysql username
   $password = ""; // Mysql password
   $db_name = "database_final"; // Database name
   $tbl_name = "finale_patient_data"; // Table name
   $CBOUNCE = NULL;
   // Connect to server and select databse.
   mysql_connect("$host", "$username", "$password") or die("cannot connect");
   mysql_select_db("$db_name") or die("cannot select DB");
	$sql="SELECT F.admitting_diagnosis_code ,M.short_description , count(*) as total_count from finale_patient_data as F, diagnosis_code_mapping as M where F.admitting_diagnosis_code = M.diagnosis_code group by admitting_diagnosis_code order by count(*) desc limit 20 ";
	//$sql="SELECT prd_name FROM prd_list WHERE prd_name LIKE '%$my_data%' ORDER BY prd_name";

	$result = mysql_query($sql);

	if($result)
	{
		while($row=mysql_fetch_array($result))
		{
				echo '"'.$row['1'].'","'.$row['0'].'",';
		}
	}

	echo '"[comp1]	prod1	-{unit1}"';
?>

    ];
    $().ready(function(){

      $("#adc").autocomplete({
        source: availableTags
      });
      });

    })

  </script>




  <script type="text/javascript">

  	function searchUserDetails() {


  		$.ajax({
  			url: 'search-details.php',
  			type: 'POST',
  			data: $("#search-form").serialize()
  		})
  		.done(function(res) {
  			$('#user-search-div').empty();
  			$('#user-search-div').append(res);


        Myfunction();
  		})

  }
function Myfunction(){
  $.ajax({type: "POST",
          url: "./DB_LoadData.php",
          data: $("#search-form").serialize(),
    		      success:function(result){
    $("#chart-2").html(result);
    Myfunction1();
  }});
}
function Myfunction1(){
  $.ajax({type: "POST",
          url: "./DB_LoadData2.php",
          data: $("#search-form").serialize(),
    		      success:function(result){
    $("#chart-3").html(result);
    Myfunction2();
  }});
}
function Myfunction2(){
  $.ajax({type: "POST",
          url: "./DB_LoadData3.php",
          data: $("#search-form").serialize(),
    		      success:function(result){
    $("#chart-4").html(result);
  }});
}


  </script>

</head>
<body>


  <header class="head"><h2>Hospital Suggestion</h2></header>

  <form class="form-inline formm" id="search-form" action="javascript:void(0)">
    <div class="form-group">
      <label for="email">SELECT A SYMPTOM:</label>
      <input type="text" class="form-control" id="adc" name="adc" placeholder="Symptom" size="20" required>
    </div>
    <div class="form-group styleDrop">
      <label for="pwd">GENDER</label>
      <select name="gender" id="gender" >
        <option value="">Select Gender</option>
        <option value="Male" >Male</option>
        <option value="Female">Female</option>
      </select>
    </div>
    <div class="form-group styleDrop">
      <label for="pwd">AGE-RANGE</label>
      <select name="age_range" id="age_range">
        <option value="">Select Age Range</option>
        <option value="1" >less than 25</option>
        <option value="2"> 25 - 44</option>
        <option value="3">45 - 64</option>
        <option value="4">65 -69</option>
        <option value="5" >70 - 74 </option>
        <option value="6">75 - 79</option>
        <option value="7" >80 - 84 </option>
        <option value="8">85 - 89</option>
        <option value ="9"> 90 and above </option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-left:80px;" onclick="searchUserDetails()">Search</button>

  </form>



  <!-- <form class="form-inline formm" id="generate-graph" action="javascript:void(0)">
    <button type="submit" class="btn btn-primary" style="margin-left:80px;" id="Shareitem">Generate Graphs</button>
  </form> -->


  <div class="container" style="maxHeight:600px;overflow-y:scroll;">
    <div class="row">
      <div class="col-md-6">
        <div id="user-search-div">

        </div>
      </div>

      <div class="col-md-6">
        <div id="chart-2">

        </div>

        <div id = "chart-3">

        </div>

        <div id="chart-4">

        </div>
      </div>
    </div>
  </div>

</body>
</html>
