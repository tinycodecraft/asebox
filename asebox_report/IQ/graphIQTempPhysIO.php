<?php
  include ("../ARContext_restore.php");
  include ("../connectArchiveServer.php");	
  include ("../".$jpgraph_home."/src/jpgraph.php");
  include ("../".$jpgraph_home."/src/jpgraph_line.php");


		$result=sybase_query(
"select Ts=convert(varchar,Timestamp,3)+' '+convert(varchar,Timestamp,108), 
TempPhysicalWrites=(d_TempPhysicalWrites*1000.)/(Interval), 
TempPhysicalReads=(d_TempPhysicalReads*1000.)/(Interval)
from ".$ServerName."_IQStatus
where Timestamp >='".$StartTimestamp."'
and Timestamp <='".$EndTimestamp."'
order by Timestamp",
 $pid);
		while (($row=sybase_fetch_array($result)))
		{
			$Timestamp[]= $row["Ts"];
			$TempPhysicalWrites[] = $row["TempPhysicalWrites"];
			$TempPhysicalReads[] = $row["TempPhysicalReads"];

		}


	
		
		// New graph with a background image and drop shadow
		$graph = new Graph(1000,300,"auto");
        $graph->SetScale("textlin");
        $theme_class = new AsemonTheme;
        $graph->SetTheme($theme_class);


		// Set title and subtitle
		$graph->title->Set("Temp Physical IO (Writes + Reads accumulated)");
		$graph->subtitle->Set("From: ".$StartTimestamp." To: ".$EndTimestamp);

		// Use built in font
		$graph->title->SetFont(FF_FONT1,FS_BOLD);

		// Make the margin around the plot a little bit bigger
		// then default
		$graph->img->SetMargin(95,20,40,100);	


		// Display every 10:th datalabel
		$graph->xaxis->SetTickLabels($Timestamp);
		$nbVal=sizeof($Timestamp);
		if ( $nbVal>20 )
		  $graph->xaxis->SetTextTickInterval($nbVal/20);
		else
		  $graph->xaxis->SetTextTickInterval(1);
		  		
		// Create the write line plot
		$write_gr = new LinePlot($TempPhysicalWrites);
		$write_gr ->SetLegend("Writes / s");
		
		// Create the reads line plot
		$read_gr = new LinePlot($TempPhysicalReads);
		$read_gr ->SetLegend("Reads / s");

		$acc_gr = new AccLinePlot(array($write_gr, $read_gr));
		

		$graph->Add($acc_gr);
		
		$graph -> legend  -> SetLayout(LEGEND_HOR);
		$graph -> legend  -> Pos(0.05, 0.05, "rigth", "top");

		
        $graph->graph_theme=null; // This fix bottom margin bad computation
		// Finally output the  image
		$graph->Stroke();

	?>
