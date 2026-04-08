<?php
/**
 * Created            17/02/2017 17:46
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use phpGPX\Helpers\SerializationHelper;
use phpGPX\Parsers\ExtensionParser;
use phpGPX\Parsers\MetadataParser;
use phpGPX\Parsers\PointParser;
use phpGPX\Parsers\RouteParser;
use phpGPX\Parsers\TrackParser;
use phpGPX\phpGPX;

/**
 * Class GpxFile
 * Representation of GPX file.
 * @package phpGPX\Models
 */
class GpxFile implements Summarizable
{
	/**
	 * A list of waypoints.
	 * @var Point[]
	 */
	public $waypoints;

	/**
	 * A list of routes.
	 * @var Route[]
	 */
	public $routes;

	/**
	 * A list of tracks.
	 * @var Track[]
	 */
	public $tracks;

	/**
	 * Metadata about the file.
	 * The original GPX 1.1 attribute.
	 * @var Metadata|null
	 */
	public $metadata;

	/**
	 * @var Extensions|null
	 */
	public $extensions;

	/**
	 * Creator of GPX file.
	 * @var string|null
	 */
	public $creator;

	/**
	 * GpxFile constructor.
	 */
	public function __construct()
	{
		$this->waypoints = [];
		$this->routes = [];
		$this->tracks = [];
		$this->metadata = null;
		$this->extensions = null;
		$this->creator = null;
	}


	/**
	 * Serialize object to array
	 * @return array
	 */
	public function toArray()
	{
		return SerializationHelper::filterNotNull([
			'creator' => SerializationHelper::stringOrNull($this->creator),
			'metadata' => SerializationHelper::serialize($this->metadata),
			'waypoints' => SerializationHelper::serialize($this->waypoints),
			'routes' => SerializationHelper::serialize($this->routes),
			'tracks' => SerializationHelper::serialize($this->tracks),
			'extensions' => SerializationHelper::serialize($this->extensions)
		]);
	}

	/**
	 * Return JSON representation of GPX file with statistics.
	 * @return string
	 */
	public function toJSON()
	{
		return json_encode($this->toArray(), phpGPX::$PRETTY_PRINT ? JSON_PRETTY_PRINT : null);
	}

