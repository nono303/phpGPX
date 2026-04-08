<?php
/**
 * Created            05/09/16 17:02
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Helpers;

use phpGPX\Models\Point;
use phpGPX\phpGPX;

/**
 * Class DateTimeHelper
 * @package phpGPX\Helpers
 */
class DateTimeHelper
{

	/**
	 * @param Point $point1
	 * @param Point $point2
	 * @return bool|int
	 */
	public static function comparePointsByTimestamp(Point $point1, Point $point2)
	{
		if ($point1->time == $point2->time) {
			return 0;
		}
		return $point1->time > $point2->time;
	}

	/**
	 * @param $datetime
	 * @param string $format
	 * @return null|string
	 */
	public static function formatDateTime($datetime)
	{
		if(is_null($datetime)) {
			return null;
		} elseif ($datetime instanceof \DateTime) {
			return $datetime->format(phpGPX::$DATETIME_FORMAT);
		} elseif (is_string($datetime)) {
			return ($dt = new \DateTime($datetime))->format(phpGPX::$DATETIME_FORMAT);
		} else {
			throw new \Exception("Unknown datetime format");
		}
	}

	/**
	 * @param $value
	 * @return \DateTime
	 */
	public static function parseDateTime($value)
	{
		return new \DateTime($value);
	}
}
