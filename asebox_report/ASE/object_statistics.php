<?php
<<<<<<< HEAD

        if ( isset($_POST['orderObj'])) $orderObj=$_POST['orderObj']; else $orderObj=$order_by;
        if ( isset($_POST['rowcnt'])  ) $rowcnt=  $_POST['rowcnt'];   else $rowcnt=200;
        if ( isset($_POST['filterdbname']) ) $filterdbname= $_POST['filterdbname'];  else $filterdbname="";
        if ( isset($_POST['filterobjname']) ) $filterobjname= $_POST['filterobjname'];  else $filterobjname="";
        if ( isset($_POST['filterindname']) ) $filterindname= $_POST['filterindname'];  else $filterindname="";
        if ( isset($_POST['filterindid']) ) $filterindid= $_POST['filterindid'];  else $filterindid="";
        if ( isset($_POST['show_index_not_used']) ) $show_index_not_used= $_POST['show_index_not_used'];  else $show_index_not_used="";

        if ( $show_index_not_used == "") 
            $index_not_usedBtnValue= "ShowIndexNotUsedOnly";
        else 
            $index_not_usedBtnValue= "RemoveShowIndexNotUsedOnly";

        if ( isset($_POST['sc_show_table_scans']) ) $sc_show_table_scans= $_POST['sc_show_table_scans'];  else $sc_show_table_scans="";

        if ( $sc_show_table_scans == "") 
            $show_table_scanBtnValue= "ShowTableScansOnly";
        else 
            $show_table_scanBtnValue= "RemoveShowTableScansOnly";



  // Check if indname col exists in OpObjAct table
  $result = sybase_query(
      "select cnt=count(*) from syscolumns where id=object_id('".$ServerName."_OpObjAct') and name='indname'"
      , $pid);
	if ($result==false){ 
        sybase_close($pid); 
        $pid=0;
        include ("../connectArchiveServer.php");	
        echo "<tr><td>Error</td></tr></table>";
        return(0);
	}
  $row = sybase_fetch_array($result);
  if ( $row["cnt"] == 1 ) {
          $indname_clause = ",indname";
          $indname_filterclause = "and (indname like '".$filterindname."' or '".$filterindname."'='')";
  }
  else {
          $indname_clause = "";
          $indname_filterclause = "";
  }


  // Check if table xxxx_OpObjAct supports 15.0
  $query = "select cnt=count(*) from syscolumns where id =object_id('".$ServerName."_OpObjAct') and name in ('HkgcRequests', 'HkgcPending', 'HkgcOverflows')";
  $result = sybase_query($query,$pid);
  $row = sybase_fetch_array($result);
  if ($row["cnt"] == 3)
      $support150=1;
  else
      $support150=0;

  // Check if table xxxx_OpObjAct supports 15.7
  $query = "select cnt=count(*) from syscolumns where id =object_id('".$ServerName."_OpObjAct') and name in ('SharedLockWaitTime', 'ExclusiveLockWaitTime', 'UpdateLockWaitTime', 'ObjectCacheDate')";
  $result = sybase_query($query,$pid);
  $row = sybase_fetch_array($result);
  if ($row["cnt"] == 4)
      $support157=1;
  else
      $support157=0;


	include './ASE/sql/sql_object_statistics.php';
?>

<script type="text/javascript">
var WindowObjectReference; // global variable

setStatMainTableSize(0);
=======
if ( isset($_POST['orderObj'])) $orderObj=$_POST['orderObj']; else $orderObj=$order_by;
if ( isset($_POST['rowcnt'])  ) $rowcnt=  $_POST['rowcnt'];   else $rowcnt=200;
if ( isset($_POST['filterdbname']) ) $filterdbname= $_POST['filterdbname'];  else $filterdbname="";
if ( isset($_POST['filterobjname']) ) $filterobjname= $_POST['filterobjname'];  else $filterobjname="";
if ( isset($_POST['filterindname']) ) $filterindname= $_POST['filterindname'];  else $filterindname="";
if ( isset($_POST['filterindid']) ) $filterindid= $_POST['filterindid'];  else $filterindid="";
if ( isset($_POST['show_index_not_used']) ) $show_index_not_used= $_POST['show_index_not_used'];  else $show_index_not_used="";

if ( $show_index_not_used == "") 
    $index_not_usedBtnValue= "ShowIndexNotUsedOnly";
else 
    $index_not_usedBtnValue= "RemoveShowIndexNotUsedOnly";

if ( isset($_POST['sc_show_table_scans']) ) $sc_show_table_scans= $_POST['sc_show_table_scans'];  else $sc_show_table_scans="";

