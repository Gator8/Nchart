<?php
// Parse the ini file
$ini_array = parse_ini_file("Nestphp.ini");

$db_name=$ini_array["db_name"];
$db_server=$ini_array["db_server"];
$db_user=$ini_array["db_user"];
$db_pass=$ini_array["db_pass"];
$t_scale=$ini_array["t_scale"];
$d_scale=$ini_array["d_scale"];
$s_scale=$ini_array["s_scale"];
$pn_scale=$ini_array["pn_scale"];
$pe_scale=$ini_array["pe_scale"];
$nest_table = $ini_array["db_table"];

// get _GET variable
if(isset($_GET)){
    if ($_GET['days']==="") {
        $bdays = 7;
    } elseif ($_GET['days']==="1") {
        $bdays = 1;
    } elseif ($_GET['days']==="3") {
        $bdays = 3;
    } elseif ($_GET['days']==="7") {
        $bdays = 7;
    } elseif ($_GET['days']==="14") {
        $bdays = 14;
    } elseif ($_GET['days']==="30") {
        $bdays = 30;
    } elseif ($_GET['days']==="180") {
        $bdays = 180;
    } elseif ($_GET['days']==="365") {
        $bdays = 365;
    } else {
        $bdays = 7;
    }
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
 <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Nest Statistics</title>
    <script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.resize.min.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.rangeselection.min.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.time.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.tooltip.min.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.selection.js"></script>
   </head>
   <body>
   <div id="wrapper" style="width:1000px;margin:0 auto;">
      <p>Nest Statistics for the last ' . $bdays . ' days!</p>
      <div style="width:1000px">TEMPERATURE</div>
      <div id="temperature" style="width:1000px;height:500px;"></div>
      <div id="navigation" style="width:1000px;height:60px;"><BR/>
      <table>
         <tr>
            <td><form action="nchart.php" method="get"><input type="hidden" name="days" value="1"><input type="submit" value="1 day"></form></td>
            <td><form action="nchart.php" method="get"><input type="hidden" name="days" value="3"><input type="submit" value="3 days"></form></td>
            <td><form action="nchart.php" method="get"><input type="hidden" name="days" value="7"><input type="submit" value="7 days"></form></td>
            <td><form action="nchart.php" method="get"><input type="hidden" name="days" value="14"><input type="submit" value="2 weeks"></form></td>
            <td><form action="nchart.php" method="get"><input type="hidden" name="days" value="30"><input type="submit" value="1 month"></form></td>
            <td><form action="nchart.php" method="get"><input type="hidden" name="days" value="90"><input type="submit" value="3 months"></form></td>
            <td><form action="nchart.php" method="get"><input type="hidden" name="days" value="180"><input type="submit" value="6 months"></form></td>
            <td><form action="nchart.php" method="get"><input type="hidden" name="days" value="364"><input type="submit" value="1 year"></form></td>
         </TR>
      </table>
      </div>
      <div style="width:1000px;height:10px;">&nbsp;</div>
      <div style="width:500px;float:left;">HUMIDITY</div>
      <div style="width:500px;float:right;">BATTERY LEVEL</div>
      <div id="humidity" style="float:left;width:500px;height:250px;"></div>
      <div id="placeholder" style="float:right;width:500px;height:250px;"></div>
   </div>

   <div id="placeholder" style="width:50%;height:300px;"></div>';

$link = mysql_connect($db_server, $db_user, $db_pass);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}

