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
    <title>Nest Statistics for the last ' . $bdays . ' days!</title>
    <script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.resize.min.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.rangeselection.min.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.time.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.tooltip.min.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.selection.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.wxgauges.js"></script>
<style type="text/css">
table.gridtable {
    font-family: verdana,arial,sans-serif;
    font-size:11px;
    color:#333333;
    border-width: 1px;
    border-color: #666666;
    border-collapse: collapse;
}
table.gridtable th {
    border-width: 1px;
    padding: 8px;
    border-style: solid;
    border-color: #666666;
    background-color: #dedede;
}
table.gridtable td {
    border-width: 1px;
    padding: 8px;
    border-style: solid;
    border-color: #666666;
    background-color: #ffffff;
}
</style>
    <style>

.gaugelabel2 {font-size:8px;font-family:Tahoma,sans-serif;font-weight:bold;color:#e5e5e5;text-align:center}
.valuelabel2 div{font-size:9px;font-family:Tahoma,sans-serif;font-weight:bold;color:#f1f1f1;text-align:center;width:40px}
.graph {width:150px;height:150px;background:url("images/gaug.png") -1px -2px no-repeat;}
.raingraph {width:100px;height:220px;background:url("images/gaugb.png") no-repeat;}
.dirgraph {width:150px;height:150px;background:url(""images/gaugc.png") 0 1px no-repeat;}
.gaugetd {width:25%;text-align:center;vertical-align:top;}

.misc{background-image:url(images/nordicicons.png);background-color:transparent;background-repeat:no-repeat;}
.android{ background-position: 0 0; width: 16px; height: 16px; } 
.ax{ background-position: -17px 0; width: 18px; height: 12px; } 
.balloon{ background-position: -36px 0; width: 16px; height: 20px; } 
.bft_0{ background-position: -53px 0; width: 11px; height: 10px; } 
.bft_1{ background-position: -65px 0; width: 11px; height: 10px; } 
.bft_10{ background-position: -77px 0; width: 92px; height: 10px; } 
.bft_2{ background-position: -170px 0; width: 20px; height: 10px; } 
.bft_3{ background-position: -191px 0; width: 29px; height: 10px; } 
.bft_4{ background-position: -221px 0; width: 38px; height: 10px; } 
.bft_5{ background-position: -260px 0; width: 47px; height: 10px; } 
.bft_6{ background-position: -308px 0; width: 56px; height: 10px; } 
.bft_7{ background-position: -365px 0; width: 65px; height: 10px; } 
.bft_8{ background-position: -431px 0; width: 74px; height: 10px; } 
.bft_9{ background-position: -506px 0; width: 83px; height: 10px; } 
.chart_line{ background-position: -590px 0; width: 16px; height: 16px; } 
.cold{ background-position: -607px 0; width: 26px; height: 26px; } 
.cross{ background-position: -634px 0; width: 12px; height: 12px; } 
.dk{ background-position: -647px 0; width: 18px; height: 12px; } 
.down_blue{ background-position: -666px 0; width: 7px; height: 8px; } 
.down_red{ background-position: -674px 0; width: 7px; height: 8px; } 
.drop{ background-position: -682px 0; width: 10px; height: 10px; } 
.fi{ background-position: -693px 0; width: 18px; height: 12px; } 
.gb{ background-position: -712px 0; width: 18px; height: 12px; } 
.gl{ background-position: -731px 0; width: 18px; height: 12px; } 
.home{ background-position: -750px 0; width: 22px; height: 22px; } 
.icn-block{ background-position: -773px 0; width: 26px; height: 23px; } 
.icn-check{ background-position: -800px 0; width: 12px; height: 12px; } 
.icn-download{ background-position: -813px 0; width: 20px; height: 22px; } 
.icn-info{ background-position: -834px 0; width: 20px; height: 21px; } 
.is{ background-position: -855px 0; width: 18px; height: 12px; } 
.no{ background-position: -874px 0; width: 18px; height: 12px; } 
.se{ background-position: -893px 0; width: 18px; height: 12px; } 
.selmenu{ background-position: -912px 0; width: 200px; height: 28px; } 
.sidebottom{ background-position: -1113px 0; width: 200px; height: 300px; } 
.sidemiddle{ background-position: -1314px 0; width: 200px; height: 300px; } 
.sidetop{ background-position: -1515px 0; width: 200px; height: 300px; } 
.snow{ background-position: -1716px 0; width: 10px; height: 10px; } 
.star{ background-position: -1727px 0; width: 16px; height: 16px; } 
.sun_down{ background-position: -1744px 0; width: 16px; height: 16px; } 
.sun_up{ background-position: -1761px 0; width: 16px; height: 16px; } 
.ufo{ background-position: -1778px 0; width: 35px; height: 27px; } 
.up_green{ background-position: -1814px 0; width: 7px; height: 8px; } 
.up_red{ background-position: -1822px 0; width: 7px; height: 8px; } 
.warm{ background-position: -1830px 0; width: 26px; height: 26px; } 
.wet{ background-position: -1857px 0; width: 26px; height: 26px; } 
.wind{ background-position: -1884px 0; width: 26px; height: 26px; } 
</style>
   </head>
   <body>';

   // Current Nest and Weather Conditions
$link = mysql_connect($db_server, $db_user, $db_pass);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}

$db_selected = mysql_select_db($db_name, $link);
if (!$db_selected) {
    die ('Can\'t use foo : ' . mysql_error());
}

    //GET LAST RECORD
    $query = "SELECT * FROM " . $nest_table . " ORDER BY ddate DESC LIMIT 1";
    $result = mysql_query($query);
    while($row = mysql_fetch_assoc($result))
    {
        $last_temp_i = $row['current_temperature'];
        $last_temp_o = $row['z_temperature'];
        $last_hum_i = $row['current_humidity'];
        $last_hum_o = $row['z_relative_humidity'];
        $last_t_scale = $row['temperature_scale'];
        $last_curr_mode = $row['current_schedule_mode'];
    }
    if ($last_curr_mode==="COOL") {
        $bkgnd = "blue";
    } else {
        $bkgnd = "red";
    }
    echo '<div id="wrapper" style="width:1000px;margin:0 auto;">
      <table>
      <tr><td colspan=6 bgcolor="' . $bkgnd . '">&nbsp;</td></tr>
      <tr>
         <td align=middle>Current Temperature</td>
         <td align=middle>Current Humidity</td>
         <td align=middle>Current Pressure</td>
         <td align=middle>Current Wind Speed</td>
         <td align=middle>Current Wind Dir</td>
         <td align=middle>Current Windchill</td>
      </tr>
      <tr>
         <td><div id="temp" class="graph"></div></td>
         <td><div id="hum" class="graph"></div></td>
         <td><div id="baro" class="graph"></div></td>
         <td><div id="wind" class="graph"></div></td>
         <td><div id="dir" class="graph"></div></td>
         <td><div id="chill" class="graph"></div></td>
      </tr>
      </table>
<p>
      <div style="width:1000px">TEMPERATURE</div>
      <div id="temperature" style="width:1000px;height:500px;"></div>
      <div id="navigation" style="width:1000px;height:60px;"><BR/>
      <table>
         <tr>
            <td><form action="index.php" method="get"><input type="hidden" name="days" value="1"><input type="submit" value="1 day"></form></td>
            <td><form action="index.php" method="get"><input type="hidden" name="days" value="3"><input type="submit" value="3 days"></form></td>
            <td><form action="index.php" method="get"><input type="hidden" name="days" value="7"><input type="submit" value="7 days"></form></td>
            <td><form action="index.php" method="get"><input type="hidden" name="days" value="14"><input type="submit" value="2 weeks"></form></td>
            <td><form action="index.php" method="get"><input type="hidden" name="days" value="30"><input type="submit" value="1 month"></form></td>
            <td><form action="index.php" method="get"><input type="hidden" name="days" value="90"><input type="submit" value="3 months"></form></td>
            <td><form action="index.php" method="get"><input type="hidden" name="days" value="180"><input type="submit" value="6 months"></form></td>
            <td><form action="index.php" method="get"><input type="hidden" name="days" value="364"><input type="submit" value="1 year"></form></td>
         </TR>
      </table>
      </div>
      <div style="width:1000px;height:10px;">&nbsp;</div>
      <div style="width:500px;float:left;">HUMIDITY</div>
      <div style="width:500px;float:right;">BATTERY LEVEL</div>
      <div id="humidity" style="float:left;width:500px;height:250px;"></div>
      <div id="placeholder" style="float:right;width:500px;height:250px;"></div>
   </div>
   <div id="placeholder" style="width:50%;height:300px;"></div>
   ';
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
