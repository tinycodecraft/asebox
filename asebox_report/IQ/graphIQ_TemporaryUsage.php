<?php
  include ("../ARContext_restore.php");
  include ("../connectArchiveServer.php");	
  include ("../".$jpgraph_home."/src/jpgraph.php");
  include ("../".$jpgraph_home."/src/jpgraph_line.php");


  $result=sybase_query(
          "select Ts=convert(varchar,Timestamp,3)+' '+convert(varchar,Timestamp,108), 
          TempUsed_Mb=TempIQUsedBlocks*BlockSizeBytes/1024/1024, TempCapacity_Mb=TempIQCapacityBlocks*BlockSizeBytes/1024/1024
          from ".$ServerName."_IQStatus
          where Timestamp >='".$StartTimestamp."'
          and Timestamp <='".$EndTimestamp."'
          order by Timestamp",
          $pid);
  while (($row=sybase_fetch_array($result)))
	{
			$Timestamp[]= $row["Ts"];
			$TempUsed_Mb[] = $row["TempUsed_Mb"];
			$TempCapacity_Mb[] = $row["TempCapacity_Mb"];

	}


	
		
		// New graph with a background image and drop shadow
		$graph = new Graph(1000,300,"auto");
        $graph->SetScale("textlin");
        $theme_class = new AsemonTheme;
        $graph->SetTheme($theme_class);


		// Set title and subtitle
		$graph->title->Set("Temporary store usage");
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
		  		

		// Create the Pct_used line plot
		$temp_used_gr = new LinePlot($TempUsed_Mb);
		$temp_used_gr ->SetLegend("Temp used");
		
		// Create the size plot
		$capacity_gr = new LinePlot($TempCapacity_Mb);
		$capacity_gr ->SetLegend("Temp capacity (Mb)");


		//$acc_gr = new AccLinePlot(array($in_gr, $out_gr));
		
		$graph -> yaxis-> scale -> setAutoMin(0);

		//$graph->Add($acc_gr);
		$graph->Add($capacity_gr);
		$graph->Add($temp_used_gr);
		
		$graph -> legend  -> SetLayout(LEGEND_HOR);
		$graph -> legend  -> Pos(0.05, 0.05, "rigth", "top");

		
        $graph->graph_theme=null; // This fix bottom margin bad computation
		// Finally output the  image
		$graph->Stroke();

	?>
