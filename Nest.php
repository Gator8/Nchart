<?php
$i=0;
// Parse the ini file
$ini_array = parse_ini_file("Nestphp.ini");
define("DEFAULT_LOG","nest_run.log");
$lresult = write_log("============================================");
$lresult = write_log("BEGIN");
$db_name=$ini_array["db_name"];
$db_server=$ini_array["db_server"];
$db_user=$ini_array["db_user"];
$db_pass=$ini_array["db_pass"];
$db_port=$ini_array["db_port"];
$t_scale=$ini_array["t_scale"];
$d_scale=$ini_array["d_scale"];
$s_scale=$ini_array["s_scale"];
$pn_scale=$ini_array["pn_scale"];
$pe_scale=$ini_array["pe_scale"];
$nest_region=$ini_array["N_region"];
$nest_name=$ini_array["N_name"];

$db_exist=0;

//Get outside temperature is openweather key is available
if (!empty($ini_array['openweather_key'])) {
   if ($ini_array['wea_meth'] == "1") {
      $xml_string = file_get_contents("http://api.openweathermap.org/data/2.5/weather?id=".$ini_array["cityid"]."&appid=".$ini_array["openweather_key"]."&units=".$ini_array["openweather_format"]."&mode=xml");
      $json_uv_string = file_get_contents("http://api.openweathermap.org/data/2.5/uvi?lat=".$ini_array["lat"]."&lon=".$ini_array["lon"]."&appid=".$ini_array["openweather_key"]);
      $lresult = write_log("Got the weather.");
   } elseif ($ini_array['wea_meth'] == "2") {
      $xml_string = file_get_contents("http://api.openweathermap.org/data/2.5/weather?lat=".$ini_array["lat"]."&lon=".$ini_array["lon"]."&appid=".$ini_array["openweather_key"]."&units=".$ini_array["openweather_format"]."&mode=xml");
      $json_uv_string = file_get_contents("http://api.openweathermap.org/data/2.5/uvi?lat=".$ini_array["lat"]."&lon=".$ini_array["lon"]."&appid=".$ini_array["openweather_key"]);
      $lresult = write_log("Got the weather.");
   } else {
      $status = "NO VALID WEATHER GATHERING METHOD. TERMINATING.";
      $lresult = write_log($status);
      exit ($status);
   }
}

   $parsed_xml = simplexml_load_string($xml_string);
   $parsed_uv_json = json_decode($json_uv_string);
   
   // if weather data is too old, go to alternate location.
   $z_local_epoch = strtotime(gmdate("Y/m/j H:i:s", time()));
   $z_observed_epoch = strtotime($parsed_xml->lastupdate[0]['value']);
   $z_epoch_diff =  $z_local_epoch - $z_observed_epoch;

   if ($z_epoch_diff > 14400) {
    echo "Weather station down or not responding. Using alternate location.\n";
    if ($ini_array['wea_meth'] == "1") {
      $xml_string = file_get_contents("http://api.openweathermap.org/data/2.5/weather?id=".$ini_array["alt_cityid"]."&appid=".$ini_array["openweather_key"]."&units=".$ini_array["openweather_format"]."&mode=xml");
      $json_uv_string = file_get_contents("http://api.openweathermap.org/data/2.5/uvi?lat=".$ini_array["alt_lat"]."&lon=".$ini_array["alt_lon"]."&appid=".$ini_array["openweather_key"]);
      $lresult = write_log("Got the weather. (alt_loc)");
    } elseif ($ini_array['wea_meth'] == "2") {
      $xml_string = file_get_contents("http://api.openweathermap.org/data/2.5/weather?lat=".$ini_array["alt_lat"]."&lon=".$ini_array["alt_lon"]."&appid=".$ini_array["openweather_key"]."&units=".$ini_array["openweather_format"]."&mode=xml");
      $json_uv_string = file_get_contents("http://api.openweathermap.org/data/2.5/uvi?lat=".$ini_array["alt_lat"]."&lon=".$ini_array["alt_lon"]."&appid=".$ini_array["openweather_key"]);
      $lresult = write_log("Got the weather. (alt_lat_lon)");
    } else {
      $status = "NO VALID WEATHER GATHERING METHOD. TERMINATING.";
      $lresult = write_log($status);
      exit ($status);
    }
   $parsed_xml = simplexml_load_string($xml_string);
   $parsed_uv_json = json_decode($json_uv_string); 
   }
   
   // Temperature
   $z_temperature = $parsed_xml->temperature[0]['value'];
   //echo $z_temperature;
   $lresult = write_log("z_temperature:".$z_temperature);
   
   // Relative Humidity
   $z_relative_humidity = $parsed_xml->humidity[0]['value'];
   //echo $z_relative_humidity;
   $lresult = write_log("z_relative_humidity:".$z_relative_humidity);
   
   // Wind
   $z_wind_dir = $parsed_xml->wind->direction[0]['code'];
   //echo $z_wind_dir;
   $lresult = write_log("z_wind_dir:".$z_wind_dir);
   $z_wind_degrees = $parsed_xml->wind->direction[0]['value'];
   $lresult = write_log("z_wind_degrees:".$z_wind_degrees);
   $z_wind_speed = $parsed_xml->wind->speed[0]['value'];
   $lresult = write_log("z_wind_speed:".$z_wind_speed);
   $z_wind_gust_speed = $parsed_xml->wind->gusts[0]['value'];
   if (!$z_wind_gust_speed) {$z_wind_gust_speed = '0';}
   //echo $z_wind_gust_speed;
   $lresult = write_log("z_wind_gust_speed:".$z_wind_gust_speed);
   
   // Pressure
   $z_pressure = $parsed_xml->pressure[0]['value'];
   $lresult = write_log("z_pressure:".$z_pressure);
      /// nothing for this yet
   $z_pressure_trend = $parsed_xml->{'current_observation'}->{'pressure_trend'};
   $lresult = write_log("z_pressure_trend:".$z_pressure_trend);
   
   // Dew Point
     /// nothing for this yet
   $z_dewpoint = $parsed_xml->{'current_observation'}->{'dewpoint_'.$t_scale};
   if (!$z_dewpoint) {$z_dewpoint = '0';}
   $lresult = write_log("z_dewpoint:".$z_dewpoint);
   
   // Heat Index
     /// nothing for this yet
   $z_heat_index = $parsed_xml->{'current_observation'}->{'heat_index_'.$t_scale};
   $lresult = write_log("z_heat_index:".$z_heat_index);
   $z_windchill = $parsed_xml->{'current_observation'}->{'windchill_'.$t_scale};
   $lresult = write_log("z_windchill:".$z_windchill);
   $z_feelslike = $parsed_xml->{'current_observation'}->{'feelslike_'.$t_scale};
   if (!$z_feelslike) {$z_feelslike = '0';}
   $lresult = write_log("z_feelslike:".$z_feelslike);
   
   // Visibility
   $z_visibility = round($parsed_xml->visibility[0]['value'] * 0.000621,2);
   $lresult = write_log("z_visibility:".$z_visibility);
   
   // UV
   $z_UV = $parsed_uv_json->{'value'};
   $lresult = write_log("z_UV:".$z_UV);
   
   // Precipitation
   $z_precip_today = $parsed_xml->precipitation[0]['value'];
   if (!$z_precip_today) {$z_precip_today = '0';}
   $lresult = write_log("z_precip_today:".$z_precip_today);

