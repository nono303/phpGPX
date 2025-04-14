<?php
/**
 * Created            30/08/16 17:27
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Helpers;

use phpGPX\Models\Point;
use phpGPX\phpGPX;
use phpGPX\Models\Track;
use phpGPX\Models\Route;

/**
 * Class GeoHelper
 * Geolocation methods.
 * @package phpGPX\Helpers
 */
abstract class GeoHelper
{
	const EARTH_RADIUS = 6378137;

	/**
	 * Returns distance in meters between two Points according to GPX coordinates.
	 * @see Point
	 * @param Point $point1
	 * @param Point $point2
	 * @return float
	 */
	public static function getRawDistance(Point $point1, Point $point2)
	{
		$latFrom = deg2rad($point1->latitude);
		$lonFrom = deg2rad($point1->longitude);
		$latTo = deg2rad($point2->latitude);
		$lonTo = deg2rad($point2->longitude);

		$lonDelta = $lonTo - $lonFrom;
		$a = pow(cos($latTo) * sin($lonDelta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
		$b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
		$angle = atan2(sqrt($a), $b);

		return $angle * self::EARTH_RADIUS;
	}

	/**
	 * Returns distance between two points including elevation gain/loss
	 * @param Point $point1
	 * @param Point $point2
	 * @return float
	 */
	public static function getRealDistance(Point $point1, Point $point2)
	{
		$distance = self::getRawDistance($point1, $point2);

		$elevation1 = $point1->elevation != null ? $point1->elevation : 0;
		$elevation2 = $point2->elevation != null ? $point2->elevation : 0;
		$elevDiff = abs($elevation1 - $elevation2);

		return sqrt(pow($distance, 2) + pow($elevDiff, 2));
	}
	
	public static function setAlt(&$trkrte, $trkrtes){
		if(is_a($trkrte, "phpGPX\Models\Track")){
			$display = "trk";
			$check = $trkrte->segments[0];
		} elseif(is_a($trkrte, "phpGPX\Models\Route")){
			$display = "rte";
			$check = $trkrte;
		}
		if(phpGPX::$ELEVATION_EXTERNAL && !$check->points[0]->elevation){
			if(is_a($trkrte, "phpGPX\Models\Track")){
				for($s = 0; $s < ($nbs = count($trkrte->segments)); $s++)
					self::subSetAlt($trkrte->segments[$s],$display,$trkrtes,$s);
			} elseif(is_a($trkrte, "phpGPX\Models\Route")){
				self::subSetAlt($trkrte,$display,$trkrtes);
			}
		} elseif(phpGPX::$ELEVATION_EXTERNAL){
			phpGPX::logstdout(phpGPX::LOG_DEBUG,"ele present for ".$display."[".sizeof($trkrtes)."]");
		} elseif(!$check->points[0]->elevation){
			phpGPX::logstdout(phpGPX::LOG_DEBUG,"ele missing for ".$display."[".sizeof($trkrtes)."]");
		}
	}
	
	private static function subSetAlt(&$segrte,$display,$trkrtes,$seg = null){
		if(phpGPX::$DEBUG)
			$bt = microtime(true);
		try{
			$ret = \Elevation::getAltitudeFromArray(((array) $segrte->points),"latitude","longitude");
		} catch(\Exception $e){
			phpGPX::logstdout(phpGPX::LOG_ERROR,$e->getMessage());
			return;
		}
		for($p = 0; $p < ($nbp = count($segrte->points)); $p++)
			$segrte->points[$p]->elevation = $ret[$p];	
		phpGPX::logstdout(phpGPX::LOG_INFO,"setting ".$nbp." ele for ".$display."[".sizeof($trkrtes)."]".(is_null($seg) ?: " seg[".$seg."]").(phpGPX::$DEBUG ? " in ".round((microtime(true)-$bt)*1000)."ms" : ""));
	}
}