	/**
	 * Create XML representation of GPX file.
	 * @return \DOMDocument
	 */
	public function toXML()
	{
		$document = new \DOMDocument("1.0", 'UTF-8');

		$gpx = $document->createElementNS("http://www.topografix.com/GPX/1/1", "gpx");
		$gpx->setAttribute("version", "1.1");
		$gpx->setAttribute("creator", $this->creator ? $this->creator : phpGPX::getSignature());

		ExtensionParser::$usedNamespaces = [
				["prefix" => "wptx1",	"namespace" => "http://www.garmin.com/xmlschemas/WaypointExtension/v1",						"xsd" => "https://www8.garmin.com/xmlschemas/WaypointExtensionv1.xsd"],
				["prefix" => "gpxtrx",	"namespace" => "http://www.garmin.com/xmlschemas/GpxExtensions/v3",							"xsd" => "http://www8.garmin.com/xmlschemas/GpxExtensionsv3.xsd"],
				["prefix" => "gpxx",	"namespace" => "http://www.garmin.com/xmlschemas/GpxExtensions/v3",							"xsd" => "http://www8.garmin.com/xmlschemas/GpxExtensionsv3.xsd"],
				["prefix" => "gpxtpx",	"namespace" => "http://www.garmin.com/xmlschemas/TrackPointExtension/v2",					"xsd" => "https://www8.garmin.com/xmlschemas/TrackPointExtensionv2.xsd"],
				["prefix" => "trp",		"namespace" => "http://www.garmin.com/xmlschemas/TripExtensions/v2",						"xsd" => "https://www8.garmin.com/xmlschemas/TripExtensionsv2.xsd"],
				["prefix" => "adv",		"namespace" => "http://www.garmin.com/xmlschemas/AdventuresExtensions/v1",					"xsd" => "http://www8.garmin.com/xmlschemas/AdventuresExtensionv1.xsd"],
				["prefix" => "prs",		"namespace" => "http://www.garmin.com/xmlschemas/PressureExtension/v1",						"xsd" => "http://www.garmin.com/xmlschemas/PressureExtensionv1.xsd"],
				["prefix" => "tmd",		"namespace" => "http://www.garmin.com/xmlschemas/TripMetaDataExtensions/v1",				"xsd" => "http://www.garmin.com/xmlschemas/TripMetaDataExtensionsv1.xsd"],
				["prefix" => "vptm",	"namespace" => "http://www.garmin.com/xmlschemas/ViaPointTransportationModeExtensions/v1",	"xsd" => "http://www.garmin.com/xmlschemas/ViaPointTransportationModeExtensionsv1.xsd"],
				["prefix" => "ctx",		"namespace" => "http://www.garmin.com/xmlschemas/CreationTimeExtension/v1",					"xsd" => "http://www.garmin.com/xmlschemas/CreationTimeExtensionsv1.xsd"],
				["prefix" => "gpxacc",	"namespace" => "http://www.garmin.com/xmlschemas/AccelerationExtension/v1",					"xsd" => "http://www.garmin.com/xmlschemas/AccelerationExtensionv1.xsd"],
				["prefix" => "vidx1",	"namespace" => "http://www.garmin.com/xmlschemas/VideoExtension/v1",						"xsd" => "http://www.garmin.com/xmlschemas/VideoExtensionv1.xsd"],
				["prefix" => "gpxpx",	"namespace" => "http://www.garmin.com/xmlschemas/PowerExtension/v1",						"xsd" => "https://www8.garmin.com/xmlschemas/PowerExtensionv1.xsd"],
				["prefix" => "gpxtrkx",	"namespace" => "http://www.garmin.com/xmlschemas/TrackStatsExtension/v1",					"xsd" => "https://www8.garmin.com/xmlschemas/TrackStatsExtension.xsd"],
				["prefix" => "ns3",		"namespace" => "http://www.garmin.com/xmlschemas/ActivityExtension/v2",						"xsd" => "http://www8.garmin.com/xmlschemas/ActivityExtensionv2.xsd"],
				["prefix" => "twonav",	"namespace" => "http://twonav.com/twonav"],
				["prefix" => "locus",	"namespace" => "http://www.locusmap.eu"],
				["prefix" => "ogr",		"namespace" => "http://osgeo.org/gdal"],
				["prefix" => "opencpn",	"namespace" => "http://www.opencpn.org"],
			];

		if (!empty($this->metadata)) {
			$gpx->appendChild(MetadataParser::toXML($this->metadata, $document));
		}

		foreach ($this->waypoints as $waypoint) {
			$gpx->appendChild(PointParser::toXML($waypoint, $document));
		}

		foreach ($this->routes as $route) {
			$gpx->appendChild(RouteParser::toXML($route, $document));
		}

		foreach ($this->tracks as $track) {
			$gpx->appendChild(TrackParser::toXML($track, $document));
		}

		if (!empty($this->extensions)) {
			$gpx->appendChild(ExtensionParser::toXML($this->extensions, $document));
		}

		// Namespaces
		$schemaLocationArray = [
			'http://www.topografix.com/GPX/1/1',
			'http://www.topografix.com/GPX/1/1/gpx.xsd'
		];

		foreach (ExtensionParser::$usedNamespaces as $usedNamespace) {
			$gpx->setAttributeNS(
				"http://www.w3.org/2000/xmlns/",
				sprintf("xmlns:%s", $usedNamespace['prefix']),
				$usedNamespace['namespace']
			);
			if($usedNamespace['xsd']) {
				$schemaLocationArray[] = $usedNamespace['namespace'];
				$schemaLocationArray[] = $usedNamespace['xsd'];
			}
		}

		$gpx->setAttributeNS(
			'http://www.w3.org/2001/XMLSchema-instance',
			'xsi:schemaLocation',
			implode(" ", $schemaLocationArray)
		);

		$document->appendChild($gpx);

		if (phpGPX::$PRETTY_PRINT) {
			$document->formatOutput = true;
			$document->preserveWhiteSpace = true;
		}
		return $document;
	}

	/**
	 * Save data to file according to selected format.
	 * @param string $path
	 * @param string $format
	 */
	public function save($path, $format)
	{
		switch ($format) {
			case phpGPX::XML_FORMAT:
				$document = $this->toXML();
				$document->save($path);
				break;
			case phpGPX::JSON_FORMAT:
				file_put_contents($path, $this->toJSON());
				break;
			default:
				throw new \RuntimeException("Unsupported file format!");
		};
	}
}
