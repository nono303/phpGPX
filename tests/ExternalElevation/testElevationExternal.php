<?php
	require_once 'vendor/autoload.php';
	use phpGPX\phpGPX;
	
	$gpx = new phpGPX();
	$gpx::$APPLY_ELEVATION_SMOOTHING = true;
	$gpx::$ELEVATION_SMOOTHING_THRESHOLD = 2;
	$gpx::$ELEVATION_EXTERNAL = true;
	$gpx::$DEBUG = phpGPX::LOG_INFO;

	foreach(glob("./gpx/*.gpx") as $gpxfile){
		try{
			$pb = microtime(true);
			$file = $gpx->load($gpxfile);
			echo ">>>>>>>> loading ".realpath($gpxfile)." ".PHP_EOL.(round((microtime(true)-$pb)*1000))."ms".PHP_EOL;
			// echo "###################### METADATA".PHP_EOL.print_r($file->metadata,true);
			// echo "###################### CREATOR".PHP_EOL.print_r($file->creator,true);
			// echo PHP_EOL."###################### EXTENSIONS".PHP_EOL.print_r($file->extensions,true);
			foreach([$file->tracks, $file->routes] as $statspointer){
				$t = 0;
				foreach($statspointer as $trkrte){
					$stats = $trkrte->stats->toArray();
					// echo PHP_EOL."###################### STATS".PHP_EOL.print_r($stats,true);
					// echo "######## ".get_class($trkrte)."[".($t++)."]".PHP_EOL;
					foreach(["minAltitude","maxAltitude","cumulativeElevationGain","cumulativeElevationLoss"] as $d)
						echo "\t".$d.": ".$stats[$d].PHP_EOL;
				}
			}
			file_put_contents("./res/".basename($gpxfile),$file->toXML()->saveXML());
		} catch(Exception $e){
			echo "!!!!!!!! EXCEPTION ".realpath($gpxfile).": ".$e->getMessage().PHP_EOL;//.$e->getTraceAsString().PHP_EOL;
		}
	}
?>