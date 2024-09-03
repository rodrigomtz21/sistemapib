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
use App\Libraries\ResumenFunctions;
use App\Libraries\PibNacionalEvaluacionFunctions;

class EvaluacionFunctions
{
	function main_consulta($data){ 
		/******Declaracion de variables objeto Data****/
			$function = new PibFunctions();
			$function_evaluacion = new EvaluacionFunctions();
			$function_resumen = new ResumenFunctions();
			$evaluacion_nacional = new PibNacionalEvaluacionFunctions();
			$back_estadisticas = [];
			$base = $data['selectedValoracion'];
	        $actividad = $data['selectedActividad'];
	        $cobertura = $data['selectedSector'];
	        $subsector = $data['selectedSubsector'];
	        $periodos = $data['periodos'];
	        $entidades = $data['entidades'];
	        $region = $data['selectedRegion'];
	        $regiones = $data['regiones'];
	        $denominacion = $data['selectedDenominacion'];
	        $espacio = $data['selectedespaciotipo'];
	        $contribucion_quey = [];
        /******Declaracion de variables objeto Data End****/
        /*********Consulta Principal*****/
			if($espacio == "region"){
	            $estadisticas = $function_resumen->consulta_region($base, $actividad, $cobertura,$subsector, $region, $denominacion);
	            $contribucion_quey = $function_resumen->consulta_region('pib_precios_constantes_particip_nacional', $actividad, $cobertura,$subsector, $region, $denominacion);
	        }elseif($espacio == "region_nacional"){
	            $estadisticas = $function_resumen->consulta_region_nacional($base, $actividad, $cobertura,$subsector, $regiones, $denominacion);
	            $contribucion_quey = $function_resumen->consulta_region_nacional('pib_precios_constantes_particip_nacional', $actividad, $cobertura,$subsector, $regiones, $denominacion);
	        }elseif ($espacio == "entidad") {
	            $estadisticas = $function_resumen->consulta_nacional($base, $actividad, $cobertura,$subsector, $entidades, $denominacion);
	            $contribucion_quey = $function_resumen->consulta_nacional('pib_precios_constantes_particip_nacional', $actividad, $cobertura,$subsector, $entidades, $denominacion);
	        }
        /*********Consulta Principal End *****/

		 
        /*********Calcular promedios array promedios**********/
	        $promedios = $data['promedios'];
			$back_estadisticas = $evaluacion_nacional->add_promedios($estadisticas, $promedios);
			/*$estadisticas_promedio = [];
	        $back_estadisticas = $estadisticas;
	        foreach ($promedios as $promedio) {
				$estadisticas_promedio = $function->promedio(['consulta'=>$estadisticas,'year_1' => $promedio['year_1'],'year_2' => $promedio['year_2']]);
				$counter = 0;
				foreach ($estadisticas_promedio as $row) {
					$back_estadisticas[$counter][$promedio['name']] = $row['Promedio'];
					$counter++;
				}
			}*/
		/*********Calcular promedios array promedios end **********/
		
		/*********Calcular variables array variabled***************/
			//$participacion = $function->participacion($back_estadisticas,'Ayo', '2001', '2001');
			$participacion = $function->participacion($estadisticas,'Ayo', '2001', '2001');
			$participacion = $evaluacion_nacional->add_promedios($participacion, $promedios);
			$variacion = $function->variacion($estadisticas,'Ayo', '2001', '2001');//dd($data);
			$variacion = $evaluacion_nacional->add_promedios($variacion, $promedios);
			/*foreach ($promedios as $promedio) {
				$estadisticas_promedio = $function->promedio(['consulta'=>$variacion,'year_1' => $promedio['year_1'],'year_2' => $promedio['year_2']]);
				$counter = 0;
				foreach ($estadisticas_promedio as $row) {
					$variacion[$counter][$promedio['name']] = $row['Promedio'];
					$counter++;
				}
			}*/
			$contribucion_puntos = [];
			$contribucion_porcentaje = [];
			$diferencia = [];

			foreach ($data['variables'] as $variable) {
				if ($variable['id'] == '2') {
					//$contribucion_puntos = $function->variacion_puntos($back_estadisticas,'Ayo', '2001', '2001');
					//$contribucion_porcentaje = $function->variacion_porcentual($back_estadisticas,'Ayo', '2001', '2001');
					//$contribucion_puntos = $evaluacion_nacional->variacion_puntos($estadisticas, 'Ayo', '2004', '2010', $promedios);
					if ($espacio == 'region') {
						$contribucion_puntos = $function->variacion_puntos($back_estadisticas,'Ayo', '2001', '2001');
						$contribucion_porcentaje = $function->variacion_porcentual($back_estadisticas,'Ayo', '2001', '2001');
					}else{
						$contribucion_puntos = $contribucion_quey;
						$contribucion_porcentaje = $evaluacion_nacional->variacion_porcentual_query($estadisticas, $contribucion_quey, 'Ayo', '2004', '2010', $promedios);
					}
					
					$contribucion_puntos = $evaluacion_nacional->add_promedios($contribucion_puntos, $promedios);

    				//$contribucion_porcentaje = $evaluacion_nacional->variacion_porcentual($estadisticas, 'Ayo', '2004', '2010', $promedios);
    				
    				$contribucion_porcentaje = $evaluacion_nacional->add_promedios($contribucion_porcentaje, $promedios);
    				//dd($contribucion_porcentaje);
				}elseif ($variable['id'] == '3') {
					if ($espacio == 'region') {
						$diferencia = $function->eficiencia($back_estadisticas,'Ayo', '2001', '2001');
					}else{
						$diferencia = $evaluacion_nacional->eficiencia_query($estadisticas, $contribucion_quey, 'Ayo', '2004', '2010', $promedios);
					}
					//$diferencia = $function->eficiencia($back_estadisticas,'Ayo', '2001', '2001');
					//$diferencia = $evaluacion_nacional->eficiencia($estadisticas, 'Ayo', '2004', '2010', $promedios);
					
					$diferencia = $evaluacion_nacional->add_promedios($diferencia, $promedios);
					//dd(['diferencia'=>$diferencia]);
				}
			}
		/*********Calcular variables array variabled end***************/
		/*********Evaluar posiciones para cada variable*********************/
			$estadisticas = $back_estadisticas;
			foreach ($data['periodos'] as $value) {

				$back_estadisticas = $function_evaluacion->posiciones($back_estadisticas, $estadisticas, $value);
				$participacion = $function_evaluacion->posiciones($participacion, $estadisticas, $value);
				$variacion = $function_evaluacion->posiciones($variacion, $estadisticas, $value);
				$contribucion_puntos = $function_evaluacion->posiciones($contribucion_puntos, $estadisticas, $value);
				$contribucion_porcentaje = $function_evaluacion->posiciones($contribucion_porcentaje, $estadisticas, $value);
				$diferencia = $function_evaluacion->posiciones_diferencia($diferencia, $estadisticas, $value, $variacion[0][$value]);
			}
			foreach ($promedios as $value) {

				$back_estadisticas = $function_evaluacion->posiciones($back_estadisticas, $estadisticas, $value['name']);
				$participacion = $function_evaluacion->posiciones($participacion, $estadisticas, $value['name']);
				$variacion = $function_evaluacion->posiciones($variacion, $estadisticas, $value['name']);
				$contribucion_puntos = $function_evaluacion->posiciones($contribucion_puntos, $estadisticas, $value['name']);
				$contribucion_porcentaje = $function_evaluacion->posiciones($contribucion_porcentaje, $estadisticas, $value['name']);
				$diferencia = $function_evaluacion->posiciones_diferencia($diferencia, $estadisticas, $value['name'], $variacion[0][$value['name']]);
			}
			$estadisticas = $back_estadisticas;
			//dd($estadisticas);
		/*********Evaluar posiciones para cada variable end *********************/
		/*********Cambiar claves entiades por nombres*********/ 
			$places = [];
			$places_regiones = [];
			$places = DB::table('mge')->select('id', 'nom_ent as nombre', 'cve_ent as key')->get();
			if ($espacio == 'region_nacional') {
				$places_regiones = DB::table('regiones')->select('id', 'Nombre as nombre', 'clave as key')->get();
			}else{
				$places = DB::table('mge')->select('id', 'nom_ent as nombre', 'cve_ent as key')->get();
			}
			$row_count = 0;
	        foreach ($estadisticas as $row) {
	            foreach ($row as $key => $value) {
	                if($key == 'cve_ent'){
	                    if($value != '0'){
	                        if($espacio == "region_nacional"){
	                            foreach ($places_regiones as $place) {
	                            	if('a'.$value == 'a'.$place->key){
	                                //if($value == $place->key){
	                                    $estadisticas[$row_count]['nombre'] = $place->nombre;
	                                    $participacion[$row_count]['nombre'] = $place->nombre;
	                                    $variacion[$row_count]['nombre'] = $place->nombre;
	                                    $contribucion_puntos[$row_count]['nombre'] = $place->nombre;
	                                    $contribucion_porcentaje[$row_count]['nombre'] = $place->nombre;
	                                    $diferencia[$row_count]['nombre'] = $place->nombre;        
	                                }
	                            }
	                            foreach ($places as $place) {
		                            //if($value == $place->key){
	                            	if('a'.$value == 'a'.$place->key){
		                                $estadisticas[$row_count]['nombre'] = $place->nombre;
		                                $participacion[$row_count]['nombre'] = $place->nombre;
	                                    $variacion[$row_count]['nombre'] = $place->nombre;
	                                    $contribucion_puntos[$row_count]['nombre'] = $place->nombre;
	                                    $contribucion_porcentaje[$row_count]['nombre'] = $place->nombre;
	                                    $diferencia[$row_count]['nombre'] = $place->nombre;
		                            }
		                        }
	                            
	                        }else{
	                        	foreach ($places as $place) {
		                            if($value == $place->key){
		                                $estadisticas[$row_count]['nombre'] = $place->nombre;
		                                $participacion[$row_count]['nombre'] = $place->nombre;
	                                    $variacion[$row_count]['nombre'] = $place->nombre;
	                                    $contribucion_puntos[$row_count]['nombre'] = $place->nombre;
	                                    $contribucion_porcentaje[$row_count]['nombre'] = $place->nombre;
	                                    $diferencia[$row_count]['nombre'] = $place->nombre;
		                            }
		                        }	
	                        }
	                    }else{
	                    	if ($espacio == 'region') {
	                    		$row_region = DB::table('regiones')->select('id', 'Nombre as nombre', 'clave as key')->where('clave', '=', $region)->first();//dd($row_region['nombre']);
	              	      		$estadisticas[$row_count]['nombre'] = $row_region->nombre;	
	              	      		$participacion[$row_count]['nombre'] = $row_region->nombre;
                                $variacion[$row_count]['nombre'] = $row_region->nombre;
                                $contribucion_puntos[$row_count]['nombre'] = $row_region->nombre;
                                $contribucion_porcentaje[$row_count]['nombre'] = $row_region->nombre;
                                $diferencia[$row_count]['nombre'] = $row_region->nombre;
	                    	}else{
	                    		$estadisticas[$row_count]['nombre'] = 'Nacional';
	                    		$participacion[$row_count]['nombre'] = 'Nacional';
                                $variacion[$row_count]['nombre'] = 'Nacional';
                                $contribucion_puntos[$row_count]['nombre'] = 'Nacional';
                                $contribucion_porcentaje[$row_count]['nombre'] = 'Nacional';
                                $diferencia[$row_count]['nombre'] = 'Nacional';
	                    	}
	                        
	                        
	                    }
	                }
	            }
	            $row_count ++;
	        }
        /*********Cambiar claves entiades por nombres end*********/

		/*********Obtener cabeceras de apartir de la consulta principal********************/
			$headers = [];
			if ($espacio == 'region') {
	            array_push($headers, array('nickname'=>'Región', 'header'=>'nombre'));
	        }elseif($espacio == 'region_nacional'){
	            array_push($headers, array('nickname'=>'Entidad Federativa o Región', 'header'=>'nombre'));
	        }elseif($espacio == 'entidad'){
	            array_push($headers, array('nickname'=>'Entidad Federativa', 'header'=>'nombre'));
	        }
			foreach ($estadisticas[0] as $key => $value) {
				
				if($key == 'cve_ent' || $key == 'Actividad_economica'){
					array_unshift($headers, ['nickname' => 'Clave', 'header' => $key]);
				}else{
					foreach ($periodos as $periodo) {
						if ('p'.$key == 'p'.$periodo) {
							array_push($headers, ['nickname' => $key, 'header' => $key]);
							array_push($headers, ['nickname' => 'Posición', 'header'=>'posicion_'.$key]);
						}
						
					}
					foreach ($promedios as $promedio) {
						if ('p'.$key == 'p'.$promedio['name']) {
							//array_push($headers, ['header'=>$key]);
							//array_push($headers, ['header'=>'posicion_'.$key]);
							array_push($headers, ['nickname' => $key, 'header' => $key]);
							array_push($headers, ['nickname' => 'Posición', 'header'=>'posicion_'.$key]);
						}
					}
					
				}
				
			}//dd($headers);
		/*********Obtener cabeceras de apartir de la consulta principal********************/
		//dd($diferencia);
		return ['headers' => $headers, 'estadisticas'=>$estadisticas, 'participacion'=>$participacion, 'variacion'=>$variacion, 'contribucion_puntos'=>$contribucion_puntos, 'contribucion_porcentaje'=>$contribucion_porcentaje, 'diferencia'=>$diferencia];
	}