if ( $sc_show_table_scans == "") 
    $show_table_scanBtnValue= "ShowTableScansOnly";
else 
    $show_table_scanBtnValue= "RemoveShowTableScansOnly";

//----------------------------------------------------------------------------------------------------
// Check if indname col exists in OpObjAct table
$result = sybase_query(
     "select cnt=count(*) from syscolumns where id=object_id('".$ServerName."_OpObjAct') and name='indname'"
     , $pid);
if ($result==false){ 
       sybase_close($pid); 
       $pid=0;
       include ("../connectArchiveServer.php");	
       echo "<tr><td>Error</td></tr></table>";
       return(0);
}
$row = sybase_fetch_array($result);
if ( $row["cnt"] == 1 ) {
        $indname_clause = ",indname";
        $indname_filterclause = "and (indname like '".$filterindname."' or '".$filterindname."'='')";
}
else {
        $indname_clause = "";
        $indname_filterclause = "";
}
//----------------------------------------------------------------------------------------------------
// Check if table xxxx_OpObjAct supports 15.0
$query = "select cnt=count(*) from syscolumns where id =object_id('".$ServerName."_OpObjAct') and name in ('HkgcRequests', 'HkgcPending', 'HkgcOverflows')";
$result = sybase_query($query,$pid);
$row = sybase_fetch_array($result);
if ($row["cnt"] == 3)
    $support150=1;
else
    $support150=0;

//----------------------------------------------------------------------------------------------------
// Check if table xxxx_OpObjAct supports 15.7
$query = "select cnt=count(*) from syscolumns where id =object_id('".$ServerName."_OpObjAct') and name in ('SharedLockWaitTime', 'ExclusiveLockWaitTime', 'UpdateLockWaitTime', 'ObjectCacheDate')";
$result = sybase_query($query,$pid);
$row = sybase_fetch_array($result);
if ($row["cnt"] == 4)
    $support157=1;
else
    $support157=0;


include './ASE/sql/sql_object_statistics.php';

$debug=0;
if ($debug == 1) {
  echo "<br>query=$query";   //debug
}

?>

<!---------------------------------------------------------------------------------------------------->
<!--SCRIPTS -->
<script type="text/javascript">
var WindowObjectReference; // global variable

<?php
if ( $Title != "Table Statistics" ) {
?>
   setStatMainTableSize(0);
<?php
}
?>

>>>>>>> 3.1.0

function getObjectDetail(ObjectDbName, ObjectName, IndexID)
{
  ARContextJSON = document.inputparam.ARContextJSON.value;
  WindowObjectReference = window.open("./ASE/object_detail.php?ObjectDbName="+ObjectDbName+"&ObjectName="+ObjectName+"&ARContextJSON="+ARContextJSON+"#top", "_blank");
  WindowObjectReference.focus();
}

function getNotUsedIndex() {
	if (document.inputparam.show_index_not_used.value == "") {
   // cancel getTableScans
   document.inputparam.sc_show_table_scans.value = "";
   document.inputparam.show_table_scanBtnName.value = "ShowTableScansOnly";
   // activate getNotUsedIndex
   document.inputparam.show_index_not_used.value = "1";
   document.inputparam.index_not_usedBtnName.value = "RemoveShowIndexNotUsedOnly";
  }
  else {
  	// cancel getNotUsedIndex
   document.inputparam.show_index_not_used.value = "";
   document.inputparam.index_not_usedBtnName.value = "ShowIndexNotUsedOnly";
  }
  document.inputparam.submit();
}
function getTableScans() {
	if (document.inputparam.sc_show_table_scans.value == "") {
  	// cancel getNotUsedIndex
   document.inputparam.show_index_not_used.value = "";
   document.inputparam.index_not_usedBtnName.value = "ShowIndexNotUsedOnly";
   // activate getTableScans
   document.inputparam.sc_show_table_scans.value = " and IndID = 0 and UsedCount > 0 ";
   document.inputparam.show_table_scanBtnName.value = "RemoveShowTableScansOnly";
  }
  else {
  	// cancel getTableScans
   document.inputparam.sc_show_table_scans.value = "";
   document.inputparam.show_table_scanBtnName.value = "ShowTableScansOnly";
  }
  document.inputparam.submit();
}
<<<<<<< HEAD


</script>


=======
</script>

<!---------------------------------------------------------------------------------------------------->
<!--Hidd fields -->
>>>>>>> 3.1.0
<input type="hidden" name="show_index_not_used" value="<?php echo $show_index_not_used ?>">
<input type="hidden" name="sc_show_table_scans" value="<?php echo $sc_show_table_scans ?>">

<center>

<<<<<<< HEAD





