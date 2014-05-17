<?php
    if ( isset($_POST['orderChk'      ]) ) $orderChk=      $_POST['orderChk'];       else $orderChk=$order_by;
    if ( isset($_POST['rowcnt'        ]) ) $rowcnt=        $_POST['rowcnt'];         else $rowcnt=0;
    if ( isset($_POST['filterappli'   ]) ) $filterappli=   $_POST['filterappli'];    else $filterappli="";    
    if ( isset($_POST['filterserver'  ]) ) $filterserver=  $_POST['filterserver'];   else $filterserver="";    
    if ( isset($_POST['filtername'    ]) ) $filtername=    $_POST['filtername'];     else $filtername="";    
    if ( isset($_POST['filtertime'    ]) ) $filtertime=    $_POST['filtertime'];     else $filtertime="";
    if ( isset($_POST['filterstatus'  ]) ) $filterstatus=  $_POST['filterstatus'];   else $filterstatus="";
    if ( isset($_POST['filtermessage' ]) ) $filtermessage= $_POST['filtermessage'];  else $filtermessage="";
?>

<?php
//----------------------------------------------------------------------------------------------------
// Check table exists
$query = "select cnt=count(*) from sysobjects where name = '".$ServerName."_Checks'";
$result = sybase_query($query,$pid);

$row = sybase_fetch_array($result);
if ($row["cnt"] == 0) {
//if ($result==false) {
   echo "Application Checking data is not available. The Checks view has not been activated for server ".$ServerName.".";
   exit();
}
	
//----------------------------------------------------------------------------------------------------
?>
<script type="text/javascript">
function getCheckDoc(FileName)
{
  var strWindowFeatures = "resizable=yes,scrollbars=yes";  
  
  if (FileName != "") {
  	
     WindowObjectReference = window.open(FileName,
       "_blank", strWindowFeatures);
     WindowObjectReference.focus();	
  }
}
<!---------------------------------------------------------------------------------------------------->
function getCheckHist(CheckName)
{
  ARContextJSON = document.inputparam.ARContextJSON.value;
    WindowObjectReference = window.open("./ASE/AppChecksHistX.php?filtername="+CheckName+"&ARContextJSON="+ARContextJSON+"#top",
    "_blank");
    
  WindowObjectReference.focus();
}	
<!---------------------------------------------------------------------------------------------------->

var WindowObjectReference; // global variable

setStatMainTableSize(0);

</script>

<?php
//----------------------------------------------------------------------------------------------------
if ($orderChk == "") 
   $orderChk=$order_by;

$query = "rollback";
$result = sybase_query($query,$pid);

$query = "if object_id('#checksall') is not null drop table #checksall";
$result = sybase_query($query,$pid);

include ("sql/sql_appchecksall.php");

$debug=0;
if ($debug == 1) {
  echo "<br>query=$query";   //debug
}

function calcColor( $statusmsg ) {
  
       if ($statusmsg == "CRITICAL")
   	                               echo "statTableRed";
  else if ($statusmsg == "WARNING")
                                   echo "statTableYellow";
  else echo "statTable";
}
?>

<INPUT type="hidden" name="filter_clause" value='<?php echo urlencode($filter_clause);?>' >

<div class="boxinmain" style="min-width:800px">
<div class="boxtop">
<div style="float:left; position: relative; top: 3px; left: 6px"><?php include './export/export-table.php' ?></div>
<div class="title"><?php echo "$Title" ?></div>
<a   href="http://github.com/asebox/asebox/App-Log-Statistics" TARGET="_blank"> <img class="help" SRC="images/Help-circle-blue-32.png" ALT="Process help" TITLE="Checks Status Help"  /> </a>
</div>

<div class="boxcontent">

<!-- ---------------------------------------------------------------------------------------------------->
<!-- Top buttons -->
<div class="boxbtns" >
<table align="left" cellspacing="2px" ><tr>

<td> <!-- Refresh button -->
	<img src="images/button_sideLt.gif"  class="btn" height="20px" >
    <INPUT style="height:20px; " class="btn" type="submit" value="Refresh" name="RefreshStmt" >
    <img src="images/button_sideRt.gif"  class="btn" height="20px">
</td>
</tr></table>
</div>

