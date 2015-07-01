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
$t_scale=$ini_array["t_scale"];
$d_scale=$ini_array["d_scale"];
$s_scale=$ini_array["s_scale"];
$pn_scale=$ini_array["pn_scale"];
$pe_scale=$ini_array["pe_scale"];
$nest_region=$ini_array["N_region"];
$nest_name=$ini_array["N_name"];

$db_exist=0;

//Get outside temperature is wunderground key is available
if (!empty($ini_array['wunderground_key'])) {
   $json_string = file_get_contents("http://api.wunderground.com/api/".$ini_array['wunderground_key']."/conditions/q/".$ini_array['lat_lon'].".json");
   $lresult = write_log("Got the weather.");

   $parsed_json = json_decode($json_string);
   $z_temperature = $parsed_json->{'current_observation'}->{'temp_'.$t_scale};
   $z_relative_humidity = str_replace("%","",$parsed_json->{'current_observation'}->{'relative_humidity'});
   $z_wind_dir = $parsed_json->{'current_observation'}->{'wind_dir'};
   $z_wind_degrees = $parsed_json->{'current_observation'}->{'wind_degrees'};
   $z_wind_speed = $parsed_json->{'current_observation'}->{'wind_'.$s_scale};
   $z_wind_gust_speed = $parsed_json->{'current_observation'}->{'wind_gust_'.$s_scale};
   $z_pressure = $parsed_json->{'current_observation'}->{'pressure_'.$pe_scale};
   $z_pressure_trend = $parsed_json->{'current_observation'}->{'pressure_trend'};
   $z_dewpoint = $parsed_json->{'current_observation'}->{'dewpoint_'.$t_scale};
   $z_heat_index = $parsed_json->{'current_observation'}->{'heat_index_'.$t_scale};
   $z_windchill = $parsed_json->{'current_observation'}->{'windchill_'.$t_scale};
   $z_feelslike = $parsed_json->{'current_observation'}->{'feelslike_'.$t_scale};
   $z_visibility = $parsed_json->{'current_observation'}->{'visibility_'.$d_scale};
   $z_UV = $parsed_json->{'current_observation'}->{'UV'};
   $z_precip_today = $parsed_json->{'current_observation'}->{'precip_today_'.$pn_scale};
}
// fix path to python executable as well as path to py file
$command = 'python nest.py --user ' .$ini_array["N_user"]. ' --password ' .$ini_array["N_pass"]. ' show';

$command_l = 'python nest.py --user XXXXXXX --password XXXXXXX show';
$lresult = write_log("PYTHON Command: " . $command_l);
$temp = exec($command, $output);

// number of elements in output
$ar_count = count($output);
$f_nest = array();
$lresult = write_log("Returned elements: " . $ar_count);
if (!$ar_count) {
    $lresult = write_log("No elements means python command failed. TERMINATING.");
    exit;
}

// need to write out nest_raw_data.txt for gauges
//$nest_raw_data="LastData ". $z_wind_speed . " " . $z_wind_gust_speed . " " . $z_wind_degrees . " " . $z_temperature . " " . $z_relative_humidity . " " . $z_pressure . " " .$z_precip_today . "    " . $current_temperature. " " . $current_humidity. "                             " . $z_windchill. " " .$z_heat_index;
//$fn_name='/volume1/web/nest/nest_raw_data.txt';
//file_put_contents($fn_name,$nest_raw_data);

foreach ($output as $var_s) {
     list($m_vars,$m_vals) = explode(':', $var_s);
     if ($m_vars=="\$timestamp") {
         $m_vars='timestamp';
     } elseif  ($m_vars=="\$version") {
         $m_vars='version';
     }
     $f_nest[$i][0] = trim($m_vars);
     $f_nest[$i][1] = trim($m_vals);
     $i++;
}

foreach($f_nest as $item) {
   if($item[0]== "name"){
      $tbl_name = $item[1];
      $lresult = write_log("Table Name: " . $tbl_name);
   }
}