$db_selected = mysql_select_db($db_name, $link);
if (!$db_selected) {
    die ('Can\'t use foo : ' . mysql_error());
}
// subtract 30 cays from current date and convert to seconds
    $adate = new DateTime();
    $bdays = "P" . $bdays . "D";
    $d_interval = new DateInterval( "$bdays" );
    $d_interval->invert = 1;
    $targetdate = $adate->add( $d_interval );
    $targetdate = $adate->getTimestamp();

    // TEMPERATURE
    $query = "SELECT ddate, current_temperature, target_temperature, z_temperature, auto_away FROM " . $nest_table . " WHERE ddate > " . $targetdate;
    $result = mysql_query($query);
    while($row = mysql_fetch_assoc($result))
    {
        $dataseta[] = array($row['ddate']*1000,$row['current_temperature']);
        $datasetb[] = array($row['ddate']*1000,$row['target_temperature'],);
        $datasetc[] = array($row['ddate']*1000,$row['z_temperature']);
        $datasetd[] = array($row['ddate']*1000,$row['auto_away']);
    }
    $final_temp_a = json_encode(($dataseta),JSON_NUMERIC_CHECK);
    $final_temp_b = json_encode(($datasetb),JSON_NUMERIC_CHECK);
    $final_temp_c = json_encode(($datasetc),JSON_NUMERIC_CHECK);
    $final_temp_d = json_encode(($datasetd),JSON_NUMERIC_CHECK);
    $bdate = new DateTime();

    // BATTERY_LEVEL
    $query = "SELECT ddate, battery_level FROM " . $nest_table . " WHERE ddate > " . $targetdate;
    $result = mysql_query($query);
    while($row = mysql_fetch_assoc($result))
    {
        $dataset1[] = array($row['ddate']*1000,$row['battery_level']);
    }
    $final_misc = json_encode(($dataset1),JSON_NUMERIC_CHECK);

    // HUMIDITY
    $query = "SELECT ddate, current_humidity, target_humidity, z_relative_humidity FROM " . $nest_table . " WHERE ddate > " . $targetdate;
    $result = mysql_query($query);
    while($row = mysql_fetch_assoc($result))
    {
        $dataset2a[] = array($row['ddate']*1000,$row['current_humidity']);
        $dataset2b[] = array($row['ddate']*1000,$row['target_humidity']);
        $dataset2c[] = array($row['ddate']*1000,$row['z_relative_humidity']);
    }
    $final_hum_a = json_encode(($dataset2a),JSON_NUMERIC_CHECK);
    $final_hum_b = json_encode(($dataset2b),JSON_NUMERIC_CHECK);
    $final_hum_c = json_encode(($dataset2c),JSON_NUMERIC_CHECK);
    
    //now craft the html
   echo '<script type="text/javascript">
    $(function () {
     var battery = ' . $final_misc . ';
     $.plot("#placeholder",[{label: "voltage", data: battery}],
          {
               xaxis:  {
                      mode: "time",
                      timeformat: "%m/%d/%y",
                      minTickSize: [1, "day"],
                      axisLabel: "Date",
                      axisLabelUseCanvas: true,
                      axisLabelFontSizePixels: 12,
                      axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
                      axisLabelPadding: 5
                },
                yaxis:  {
                      min:3.6,
                      max:4
                }
          }
     );

     var humidity_a = ' . $final_hum_a . ';
     var humidity_b = ' . $final_hum_b . ';
     var humidity_c = ' . $final_hum_c . ';
     $.plot("#humidity",[
     {label: "Current Humidity", data: humidity_a},
     {label: "Target Humidity", data: humidity_b},
     {label: "Outdoor Humidity", data: humidity_c},
     
     ],
          {
               xaxis:  {
                      mode: "time",
                      timeformat: "%m/%d/%y",
                      minTickSize: [1, "day"],
                      axisLabel: "Date",
                      axisLabelUseCanvas: true,
                      axisLabelFontSizePixels: 12,
                      axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
                      axisLabelPadding: 5
                },
                yaxis:  {
                      min:0,
                      max:100
                },
                legend: { 
                      position: "sw"
                },
                colors: ["#2E8ADB", "#2EDB76","#A3D0F7"],
                grid: {
                      hoverable: true, 
                },
                tooltip:true
          }
     );

     var temp_a = ' . $final_temp_a . ';
     var temp_b = ' . $final_temp_b . ';
     var temp_c = ' . $final_temp_c . ';
     var temp_d = ' . $final_temp_d . ';
     $.plot("#temperature",[
     {label: "Current Temp", data: temp_a},
     {label: "Target Temp", data: temp_b},
     {label: "Outdoor Temp", data: temp_c},
     {label: "Auto Away", data: temp_d, yaxis: 2, lines:{fill:.5, lineWidth:1}},
     ],
          {
               xaxis:  {
                      mode: "time",
                      timeformat: "%m/%d/%y",
                      minTickSize: [1, "day"],
                      axisLabel: "Date",
                      axisLabelUseCanvas: true,
                      axisLabelFontSizePixels: 12,
                      axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
                      axisLabelPadding: 5
                },
                yaxes: [{
                      min:-5,
                      max:35
                },
                {
                      min:0,
                      max:1,
                      show:false
                }],
                legend: { 
                      position: "sw"
                },
                colors: ["#2E8ADB", "#2EDB76","#A3D0F7","#FAAFC8"],
                grid: {
                      hoverable: true, 
                },
                tooltip:true
          }
     );
});
     </script>
   </body>
</html>';
?>
