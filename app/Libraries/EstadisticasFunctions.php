<?php

namespace App\Libraries;
use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;
use App\Libraries\PibFunctions;

class EstadisticasFunctions
{
	function main_consulta($data){ 
		$function = new PibFunctions();
		$back_estadisticas = [];
		$estadisticas = $function->consulta_principal($data);

		$data['selectedValoracion'] = 'pib_precios_constantes_particip';
		$contribucion_query = $function->consulta_principal($data);
		$data['selectedValoracion'] = 'pib_precios_constantes_particip_nacional';
		$contribucion_query_nacional = $function->consulta_principal($data);
		//dd($contribucion_query);
		$periodos = $data['periodos'];
		$promedios = $data['promedios'];
		$estadisticas_promedio = [];
		//dd($estadisticas);
		/*******Canbiar claves por nombress**********/
		$places = [];
		$places_regiones = [];
		$espacio = $data['selectedespaciotipo'];
		if ($espacio == 'region') {
			$places_regiones = DB::table('regiones')->select('id', 'Nombre as nombre', 'clave as key')->get();
		}else{
			$places = DB::table('mge')->select('id', 'nom_ent as nombre', 'cve_ent as key')->get();
		}
		$row_count = 0;
		//dd($estadisticas);
        foreach ($estadisticas as $row) {
            foreach ($row as $key => $value) {
                if($key == 'cve_ent'){
                    if($value != '0'){
                        
                        if($espacio == "region"){
                            foreach ($places_regiones as $place) {
                                if($value == $place->key){
                                    $estadisticas[$row_count][$key] = $place->nombre;
                                    $contribucion_query[$row_count][$key] = $place->nombre;
                                    $contribucion_query_nacional[$row_count][$key] = $place->nombre;        
                                }
                            }
                            
                        }else{//dd($places);
                        	foreach ($places as $place) {
	                            if($value == $place->key){
	                                $estadisticas[$row_count][$key] = $place->nombre;
	                                $contribucion_query[$row_count][$key] = $place->nombre;
	                                $contribucion_query_nacional[$row_count][$key] = $place->nombre;
	                            }
	                        }	
                        }
                    }else{
                        $estadisticas[$row_count][$key] = 'Nacional';
                        $contribucion_query[$row_count][$key] = 'Nacional';
                        $contribucion_query_nacional[$row_count][$key] = 'Nacional';
                        
                    }
                }
            }
            $row_count ++;
        }//dd($contribucion_query);
        /*******Canbiar claves por nombres end**********/
        /*******Calcular promedios**************/
        $back_estadisticas = $estadisticas;
        foreach ($promedios as $promedio) {
			$estadisticas_promedio = $function->promedio(['consulta'=>$estadisticas,'year_1' => $promedio['year_1'],'year_2' => $promedio['year_2']]);
			$counter = 0;
			foreach ($estadisticas_promedio as $row) {
				$back_estadisticas[$counter][$promedio['name']] = $row['Promedio'];
				$counter++;
			}
		}
		/*******Calcular promedios end**************/
		/*******Evaluar variables********************/
		$participacion = [];
		$variacion = [];
		$contribucion_puntos = [];
		$contribucion_porcentaje = [];
		$diferencia = [];

		foreach ($data['variables'] as $variable) {
			if ($variable['id'] == '0') {
				//$participacion = $function->participacion($back_estadisticas,'Ayo', '2001', '2001');
				$participacion = $function->participacion($estadisticas,'Ayo', '2001', '2001');
				foreach ($promedios as $promedio) {
					$estadisticas_promedio = $function->promedio(['consulta'=>$participacion,'year_1' => $promedio['year_1'],'year_2' => $promedio['year_2']]);
					$counter = 0;
					foreach ($estadisticas_promedio as $row) {
						$participacion[$counter][$promedio['name']] = $row['Promedio'];
						$counter++;
					}
				}
			}elseif ($variable['id'] == '1') {
				$variacion = $function->variacion($estadisticas,'Ayo', '2001', '2001');//dd($data);
				foreach ($promedios as $promedio) {
					$estadisticas_promedio = $function->promedio(['consulta'=>$variacion,'year_1' => $promedio['year_1'],'year_2' => $promedio['year_2']]);
					$counter = 0;
					foreach ($estadisticas_promedio as $row) {
						$variacion[$counter][$promedio['name']] = $row['Promedio'];
						$counter++;
					}
				}
			}elseif ($variable['id'] == '2') {
				//$contribucion_puntos = $function->variacion_puntos($back_estadisticas,'Ayo', '2001', '2001');
				//$contribucion_porcentaje = $function->variacion_porcentual($back_estadisticas,'Ayo', '2001', '2001');

				if ($data['selectedTipo'] == '1') {
					if ($data['selectedCobertura'] != 'Manufactura') {
						if ($espacio == 'region') {
							$contribucion_puntos = $function->variacion_puntos($estadisticas,'Ayo', '2001', '2001');
							$contribucion_porcentaje = $function->variacion_porcentual($estadisticas,'Ayo', '2001', '2001');
						}else{
							$contribucion_puntos = $contribucion_query;
							$contribucion_porcentaje = $function->variacion_porcentual_query($estadisticas, $contribucion_query,'Ayo', '2001', '2001');
						}
						
						
					}else{
						$contribucion_puntos = $function->variacion_puntos($estadisticas,'Ayo', '2001', '2001');
						$contribucion_porcentaje = $function->variacion_porcentual($estadisticas,'Ayo', '2001', '2001');
					}
					foreach ($promedios as $promedio) {
						$estadisticas_promedio = $function->promedio(['consulta'=>$contribucion_puntos,'year_1' => $promedio['year_1'],'year_2' => $promedio['year_2']]);
						$counter = 0;
						foreach ($estadisticas_promedio as $row) {
							$contribucion_puntos[$counter][$promedio['name']] = $row['Promedio'];
							$counter++;
						}
					}
					foreach ($promedios as $promedio) {
						$estadisticas_promedio = $function->promedio(['consulta'=>$contribucion_porcentaje,'year_1' => $promedio['year_1'],'year_2' => $promedio['year_2']]);
						$counter = 0;
						foreach ($estadisticas_promedio as $row) {
							$contribucion_porcentaje[$counter][$promedio['name']] = $row['Promedio'];
							$counter++;
						}
					}
					
				}else{//dd($estadisticas);
					if ($espacio == 'region') {
						$contribucion_puntos = $function->variacion_puntos($estadisticas,'Ayo', '2001', '2001');
						$contribucion_porcentaje = $function->variacion_porcentual($estadisticas,'Ayo', '2001', '2001');
					}else{
						$contribucion_puntos = $contribucion_query_nacional;//dd($contribucion_puntos);
						$contribucion_porcentaje = $function->variacion_porcentual_query($estadisticas, $contribucion_query_nacional,'Ayo', '2001', '2001');
					}
					
					foreach ($promedios as $promedio) {
						
						$estadisticas_promedio = $function->promedio(['consulta'=>$contribucion_puntos,'year_1' => $promedio['year_1'],'year_2' => $promedio['year_2']]);
						
						$counter = 0;
						foreach ($estadisticas_promedio as $row) {
							$contribucion_puntos[$counter][$promedio['name']] = $row['Promedio'];
							$counter++;
						}
					}
					
					foreach ($promedios as $promedio) {
						$estadisticas_promedio = $function->promedio(['consulta'=>$contribucion_porcentaje,'year_1' => $promedio['year_1'],'year_2' => $promedio['year_2']]);
						$counter = 0;
						foreach ($estadisticas_promedio as $row) {
							$contribucion_porcentaje[$counter][$promedio['name']] = $row['Promedio'];
							$counter++;
						}
					}
				}

			}elseif ($variable['id'] == '3') {
				if ($data['selectedTipo'] == '1') {
					if ($data['selectedCobertura'] != 'Manufactura') {
						if ($espacio == 'region') {
							$diferencia = $function->eficiencia($estadisticas,'Ayo', '2001', '2001');
						}else{
							$diferencia = $function->eficiencia_query($estadisticas, $contribucion_query,'Ayo', '2001', '2001');
						}
						
					}else{
						$diferencia = $function->eficiencia($estadisticas,'Ayo', '2001', '2001');
					}

					foreach ($promedios as $promedio) {
						$estadisticas_promedio = $function->promedio(['consulta'=>$diferencia,'year_1' => $promedio['year_1'],'year_2' => $promedio['year_2']]);
						$counter = 0;
						foreach ($estadisticas_promedio as $row) {
							$diferencia[$counter][$promedio['name']] = $row['Promedio'];
							$counter++;
						}
					}
				}else{
					if ($espacio == 'region') {
						$diferencia = $function->eficiencia($estadisticas,'Ayo', '2001', '2001');
					}else{
						$diferencia = $function->eficiencia_query($estadisticas, $contribucion_query_nacional,'Ayo', '2001', '2001');
					}
					foreach ($promedios as $promedio) {
						$estadisticas_promedio = $function->promedio(['consulta'=>$diferencia,'year_1' => $promedio['year_1'],'year_2' => $promedio['year_2']]);
						$counter = 0;
						foreach ($estadisticas_promedio as $row) {
							$diferencia[$counter][$promedio['name']] = $row['Promedio'];
							$counter++;
						}
					}
				}
				//$diferencia = $function->eficiencia($back_estadisticas,'Ayo', '2001', '2001');
				
			}
		}
		
		$estadisticas = $back_estadisticas;
		/*******Evaluar variables end********************/
		/*******Obtener headers**************/
		$headers = [];
		//dd($estadisticas[0]);
		foreach ($estadisticas[0] as $key => $value) {
			if($key == 'Actividad_economica'){
				array_unshift($headers, ['nickname' => 'Concepto', 'header' => $key]);
			}elseif($key == 'cve_ent'){
				if ($espacio == 'region') {
					array_unshift($headers, ['nickname' => 'Región', 'header' => $key]);
				}else{
					array_unshift($headers, ['nickname' => 'Entidad Federativa', 'header' => $key]);
				}
				
			}else{
				foreach ($periodos as $periodo) {
                    if ((string)$key == (string)$periodo) {
						array_push($headers, ['nickname' => $key, 'header'=>$key]);
					}
				}
				foreach ($promedios as $promedio) {
					if((string)$key == (string)$promedio['name']){
						array_push($headers, ['nickname' => $key, 'header'=>$key]);
					}
				}
			}
			
		}
		/*******Obtener headers**************/

		return ['headers' => $headers, 'estadisticas'=>$estadisticas, 'participacion'=>$participacion, 'variacion'=>$variacion, 'contribucion_puntos'=>$contribucion_puntos, 'contribucion_porcentaje'=>$contribucion_porcentaje, 'diferencia'=>$diferencia];
	}

	function consulta($data){ 

	}
}