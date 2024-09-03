<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CensosController extends Controller
{
    //
    public function index(){
        $tablas = DB::table('index')->get();
        return response()->json(['data' => $tablas]);
    }

    public function columnas(Request $request)
    {
    	$data = $request->all();
    	$model_columns = [];
    	$columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_schema = 'public' AND table_name   = '".$data['tabla']."'");
        foreach ($columns as $column) {
        	$name = DB::table('censos_economicos_diccionario')->select('nombre','underlay')->where('clave', '=', $column->column_name)->first();
        	//var_dump($name);
        	array_push($model_columns, array("column_name"=>$column->column_name, "nombre" => $name));
        }
    	return response()->json(['columns' => $model_columns], 200);
    }

    public function data_column(Request $request)
    {
    	$data = $request->all();
    	$model_columns = [];
    	$consult = [];
    	$model_consult_columns = [];
    	array_push($model_consult_columns, 'censos_economicos_diccionario.nombre');
    	foreach ($data['column'] as $column) {
    		array_push($model_consult_columns, $column);
    		$consult = DB::table($data['table'])
    		->join('censos_economicos_diccionario', $data['table'].'.'.$column, '=', 'censos_economicos_diccionario.clave')
    		->select($model_consult_columns)->orderBy($column)->distinct()->get();
    		//array_push($model_columns,$consult);

    		$model_columns = array_merge($model_columns, [$column=>$consult]);
    	}
    	
    	return response()->json(['columns' => $model_columns], 200);
    }
    public function consult_data(Request $request){
    	$data = $request->all();

    	$model_theme = [];
    	$clave_theme = $data['values']['theme']['clave'];//var_dump([$data['values']['theme']['values']]);
    	$columns_theme = $data['values']['theme']['columns'];
    	//foreach ($data['values']['theme']['values'] as $theme) {
    		//var_dump([$theme]);
    		array_push($model_theme, $data['values']['theme']['values']['column_name']);
    	//}

    	$model_axis_x = [];
    	$clave_axis_x = $data['values']['axis_x']['clave'];
    	$columns_axis_x = $data['values']['axis_x']['columns'];
    	foreach ($data['values']['axis_x']['values'] as $theme) {
    		array_push($model_axis_x, $theme['column_name']);
    	}
    	//var_dump([$columns_axis_x]);
    	$main_query = DB::table($data['values']['table'])->select('*');
    	//$main_query->whereIn($clave_theme, $model_theme);
    	//$main_query->whereIn($clave_axis_x, $model_axis_x)->get();
    	/*->where(function($query) use ($columns_theme, $model_theme, $columns_axis_x, $model_axis_x)
    	{*/
    		foreach ($columns_theme as $value) { 
    			$main_query->whereIn($value, $model_theme);	
    			
    		}
    		foreach ($columns_axis_x as $value) {
    			$main_query->whereIn($value, $model_axis_x);
    			//var_dump($value);
    		}
    		
    	//})
		$consulta = $main_query->get();
		//var_dump($consulta);

    	/*$query = DB::table($data['values']['table'])
    	->whereIn($clave_theme, $model_theme)
    	->whereIn($clave_axis_x, $model_axis_x)->get();*/
    	return response()->json(['query' => $consulta], 200);
    }

}
