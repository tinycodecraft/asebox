<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3c.org/TR/1999/REC-html401-19991224/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <script LANGUAGE="javascript" type="text/javascript" SRC="../scripts/jsDate.js"></script>
    <script LANGUAGE="javascript" SRC="../scripts/json2.js"> </script>
    <script LANGUAGE="javascript" SRC="../scripts/calendrier.js"> </script>
    <script LANGUAGE="javascript" SRC="../scripts/parsedate.js"> </script>
    <script LANGUAGE="javascript" SRC="../scripts/asemon_report.js"> </script>
<<<<<<< HEAD
    <link rel=STYLESHEET type="text/css" href="../stylesheets/common.css" >
    <link rel=STYLESHEET type="text/css" href="../stylesheets/maindiv.css" >
    <link rel=STYLESHEET type="text/css" href="../stylesheets/stylecalend.css" >
=======
    <link rel=STYLESHEET type="text/css" href="../stylesheets/asebox.css" >
>>>>>>> 3.1.0

    <?php
    // Retreive session context
    include ("../ARContext_restore.php");

    // Retreive search panel parameters
    if ( isset($_POST['StartTimestamp' ]) ) $StartTimestamp= $_POST['StartTimestamp'];
    if ( isset($_POST['EndTimestamp'   ]) ) $EndTimestamp=   $_POST['EndTimestamp'];
    if ( isset($_POST['SrvType' ])        ) $SrvType=        $_POST['SrvType'];
    if ( isset($_POST['ServerName'     ]) ) $ServerName=     $_POST['ServerName'];
    if ( isset($_POST['DFormat'  ])       ) $DFormat=        $_POST['DFormat'];

    include ("../connectArchiveServer.php");	
    ?>

<title>Asemon Report - Caches repartition</title>

</head>

<body>
  <script type=text/javascript> setMainDivSize(false); </script>
  <form name="inputparam" method="POST" action="">
  <?php
  $displaylevel=1;
<<<<<<< HEAD
  include ("../asemon_search_panel.php");
=======
  include ("../compare_search_panel.php");
>>>>>>> 3.1.0
  ?>
  <INPUT type="HIDDEN" name="ARContextJSON" value='<?php echo $ARContextJSON;?>' >
   
  <center>
       



<?php


         
    // Find caches          
    $query="select distinct CacheID, CacheName
                          from ".$ServerName."_CachedObj
                          where Timestamp >='".$StartTimestamp."'
                          and Timestamp <'".$EndTimestamp."'
                          order by CacheID";
    
    $result=sybase_query($query, $pid);
    $cnt=0;
    if ($result==false){ 
         sybase_close($pid); 
         $pid=0;
         include ("../connectArchiveServer.php");	
         echo "Error";
         return(0);
    }
    while (($row=sybase_fetch_array($result)))
    {
         $t_CacheID[]      = $row["CacheID"];
         $t_CacheName[]  = $row["CacheName"];
    }
    $nbCaches=count($t_CacheID);
    
    if ($nbCaches==0) 	exit("pas de valeurs");
     
     
    // Loop on caches
    for ($i=0; $i<$nbCaches; $i++ ) {


        $CacheName=$t_CacheName[$i]; 

//        echo "<H1>".$CacheName."</H1>";


        ?>


   <H1><?php echo $CacheName; ?></H1>

        <p>
	        <img src='<?php echo "${HomeUrl}/ASE/graphCachedObj_TOPperCache.php?CacheName=".urlencode($CacheName)."&ARContextJSON=".urlencode($ARContextJSON); ?> '>
        </p>

        <?php
            include './pool_statistics.php';
        ?>
    
        <br>

    <?php
    }   // End loop on caches
    ?>
    </center>
</form>
</body>
