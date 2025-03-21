<?php
	require_once 'vendor/autoload.php';
	use phpGPX\phpGPX;
	
	$gpx = new phpGPX();
	$gpx::$APPLY_ELEVATION_SMOOTHING = true;
	$gpx::$ELEVATION_SMOOTHING_THRESHOLD = 2;
	$gpx::$ELEVATION_EXTERNAL = true;
	$gpx::$DEBUG = true;

	foreach([
		"./gpx/2024-08-21 080359__20240821_0803.gpx",
		"./gpx/2024-08-21 080359__20240821_0803-noalt.gpx",
		"./gpx/rte.gpx",
		"./gpx/rte-noalt.gpx",
		"./gpx/ign.gpx",
		"./gpx/ign-noalt.gpx",
		"./gpx/2024-08-21-08-03-53.fit_reduced.gpx",
	] as $gpxfile){
		echo ">>>>>>>> loading ".realpath($gpxfile).PHP_EOL;
		$file = $gpx->load($gpxfile);
		echo "###################### METADATA".PHP_EOL.print_r($file->metadata,true);
		echo "###################### CREATOR".PHP_EOL.print_r($file->creator,true);
		echo PHP_EOL."###################### EXTENSIONS".PHP_EOL.print_r($file->extensions,true);
		foreach([$file->tracks, $file->routes] as $statspointer){
			$t = 0;
			foreach($statspointer as $trkrte){
				$stats = $trkrte->stats->toArray();
				echo "######## ".get_class($trkrte)."[".($t++)."]".PHP_EOL;
				foreach(["minAltitude","maxAltitude"] as $d)
					echo "\t".$d.": ".$stats[$d].PHP_EOL;
			}
		}
	}
?>