=======
>>>>>>> 3.1.0
<div class="boxinmain" style="min-width:800px">
<div class="boxtop">
<div style="float:left; position: relative; top: 3px; left: 6px"><?php include './export/export-table.php' ?></div>
<div class="title"><?php echo  $Title ?></div>
<<<<<<< HEAD
<a href="http://github.com/asebox/asebox?title=AseRep_ASEObjStats" TARGET="_blank"> <img class="help" SRC="images/Help-circle-blue-32.png" ALT="Object help" TITLE="Object help"  width="32" height="32" /> </a>
=======
<a href="http://github.com/asebox/asebox/ASE-Object-Statistics" TARGET="_blank"> <img class="help" SRC="images/Help-circle-blue-32.png" ALT="Object help" TITLE="Object help"  width="32" height="32" /> </a>
>>>>>>> 3.1.0
</div>

<div class="boxcontent">


<div class="boxbtns" >
<table align="left" cellspacing="2px" ><tr>
<td>Max rows (0 = unlimited) :</td>
<td>
	<input type="text" name="rowcnt" value="<?php if( isset($rowcnt) ){ echo $rowcnt ; } ?>">
</td>
<td>
	<img src="images/button_sideLt.gif"  class="btn" height="20px" >
    <INPUT style="height:20px; " class="btn" type="submit" value="Refresh" name="RefreshStmt" >
    <img src="images/button_sideRt.gif"  class="btn" height="20px">
</td>

<td>
    <img src="images/button_sideLt.gif"  class="btn" height="20px" >
    <input style="height:20px; " class="btn" type="button" value="<?php echo $index_not_usedBtnValue ?>" name="index_not_usedBtnName" onclick="javascript:getNotUsedIndex();">
    <img src="images/button_sideRt.gif"  class="btn" height="20px">
</td>
<td>
    <img src="images/button_sideLt.gif"  class="btn" height="20px" >
    <input style="height:20px; " class="btn" type="button" value="<?php echo $show_table_scanBtnValue ?>" name="show_table_scanBtnName" onclick="javascript:getTableScans();">
    <img src="images/button_sideRt.gif"  class="btn" height="20px">
    <?php
    if ($show_index_not_used==1) echo "WARNING : UNIQ indexes may have a UsedCount=0 but are used for integrity"
    ?>
</td>

</tr></table>
</div>



<div class="statMainTable">









<table cellspacing=2 cellpadding=4 >
    <tr> 
<<<<<<< HEAD
      <td class="statTabletitle" > DBName  </td>
      <td class="statTabletitle" > ObjName  </td>
      <?php
      if ($indname_clause!="") {
      ?>
          <td class="statTabletitle" > IndName   </td>
      <?php
      }
      ?>
      <td class="statTabletitle" > IndID   </td>
=======
      <td class="statTabletitle" > Database  </td>
      <td class="statTabletitle" > Table Name  </td>
      <?php
      if ($indname_clause!="") {
      ?>
          <td class="statTabletitle" > Index Name   </td>
      <?php
      }
      ?>
      <td class="statTabletitle" > IndID</td>
