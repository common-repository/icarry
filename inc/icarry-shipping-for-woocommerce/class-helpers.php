<?php
namespace ICarryShippingForWooCommerce;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ICARRY_Helpers
{


	public static function icarry_round(float $value, int $accuracy = 2): float
	{
		return (float) number_format($value, $accuracy, '.', '');
	}


	public static function icarry_convertWeight(string $input_unit, string $output_unit, $weight = 0): float
	{

		// kg, g, lbs, oz
		if ($input_unit === $output_unit or $weight === 0) {
			return $weight;
		}

		$converted_weight = $grams = 0;

		switch ($input_unit) {
			case 'kg':
				$grams = $weight * 1000;
				break;
			case 'lbs':
				$grams = $weight * 453.592;
				break;
			case 'oz':
				$grams = $weight * 28.3495;
				break;
			case 'g':
				$grams = $weight;
				break;
		}

		switch ($output_unit) {
			case 'kg':
				$converted_weight = $grams / 1000;
				break;
			case 'lbs':
				$converted_weight = $grams / 453.592;
				break;
			case 'oz':
				$converted_weight = $grams / 28.3495;
				break;
			case 'g':
				$converted_weight = $grams;
				break;
		}

		return $converted_weight;
	}



	public static function icarry_convertDimension(float $dimension, string $input_unit, string $output_unit): float
	{
		// m, cm, mm, in, yd
		if ($input_unit === $output_unit) {
			return $dimension;
		}

		$converted_dimension = $mm = 0;

		switch ($input_unit) {
			case 'cm':
				$mm = $dimension * 100;
				break;
			case 'm':
				$mm = $dimension * 1000;
				break;
			case 'in':
				$mm = $dimension * 25.4;
				break;
			case 'yd':
				$mm = $dimension * 914.4;
				break;
		}

		switch ($output_unit) {
			case 'cm':
				$converted_dimension = $mm / 100;
				break;
			case 'm':
				$converted_dimension = $mm / 1000;
				break;
			case 'in':
				$converted_dimension = $mm / 25.4;
				break;
			case 'yd':
				$converted_dimension = $mm / 914.4;
				break;
		}

		return $converted_dimension;
	}

	

	public static function icarry_convertDimensionToCm(float $dimension, string $input_unit): float
	{
		$converted_dimension = $mm = 0;

		switch ($input_unit) {
			case 'mm':
				$converted_dimension = $dimension / 10;
			break;
			case 'in':
				$converted_dimension = $dimension / 25.4;
			break;
			case 'yd':
				$converted_dimension = $dimension / 914.4;
			break;
			case 'cm':
				$converted_dimension = $dimension;
			break;
		}
		return $converted_dimension;
	}
}
