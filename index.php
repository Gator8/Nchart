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
$tzone = $ini_array["location_text"];

$show_ind=$ini_array["show_current"];
$show_pws=$ini_array["show_pws"];
$show_precip=$ini_array["show_precip"];

$tc_ctemp=$ini_array["current_temp"];
$tc_ttemp=$ini_array["target_temp"];
$tc_otemp=$ini_array["outdoor_temp"];
$tc_acon=$ini_array["ac_on"];
$tc_heaton=$ini_array["heat_on"];
$tc_aaway=$ini_array["auto_away"];
$tc_fon=$ini_array["fan_on"];
$tc_leaf=$ini_array["leaf_earn"];

$i_hc_mode=$ini_array["heat_cool_mode"];
$i_f_mode=$ini_array["fan_mode"];
$i_leaf=$ini_array["leaf"];
// --------------------------------

define("DEFAULT_LOG","load.log");
if ($t_scale==="c") {
    $tmode = "Celsius";
    $tmode_min = "C";
    $t_min=-30;
    $t_max=35;
} else {
    $tmode = "Fahrenheit";
    $tmode_min = "F";
    $t_min=-20;
    $t_max=90;
}
if ($pe_scale==="mb") {
    $bmode = "millibars";
    $baro_max=1040;
    $baro_min=970;
} else {
    $bmode = "inches";
    $baro_max=30.25;
    $baro_min=29.5;
}
if ($s_scale==="kph") {
    $wmode = "kph";
    $ws_min=0;
    $ws_max=90;
} else {
    $wmode = "mph";
    $ws_min=0;
    $ws_max=50;
}
if ($pn_scale==="metric") {
    $rmode = "mm";
    $r_min=0;
    $r_max=50;
} else {
    $rmode = "in";
    $r_min=0;
    $r_max=2;
}
// get _GET variable
if(isset($_GET['days'])){
    if ($_GET['days']==="1") {
        $bdays = 1;
    } elseif ($_GET['days']==="3") {
        $bdays = 3;
    } elseif ($_GET['days']==="7") {
        $bdays = 7;
    } elseif ($_GET['days']==="14") {
        $bdays = 14;
    } elseif ($_GET['days']==="30") {
        $bdays = 30;
    } elseif ($_GET['days']==="90") {
        $bdays = 90;
    } elseif ($_GET['days']==="180") {
        $bdays = 180;
    } elseif ($_GET['days']==="365") {
        $bdays = 365;
    } else {
        $bdays = $_GET['days'];
    }
   } else {
      $bdays = 7;
}
$lresult = write_log("No. of Days: " . $bdays);

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
 <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Nest Statistics for the last ' . $bdays . ' days!</title>
    <script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.resize.min.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.axislabels.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.rangeselection.min.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.time.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.direction.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.tooltip.min.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.selection.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.tooltip.js"></script>
    <script language="javascript" type="text/javascript" src="flot/tween-min.js"></script>
    <script language="javascript" type="text/javascript" src="flot/steelseries-min.js"></script>
</head>
   <body onload="init()">';

   // Current Nest and Weather Conditions
$link = mysql_connect($db_server, $db_user, $db_pass);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}

$db_selected = mysql_select_db($db_name, $link);
if (!$db_selected) {
    die ('Can\'t use foo : ' . mysql_error());
}