// fix path to python executable as well as path to py file
$command = 'python nest.py --user ' .$ini_array["N_user"]. ' --password ' .$ini_array["N_pass"]. ' show';
//$lresult = write_log("PYTHON Command: " . $command);
$temp = exec($command, $output);

// number of elements in output
$ar_count = count($output);
$f_nest = array();
$lresult = write_log("Returned elements: " . $ar_count);
if (!$ar_count) {
    $lresult = write_log("No elements means python command failed. TERMINATING.");
    exit;
}

foreach ($output as $var_s) {
     list($m_vars,$m_vals) = explode(':', $var_s);
     if ($m_vars=="\$timestamp") {
         $m_vars='timestamp';
     } elseif  ($m_vars=="\$version") {
         $m_vars='version';
     }
     if ($m_vars=="eco") {
         $m_vals="eco";
     }
     
     $f_nest[$i][0] = trim($m_vars);
     $f_nest[$i][1] = trim($m_vals);
     $i++;
}

foreach($f_nest as $item) {
   if($item[0]== "name"){
      $tbl_name = $item[1];
      //$lresult = write_log("Table Name: " . $tbl_name);
   }
}

// if python returns blank name, used predefined name from INI file
if ($tbl_name==""){
    $tbl_name=$nest_name;
}

//$lresult = write_log("db connect:".$db_server." " .$db_user." " .$db_pass." " .$db_name);
$link = mysqli_connect($db_server, $db_user, $db_pass, $db_name, $db_port);