	function posiciones($consulta, $consulta_base, $periodo){
        $dat = [];//dd($consulta);
        $consulta_this = $consulta; 
        $count = 0;
        foreach ($consulta as $clave => $fila) {
        	//dd($periodo);
            $dat[$clave] = $fila[$periodo];
            $consulta[$count]['valor'] = $consulta_base[$count][$periodo];
            $count++;
        }
        array_multisort($dat, SORT_DESC, $consulta);
        $poss = []; $cont = 1; $contador = 0;

        foreach ($consulta as $con) {
            $poss_aux = [];
            if ($con['valor'] == '0' || $con['valor'] == 'Nacional') {
                $poss_aux["e_".$con['cve_ent']] = ['posicion'=>' '];
            }else{
                if ($con['cve_ent'] == "0" || $con['cve_ent'] == "Nacional"|| 'a'.$con['cve_ent'] == "a1" || 'a'.$con['cve_ent'] == "a2" || 'a'.$con['cve_ent'] == "a3" || 'a'.$con['cve_ent'] == "a4" || 'a'.$con['cve_ent'] == "a5") {
                    $poss_aux["e_".$con['cve_ent']] = ['posicion'=>' '];
                }else{
                    $poss_aux["e_".$con['cve_ent']] = ['posicion'=>$cont];
                    $cont++;
                }
            }
            
            $poss = array_merge($poss, $poss_aux); 
            $contador++;
        }
        $cnt = 0;
        foreach ($consulta_this as $consult) {
            $tamano = count($consult);
            
            $consulta_this[$cnt]['posicion_'.$periodo] = $poss["e_".$consult['cve_ent']]['posicion'];
            
            
            $cnt++;
        }
        return $consulta_this;
    }

