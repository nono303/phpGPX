<?php
/**
 * Created            26/08/16 13:45
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX;

include_once "nono/Common.php";

use phpGPX\Models\GpxFile;
use phpGPX\Parsers\MetadataParser;
use phpGPX\Parsers\RouteParser;
use phpGPX\Parsers\TrackParser;
use phpGPX\Parsers\WaypointParser;

/**
 * Class phpGPX
 * @package phpGPX
 */
class phpGPX
{
	const JSON_FORMAT = 'json';
	const XML_FORMAT = 'xml';

	const PACKAGE_NAME = 'phpGPX';
	const VERSION = '1.4.0';

	/**
	 * Create Stats object for each track, segment and route
	 * @var bool
	 */
	public static $CALCULATE_STATS = true;

	/**
	 * Additional sort based on timestamp in Routes & Tracks on XML read.
	 * Disabled by default, data should be already sorted.
	 * @var bool
	 */
	public static $SORT_BY_TIMESTAMP = false;

	/**
	 * Default DateTime output format in JSON serialization.
	 * @var string
	 */
	public static $DATETIME_FORMAT = 'Y-m-d\TH:i:sp';

	/**
	 * Pretty print.
	 * @var bool
	 */
	public static $PRETTY_PRINT = true;

	/**
	 * In stats elevation calculation: ignore points with an elevation of 0
	 * This can happen with some GPS software adding a point with 0 elevation
	 *
	 * @var bool
	 */
	public static $IGNORE_ELEVATION_0 = true;

	/**
	 * Apply elevation gain/loss smoothing? If true, the threshold in
	 * ELEVATION_SMOOTHING_THRESHOLD and ELEVATION_SMOOTHING_SPIKES_THRESHOLD (if not null) applies
	 * @var bool
	 */
	public static $APPLY_ELEVATION_SMOOTHING = false;

	/**
	 * if APPLY_ELEVATION_SMOOTHING is true
	 * the minimum elevation difference between considered points in meters
	 * @var int
	 */
	public static $ELEVATION_SMOOTHING_THRESHOLD = 2;

	/**
	 * if APPLY_ELEVATION_SMOOTHING is true
	 * the maximum elevation difference between considered points in meters
	 * @var int|null
	 */
	public static $ELEVATION_SMOOTHING_SPIKES_THRESHOLD = null;

	/**
	 * if elevation not set, retreieve it from external function
	 */
	public static $ELEVATION_EXTERNAL = false;

	/**
	 * Apply distance calculation smoothing? If true, the threshold in
	 * DISTANCE_SMOOTHING_THRESHOLD applies
	 * @var bool
	 */
	public static $APPLY_DISTANCE_SMOOTHING = false;

	/**
	 * if APPLY_DISTANCE_SMOOTHING is true
	 * the minimum distance between considered points in meters
	 * @var int
	 */
	public static $DISTANCE_SMOOTHING_THRESHOLD = 2;

	/**
	 * stdout debug if true
	 */
	public static $DEBUG = null;

	const LOG_DEBUG	= 1;
	const LOG_INFO	= 2;
	const LOG_WARN	= 3;
	const LOG_ERROR	= 4;
	const LOG_FATAL	= 5;
	const LOG_STRING = [
		phpGPX::LOG_DEBUG	=> "DEBUG",
		phpGPX::LOG_INFO	=> "INFO",
		phpGPX::LOG_WARN	=> "WARN",
		phpGPX::LOG_ERROR	=> "ERROR",
		phpGPX::LOG_FATAL	=> "FATAL"
	];

	// LIBXML_NOBLANKS: better performance
	const LIBXML_DEFAULT_FLAGS = LIBXML_BIGLINES | LIBXML_COMPACT | LIBXML_NOBLANKS | LIBXML_NOCDATA | LIBXML_NSCLEAN | LIBXML_PARSEHUGE | LIBXML_NO_XXE | LIBXML_NOENT;

	public static function logstdout($level,$message){
		if(phpGPX::$DEBUG && $level >= phpGPX::$DEBUG)
			echo phpGPX::LOG_STRING[$level]."\t".$message.PHP_EOL;
	}

	/**
	 * Load GPX file.
	 * @param $path
	 * @return GpxFile
	 */
	public static function load($path,$flags = phpGPX::LIBXML_DEFAULT_FLAGS)
	{

		return self::parse(file_get_contents(realpath($path)));
	}

	/**
	 * Parse GPX data string.
	 * @param $xml
	 * @return GpxFile
	 */
	public static function parse($xml,$flags = phpGPX::LIBXML_DEFAULT_FLAGS)
	{
		if(gettype($xml) == "string"){
			$xml = \Common::xml_load_string($xml, 'SimpleXMLElement', $flags);
		} elseif(get_class($xml) != "SimpleXMLElement"){
			Throw new \Exception("Unknow type of xml: ".get_class($xml));
		}

		$gpx = new GpxFile();

		if(phpGPX::$ELEVATION_EXTERNAL)
			include_once("gis/Elevation.php");

		// Parse creator
		$gpx->creator = isset($xml['creator']) ? (string)$xml['creator'] : null;

		// Parse metadata
		$gpx->metadata = isset($xml->metadata) ? MetadataParser::parse($xml->metadata) : null;

		// Parse waypoints
		$gpx->waypoints = isset($xml->wpt) ? WaypointParser::parse($xml->wpt) : [];

		// Parse tracks
		$gpx->tracks = isset($xml->trk) ? TrackParser::parse($xml->trk,$gpx) : [];

		// Parse routes
		$gpx->routes = isset($xml->rte) ? RouteParser::parse($xml->rte,$gpx) : [];

		return $gpx;
	}

	/**
	 * Create library signature from name and version.
	 * @return string
	 */
	public static function getSignature()
	{
		return sprintf("%s/%s", self::PACKAGE_NAME, self::VERSION);
	}
}