>>>>>>> 3.1.0
      <td class="statTabletitle" > LReads   </td>
      <td class="statTabletitle" > PReads  </td>
      <td class="statTabletitle" > APFReads   </td>
      <td class="statTabletitle" > PageReads  </td>
      <td class="statTabletitle" > CacheHit%  </td>
      <td class="statTabletitle" > PWrites   </td>
      <td class="statTabletitle" > PageWrites  </td>
      <td class="statTabletitle" > RowIns  </td>
      <td class="statTabletitle" > RowDel  </td>
      <td class="statTabletitle" > RowUpd  </td>
      <td class="statTabletitle" > operations  </td>
      <td class="statTabletitle" > LockRequests  </td>
      <td class="statTabletitle" > LockWaits  </td>
      <td class="statTabletitle" > UsedCount  </td>
      <td class="statTabletitle" > LReads_per_UsedCnt  </td>
      <?php if ($support150==1) { ?>
      <td class="statTabletitle" > HkgcRequests  </td>
      <td class="statTabletitle" > maxHkgcPending  </td>
      <td class="statTabletitle" > HkgcOverflows  </td>
      <?php } ?>
      <?php if ($support157==1) { ?>
      <td class="statTabletitle" > sumSharedLockWaitTime  </td>
      <td class="statTabletitle" > sumExclusiveLockWaitTime  </td>
      <td class="statTabletitle" > sumUpdateLockWaitTime  </td>
      <td class="statTabletitle" > maxObjectCacheDate  </td>
      <?php } ?>
    </tr>
    <tr class=statTableTitle>  
      <td colspan=3 align="center" class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="dbname, objname, IndID"      <?php if ($orderObj=="dbname, objname, IndID")      echo "CHECKED";  ?> > </td> 
      <?php
      if ($indname_clause!="") {
      ?>
          <td>    </td>
      <?php
      }
      ?>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="LReads DESC"      <?php if ($orderObj=="LReads DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="PReads DESC"      <?php if ($orderObj=="PReads DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="AReads DESC"      <?php if ($orderObj=="AReads DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="PgReads DESC"      <?php if ($orderObj=="PgReads DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="CacheHitPct"      <?php if ($orderObj=="CacheHitPct")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="PWrites DESC"      <?php if ($orderObj=="PWrites DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="PgWrites DESC"      <?php if ($orderObj=="PgWrites DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="RowIns DESC"      <?php if ($orderObj=="RowIns DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="RowDel DESC"      <?php if ($orderObj=="RowDel DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="RowUpd DESC"      <?php if ($orderObj=="RowUpd DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="Opers DESC"      <?php if ($orderObj=="Opers DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="LockR DESC"      <?php if ($orderObj=="LockR DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="LockW DESC"      <?php if ($orderObj=="LockW DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="UsedCnt DESC"      <?php if ($orderObj=="UsedCnt DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="LReads_per_UsedCnt DESC"      <?php if ($orderObj=="LReads_per_UsedCnt DESC")      echo "CHECKED";  ?> > </td>
      <?php if ($support150==1) { ?>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="HkgcRequests DESC"      <?php if ($orderObj=="HkgcRequests DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="HkgcPending DESC"      <?php if ($orderObj=="HkgcPending DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="HkgcOverflows DESC"      <?php if ($orderObj=="HkgcOverflows DESC")      echo "CHECKED";  ?> > </td>
      <?php } ?>
      <?php if ($support157==1) { ?>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="sumSharedLockWaitTime DESC"      <?php if ($orderObj=="sumSharedLockWaitTime DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="sumExclusiveLockWaitTime DESC"      <?php if ($orderObj=="sumExclusiveLockWaitTime DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="sumUpdateLockWaitTime DESC"      <?php if ($orderObj=="sumUpdateLockWaitTime DESC")      echo "CHECKED";  ?> > </td>
      <td  class="statTableBtn"> <INPUT TYPE=radio NAME="orderObj"  VALUE="maxObjectCacheDate"      <?php if ($orderObj=="maxObjectCacheDate")      echo "CHECKED";  ?> > </td>
      <?php } ?>
    </tr>


    <tr class=statTableTitle> 
<<<<<<< HEAD
      <td  class="statTableBtn"> <INPUT TYPE=text NAME="filterdbname"  value="<?php if( isset($filterdbname) ){ echo $filterdbname ; } ?>" > </td>
      <td  class="statTableBtn"> <INPUT TYPE=text NAME="filterobjname"  value="<?php if( isset($filterobjname) ){ echo $filterobjname ; } ?>" > </td>
      <?php
      if ($indname_clause!="") {
      ?>
          <td  class="statTableBtn"> <INPUT TYPE=text NAME="filterindname"  value="<?php if( isset($filterindname) ){ echo $filterindname ; } ?>" > </td>
      <?php
      }
      ?>
      <td  class="statTableBtn"> <INPUT TYPE=text NAME="filterindid" SIZE="3" value="<?php if( isset($filterindid) ){ echo $filterindid ; } ?>" > </td>
=======
      <td  class="statTableBtn"> <INPUT TYPE=text NAME="filterdbname"  SIZE="10" value="<?php if( isset($filterdbname) ){ echo $filterdbname ; } ?>" > </td>
      <td  class="statTableBtn"> <INPUT TYPE=text NAME="filterobjname" SIZE="13" value="<?php if( isset($filterobjname) ){ echo $filterobjname ; } ?>" > </td>
      <?php
      if ($indname_clause!="") {
      ?>
          <td  class="statTableBtn"> <INPUT TYPE=text NAME="filterindname" SIZE="13" value="<?php if( isset($filterindname) ){ echo $filterindname ; } ?>" > </td>
      <?php
      }
      ?>
      <td  class="statTableBtn"> <INPUT TYPE=text NAME="filterindid" SIZE="1" value="<?php if( isset($filterindid) ){ echo $filterindid ; } ?>" > </td>
>>>>>>> 3.1.0
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <?php if ($support150==1) { ?>
      <td></td> 
      <td></td> 
      <td></td> 
      <?php } ?>
      <?php if ($support157==1) { ?>
      <td></td> 
      <td></td> 
      <td></td> 
      <td></td> 
      <?php } ?>
    </tr>



    <?php

	$grandTotLreads = 0;
	$grandTotPReads = 0;
	$grandTotPWrites = 0;

	$result = sybase_query("set rowcount ".$rowcnt." ".$query." set rowcount 0",$pid);
	
	$rw=0;
	$cpt=0;
    while($row = sybase_fetch_array($result))
    {
            if($cpt==0)
                 $parite="impair";
            else
                 $parite="pair";
            ?>
            <tr class="statTable<?php echo $parite; ?>" onMouseOut="this.className='statTable<?php echo $parite; ?>';" onMouseOver="this.className='<?php echo $parite; ?>onMouseOver';" Onclick='javascript:getObjectDetail("<?php echo urlencode($row["dbname"])?>","<?php echo urlencode($row["objname"])?>","<?php echo $row["IndID"]?>" )' >
            <?php
            $cpt=1-$cpt;
            $grandTotLreads = $grandTotLreads + $row["LReads"];
            $grandTotPReads = $grandTotPReads + $row["PReads"];
            $grandTotPWrites = $grandTotPWrites + $row["PWrites"];
		    ?>
            <td class="statTablePtr" > <?php echo $row["dbname"] ?>  </td>
            <td class="statTablePtr" > <?php echo $row["objname"] ?>  </td>
            <?php
            if ($indname_clause!="") {
            ?>
            <td class="statTablePtr" > <?php echo $row["indname"] ?>  </td>
            <?php
            }
            ?>
			<td class="statTablePtr" align="right" > <?php echo $row["IndID"] ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["LReads"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["PReads"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["AReads"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["PgReads"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo $row["CacheHitPct"] ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["PWrites"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["PgWrites"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["RowIns"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["RowDel"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["RowUpd"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["Opers"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["LockR"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["LockW"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["UsedCnt"]) ?>  </td>
			<td class="statTablePtr" align="right" > <?php echo number_format($row["LReads_per_UsedCnt"]) ?>  </td>
            <?php if ($support150==1) { ?>
            <td class="statTablePtr" align="right"> <?php echo number_format($row["sumHkgcRequests"]) ?>  </td>
            <td class="statTablePtr" align="right"> <?php echo number_format($row["maxHkgcPending"] ) ?> </td>
            <td class="statTablePtr" align="right"> <?php echo number_format($row["sumHkgcOverflows"]) ?>  </td>
            <?php } ?>
            <?php if ($support157==1) { ?>
            <td class="statTablePtr" align="right"> <?php echo number_format($row["sumSharedLockWaitTime"]) ?>  </td>
            <td class="statTablePtr" align="right"> <?php echo number_format($row["sumExclusiveLockWaitTime"] ) ?> </td>
            <td class="statTablePtr" align="right"> <?php echo number_format($row["sumUpdateLockWaitTime"]) ?>  </td>
            <td class="statTablePtr" >              <?php echo               $row["maxObjectCacheDate"] ?>  </td>
            <?php } ?>
        </tr> 
        <?php
    } // while($row = sybase_fetch_array($result))
    ?>
    <tr>    
        <td class="statTable" > <?php echo "" ?>  </td>
        <td class="statTable" align="right"> <?php echo "Total =" ?>  </td>
        <td class="statTable" > <?php echo "" ?>  </td>
        <?php
            if ($indname_clause!="") {
            ?>
            <td class="statTable" > <?php echo "" ?>  </td>
            <?php
            }
        ?>
	    <td class="statTable" align="right"> <?php echo number_format($grandTotLreads) ?>  </td>
	    <td class="statTable" align="right"> <?php echo number_format($grandTotPReads) ?>  </td>
	    <td class="statTable" > <?php echo "" ?>  </td>
	    <td class="statTable" > <?php echo "" ?>  </td>
	    <td class="statTable" > <?php echo "" ?>  </td>
	    <td class="statTable" align="right"> <?php echo number_format($grandTotPWrites) ?>  </td>
	    <td class="statTable" > <?php echo "" ?>  </td>
	    <td class="statTable" > <?php echo "" ?>  </td>
	    <td class="statTable" > <?php echo "" ?>  </td>
	    <td class="statTable" > <?php echo "" ?>  </td>
	    <td class="statTable" > <?php echo "" ?>  </td>
	    <td class="statTable" > <?php echo "" ?>  </td>
	    <td class="statTable" > <?php echo "" ?>  </td>
	    <td class="statTable" > <?php echo "" ?>  </td>
	    <td class="statTable" > <?php echo "" ?>  </td>
     </tr>

</table>
</div>
</div>
</div>
</center>
