<?php

function calcColor($row) {

  if ($row["Status"]=="RUNNING")
   	      echo "statTableGreen";
<<<<<<< HEAD
   	  else if ($row["Status"]=="DELAYED")
   	      echo "statTableYellow";
   	      else echo "statTableRed";
=======
  else if ($row["Status"]=="DELAYED")
   	      echo "statTableYellow";
  else if ($row["Status"]=="INACTIVE")
   	      echo "statTableGrey";
  else echo "statTableRed";
>>>>>>> 3.1.0
}

$pid_save=$pid;
$archive_server_save=$archive_server;

$temp_archive_server=$default_archive_server_list[0];


echo '</div>';
echo '</div>';
echo '</div>';

echo '</br>';


foreach ($default_archive_server_list as $temp_archive_server) {
    include("StatServersOne.php");

}

echo '</div>';