    function posiciones_diferencia($consulta, $consulta_base, $periodo, $base_nacional){
        $dat = [];
        $consulta_this = $consulta; $count = 0;
        //dd($consulta);
        foreach ($consulta as $clave => $fila) {
            $dat[$clave] = $fila[$periodo];
            $consulta[$count]['valor'] = $consulta_base[$count][$periodo];
            $count++;
        }
        if ($base_nacional<0) {
            array_multisort($dat, SORT_ASC, $consulta);
        }else{
            array_multisort($dat, SORT_DESC, $consulta);
        }
        $poss = []; $cont = 1; $contador = 0;
        foreach ($consulta as $con) {
            $poss_aux = [];
            if ($con['valor'] == '0' || $con['valor'] == 'Nacional') {
                $poss_aux["e_".$con['cve_ent']] = ['posicion'=>' '];
            }else{
                if ($con['cve_ent'] == "0" || $con['cve_ent'] == "Nacional" || 'a'.$con['cve_ent'] == "a1" || 'a'.$con['cve_ent'] == "a2" || 'a'.$con['cve_ent'] == "a3" || 'a'.$con['cve_ent'] == "a4" || 'a'.$con['cve_ent'] == "a5") {
                    $poss_aux["e_".$con['cve_ent']] = ['posicion'=>' '];
                }else{
                    $poss_aux["e_".$con['cve_ent']] = ['posicion'=>$cont];
                    $cont++;
                }
            }
            
            $poss = array_merge($poss, $poss_aux); 
            $contador++;
        }
        $cnt = 0;
        foreach ($consulta_this as $consult) {
            $tamano = count($consult);
            
            $consulta_this[$cnt]['posicion_'.$periodo] = $poss["e_".$consult['cve_ent']]['posicion'];
            
            
            $cnt++;
        }
        return $consulta_this;
    }
}