// if python returns blank name, used predefined name from INI file
if ($tbl_name==""){
    $tbl_name=$nest_name;
}

$link = mysql_connect($db_server, $db_user, $db_pass, TRUE);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}

$db_selected = mysql_select_db($db_name, $link);

if (!$db_selected) {
    die ('Can\'t use foo : ' . mysql_error());
}

$query = sprintf("show tables from $db_name");
$lresult = write_log("Table query: " . $query);
$result = mysql_query($query);

if (!$result) {
    echo "DB Error, could not list tables\n";
    echo 'MySQL Error: ' . mysql_error();
    exit;
}

$num_rows = mysql_num_rows($result);
$lresult = write_log("Number of Rows: " . $num_rows);
while ($row = mysql_fetch_row($result)) {
    $x_tbl_name = strcasecmp($row[0],$tbl_name);
     if ($x_tbl_name == "0") {
        $db_exist = 1;
    }
}

mysql_free_result($result);

// get last row in the db as a fallback
$query = sprintf("SELECT * FROM $tbl_name ORDER BY ddate desc LIMIT 0,1");
$lresult = write_log("Table query: " . $query);
$result_bk = mysql_query($query);
$lst_row = array();
while ($row = mysql_fetch_array($result_bk)) {
    $lst_row[] = $row;
}

// if table does not exist, then create it
if ($db_exist=="0") {
    $lresult = write_log("Database does not exist. Creating from defined version.");
    //read sql file into a variable
    $db_def_file= $nest_region . ".txt";
    $tbl_sql = file_get_contents($db_def_file);

    $query = sprintf("create table $tbl_name ( $tbl_sql )");
    $result = mysql_query($query);

    if (!$result) {
        $message  = 'Invalid query: ' . mysql_error() . "\n";
        $message .= 'Whole query: ' . $query;
        die($message);
    }

    // create an index
    $query = sprintf("ALTER TABLE `$tbl_name` ADD INDEX ( `ddate` ) ");
    $result = mysql_query($query);

    if (!$result) {
        $message  = 'Invalid query: ' . mysql_error() . "\n";
        $message .= 'Whole query: ' . $query;
        die($message);
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
    $i++;
 }

 // use this if wunderground key is available
 if (!empty($ini_array['wunderground_key'])) {
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

     //ignore innaccurate data
     if (($z_temperature > $lst_row[0]['z_temperature']+5) || ($z_temperature < $lst_row[0]['z_temperature']-5)) {
        $z_temperature =  $lst_row[0]['z_temperature'];
        $z_wind_speed =  $lst_row[0]['z_wind_speed'];
        $z_wind_gust_speed =  $lst_row[0]['z_wind_gust_speed'];
        $z_dewpoint =  $lst_row[0]['z_dewpoint'];
        $z_windchill =  $lst_row[0]['z_windchill'];
        $z_feelslike =  $lst_row[0]['z_feelslike'];
     }

     $tbl_vars = $tbl_vars.",z_temperature,z_relative_humidity,z_wind_dir,z_wind_degrees,z_wind_speed,z_wind_gust_speed,z_pressure,z_pressure_trend,z_dewpoint,z_heat_index,z_windchill,z_feelslike,z_visibility,z_UV,z_precip_today";
     $tbl_vals = $tbl_vals.",'".$z_temperature."','".$z_relative_humidity."','".$z_wind_dir."','".$z_wind_degrees."','".$z_wind_speed."','".$z_wind_gust_speed."','".$z_pressure."','".$z_pressure_trend."','".$z_dewpoint."','".$z_heat_index."','".$z_windchill."','".$z_feelslike."','".$z_visibility."','".$z_UV."','".$z_precip_today."'";
 }

     $query = sprintf("insert into `%s` (%s) values (%s)", $tbl_name, $tbl_vars, $tbl_vals);
     $lresult = write_log("Table insert: " . $query);
     $result = mysql_query($query);

    if (!$result) {
        $message  = 'Invalid query: ' . mysql_error() . "\n";
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