$query = sprintf("show tables from $db_name");
//$lresult = write_log("Table query: " . $query);
$result = mysqli_query($link,$query);

if (!$result) {
    echo "DB Error, could not list tables\n";
    echo 'MySQL Error: ' . mysqli_connect_error();
    exit;
}

$num_rows = mysqli_num_rows($result);
//$lresult = write_log("Number of Rows: " . $num_rows);
while ($row = mysqli_fetch_row($result)) {
    $x_tbl_name = strcasecmp($row[0],$tbl_name);
     if ($x_tbl_name == "0") {
        $db_exist = 1;
    }
}

mysqli_free_result($result);

// get last row in the db as a fallback
$query = sprintf("SELECT * FROM $tbl_name ORDER BY ddate desc LIMIT 0,1");
//$lresult = write_log("Table query: " . $query);
$result_bk = mysqli_query($link,$query);
$lst_row = array();
while ($row = mysqli_fetch_array($result_bk,MYSQLI_ASSOC)) {
    $lst_row[] = $row;
}

// if table does not exist, then create it
if ($db_exist=="0") {
    $lresult = write_log("Database does not exist. Creating from defined version.");
    //read sql file into a variable
    $db_def_file= $nest_region . ".txt";
    $tbl_sql = file_get_contents($db_def_file);

    $query = sprintf("create table $tbl_name ( $tbl_sql )");
    $result = mysqli_query($link,$query);

    if (!$result) {
        $message  = 'Invalid query: ' . mysqli_error($link) . "\n";
        $message .= 'Whole query: ' . $query;
        die($message);
    }

    // create an index
    $query = sprintf("ALTER TABLE `$tbl_name` ADD INDEX ( `ddate` ) ");
    $result = mysqli_query($link,$query);

    if (!$result) {
        $message  = 'Invalid query: ' . mysqli_error($link) . "\n";
        $message .= 'Whole query: ' . $query;
        die($message);
    }
} 

