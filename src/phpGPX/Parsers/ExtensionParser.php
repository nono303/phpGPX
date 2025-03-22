<?php
/**
 * Created            15/02/2017 18:29
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use phpGPX\Models\Extensions;
use phpGPX\Models\Extensions\TrackPointExtension;
use phpGPX\Parsers\Extensions\TrackPointExtensionParser;

/**
 * Class ExtensionParser
 * @package phpGPX\Parsers
 */
abstract class ExtensionParser
{
	public static $tagName = 'extensions';

	public static $usedNamespaces = [];
	
	const EXCLUDED_NAMESPACE = ["http://www.topografix.com/GPX/1/1"];

	/**
	 * @param \SimpleXMLElement $nodes
	 * @return Extensions
	 */
	public static function parse($nodes)
	{
		$extensions = new Extensions();

		$nodeNamespaces = array_diff($nodes->getNamespaces(true), self::EXCLUDED_NAMESPACE);

		foreach ($nodeNamespaces as $key => $namespace) {
			$node = $nodes->children($namespace);
			switch ($namespace) {
				case TrackPointExtension::EXTENSION_NAMESPACE:
					$extensions->trackPointExtension = TrackPointExtensionParser::parse($node);
					break;
				case TrackPointExtension::EXTENSION_V1_NAMESPACE:
					$extensions->trackPointExtension = TrackPointExtensionParser::parse($node);
					break;
				default:
					foreach ($nodes->children($namespace) as $child_key => $value) 
						$extensions->unsupported[$key ? "$key:$child_key" : "$child_key"] = (string) $value;
			}
		}

		return $extensions;
	}


	/**
	 * @param Extensions $extensions
	 * @param \DOMDocument $document
	 * @return \DOMElement|null
	 */
	public static function toXML(Extensions $extensions, \DOMDocument &$document)
	{
		$node =  $document->createElement(self::$tagName);

		if (null !== $extensions->trackPointExtension) {
			$child = TrackPointExtensionParser::toXML($extensions->trackPointExtension, $document);
			$node->appendChild($child);
		}

		if (!empty($extensions->unsupported)) {
			foreach ($extensions->unsupported as $key => $value) {
				$child = $document->createElement($key, $value);
				$node->appendChild($child);
			}
		}

		return $node;
	}
}