<!-- ---------------------------------------------------------------------------------------------------->
<!-- Main Table -->
<div class="statMainTable">
<table cellspacing=2 cellpadding=4 >
    <tr> 
      <td> </td>
      <td class="statTabletitle" > Application</td>
      <td class="statTabletitle" > Server     </td>
      <td class="statTabletitle" > Check      </td>
      <td class="statTabletitle" > Last Check </td>
      <td class="statTabletitle" > Status     </td>
      <td class="statTabletitle" > Message    </td>
    </tr>
    <tr>  
      <td> </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderChk"  VALUE="Appli"           <?php if ($orderChk=="Appli"   )       echo "CHECKED"; ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderChk"  VALUE="Server"          <?php if ($orderChk=="Server"  )       echo "CHECKED"; ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderChk"  VALUE="ChkName"         <?php if ($orderChk=="ChkName" )       echo "CHECKED"; ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderChk"  VALUE="ChkTime"         <?php if ($orderChk=="ChkTime" )       echo "CHECKED"; ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderChk"  VALUE="ChkStatus DESC,ChkName" <?php if ($orderChk=="ChkStatus DESC,ChkName") echo "CHECKED"; ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderChk"  VALUE="ChkMessage"      <?php if ($orderChk=="ChkMessage")     echo "CHECKED"; ?> > </td>
    </tr>
    <tr> 
      <td> </td>
      <td  class="statTableBtn"> <INPUT TYPE=text NAME="filterappli"              value="<?php if( isset($filterappli   ) ){ echo $filterappli ; } ?>" > </td>
      <td  class="statTableBtn"> <INPUT TYPE=text NAME="filterserver"             value="<?php if( isset($filterserver  ) ){ echo $filterserver; } ?>" > </td>
      <td  class="statTableBtn"> <INPUT TYPE=text NAME="filtername"               value="<?php if( isset($filtername    ) ){ echo $filtername  ; } ?>" > </td>
      <td></td>
      <td  class="statTableBtn"> <INPUT TYPE=text NAME="filterstatus"   SIZE="4"  value="<?php if( isset($filterstatus  ) ){ echo $filterstatus  ; } ?>" > </td>
      <td  class="statTableBtn"> <INPUT TYPE=text NAME="filteremessage" SIZE="60" value="<?php if( isset($filteremessage) ){ echo $filtermessage ; } ?>" > </td>
      <td></td> 
    </tr>

   
<?php
    $result = sybase_query("set rowcount ".$rowcnt."
                           ".$query."
                           set rowcount 0",
                           $pid);                       
    if ($result==false){ 
            sybase_close($pid); 
            $pid=0;
            include ("../connectArchiveServer.php");   
            echo "<tr><td>Error</td></tr></table>";
            return(0);
    }

    $rw=0;
    $cpt=0;
    $TotalTime=0;
    while($row = sybase_fetch_array($result))
    {
        $rw++;
        $TotalTime = $TotalTime  + $row["Time"];
        if($cpt==0)
             $parite="impair";
        else
             $parite="pair";
        $AppCode = substr( $ServerName, 0, 3);
        //print "HELLO ".$AppCode."<br>";
      //$filename = '/CHECKS/'.$ServerName.'/'.$row["ChkName" ].'.html';
        $filename = '/CHECKS/'.$AppCode.'/'.$row["ChkName" ].'.html';
        $filefull = 'C:/AsemonReportSRV/CHECKS/'.$AppCode.'/'.$row["ChkName" ].'.html';
        //$filefull = 'http://parwvm000642/CHECKS/'.$AppCode.'/'.$row["ChkName" ].'.html';
        
        
        if (! file_exists($filefull)) {
        	  //print "File $filename does not exist<br>";
        	  $filename = "";
        }
        
        ?>
        
        <tr class="statTable<?php echo $parite; ?>" onMouseOut="this.className='statTable<?php echo $parite; ?>';" onMouseOver="this.className='<?php echo $parite; ?>;" >
        <?php
        $cpt=1-$cpt;
        ?>

        <td Onclick='getCheckHist("<?php echo $row["ChkName"]?>" ); return false;'> <img class="help" SRC="images/chart.png" ALT="Checks History" TITLE="Checks History"  /> </td>
        <td class="statTablePtr"                                 > <?php echo $row["Appli"]  ?> </td> 
        <td class="statTablePtr"                                 > <?php echo $row["Server"] ?> </td>         
        <td Onclick='getCheckDoc("<?php echo $filename; ?>" ); return false;' class="statTablePtr"            NOWRAP> <?php echo $row["ChkName" ]   ?> </td> 
        <td class="statTablePtr"                           NOWRAP> <?php echo $row["ChkTime"   ] ?> </td> 
        <td class=<?php echo calcColor($row["ChkStatMsg"])?>     > <?php echo $row["ChkStatMsg"] ?> </td>     
        <td class="statTablePtr"                                 > <?php echo $row["ChkMessage"] ?> </td> 
        </tr> 
    <?php
    }
    ?>

</table>
</div>
</div>
</div>