// If column is missing, create it (default varchar 20)
$query = sprintf("SELECT * FROM $tbl_name ORDER BY ddate desc LIMIT 0,1");
//$query = sprintf("SELECT count(*) FROM information_schema.columns WHERE table_name = '$tbl_name'");
$col_res = mysqli_query($link,$query);
$col_count = mysqli_num_fields($col_res);
$lresult = write_log("DB Columns: $col_count");
if ($ar_count + 16 > $col_count) {
    /// need routine to find differences
   $lresult = write_log("!!!!NEW COLUMNS!!!!");
    
   // get column names and create array
   $query = sprintf("SELECT column_name FROM information_schema.columns WHERE table_name='$tbl_name'");
   $result_c_names = mysqli_query($link,$query);
   while ($row = mysqli_fetch_array($result_c_names,MYSQLI_BOTH)) {
        $db_col_names[] = $row;
      }
      
   // now find the diff
   $i = 0;
   foreach ($f_nest as $m_col_name ) {
       if ($m_col_name != 'ddate') {
         // try and find where in the list the new column is supposed to fit  
         foreach($db_col_names as $c_name) {
            if($c_name[0] == $m_col_name[0]){              
                $c_found = 1;
                break;
                } else {
                	$c_found = -1;
                }
         }
            if ($c_found != 1) {
                //find out what column comes before it
                $prev_col =  $f_nest[$i-1];
                $query = sprintf("ALTER TABLE `$tbl_name` ADD `$m_col_name[0]` VARCHAR(20) NULL AFTER `$prev_col[0]`");
                $result = mysqli_query($link,$query);
                $lresult = write_log("NEW Column Found - $m_col_name[0]");
            }
       }
        $c_found = 0;
        $i++;
   }
}
// now insert values into the table
// need list of values to be inserted
$hereandnow = strtotime(date('Y-m-d H:i:s'));
$i=0;
 foreach ($f_nest as $var_s1) {
     if ($i=="0"){
         $tbl_vars = "ddate,".$var_s1[0];
         $tbl_vals = $hereandnow.",'".$var_s1[1]."'";
     } else {
         $tbl_vars = $tbl_vars.",".$var_s1[0];
         $tbl_vals = $tbl_vals.",'".$var_s1[1]."'";
     }
     //print_r($var_s1); 
    $i++;
 }

 // use this if openweather key is available
 if (!empty($ini_array['openweather_key'])) {
     // if value is empty, use previous value
     if (empty($z_temperature)) {
        $z_temperature =  $lst_row[0]['z_temperature'];
        $z_relative_humidity =  $lst_row[0]['z_relative_humidity'];
        $z_wind_dir =  $lst_row[0]['z_wind_dir'];
        $z_wind_degrees =  $lst_row[0]['z_wind_degrees'];
        $z_wind_speed =  $lst_row[0]['z_wind_speed'];
        $z_wind_gust_speed =  $lst_row[0]['z_wind_gust_speed'];
        $z_pressure =  $lst_row[0]['z_pressure'];
        $z_pressure_trend =  $lst_row[0]['z_pressure_trend'];
        $z_dewpoint =  $lst_row[0]['z_dewpoint'];
        $z_heat_index =  $lst_row[0]['z_heat_index'];
        $z_windchill =  $lst_row[0]['z_windchill'];
        $z_feelslike =  $lst_row[0]['z_feelslike'];
        $z_visibility =  $lst_row[0]['z_visibility'];
        $z_UV =  $lst_row[0]['z_UV'];
        $z_precip_today =  $lst_row[0]['z_precip_today'];
        echo "Weather call failed. Using previous values.\n";
     }

     //ignore innaccurate data - THIS NEEDS TO BE REWRITTEN
     // some times weather station data comes back as invalid. use last recorded data instead
     if ($z_temperature < $lst_row[0]['z_temperature']-100) {
     //if (($z_temperature > $lst_row[0]['z_temperature']+5) || ($z_temperature < $lst_row[0]['z_temperature']-5)) {
        $lresult = write_log("station temperature: " . $z_temperature . ". Using " . $lst_row[0]['z_temperature'] . " instead.");
        $z_temperature =  $lst_row[0]['z_temperature'];
        $z_relative_humidity =  $lst_row[0]['z_relative_humidity'];
        $z_wind_speed =  $lst_row[0]['z_wind_speed'];
        $z_wind_dir =  $lst_row[0]['z_wind_degrees'];
        $z_wind_gust_speed =  $lst_row[0]['z_wind_gust_speed'];
        $z_dewpoint =  $lst_row[0]['z_dewpoint'];
        $z_windchill =  $lst_row[0]['z_windchill'];
        $z_feelslike =  $lst_row[0]['z_feelslike'];
        $lresult = write_log("alternate weather");
     }

     $tbl_vars = $tbl_vars.",z_temperature,z_relative_humidity,z_wind_dir,z_wind_degrees,z_wind_speed,z_wind_gust_speed,z_pressure,z_pressure_trend,z_dewpoint,z_heat_index,z_windchill,z_feelslike,z_visibility,z_UV,z_precip_today";
     $tbl_vals = $tbl_vals.",'".$z_temperature."','".$z_relative_humidity."','".$z_wind_dir."','".$z_wind_degrees."','".$z_wind_speed."','".$z_wind_gust_speed."','".$z_pressure."','".$z_pressure_trend."','".$z_dewpoint."','".$z_heat_index."','".$z_windchill."','".$z_feelslike."','".$z_visibility."','".$z_UV."','".$z_precip_today."'";
 }

     $query = sprintf("insert into `%s` (%s) values (%s)", $tbl_name, $tbl_vars, $tbl_vals);
     // $lresult = write_log("Table insert: " . $query);
     // $lresult = write_log("z_temperature:".$z_temperature);
     $result = mysqli_query($link,$query);

     if (!$result) {
        $message  = 'Invalid query: ' . mysqli_error($link) . "\n";
        $message .= 'Whole query: ' . $query;
        $lresult = write_log("END WITH ERROR");
        die($message);
     }

echo "Data added.\n";
$lresult = write_log("END");

//
//  Log Function
//

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
 
//    if($result > 0)
//      return array(status => true);  
//    else
//      return array(status => false, message => 'Unable to write to '.$logfile.'!');
  }
//  else {
//    return array(status => false, message => 'Unable to open log '.$logfile.'!');
//  }
}
?>
