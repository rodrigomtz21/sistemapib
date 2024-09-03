<?php

namespace App\Libraries\Censos;
use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;

class FormulasFunctions
{
	function formula_one($a, $b){
		$result = 0;
		try {
			$result = $a / $b;
		} catch (Exception $e) {
			$result = 0;
		}
		return $result;
	}
	function formula_two($a, $b){
		return $a + $b;
	}
	function formula_three($a, $b){//influencia //Influencia general
		$result = 0;
		try {
			$result = $a/$b*100;
		} catch (Exception $e) {
			$result = 0;
		}
		return $result;
	}
	function formula_ten($a, $b){//influencia {"formula":"formula_two","variables":{"a":"","b":""}}
		$result = 0;
		try {
			$result = $a/$b*1000000;
		} catch (Exception $e) {
			$result = 0;
		}
		return $result;
	}
	function formula_four($a, $b){
		$result = 0;
		try {
			$result = $a/($b+$a)*100;
		} catch (Exception $e) {
			$result = 0;
		}
		return $result;
	}
	function formula_five($a, $b, $c){
		return $a + $b + $c;
	}
	function formula_six($a, $b){
		$result = 0;
		try {
			$result = $a/$b;
		} catch (Exception $e) {
			$result = 0;
		}
		return $result;
	}
	function formula_seven($a, $b){//Influencia/Variacion variables
		$result = 0;
		try {
			$result = ($a/$b-1)*100;
		} catch (Exception $e) {
			$result = 0;
		}
		return $result;
	}
	function formula_eight($a, $b){
		$result = 0;
		try {
			$result = ($a-$b)/$a*100;
		} catch (Exception $e) {
			$result = 0;
		}
		return $result;
	}
	function formula_nine($a, $b){//Influencia
		return $a - $b;
	}

}