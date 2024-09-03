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
use App\Libraries\PibNacionalFunctions;
use App\Libraries\PibNacionalEvaluacionFunctions;

class MapasEvaluacionFunctions
{

	function main_consulta($data){ ini_set('max_execution_time', '300');
		/******Declaracion de variables objeto Data****/
			$function = new PibFunctions();
			$function_evaluacion = new MapasEvaluacionFunctions();
			$function_resumen = new MapasFunctions();
			$valoracion_nacional = new PibNacionalFunctions();
			$evaluacion_nacional = new PibNacionalEvaluacionFunctions();

			$back_estadisticas = [];
			$estadisticas = [];
			$estadisticas_nacional = [];

			$base = 'pib_precios_constantes';
			$nivel = $data['selectedNivel'];
			$region = $data['selectedRegion'];
			$entidad = $data['selectedEntidad'];
	        $cobertura = $data['selectedCobertura'];
	        $entidades = [$entidad];
	        $entidades_nacional = ['0'];
	        $periodos = $data['periodos'];
	        $promedios = $data['promedios'];
	        $variables = $data['variables'];
	        //dd($promedios);
	        $participacion = [];
	        $variacion = [];
	        $contribucion_porcentaje = [];
	        $contribucion_puntos = [];
	        $diferencia = [];

	        $contribucion_tabla = [];
	        $contribucion_tabla_nacional = [];
        /******Declaracion de variables objeto Data End****/
        /*********Consulta Principal*****/
			if($nivel == '1' || $nivel == '3'){
	            $estadisticas = $function_resumen->consulta($base, $cobertura, $periodos, $entidades, $nivel, $region);
	            $contribucion_tabla = $function_resumen->consulta('pib_precios_constantes_particip', $cobertura, $periodos, $entidades, $nivel, $region);
	        }else{
	            $estadisticas = $function_resumen->consulta($base, $cobertura, $periodos, $entidades, $nivel, $region);
	            $contribucion_tabla = $function_resumen->consulta('pib_precios_constantes_particip_nacional', $cobertura, $periodos, $entidades, $nivel, $region);
        		$estadisticas_nacional = $function_resumen->consulta($base, $cobertura, $periodos, $entidades_nacional, '1', $region);
        		//$contribucion_tabla_nacional = $function_resumen->consulta('pib_precios_constantes_particip', $cobertura, $periodos, $entidades_nacional, '1', $region);
	        }
	        


        /*********Consulta Principal End *****/
        /*********Calcular promedios array promedios**********/
	        
	        //$back_estadisticas = $evaluacion_nacional->add_promedios($estadisticas, $promedios);
        	$back_estadisticas = $estadisticas;
			$back_estadisticas_nacional = [];
			if($nivel == '2' || $nivel == '4'){
				//$back_estadisticas_nacional = $evaluacion_nacional->add_promedios($estadisticas_nacional, $promedios);
				$back_estadisticas_nacional = $estadisticas_nacional;
			}
			
		/*********Calcular promedios array promedios end **********/
        /*********Calcular variables array variabled***************/
	        if($nivel == '1' || $nivel == '3'){

	        	$participacion = $function->participacion($back_estadisticas, 'Ayo', '2004', '2010');
	        	$participacion = $evaluacion_nacional->add_promedios($participacion, $promedios);
				
			}else{

				$participacion = $valoracion_nacional->participacion($back_estadisticas, $back_estadisticas_nacional, 'Ayo', '2004', '2010');
				$participacion = $evaluacion_nacional->add_promedios($participacion, $promedios);

				$participacion = $evaluacion_nacional->posicion_participacion($base, $participacion, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
			}


			$variacion = [];
			$variacion_nacional = [];
			$contribucion_puntos = [];
			$contribucion_porcentaje = [];
			$diferencia = [];

			if($nivel == '1' || $nivel == '3'){
				foreach ($data['variables'] as $variable) {
					if ($variable['id'] == '1') {
						$variacion = $function->variacion($estadisticas, 'Ayo', '2004', '2010');
						$variacion = $evaluacion_nacional->add_promedios($variacion, $promedios);
					}elseif ($variable['id'] == '2') {
						if($cobertura != 'Manufactura'){
							if ($nivel == '1') {
								$contribucion_puntos = $evaluacion_nacional->add_promedios($contribucion_tabla, $promedios);

	        					$contribucion_porcentaje = $evaluacion_nacional->variacion_porcentual_query($estadisticas, $contribucion_tabla, 'Ayo', '2004', '2010', $promedios);
	        					$contribucion_porcentaje = $evaluacion_nacional->add_promedios($contribucion_porcentaje, $promedios);
							}else{
								$contribucion_puntos = $evaluacion_nacional->variacion_puntos($estadisticas, 'Ayo', '2004', '2010', $promedios);
								$contribucion_puntos = $evaluacion_nacional->add_promedios($contribucion_puntos, $promedios);
        						$contribucion_porcentaje = $evaluacion_nacional->variacion_porcentual($estadisticas, 'Ayo', '2004', '2010', $promedios);
        						$contribucion_porcentaje = $evaluacion_nacional->add_promedios($contribucion_porcentaje, $promedios);
							}
							
						}else{
							$contribucion_puntos = $evaluacion_nacional->variacion_puntos($estadisticas, 'Ayo', '2004', '2010', $promedios);
							$contribucion_puntos = $evaluacion_nacional->add_promedios($contribucion_puntos, $promedios);
        					$contribucion_porcentaje = $evaluacion_nacional->variacion_porcentual($estadisticas, 'Ayo', '2004', '2010', $promedios);
        					$contribucion_porcentaje = $evaluacion_nacional->add_promedios($contribucion_porcentaje, $promedios);
						}
						
					}elseif ($variable['id'] == '3') {
						if($cobertura != 'Manufactura'){
							if ($nivel == '1') {
								$diferencia = $evaluacion_nacional->eficiencia_query($estadisticas, $contribucion_tabla, 'Ayo', '2004', '2010', $promedios);
								$diferencia = $evaluacion_nacional->add_promedios($diferencia, $promedios);
							}else{
								$diferencia = $evaluacion_nacional->eficiencia($estadisticas, 'Ayo', '2004', '2010', $promedios);
								$diferencia = $evaluacion_nacional->add_promedios($diferencia, $promedios);
							}
							
						}else{
							$diferencia = $evaluacion_nacional->eficiencia($estadisticas, 'Ayo', '2004', '2010', $promedios);
							$diferencia = $evaluacion_nacional->add_promedios($diferencia, $promedios);
						}

					}
				}
				//$estadisticas = $back_estadisticas;
				$estadisticas = $evaluacion_nacional->add_promedios($estadisticas, $promedios);
				
			}else{
				foreach ($data['variables'] as $variable) {
					if ($variable['id'] == '1') {
						$variacion = $evaluacion_nacional->posicion_variacion($base, $estadisticas, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
						$variacion_nacional = $function->variacion($estadisticas_nacional, 'Ayo', '2004', '2010');
						$variacion_nacional = $evaluacion_nacional->add_promedios($variacion_nacional, $promedios);
					}elseif ($variable['id'] == '2') {
						if($cobertura != 'Manufactura'){
							if ($nivel == '2') {
								$contribucion_puntos = $evaluacion_nacional->posicion_contribucion_puntos_query($base, $estadisticas, $estadisticas_nacional, $contribucion_tabla, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
								$contribucion_porcentaje = $evaluacion_nacional->posicion_contribucion_porcentaje_query($base, $estadisticas, $estadisticas_nacional, $contribucion_tabla, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
							}else{
								$contribucion_puntos = $evaluacion_nacional->posicion_contribucion_puntos($base, $estadisticas, $estadisticas_nacional, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
								$contribucion_porcentaje = $evaluacion_nacional->posicion_contribucion_porcentaje($base, $estadisticas, $estadisticas_nacional, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
							}
							
						}else{	
							$contribucion_puntos = $evaluacion_nacional->posicion_contribucion_puntos_query($base, $estadisticas, $estadisticas_nacional, $contribucion_tabla, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
							$contribucion_porcentaje = $evaluacion_nacional->posicion_contribucion_porcentaje_query($base, $estadisticas, $estadisticas_nacional, $contribucion_tabla, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);					
							//$contribucion_puntos = $evaluacion_nacional->posicion_contribucion_puntos($base, $estadisticas, $estadisticas_nacional, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
							//$contribucion_porcentaje = $evaluacion_nacional->posicion_contribucion_porcentaje($base, $estadisticas, $estadisticas_nacional, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
						}
						
					}elseif ($variable['id'] == '3') {
						if($cobertura != 'Manufactura'){
							if ($nivel ==  '2') {
								$diferencia = $evaluacion_nacional->posicion_diferencia_query($base, $estadisticas, $estadisticas_nacional, $contribucion_tabla, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
							}else{
								$diferencia = $evaluacion_nacional->posicion_diferencia($base, $estadisticas, $estadisticas_nacional, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
							}
							
						}else{
							$diferencia = $evaluacion_nacional->posicion_diferencia_query($base, $estadisticas, $estadisticas_nacional, $contribucion_tabla, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
							//$diferencia = $evaluacion_nacional->posicion_diferencia($base, $estadisticas, $estadisticas_nacional, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
						}
					}
				}
				$estadisticas = $evaluacion_nacional->posicion_valoracion($base, $back_estadisticas, $entidad, 'Ayo', '2004', '2010', $region, $nivel, $promedios);
			}
			//dd($variacion_nacional[0]);
			//$estadisticas_nacional = $back_estadisticas_nacional;
			$estadisticas_nacional = $evaluacion_nacional->add_promedios($estadisticas_nacional, $promedios);

		/*********Calcular variables array variabled end***************/
        /*********Obtener cabeceras de apartir de la consulta principal********************/
			$headers = [];
			//dd($estadisticas[0]);
			//if($nivel == '1' || $nivel == '3'){
				foreach ($estadisticas[0] as $key => $value) {	
					if($key == 'cve_ent' || $key == 'Actividad_economica'){

						array_unshift($headers, ['nickname' => 'Concepto', 'header' => $key]);
					}else{
						foreach ($periodos as $periodo) {
							if ('p'.$key == 'p'.$periodo) {
								array_push($headers, ['nickname' => $key, 'header'=>$key]);
								if($nivel == '2' || $nivel == '4'){
									array_push($headers, ['nickname' => 'Posición', 'header'=>'posicion_'.$key]);
								}
							}
							
						}
						foreach ($promedios as $promedio) {
							if ('p'.$key == 'p'.$promedio['name']) {
								array_push($headers, ['nickname' => $key, 'header'=>$key]);
								if($nivel == '2' || $nivel == '4'){
									array_push($headers, ['nickname' => 'Posición', 'header'=>'posicion_'.$key]);
								}
							}
						}
						
					}		
				}
			/*}else{
				foreach ($estadisticas[0] as $key => $value) {
					
					if($key == 'cve_ent' || $key == 'Actividad_economica'){
						array_unshift($headers, ['header' => $key]);
					}else{
						foreach ($periodos as $periodo) {
							if ($key == $periodo) {
								array_push($headers, ['header'=>$key]);
								array_push($headers, ['header'=>'posicion_'.$key]);
							}
							
						}
						foreach ($promedios as $promedio) {
							if ($key == $promedio['name']) {
								array_push($headers, ['header'=>$key]);
								array_push($headers, ['header'=>'posicion_'.$key]);
							}
						}
						
					}
					
				}
			}*/
		/*********Obtener cabeceras de apartir de la consulta principal********************/
		
		return ['headers' => $headers, 'estadisticas'=>$estadisticas, 'participacion'=>$participacion, 'variacion'=>$variacion, 'contribucion_puntos'=>$contribucion_puntos, 'contribucion_porcentaje'=>$contribucion_porcentaje, 'diferencia'=>$diferencia, 'estadisticas_nacional'=>$estadisticas_nacional, 'variacion_nacional'=>$variacion_nacional];
	}
}