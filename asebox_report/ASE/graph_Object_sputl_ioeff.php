<?php
  include ("../ARContext_restore.php");
  include ("../connectArchiveServer.php");		
	include ("../".$jpgraph_home."/src/jpgraph.php");
	include ("../".$jpgraph_home."/src/jpgraph_line.php");
	include ("../".$jpgraph_home."/src/jpgraph_bar.php");	
	include ("../".$jpgraph_home."/src/jpgraph_date.php");	




	$param_list=array(
		'DbName',
		'Owner',
		'TabName',
		'IndId',
		'StartTS',
		'EndTS'
	);
	foreach ($param_list as $param)
		@$$param=${"_$_SERVER[REQUEST_METHOD]"}[$param];






  $result = sybase_query("select ts=convert(varchar,Timestamp,116), 
              sput=F.space_utilization, 
              ioeff=F.largeIO_eff
              from ".$ServerName."_Fragment F
              where F.dbname='".$DbName."'
              and F.owner='".$Owner."'
              and F.tabname='".$TabName."'
              and F.indid=convert(int,'".$IndId."')
              and F.Timestamp >= '".$StartTS."' and F.Timestamp <= '".$EndTS."'
              order by F.Timestamp",
               $pid);

		 		
		while (($row=sybase_fetch_array($result)))
		{
      $Timestamp[]=  date_format(date_create($row["ts"]),'U');
			$sput[] = $row["sput"];
			$ioeff[] = $row["ioeff"];
		}

		if (count($Timestamp)==0) 
		  JpGraphError::Raise("No values");
		
		// New graph with a background image and drop shadow
		$graph = new Graph(1000,300,"auto");
        $graph->SetScale("datlin");
        $theme_class = new AsemonTheme;
        $graph->SetTheme($theme_class);
    $graph->xaxis->scale->SetTimeAlign( MINADJ_1,  MINADJ_1  ); 
    $graph->SetTickDensity( TICKD_NORMAL, TICKD_SPARSE );
		$graph->xaxis->SetFont(FF_COURIER,FS_NORMAL,8);
		$graph->xaxis->SetLabelAngle(45);
    $graph->xaxis->scale->SetDateFormat( 'd/m/y H:i ' );

		// Set title and subtitle
		$graph->title->Set("Space and large IO");

		// Use built in font
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->subtitle->Set("From: ".$StartTS." To: ".$EndTS);

		// Make the margin around the plot a little bit bigger
		// then default
		$graph->img->SetMargin(95,20,40,100);	


		  		
		$graph->xaxis->SetFont(FF_COURIER,FS_NORMAL,8);
		$graph->xaxis->SetLabelAngle(45);

		$b1 = new LinePlot($sput, $Timestamp);
		$b1 -> setcolor("blue");
		$b1 -> SetLegend("Space utilization");
		$b1 -> SetStyle(1);
$b1->mark->SetType(MARK_UTRIANGLE);
$b1->mark->SetColor('blue');
$b1->mark->SetFillColor('blue');


		$b2 = new LinePlot($ioeff, $Timestamp);
		$b2 -> setcolor("brown");
		$b2 -> SetLegend("Large IO efficiency");
		$b2 -> SetStyle(1);
$b2->mark->SetType(MARK_UTRIANGLE);
$b2->mark->SetColor('brown');
$b2->mark->SetFillColor('brown');




		$graph -> yaxis->SetFont(FF_COURIER,FS_NORMAL,8);

		$graph -> yaxis -> scale -> setAutoMin(0);
		$graph -> yaxis -> scale -> setAutoMax(1);
		
		// The order the plots are added determines who's ontop

		$graph->Add($b1);
		$graph->Add($b2);

		$graph -> legend  -> SetLayout(LEGEND_HOR);
		$graph -> legend  -> Pos(0.05, 0.05, "rigth", "top");

        $graph->graph_theme=null; // This fix bottom margin bad computation
		// Finally output the  image
		$graph->Stroke();

	?>
