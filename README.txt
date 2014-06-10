
                      N E S T   D A T A

This series of script files will allow you to collect all of the data that your 
Nest thermostat is seding to Nest and store it in a mySQL database. You will 
also be able to graphically display your collected data in a number of charts.

Warning: Gathering data like this involves HTTP calls to the Nest secure site. 
         According to Nest, doing this may kill any existing connections you 
         have with the Nest servers - i.e. you will be requied to login on your
         mobile devices everytime you run the app.

Prerequisites
=============
You will need a few things to get this to work:
        1. a web server
        2. a mySQL server
        3. PHP installed
        4. Python installed
        5. a Wunderground key
        6. Flot charts (http://www.flotcharts.org)
        7. Nordic Weather gauges (http://www.nordicweather.net/phpscripts.php?en)


Data Gathering
==============

Step 1 - modify the NEST.sql script and replace 'TABLE' with the name of the 
         Nest thermostat. Each Nest is given it's own name by the owner - i.e.
         'Hallway', 'Bedroom', etc. Make sure the name of the table matches 
         the name of the Nest thermostat or your data will not be logged.

Step 2 - Create the database. You can name it anything you want.

Step 3 - Create the table. Use the modified Nest.sql to create the table.

Step 4 - Test the nest.py script by running it as follows:

            python nest.py --user [USER] --password [PASS] show

         where [USER] is your Nest account name and [PASS] is your Nest
         account password. The result of the command should be a screen
         full of data that has already been gathered by Nest.

Step 5 - Modify the Nestphp.ini. 

Step 6 - Test the Nest.php scipt by running the following command:

            php Nest.php

         If everything works, you should get "DATA ADDED". If you do not
         get this, try looking in the nest_run.log file for signs of error.
         This file will also give you the exact SQL statement that was used
         when trying to insert data.

Step 7 - Set up a cron job to run the Nest.php script every 10 or 15 minutes. 



Data Presentation
=================

Step 1 - Grab the latest version of Flot from http://www.flotcharts.org/

Step 2 - Install the Flot package at the same level as your Nest.php file.

Step 3 - Copy  jquery.1.6.4.min.js and jquery.wxgauges.js to flot directory.

Step 4 - Copy images directory

Step 5 - Test the package by running the nchart.php script from a browser.
         You should get a 3 paned graphic view. Don't wory right away if you
         don't see any data yet. You'll need a couple of hours worth of data
         to determine if your graph is working. Be patient.

