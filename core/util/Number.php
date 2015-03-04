<?php

namespace Naga\Core\Util;

use Naga\Core\nComponent;

/**
 * Common number related stuff.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Util
 */
class Number extends nComponent
{
	/**
	 * Rounds the specified number to the nearest five.
	 * 1, 2 rounded to 0
	 * 3, 4, 6, 7 rounded to 5
	 * 8, 9 rounded to 10
	 *
	 * @param $number
	 * @return float
	 */
	public static function roundToNearestFive($number)
	{
		$lastNumber = substr((string)$number, -1);
		if ($lastNumber <= 2 || ($lastNumber >= 6 && $lastNumber <= 7))
			$number = round($number / 5) * 5;
		else
			$number = ceil($number / 5) * 5;

		return $number;
	}

	/**
	 * Rounds the specified number to the nearest ten. 5 and 5+ rounded to 10.
	 *
	 * @param $number
	 * @return float
	 */
	public static function roundToNearestTen($number)
	{
		return round($number, -1, PHP_ROUND_HALF_UP);
	}

	/**
	 * Gets the difference between the original and the rounded number.
	 *
	 * @param $number
	 * @return float
	 */
	public static function getRoundedToNearestFiveDifference($number)
	{
		return self::roundToNearestFive($number) - $number;
	}

	/**
	 * Gets the difference between the original and the rounded number.
	 *
	 * @param $number
	 * @return float
	 */
	public static function getRoundedToNearestTenDifference($number)
	{
		return self::roundToNearestTen($number) - $number;
	}
}