// subtract 30 days from current date and convert to seconds
    $adate = new DateTime();
    $bdays = "P" . $bdays . "D";
    $d_interval = new DateInterval( "$bdays" );
    $d_interval->invert = 1;
    $targetdate = $adate->add( $d_interval );
    $targetdate = $adate->getTimestamp();

    //GET LAST RECORD
    $query = "SELECT * FROM " . $nest_table . " ORDER BY ddate DESC LIMIT 1";
    $result = mysql_query($query);
    while($row = mysql_fetch_assoc($result))
    {
        $last_temp_i = $row['current_temperature'];
        $last_temp_o = $row['z_temperature'];
        $last_hum_i = $row['current_humidity'];
        $last_hum_o = $row['z_relative_humidity'];
        $last_pressure = $row['z_pressure'];
        $last_t_scale = $row['temperature_scale'];
        $last_wind_speed = $row['z_wind_speed'];
        $last_wind_deg = $row['z_wind_degrees'];
        $last_curr_mode = $row['current_schedule_mode'];
        $temp = $row['current_temperature'];
        $last_precip = $row['z_precip_today'];
        $last_heat_st = $row['hvac_heater_state'];
        $last_cool_st = $row['hvac_ac_state'];
        $last_date = date('F d, H:i',$row['ddate']);
        $last_leaf = $row['leaf'];
        $last_aa = $row['auto_away'];
        $last_p_trend= $row['z_pressure_trend'];
        $last_fan_state= $row['hvac_fan_state'];
    }
    //GET SECOND LAST RECORD
    $query = "SELECT * FROM " . $nest_table . " ORDER BY ddate DESC LIMIT 1,1";
    $result = mysql_query($query);
    while($row = mysql_fetch_assoc($result))
    {
        $second_last_temp_o = $row['z_temperature'];
        $second_last_hum_o = $row['z_relative_humidity'];
        $second_last_wind_speed = $row['z_wind_speed'];
    }

    //set status led color
    if ($last_curr_mode==="COOL") {
        $bkgnd = "blue";
        $led_col = "BLUE_LED";
    } elseif ($last_curr_mode==="HEAT") {
        $bkgnd = "red";
        $led_col = "RED_LED";
    } elseif ($last_curr_mode==="RANGE") {
        $bkgnd = "purple";
        $led_col = "MAGENTA_LED";
    }

    //turn temp led on
    if ($last_heat_st=='True') {
        $led_on = "true";
    }
    if ($last_cool_st=='True') {
        $led_on = "true";
    }

    //turn leaf led on
    if ($last_leaf=='True') {
        $leaf_led_on = "true";
        $leaf_led_col = "GREEN_LED";
    } else {
        $leaf_led_on = "false";
        $leaf_led_col = "GREEN_LED";
    }

    //turn fan led on
    if ($last_fan_state=='True') {
        $fan_led_on = "true";
        $fan_led_col = "YELLOW_LED";
    } else {
        $fan_led_on = "false";
        $fan_led_col = "YELLOW_LED";
    }

    //turn aa led on
    if ($last_aa=='1') {
        $aa_led_on = "true";
        $aa_led_col = "MAGENTA_LED";
    } else {
        $aa_led_on = "false";
        $aa_led_col = "MAGENTA_LED";
    }

    //set pressure trend indicator direction
    if ($last_p_trend=='-') {
        $g_p_trend = "steelseries.TrendState.DOWN";
    } elseif ($last_p_trend=='+') {
        $g_p_trend = "steelseries.TrendState.UP";
    } else {
        $g_p_trend = "steelseries.TrendState.STEADY";
    }
    
    //set temp trend indicator direction
    if ($last_temp_o < $second_last_temp_o) {
        $g_t_trend = "steelseries.TrendState.DOWN";
    } elseif ($last_temp_o > $second_last_temp_o) {
        $g_t_trend = "steelseries.TrendState.UP";
    } else {
        $g_t_trend = "steelseries.TrendState.STEADY";
    }

    //set hum trend indicator direction
    if ($last_hum_o < $second_last_hum_o) {
        $g_h_trend = "steelseries.TrendState.DOWN";
    } elseif ($last_hum_o > $second_last_hum_o) {
        $g_h_trend = "steelseries.TrendState.UP";
    } else {
        $g_h_trend = "steelseries.TrendState.STEADY";
    }
    
    //set wind trend indicator direction
    if ($last_wind_speed < $second_last_wind_speed) {
        $g_w_trend = "steelseries.TrendState.DOWN";
    } elseif ($last_wind_speed > $second_last_wind_speed) {
        $g_w_trend = "steelseries.TrendState.UP";
    } else {
        $g_w_trend = "steelseries.TrendState.STEADY";
    }
    
    echo '<div id="wrapper" style="width:1000px;margin:0 auto;">';

    if ($show_ind == '1') {echo '      <table border=0>
      <tr><td colspan=6 align=center>Current Conditions (last updated '. $last_date  .')</td></tr>
      <tr><td colspan=6>
             <canvas id="canvas7" width="35" height="25"></canvas>AC/Heat Status
             <canvas id="canvas9" width="35" height="25"></canvas>Fan Status
             <canvas id="canvas8" width="35" height="25"></canvas>Leaf Status
             <canvas id="canvas10" width="35" height="25"></canvas>Auto Away
      </td></tr>
      <tr>
         <td><canvas id="canvas1" width="175" height="175"></canvas></td>
         <td><canvas id="canvas2" width="175" height="175"></canvas></td>
         <td><canvas id="canvas3" width="175" height="175"></canvas></td>
         <td><canvas id="canvas4" width="175" height="175"></canvas></td>
         <td><canvas id="canvas5" width="175" height="175"></canvas></td>
         <td><canvas id="canvas6" width="175" height="175"></canvas></td>
      </tr>
      </table>';}

      echo '<p>
      <div style="width:1100px">TEMPERATURE</div>
      <div id="temperature" style="width:1000px;height:500px;"></div>
      <div id="navigation" style="width:500px;height:60px;"><BR/>
      <table width=100%>
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
      <div style="width:1000px;height:150px;">&nbsp;</div>';
      if ($show_pws == '1') {echo '
      <div style="width:1000px;height:150px;">PRESSURE & WIND SPEED</div>
      <div id="pressure" style="float:left;width:1000px;height:150px;"></div>';
      }
      if ($show_precip == '1') {echo '
      <div style="width:1000px;height:175px;">PRECIPITATION</div>
      <div id="precip" style="float:left;width:1000px;height:150px;"></div>';
      }
      if ($show_uv == '1') {echo '
      <div style="width:1000px;height:175px;">UV</div>
      <div id="uv" style="float:left;width:1000px;height:150px;"></div>';
      }
   echo '
   </div>
   <div id="placeholder" style="width:50%;height:300px;"></div>
   ';
    // TEMPERATURE
    // build query based on INI entries
    $query="SELECT ddate";
    if ($tc_ctemp == '1') {$query = $query .',current_temperature';}
    if ($tc_ttemp == '1') {$query = $query .',target_temperature';}
    if ($tc_otemp == '1') {$query = $query .',z_temperature';}
    if ($tc_aaway == '1')  {$query = $query .',auto_away';}
    if ($tc_heaton == '1') {$query = $query .',hvac_heater_state';}
    if ($tc_acon == '1') {$query = $query .',hvac_ac_state';}
    if ($tc_fon == '1')   {$query = $query .',hvac_fan_state';}
    if ($tc_leaf == '1')  {$query = $query .',leaf';}
    $query = $query . ' FROM ' . $nest_table . ' WHERE ddate > ' . $targetdate;
    
    //$lresult = write_log("Query: " . $query);
    $result = mysql_query($query);
    $num_rows = mysql_num_rows($result);
    $query = "SELECT max(current_temperature) as max_temp, min(z_temperature) as min_temp, max(z_precip_today) as max_precip, max(z_wind_speed) as max_wind FROM " . $nest_table . " WHERE ddate > " . $targetdate;
    //$lresult = write_log("Query: " . $query);
    //$lresult = write_log("Rows Returned: " . $num_rows);
    $result2 = mysql_query($query);
    while($row = mysql_fetch_assoc($result))
    {
        if ($tc_ctemp == '1') {$dataseta[] = array($row['ddate']*1000,$row['current_temperature']);}
        if ($tc_ttemp == '1') {$datasetb[] = array($row['ddate']*1000,$row['target_temperature'],);}
        if ($tc_otemp == '1') {$datasetc[] = array($row['ddate']*1000,$row['z_temperature']);}
        if ($tc_aaway == '1')  {$datasetd[] = array($row['ddate']*1000,$row['auto_away']);}
        if ($tc_fon == '1')   {
            if ($row['hvac_fan_state']=='False') {
                $f_state = 0;
            } else {
                $f_state = .25;
            }
            $datasete[] = array($row['ddate']*1000,$f_state);
        }

        if ($tc_acon == '1') {
            if ($row['hvac_ac_state']=='False') {
                $c_state = 0;
            } else {
                $c_state = .25;
            }
            $datasetf[] = array($row['ddate']*1000,$c_state);
        }

        if ($tc_leaf == '1')  {
            if ($row['leaf']=='False') {
                $l_state = 0;
            } else {
                $l_state = .25;
            }
            $datasetg[] = array($row['ddate']*1000,$l_state);
        }

        if ($tc_heaton == '1') {
            if ($row['hvac_heater_state']=='False') {
                $h_state = 0;
            } else {
                $h_state = .25;
            }
            $dataseth[] = array($row['ddate']*1000,$h_state);
        }
    }

    while($row = mysql_fetch_assoc($result2))
    {
        $t_f_min=$row['min_temp']-5;
        $t_f_max=$row['max_temp']+5;
        $t_w_max=$row['max_wind']+5;
        if ($pn_scale==="metric") {
                $r_f_max=$row['max_precip']+5;
            } else {
                $r_f_max=$row['max_precip']+1;
            }
    }
    if ($tc_ctemp == '1') {$final_temp_a = json_encode(($dataseta),JSON_NUMERIC_CHECK);}
    if ($tc_ttemp == '1') {$final_temp_b = json_encode(($datasetb),JSON_NUMERIC_CHECK);}
    if ($tc_otemp == '1') {$final_temp_c = json_encode(($datasetc),JSON_NUMERIC_CHECK);}
    if ($tc_aaway == '1')  {$final_temp_d = json_encode(($datasetd),JSON_NUMERIC_CHECK);}
    if ($tc_fon == '1')   {$final_temp_e = json_encode(($datasete));}
    if ($tc_acon == '1') {$final_temp_f = json_encode(($datasetf));}
    if ($tc_leaf == '1')  {$final_temp_g = json_encode(($datasetg));}
    if ($tc_heaton == '1') {$final_temp_h = json_encode(($dataseth));}
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
    
    // PRESSURE WIND DIRECTION AND PRECIP
    $query = "SELECT ddate, z_pressure, z_wind_degrees, z_wind_speed, z_precip_today FROM " . $nest_table . " WHERE ddate > " . $targetdate;
    $result = mysql_query($query);
    while($row = mysql_fetch_assoc($result))
    {
        $dataset3a[] = array($row['ddate']*1000,$row['z_pressure']);
        $dataset3b[] = array($row['ddate']*1000,$row['z_wind_speed'],$row['z_wind_degrees']);
        $dataset3c[] = array($row['ddate']*1000,$row['z_precip_today']);
        $dataset3d[] = array($row['ddate']*1000,$row['z_UV']);
    }
    $final_pres_a = json_encode(($dataset3a),JSON_NUMERIC_CHECK);
    $final_pres_b = json_encode(($dataset3b),JSON_NUMERIC_CHECK);
    $final_precip = json_encode(($dataset3c),JSON_NUMERIC_CHECK);
    $final_uv     = json_encode(($dataset3d),JSON_NUMERIC_CHECK);
    
    //now craft the html
   echo '<script type="text/javascript">
    $(function () {
     var battery = ' . $final_misc . ';
     $.plot("#placeholder",[{label: "voltage", data: battery}],
          {
               xaxis:  {
                      mode: "time",
                      timeformat: "%m/%d/%y",
                      timezone: "browser",
                      minTickSize: [1, "day"],
                      axisLabelUseCanvas: true,
                      axisLabelFontSizePixels: 12,
                      axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
                      axisLabelPadding: 5
                },
                yaxis:  {
                      min:3.6,
                      max:4
                },
                grid: {
                      hoverable: true, 
                },
                tooltip:true,
                tooltipOpts: {
                    content: "%s at %x was %y V",
                    xDateFormat: "%m/%d %H:%M",
                    shifts: {
                        x: -60,
                        y: 25
                    }
                    }
          }
     );
     var UVR = ' . $final_uv . ';
     $.plot("#uv",[{label: "UV Reading", data: UVR}],
          {
               xaxis:  {
                      mode: "time",
                      timeformat: "%m/%d/%y",
                      timezone: "browser",
                      minTickSize: [1, "day"],
                      axisLabelUseCanvas: true,
                      axisLabelFontSizePixels: 12,
                      axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
                      axisLabelPadding: 5
                },
                yaxis:  {
                      min:0,
                      max:9
                },
                grid: {
                      hoverable: true, 
                },
                colors: ["#8E04BD"],
                bars: {
                     show:true,
                     align: "center",
                     barWidth: 10*1000*60,
                     lineWidth: 1
                },
                tooltip:true,
                tooltipOpts: {
                    content: "%s at %x was %y V",
                    xDateFormat: "%m/%d %H:%M",
                    shifts: {
                        x: -30,
                        y: -40
                    }
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
                      timezone: "browser",
                      minTickSize: [1, "day"],
                      axisLabelUseCanvas: true,
                      axisLabelFontSizePixels: 12,
                      axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
                      axisLabelPadding: 5
                },
                yaxis:  {
                      min:20,
                      max:100
                },
                legend: { 
                      position: "sw"
                },
                colors: ["#2E8ADB", "#2EDB76","#A3D0F7"],
                grid: {
                      hoverable: true, 
                },
                tooltip:true,
                tooltipOpts: {
                    content: "%s at %x was %y %",
                    xDateFormat: "%m/%d %H:%M",
                    shifts: {
                        x: -60,
                        y: 25
                    }
                    }

          }
     );';
   if ($show_pws == '1') {echo '
     var pressure_a = ' . $final_pres_a . ';
     var pressure_b = ' . $final_pres_b . ';
     $.plot("#pressure",[
     {label: "Pressure", data: pressure_a, yaxis: 2},
     {label: "Wind Speed", data: pressure_b},
     ],
          {
              xaxis:  {
                      mode: "time",
                      timeformat: "%m/%d/%y",
                      timezone: "browser",
                      minTickSize: [1, "day"],
                      axisLabelUseCanvas: true,
                      axisLabelFontSizePixels: 12,
                      axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
                      axisLabelPadding: 5
                },
              yaxes:  [{
                      min:0,
                      max: '. $t_w_max .',
                      show:true,
                      position: "right",
                },
              {
                      min: '. $baro_min .',
                      max: '. $baro_max .',
                      position: "left",
                      axisLabel: "' . $pe_scale . '",
                }],
              grid: {
                      hoverable: true, 
                },
              tooltip:true,
              tooltipOpts: {
                      content: "%s at %x was %y.1",
                      xDateFormat: "%m/%d %H:%M",
                      shifts: {
                          x: -60,
                          y: 25
                      }
                },
              legend: { 
                      position: "sw"
                },
              colors: ["#2E8ADB", "#2EDB76","#A3D0F7"],
          }
     );';}
   if ($show_precip == '1') {echo '
     var precip_a = ' . $final_precip . ';
     $.plot("#precip",[
     {label: "Precipitation", data: precip_a, lines:{fill:.25, lineWidth:1}},
     ],
          {
              xaxis:  {
                      mode: "time",
                      timeformat: "%m/%d/%y",
                      timezone: "browser",
                      minTickSize: [1, "day"],
                      axisLabelUseCanvas: true,
                      axisLabelFontSizePixels: 12,
                      axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
                      axisLabelPadding: 5
                },
              yaxis:  {
                      min:"' . $r_min . '",
                      max:"' . $r_f_max . '",
                      show:true,
                      position: "left",
                      axisLabel: "' . $rmode . '",
                },
              legend: { 
                      position: "nw"
                },
              grid: {
                      hoverable: true, 
                },
              tooltip:true,
              tooltipOpts: {
                      content: "%s at %x was %y.2 ' . $rmode .'",
                      xDateFormat: "%m/%d %H:%M",
                      shifts: {
                          x: -60,
                          y: 25
                      }
                },
              colors: ["#2E8ADB"],
          }
     );
     ';}

     if ($tc_ctemp == '1')  {echo "     var temp_a = $final_temp_a;\r\n";}
     if ($tc_ttemp == '1')  {echo "     var temp_b = $final_temp_b;\r\n";}
     if ($tc_otemp == '1')  {echo "     var temp_c = $final_temp_c;\r\n";}
     if ($tc_aaway == '1')  {echo "     var temp_d = $final_temp_d;\r\n";}
     if ($tc_fon == '1')    {echo "     var temp_e = $final_temp_e;\r\n";}
     if ($tc_acon == '1')   {echo "     var temp_f = $final_temp_f;\r\n";}
     if ($tc_leaf == '1')   {echo "     var temp_g = $final_temp_g;\r\n";}
     if ($tc_heaton == '1') {echo "     var temp_h = $final_temp_h;\r\n";}

     echo '     $.plot("#temperature",[';
     if ($tc_ctemp == '1')  {echo "     {label: 'Current Temp', data: temp_a},\r\n";}
     if ($tc_ttemp == '1')  {echo "     {label: 'Target Temp', data: temp_b},\r\n";}
     if ($tc_otemp == '1')  {echo "     {label: 'Outdoor Temp', data: temp_c},\r\n";}
     if ($tc_aaway == '1')  {echo "     {label: 'Auto Away', data: temp_d, yaxis: 2, lines:{fill:.25, lineWidth:1}},\r\n";}
     if ($tc_fon == '1')    {echo "     {label: 'Fan on', data: temp_e, yaxis: 3, lines:{fill:.15, lineWidth:1}},\r\n";}
     if ($tc_acon == '1')   {echo "     {label: 'A/C on', data: temp_f, yaxis: 3, lines:{fill:.15, lineWidth:1}},\r\n";}
     if ($tc_leaf == '1')   {echo "     {label: 'Leaf', data: temp_g, yaxis: 3, lines:{fill:.30, lineWidth:1}},\r\n";}
     if ($tc_heaton == '1') {echo "     {label: 'Heat On', data: temp_h, yaxis: 3, lines:{fill:.15, lineWidth:1}},\r\n";}
     echo '     ],
          {
               xaxis:  {
                      mode: "time",
                      timeformat: "%m/%d/%y",
                      timezone: "browser",
                      minTickSize: [1, "day"],
                      axisLabelUseCanvas: true,
                      axisLabelFontSizePixels: 12,
                      axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
                      axisLabelPadding: 5
                },
                yaxes: [{
                      min:'.$t_f_min.',
                      max:'.$t_f_max.',
                      axisLabel: "' . $tmode_min . '"
                },
                {
                      min:0,
                      max:1,
                      show:false,
                      axisLabel: ""
                },
                {
                      min:0,
                      max:1,
                      show:false,
                      axisLabel: ""
                }],
                legend: { 
                      position: "sw",
                      noColumns: 3,
                      backgroundOpacity: .75,
                      labelBoxBorderColor: "#000000"
                },
                colors: [';
                if ($tc_ctemp == '1')  {echo '"#2E8ADB"';}
                if ($tc_ttemp == '1')  {echo ',"#FFA617"';}
                if ($tc_otemp == '1')  {echo ',"#A3D0F7"';}
                if ($tc_aaway == '1')  {echo ',"#ED85FF"';}
                if ($tc_fon == '1')    {echo ',"#CECECE"';}
                if ($tc_acon == '1')   {echo ',"#2EAFFF"';}
                if ($tc_leaf == '1')   {echo ',"#2EDB76"';}
                if ($tc_heaton == '1') {echo ',"#F72556"';}
                echo '],
                grid: {
                      hoverable: true, 
                },
                tooltip: true,
                tooltipOpts: {
                    //content: "%s at %x was %y.1 ' . $tmode_min .'",
                    content: "%s at %x was %y.1",
                    xDateFormat: "%m/%d %H:%M",
                    shifts: {
                        x: -60,
                        y: 25
                    }
                 }
          }
     );
});
     </script>
       <script>
        function init(){
             // Define some sections
             // Temperature ';
             if ($t_scale==="c") {echo '
             var Tsections = Array(steelseries.Section(-30, 0, "rgba(0, 0, 220, 0.3)"),
                                   steelseries.Section(0, 20, "rgba(0, 220, 0, 0.3)"), 
                                   steelseries.Section(20, 75, "rgba(220, 220, 0, 0.3)"));
             var Tareas = Array(steelseries.Section(35, 45, "rgba(220, 0, 0, 0.55)"),
                                steelseries.Section(-30, -20, "rgba(108, 92, 252, 0.55)"));
                                ';
             } else {echo '
             var Tsections = Array(steelseries.Section(-20, 0, "rgba(0, 0, 220, 0.3)"),
                                   steelseries.Section(32, 70, "rgba(0, 220, 0, 0.3)"), 
                                   steelseries.Section(70, 85, "rgba(220, 220, 0, 0.3)"));
             var Tareas = Array(steelseries.Section(85, 105, "rgba(220, 0, 0, 0.55)"),
                                steelseries.Section(-20, -10, "rgba(108, 92, 252, 0.55)"));
                                ';
             }
             echo '             // Pressure';
             if ($pe_scale==="mb")  {echo '
             var Pareas = Array(steelseries.Section(970, 980, "rgba(220, 0, 0, 0.55)"),
                                steelseries.Section(1030, 1040, "rgba(220, 0, 0, 0.55)"));
             var Psections = Array(steelseries.Section(970, 980, "rgba(0, 0, 220, 0.3)"),
                                   steelseries.Section(980, 1030, "rgba(0, 220, 0, 0.3)"), 
                                   steelseries.Section(1030, 1040, "rgba(220, 220, 0, 0.3)"));
                                   ';
             } else {echo '
             var Pareas = Array(steelseries.Section(28.6, 28.9, "rgba(220, 0, 0, 0.55)"),
                                steelseries.Section(30.5, 30.7, "rgba(220, 0, 0, 0.55)"));
             var Psections = Array(steelseries.Section(28.6, 28.9, "rgba(0, 0, 220, 0.3)"),
                                   steelseries.Section(28.9, 30.5, "rgba(0, 220, 0, 0.3)"), 
                                   steelseries.Section(30.5, 30.7, "rgba(220, 220, 0, 0.3)"));
                                   ';
             }
             echo '             // Wind';
             if ($s_scale==="kph") {echo '
             var Wsections = Array(steelseries.Section(0, 20, "rgba(0, 220, 0, 0.3)"), 
                                   steelseries.Section(20, 75, "rgba(220, 220, 0, 0.3)"));
             var Wareas = Array(steelseries.Section(75, 100, "rgba(220, 0, 0, 0.55)"));
             ';
             } else {echo '
             var Wsections = Array(steelseries.Section(0, 12, "rgba(0, 220, 0, 0.3)"), 
                                   steelseries.Section(12, 45, "rgba(220, 220, 0, 0.3)"));
             var Wareas = Array(steelseries.Section(45, 100, "rgba(220, 0, 0, 0.55)"));
             ';
             }
     echo '
             var Hsections = Array(steelseries.Section(0, 20, "rgba(0, 220, 0, 0.3)"),
                                   steelseries.Section(20, 80, "rgba(220, 220, 0, 0.3)"), 
                                   steelseries.Section(80, 100, "rgba(220, 0, 0, 0.3)"));

             // Define one area
             var areas = Array(steelseries.Section(80, 100, "rgba(220, 0, 0, 0.55)"));

             //default look and feel
             var FD = steelseries.FrameDesign.TILTED_GRAY;
             var BC = steelseries.BackgroundColor.CARBON;
             var FT = steelseries.ForegroundType.TYPE1;
             var PT = steelseries.PointerType.TYPE6;
             var PC = steelseries.ColorDef.BLUE;
             var KT = steelseries.KnobType.STANDARD_KNOB;
             var KS = steelseries.KnobStyle.SILVER;
             var LC = steelseries.LcdColor.STANDARD;
             var CD = steelseries.ColorDef.BLUE;

             // Create one radial gauge
             var radial1 = new steelseries.Radial(
                    "canvas1", {
                       titleString                : "Outdoor Temp",
                       unitString                 : "'. $tmode . '",
                       section                    : Tsections,
                       area                       : Tareas,
                       size                       : 175,
                       thresholdVisible           : false,
                       minMeasuredValueVisible    : false,
                       maxMeasuredValueVisible    : false,
                       lcdDecimals                : 1,
                       ledVisible                 : false,
                       frameDesign                : FD,
                       backgroundColor            : BC,
                       foregroundType             : FT,
                       pointerType                : PT,
                       pointerColor               : PC,
                       knobType                   : KT,
                       knobStyle                  : KS,
                       lcdColor                   : LC,
                       valueColor                 : CD,
                       digitalFont                : false,
                       minValue                   : '. $t_min . ',
                       maxValue                   : '. $t_max . ',
                       tickLabelOrientation       : steelseries.TickLabelOrientation.HORIZONTAL,
                       labelNumberFormat          : steelseries.LabelNumberFormat.STANDARD,
                       degreeScale                : true,
                       trendVisible               : true,
                    });
             var radial2 = new steelseries.Radial(
                    "canvas2", {
                       titleString                : "Outdoor Humidity",
                       unitString                 : "%",
                       section                    : Hsections,
                       area                       : areas,
                       size                       : 175,
                       thresholdVisible           : false,
                       minMeasuredValueVisible    : false,
                       maxMeasuredValueVisible    : false,
                       lcdDecimals                : 1,
                       ledVisible                 : false,
                       frameDesign                : FD,
                       backgroundColor            : BC,
                       foregroundType             : FT,
                       pointerType                : PT,
                       pointerColor               : PC,
                       knobType                   : KT,
                       knobStyle                  : KS,
                       lcdColor                   : LC,
                       valueColor                 : CD,
                       digitalFont                : false,
                       minValue                   : 0,
                       maxValue                   : 100,
                       tickLabelOrientation       : steelseries.TickLabelOrientation.HORIZONTAL,
                       labelNumberFormat          : steelseries.LabelNumberFormat.STANDARD,
                       trendVisible               : true,
                    });
             var radial3 = new steelseries.Radial(
                    "canvas3", {
                       gaugeType                  : steelseries.GaugeType.TYPE3,
                       titleString                : "Barometer",
                       unitString                 : "'. $bmode . '",
                       section                    : Psections,
                       area                       : Pareas,
                       size                       : 175,
                       thresholdVisible           : false,
                       minMeasuredValueVisible    : false,
                       maxMeasuredValueVisible    : false,
                       lcdDecimals                : 1,
                       ledVisible                 : false,
                       frameDesign                : FD,
                       backgroundColor            : BC,
                       foregroundType             : FT,
                       pointerType                : PT,
                       pointerColor               : PC,
                       knobType                   : KT,
                       knobStyle                  : KS,
                       lcdColor                   : LC,
                       valueColor                 : CD,
                       digitalFont                : false,
                       minValue                   : ' . $baro_min . ',
                       maxValue                   : ' . $baro_max . ',
                       tickLabelOrientation       : steelseries.TickLabelOrientation.HORIZONTAL,
                       labelNumberFormat          : steelseries.LabelNumberFormat.STANDARD,
                       trendVisible               : true,
                    });
             var radial4 = new steelseries.Radial(
                    "canvas4", {
                       titleString                : "WindSpeed",
                       unitString                 : "'. $wmode . '",
                       section                    : Wsections,
                       area                       : Wareas,
                       size                       : 175,
                       thresholdVisible           : false,
                       minMeasuredValueVisible    : false,
                       maxMeasuredValueVisible    : false,
                       lcdDecimals                : 1,
                       ledVisible                 : false,
                       frameDesign                : FD,
                       backgroundColor            : BC,
                       foregroundType             : FT,
                       pointerType                : PT,
                       pointerColor               : PC,
                       knobType                   : KT,
                       knobStyle                  : KS,
                       lcdColor                   : LC,
                       valueColor                 : CD,
                       digitalFont                : false,
                       minValue                   : ' . $ws_min . ',
                       maxValue                   : ' . $ws_max . ',
                       tickLabelOrientation       : steelseries.TickLabelOrientation.HORIZONTAL,
                       labelNumberFormat          : steelseries.LabelNumberFormat.STANDARD,
                       trendVisible               : true,
                    });
             var radial5 = new steelseries.Compass(
                    "canvas5", {
                       titleString                : "Wind Direction",
                       //section                    : sections,
                       //area                       : areas,
                       size                       : 175,
                       thresholdVisible           : false,
                       minMeasuredValueVisible    : false,
                       maxMeasuredValueVisible    : false,
                       lcdDecimals                : 1,
                       ledVisible                 : false,
                       frameDesign                : FD,
                       backgroundColor            : BC,
                       foregroundType             : FT,
                       pointerType                : PT,
                       pointerColor               : PC,
                       knobType                   : KT,
                       knobStyle                  : KS,
                       lcdColor                   : LC,
                       valueColor                 : CD,
                       digitalFont                : false,
                       tickLabelOrientation       : steelseries.TickLabelOrientation.HORIZONTAL,
                       labelNumberFormat          : steelseries.LabelNumberFormat.STANDARD,
                    });
             var radial6 = new steelseries.Linear(
                    "canvas6", {
                       titleString                : "Precipitation",
                       unitString                 : "'. $rmode . '",
                       //section                    : sections,
                       //area                       : areas,
                       width                      : 85,
                       height                     : 185,
                       thresholdVisible           : false,
                       minMeasuredValueVisible    : false,
                       maxMeasuredValueVisible    : false,
                       lcdDecimals                : 1,
                       ledVisible                 : false,
                       frameDesign                : FD,
                       backgroundColor            : BC,
                       foregroundType             : FT,
                       pointerType                : PT,
                       pointerColor               : PC,
                       knobType                   : KT,
                       knobStyle                  : KS,
                       lcdColor                   : LC,
                       valueColor                 : CD,
                       digitalFont                : false,
                       minValue                   : ' . $r_min . ',
                       maxValue                   : ' . $r_max . ',
                       tickLabelOrientation       : steelseries.TickLabelOrientation.HORIZONTAL,
                       labelNumberFormat          : steelseries.LabelNumberFormat.STANDARD,
                    });
             var led1 = new steelseries.Led(
                    "canvas7", {
                       width                      : 25,
                       height                     : 25,
                       glowColor                  : "'. $led_col .'",
                    });
             var led2 = new steelseries.Led(
                    "canvas8", {
                       width                      : 25,
                       height                     : 25,
                       glowColor                  : "'. $fan_led_col .'",
                    });
                    // need way to show if heater or ac is ON
             var led3 = new steelseries.Led(
                    "canvas9", {
                       width                      : 25,
                       height                     : 25,
                       glowColor                  : "'. $leaf_led_col .'",
                    });
             var led4 = new steelseries.Led(
                    "canvas10", {
                       width                      : 25,
                       height                     : 25,
                       glowColor                  : "'. $aa_led_col .'",
                    });
                   radial1.setValueAnimated('. $last_temp_o .');
                   radial1.setTrend('. $g_t_trend . ');
                   radial2.setValueAnimated('. $last_hum_o .');
                   radial2.setTrend('. $g_h_trend . ');
                   radial3.setValueAnimated('. $last_pressure .');
                   radial3.setTrend('. $g_p_trend . ');
                   radial4.setValueAnimated('. $last_wind_speed .');
                   radial4.setTrend('. $g_w_trend . ');
                   radial5.setValueAnimated('. $last_wind_deg .');
                   radial6.setValueAnimated('. $last_precip .');
                   led1.setLedColor(steelseries.LedColor.' . $led_col . ');
                   led2.setLedColor(steelseries.LedColor.' . $leaf_led_col . ');
                   led3.setLedColor(steelseries.LedColor.' . $fan_led_col . ');
                   led4.setLedColor(steelseries.LedColor.' . $aa_led_col . ');
                   led1.blink('. $led_on .');
                   led2.blink('. $leaf_led_on .');
                   led3.blink('. $fan_led_on .');
                   led4.blink('. $aa_led_on .');
        }
  </script>
  <h6><center>Some data courtesy of <a href="http://www.wunderground.com" target="_blank"><img src="images/wundergroundLogo_4c_horz.jpg" width="90"></a></center></h6>
   </body>
</html>';

function write_log($message, $logfile='') {
  // Determine log file
  if($logfile == '') {
    // checking if the constant for the log file is defined
    if (defined('DEFAULT_LOG') == TRUE) {
        $logfile = DEFAULT_LOG;
    }
    // the constant is not defined and there is no log file given as input
    else {
        error_log('No log file defined!',0);
        return array(status => false, message => 'No log file defined!');
    }
  }
 
  // Get time of request
  if( ($time = $_SERVER['REQUEST_TIME']) == '') {
    $time = time();
  }

  // Format the date and time
  $date = date("Y-m-d H:i:s", $time);
 
  // Append to the log file
  if($fd = @fopen($logfile, "a")) {
    $result = fputcsv($fd, array($date, $message));
    fclose($fd);
 
    if($result > 0)
      return array(status => true);  
    else
      return array(status => false, message => 'Unable to write to '.$logfile.'!');
  }
  else {
    return array(status => false, message => 'Unable to open log '.$logfile.'!');
  }
}
?>

