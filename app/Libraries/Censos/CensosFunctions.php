<?php

namespace App\Libraries\Censos;
use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;
use App\Libraries\Censos\FormulasFunctions;
use App\Libraries\Censos\QueriesFunctions;
use Invoker;

class CensosFunctions
{   
    public function main_consulta($data){//dd($data);
        $get_indicador = new CensosFunctions();
        $query = new QueriesFunctions();
        $consulta = [];
        $response = [];
        $years = [];

        //************get request's data*********************************/
        
        $valoracion = $data['valoracion'];
        $periodo = $data['periodo'];
        $year = $data['ayo'];
        $year_1 = $data['ayo_1'];
        $year_2 = $data['ayo_2'];
        $espacio = $data['espacio'];
        $entidad = $data['entidad'];
        $municipio = $data['municipio'];
        $region = $data['region'];
        $macroregion = $data['macroregion'];
        $nivel = $data['actividad'];
        $sector = $data['sector'];
        $sectores = $data['sectores'];
        $subsector = $data['subsector'];
        $subsectores = $data['subsectores'];
        $ramas = $data['ramas'];
        $indicadores_request = $data['indicadores'];
        //************get request's data*********************************/

        /********Espacio 1 = Municipio(Entidad?), 2 = Región, 3 = Macroregion ***********/
        if ($espacio == '1') {
            $consulta = $query->municipio_query($year, $entidad, $municipio, $nivel, $sectores, $subsectores, $ramas, $periodo, $years);

        }elseif ($espacio == '2') {
            $consulta = $query->region_query($year, $entidad, $region, $nivel, $sectores, $subsectores, $ramas);
            
            $group = '';
            if ($nivel == "1") {        $group = 'sector';
            }elseif ($nivel == "2") {   $group = 'subsector';
            }elseif ($nivel == "3") {   $group = 'rama';
            }
            
            $consulta = $get_indicador->sum_region($consulta, $group);
            

        }elseif ($espacio == "3") {
            $consulta = $query->macroregion_query($year, $entidad, $macroregion, $nivel, $sectores, $subsectores, $ramas);

            $group = '';
            if ($nivel == "1") {        $group = 'sector';
            }elseif ($nivel == "2") {   $group = 'subsector';
            }elseif ($nivel == "3") {   $group = 'rama';
            }

            $consulta = $get_indicador->sum_region($consulta, $group);
        }

        /***********************Valoracion Precios constantes**************************/
        if ($valoracion == "2") {
            $consulta = $get_indicador->valoracion_precios_constantes($consulta/*, $entidad, $year*/);
        }


        
        
        return $consulta;
        //return ['consulta' => $response, 'cabeceras'=>$names_table];
    }
    public function main_consulta_actividad($data){//dd($data);
        $get_indicador = new CensosFunctions();
        $query = new QueriesFunctions();
        $consulta = [];
        $response = [];
        $years = [];

        //************get request's data*********************************/
        
        $valoracion = $data['valoracion'];
        $years = $data['ayos'];
        $espacio = $data['espacio'];
        $entidad = $data['entidad'];
        $entidades = $data['entidades'];

        //$municipio = $data['municipio'];
        $municipios = $data['municipios'];
        $regiones = $data['regiones'];
        $macroregiones = $data['macroregiones'];

        $nivel = $data['actividad'];
        $sector = $data['sector'];
        $subsector = $data['subsector'];
        $rama = $data['rama'];
        $indicador = $data['indicador'];
        //************get request's data*********************************/

        /********Espacio 1 = Municipio(Entidad?), 2 = Región, 3 = Macroregion ***********/
        if ($espacio == '0') {
            $consulta = $query->every_entidades_query($years, $entidades, $nivel, $sector, $subsector, $rama);
            //dd($consulta);
        }elseif ($espacio == '1') {
            $consulta = $query->every_municipios_query($years, $entidad, $municipios, $nivel, $sector, $subsector, $rama);
            //dd($consulta);
        }elseif ($espacio == '2') {
            $consulta = $query->every_region_query($years, $entidad, $regiones, $nivel, $sector, $subsector, $rama);
            //dd($consulta);
            $group = 'cve_region';            
            $consulta = $get_indicador->sum_region($consulta, $group);
            

        }elseif ($espacio == "3") {
            $consulta = $query->every_macroregion_query($years, $entidad, $macroregiones, $nivel, $sector, $subsector, $rama);
            //dd($consulta);
            $group = 'cve_macroregion';
            $consulta = $get_indicador->sum_region($consulta, $group);
            //dd($consulta);
        }
        //dd($consulta);
        /***********************Valoracion Precios constantes**************************/
        if ($valoracion == "2") {
            $consulta = $get_indicador->valoracion_precios_constantes($consulta);
        }

        //dd($consulta);
        
        
        return $consulta;
        //return ['consulta' => $response, 'cabeceras'=>$names_table];
    }
    public function main_consulta_vocacion($data){//dd($data);
        $get_indicador = new CensosFunctions();
        $query = new QueriesFunctions();
        $consulta = [];
        $response = [];
        $years = [];

        //************get request's data*********************************/
        
        $valoracion = $data['valoracion'];
        
        $periodo = $data['periodo'];
        $year = $data['ayo'];
        $year_1 = $data['ayo_1'];
        $year_2 = $data['ayo_2'];

        $espacio = $data['espacio'];
        $entidad = $data['entidad'];
        $region = $data['region'];
        $regiones = $data['regiones'];
        $macroregion = $data['macroregion'];
        $macroregiones = $data['macroregiones'];
        
        $nivel = $data['actividad'];
        $sector = $data['sector'];
        $sectores = $data['sectores'];
        $subsector = $data['subsector'];
        $subsectores = $data['subsectores'];
        $ramas = $data['ramas'];

        //$indicadores_request = $data['indicadores'];
        //************get request's data*********************************/

        /********Espacio 1 = Municipio(Entidad?), 2 = Región, 3 = Macroregion ***********/
        if ($espacio == '0') {//Región
            $consulta = $query->region_query($year, $entidad, $region, $nivel, $sectores, $subsectores, $ramas);

        }elseif ($espacio == '1') {//Regiones
            $consulta = $query->regiones_vocacion($year, $entidad, $regiones, $nivel, $sectores, $subsectores, $ramas);
            $group = 'cve_region';    //dd($consulta);        
            $consulta = $get_indicador->sum_actividad($consulta, $group);

        }elseif ($espacio == '2') {//Macroregión
            $consulta = $query->macroregion_query($year, $entidad, $macroregion, $nivel, $sectores, $subsectores, $ramas);
            $group = 'cve_region';//dd($consulta);
            $consulta = $get_indicador->sum_actividad($consulta, $group);
            

        }elseif ($espacio == "3") {//Macroregiones
            $consulta = $query->macroregiones_vocacion($year, $entidad, $macroregiones, $nivel, $sectores, $subsectores, $ramas);

            $group = 'cve_macroregion';
            $consulta = $get_indicador->sum_actividad($consulta, $group);
        }

        /***********************Valoracion Precios constantes**************************/
        if ($valoracion == "2") {
            $consulta = $get_indicador->valoracion_precios_constantes($consulta/*, $entidad, $year*/);
        }


        
        
        return $consulta;
        //return ['consulta' => $response, 'cabeceras'=>$names_table];
    }


    public function main_consulta_indicador($data){
        $get_indicador = new CensosFunctions();
        $query = new QueriesFunctions();

        $consulta = [];

        
        $valoracion = $data['valoracion'];
        $periodo = $data['periodo'];
        $year = $data['ayo'];
        $espacio = $data['espacio'];
        $entidad = $data['entidad'];
        $municipio = $data['municipio'];
        $region = $data['region'];
        $macroregion = $data['macroregion'];
        $nivel = $data['actividad'];
        $sector = $data['sector'];
        $subsector = $data['subsector'];
        $rama = $data['rama'];
        $indicador_request = $data['indicador'];


        $entidades = array();
        $entidades_consulta = DB::table('mge')->get();
        foreach ($entidades_consulta as $ent) {
            $entidades = array_merge($entidades, [$ent->cve_ent]);
        }

        /********Espacio 2 = Regiones, 3 = Macroregiones***********/
        $consulta = $query->municipios_query($year, $entidad, $entidades, $municipio, $nivel, $sector, $subsector, $rama);
        //dd($consulta);
        if ($espacio == '2') {
            /**************Add cve_region***********************/
            foreach ($consulta as $raw) {
                $clave_region = DB::table('areas_geoestadisticas_municipales')
                    ->where('cve_ent', '=', $entidad)
                    ->where('cve_mun', '=', $raw->cve_mun)
                    ->first();
                $raw->cve_region = $clave_region->cve_region;

            }
            /**************Add cve_region***********************/


            
            $consulta = $get_indicador->sum($consulta, 'cve_region');
        }elseif ($espacio == "3") {
            /******************Add cve_macroregion**************/
            foreach ($consulta as $raw) {
                $clave_region = DB::table('areas_geoestadisticas_municipales')
                    ->join('regiones_mun', 'regiones_mun.cve_region', '=', 'areas_geoestadisticas_municipales.cve_region')
                    ->where('areas_geoestadisticas_municipales.cve_ent', '=', $entidad)
                    ->where('areas_geoestadisticas_municipales.cve_mun', '=', $raw->cve_mun)
                    ->first();
                $raw->cve_macroregion = $clave_region->cve_macroregion;

            }
            /******************Add cve_macroregion**************/

            $consulta = $get_indicador->sum($consulta, 'cve_macroregion');
        }


        /****************Usar deflactor para convertir a precios constantes******************/
        if ($valoracion == "2") {
            $consulta = $get_indicador->valoracion_precios_constantes($consulta/*, $entidad, $year*/);
        }
        /****************Usar deflactor para convertir a precios constantes******************/
        return $consulta;
    }

    public function getVariable($variable, $consulta){

        $query_name=DB::table('diccionario')
        ->where('clave', '=', $variable)
        ->first();
        $name = "";
        if ($query_name) {
            $name = $query_name->nombre;
        }else{
            $name = $variable;
        }

        $response = ['indicador' => $name, "clave" => $variable];
        $actividad = 'actividad';

        foreach ($consulta as $raw) {
            if (DB::table('indicador')->where('clave', '=', $variable)->exists()) {
                   $response = [];
            }else{
                $response = array_merge($response, ['a'.$raw->$actividad => $raw->$variable]);
            }
        }
        return $response;

    }
	public function getIndicador($indicador, $consulta, $actividad){
		$recursivo = new CensosFunctions();
        $formula = new FormulasFunctions();
        $invoker = new Invoker\Invoker;

        $in = json_decode($indicador->variables);
        $response = ['indicador' => $indicador->nombre, "clave"=>$indicador->clave];
        //$actividad = 'actividad';
        //dd($consulta);
        foreach ($consulta as $row) {
        	$params = [];
        	foreach ($in->variables as $key => $value) {
        		$variable = $value;
        		try {
        			$params = array_merge($params, [$key => $row->$variable]);	
        		} catch (Exception $e) {
        			if (DB::table('indicador')->where('clave', '=', $variable)->exists()) {
        				$indicador = DB::table('indicador')
				        ->where('clave', '=', $variable)
				        ->first();
				        $result_recursivo = $recursivo->getIndicador($indicador, $consulta, $actividad);
				        $variable_recursivo = 'a'.$row->$actividad;
				        $params = array_merge($params, [$key => $result_recursivo[$variable_recursivo]]);
        			}else{
        				//dd('La variable no existe');
        			}
        		}	
        	}
        	
        	$result = $invoker->call(array($formula, $in->formula), $params);
        	$response = array_merge($response, ['a'.$row->$actividad => $result]);

        }
        return $response;

    }
    public function getActividad_Indicador($indicador, $consulta, $space_type, $space, $actividad){
        $recursivo = new CensosFunctions();
        $formula = new FormulasFunctions();
        $invoker = new Invoker\Invoker;
        //dd($consulta);
        $in = json_decode($indicador->variables);
        $response = ['space' => $space, "clave" => $space];
        //$actividad = 'actividad';

        foreach ($consulta as $row) {
            $params = [];
            if ($row->$space_type == $space) {
                foreach ($in->variables as $key => $value) {
                    $variable = $value;
                    try {
                        $params = array_merge($params, [$key => $row->$variable]);  
                    } catch (Exception $e) {
                        if (DB::table('indicador')->where('clave', '=', $variable)->exists()) {
                            $indicador = DB::table('indicador')
                            ->where('clave', '=', $variable)
                            ->first();
                            $result_recursivo = $recursivo->getActividad_Indicador($indicador, $consulta, $space_type, $space, $actividad);
                            $variable_recursivo = 'a'.$row->$actividad;
                            $params = array_merge($params, [$key => $result_recursivo[$variable_recursivo]]);
                        }else{
                            //dd('La variable no existe');
                        }
                    }   
                }            
                $result = $invoker->call(array($formula, $in->formula), $params);
                $response = array_merge($response, ['a'.$row->$actividad => $result]);

            }

        }
        return $response;

    }
    /*public function getUniqueIndicador($indicador, $consulta){
        $recursivo = new CensosFunctions();
        $formula = new FormulasFunctions();
        $invoker = new Invoker\Invoker;

        $in = json_decode($indicador->variables);
        $response = ['indicador' => $indicador->nombre];
        $actividad = 'cve_mun';

        foreach ($consulta as $raw) {
            $params = [];
            foreach ($in->variables as $key => $value) {
                $variable = $value;
                try {
                    $params = array_merge($params, [$key => $raw->$variable]);
                } catch (Exception $e) {
                    if (DB::table('indicador')->where('clave', '=', $variable)->exists()) {
                        $indicador = DB::table('indicador')
                        ->where('clave', '=', $variable)
                        ->first();
                        $result_recursivo = $recursivo->getUniqueIndicador($indicador, $consulta);
                        $variable_recursivo = 'a'.$raw->$actividad;
                        $params = array_merge($params, [$key => $result_recursivo[$variable_recursivo]]);
                    }else{
                        //dd('La variable no existe');
                    }
                    

                }                       
            }
            
            $result = $invoker->call(array($formula, $in->formula), $params);
            $response = array_merge($response, ['a'.$raw->$actividad => $result]);

        }
        return $response;

    }*/
    public function getChartIndicador($indicador, $consulta){
        $recursivo = new CensosFunctions();
        $formula = new FormulasFunctions();
        $invoker = new Invoker\Invoker;

        $in = json_decode($indicador->variables);
        $response = [];
        $actividad = 'cve_mun';
        //dd($consulta);
        foreach ($consulta as $raw) {
            $params = [];
            foreach ($in->variables as $key => $value) {
                $variable = $value;
                try {
                    $params = array_merge($params, [$key => $raw->$variable]);  
                } catch (Exception $e) {
                    if (DB::table('indicador')->where('clave', '=', $variable)->exists()) {
                        $indicador = DB::table('indicador')
                        ->where('clave', '=', $variable)
                        ->first();
                        $result_recursivo = $recursivo->getIndicador($indicador, $consulta);
                        $variable_recursivo = 'a'.$raw->actividad;
                        $params = array_merge($params, [$key => $result_recursivo[$variable_recursivo]]);
                        
                    }else{
                        //dd('La variable no existe');
                    }
                    

                }
                
                
                
            }
            
            $result = $invoker->call(array($formula, $in->formula), $params);
            
            array_push($response, ["key"=>$raw->$actividad, "value"=>$result]);

        }
        return $response;

    }
    public function getVariacion($data, $consulta){
        $get_indicador = new CensosFunctions();
        $formula = new FormulasFunctions();
        $invoker = new Invoker\Invoker;
        $indicadores_request = $data['indicadores'];
        $result_variacion = [];

        if ($data['ayo'] != "2004") {
            $data['ayo'] = $data['ayo']*1-5;
            $consulta_before = $get_indicador->main_consulta($data);
            $consulta_before = $get_indicador->get_indicadores($consulta_before, $indicadores_request, 'actividad');
            $count = 0;
            
            $raw_variacion = [];
            $param_b = "";
            foreach ($consulta as $raw) {

                $indicador = DB::table('indicador')
                ->where('clave', '=', $raw['clave'])
                ->first();
                $raw_variacion = [];

                foreach ($raw as $key => $value) {
                    try {
                        $param_b = $consulta_before[$count][$key];     
                    } catch (\Exception $e) {
                        $param_b = 0;
                        $value = 0;
                    }

                    if ($key != "indicador" && $key != "clave") {
                        $params = ["a"=>$value, "b"=>$param_b];
                        if ($indicador) {
                            $result = $invoker->call(array($formula, $indicador->variacion), $params);
                        }else{
                            $result = $invoker->call(array($formula, 'formula_seven'), $params);    
                        }
                        $raw_variacion = array_merge($raw_variacion, [$key => $result]);    
                        
                    }else{
                        $raw_variacion = array_merge($raw_variacion, [$key => $value]);
                    }  


                }
                array_push($result_variacion, $raw_variacion);
                $count++;
            }
        }

        //return ['consulta'=>$result_variacion, 'cabeceras'=>$consulta['cabeceras']];
        return $result_variacion;
    }

    public function getVariacion_actividad($data, $consulta){
        $get_indicador = new CensosFunctions();
        $formula = new FormulasFunctions();
        $invoker = new Invoker\Invoker;
        $indicador = $data['indicador'];
        $ayos = $data['ayos'];
        $result_variacion = [];

        $indicador_q = DB::table('indicador')
                ->where('clave', '=', $indicador)
                ->first();

        /***** Consultar con todos los años start************/
            $type = $data['espacio'];
            $indicador = $data['indicador'];
            
            $ayos_q = [];
            $years = DB::table('censos_economicos_completo')
                ->select('ayo')
                ->orderBy('ayo')
                ->distinct()
                ->get();
            foreach ($years as $year) {
                $ayos_q = array_merge($ayos_q, [$year->ayo]);
            }
            $data['ayos'] = $ayos_q;

            $spaces = [];
            if ($type == "0") {$spaces = $data['entidades'];    
            }elseif($type == "1"){$spaces = $data['municipios'];
            }elseif($type == "2"){$spaces = $data['regiones'];
            }elseif($type == "3"){$spaces = $data['macroregiones'];}
           
            $consulta_full = $get_indicador->main_consulta_actividad($data);
            $consulta_full = $get_indicador->get_indicadores_actividad($consulta_full, $indicador, $type, $spaces, 'ayo');
        /***** Consultar con todos los años end************/
        

        foreach ($consulta_full as $row) {
            $raw_variacion = [];
            foreach ($row as $key => $value) {
                if ($key != 'space' && $key != 'clave') {
                    if ($key != 'a2004') {
                        $prew_year = substr($key,1)*1-5;

                        $params = ["a"=>$value, "b"=>$row['a'.$prew_year]];
                        if ($indicador_q) {//dd($indicador);
                            $result = $invoker->call(array($formula, $indicador_q->variacion), $params);
                        }else{
                            $result = $invoker->call(array($formula, 'formula_seven'), $params);    
                        }
                        $raw_variacion = array_merge($raw_variacion, [$key => $result]); 
                    }else{
                        $raw_variacion = array_merge($raw_variacion, [$key => 0]);
                    }
                }else{
                    $raw_variacion = array_merge($raw_variacion, [$key => $value]);
                }
            }
            array_push($result_variacion, $raw_variacion);
        }

        //dd($result_variacion);

        //return ['consulta'=>$result_variacion, 'cabeceras'=>$consulta['cabeceras']];
        return $result_variacion;
    }


    public function getInfluencia($data, $consulta){
        $get_indicador = new CensosFunctions();
        $formula = new FormulasFunctions();
        $invoker = new Invoker\Invoker;

        $indicadores_request = $data['indicadores'];
        $b = "";
        $type_influencia = $data['influencia'];
        if ($type_influencia == "1") {//Respecto al nacional o estatal(Estado, Municipio, Region, Macroregion)
            if ($data['espacio'] == "1") {
                if ($data['entidad'] == $data['municipio']) {
                    if ($data['entidad'] != "00") {
                        $data['entidad'] = "00";
                        $data['municipio'] = "00";
                    }
                }elseif ($data['entidad'] != $data['municipio']) {
                    $data['municipio'] = $data['entidad'];
                }
            }elseif ($data['espacio'] == "2") {
                $data['espacio'] = '1';
                $data['municipio'] = $data['entidad'];
            }elseif ($data['espacio'] == "3") {
                $data['espacio'] = '1';
                $data['municipio'] = $data['entidad'];
            }
            
        }elseif ($type_influencia == "2") {//Respecto al total del sector o subsector
            if ($data['actividad'] == "1") {//sector
                #No es posible debe estar validado en el backend
            }elseif ($data['actividad'] == "2") {//Subsector
                $data['subsectores'] =  [$data['sector']];
                $b = "a".$data['sector'];
            }elseif ($data['actividad'] == "3") {//Rama
                $data['ramas'] = [$data['subsector']];
                $b = "a".$data['subsector'];
            }
        }
        
        $consulta_complement = $get_indicador->main_consulta($data);
        $consulta_complement = $get_indicador->get_indicadores($consulta_complement, $indicadores_request, 'actividad');
        /****************Evaluar Influencia**************************/
        $count = 0;
        $result_influencia = [];
        $raw_influencia = [];
        $param_b = "";
        foreach ($consulta as $raw_a) {
            $indicador = DB::table('indicador')
            ->where('clave', '=', $raw_a['clave'])
            ->first();
            $raw_influencia = [];
            foreach ($raw_a as $key => $value) {
                if ($type_influencia =="1") {
                    try {
                        $param_b = $consulta_complement[$count][$key];     
                    } catch (\Exception $e) {
                        $param_b = 0;
                        $value = 0;
                    }
                    
                }else{
                    $param_b = $consulta_complement[$count][$b];
                }

                if ($key != "indicador" && $key != "clave") {
                    $params = ["a"=>$value, "b"=>$param_b];
                    if ($indicador) {
                        $result = $invoker->call(array($formula, $indicador->influencia), $params);
                    }else{
                        $result = $invoker->call(array($formula, 'formula_three'), $params);
                    }
                    
                    $raw_influencia = array_merge($raw_influencia, [$key => $result]);    
                }else{
                    $raw_influencia = array_merge($raw_influencia, [$key => $value]);
                }
                
            }
            array_push($result_influencia, $raw_influencia);
            $count++;
        }
        /****************Evaluar Influencia end**************************/

        return ['influencia'=>$result_influencia, 'base'=>$consulta_complement]; 
    }

    public function getInfluencia_actividad($data, $consulta){
        $get_indicador = new CensosFunctions();
        $formula = new FormulasFunctions();
        $invoker = new Invoker\Invoker;

        $spaces = [];

        $indicador = $data['indicador'];
        $type_influencia = $data['influencia'];
        $type = $data['espacio'];
        $b = "";
        
        if ($type_influencia == "1") {//Respecto al nacional o estatal(Estado, Municipio, Region, Macroregion)
            if ($data['espacio'] == "0") {
                $data['entidades'] = ['00'];
                $spaces = $data['entidades'];
                //$data['espacio'] = "0";
            }elseif ($data['espacio'] == "1") {
                $data['entidades'] = [$data['entidad']];
                $spaces = $data['entidades'];
                $data['espacio'] = "0";
            }elseif ($data['espacio'] == "2") {
                $data['espacio'] = "0";
                $data['entidades'] = [$data['entidad']];
                $spaces = $data['entidades'];
            }elseif ($data['espacio'] == "3") {
                $data['espacio'] = "0";
                $data['entidades'] = [$data['entidad']];
                $spaces = $data['entidades'];
            }
            
        }elseif ($type_influencia == "2") {//Respecto al total del sector o subsector
            if ($data['actividad'] == "1") {//sector
                #No es posible debe estar validado en el backend
            }elseif ($data['actividad'] == "2") {//Subsector
                $data['subsector'] =  $data['sector'];
                $b = "a".$data['sector'];
            }elseif ($data['actividad'] == "3") {//Rama
                $data['rama'] = $data['subsector'];
                $b = "a".$data['subsector'];
            }
            if ($type == "0") {$spaces = $data['entidades'];    
            }elseif($type == "1"){$spaces = $data['municipios'];
            }elseif($type == "2"){$spaces = $data['regiones'];
            }elseif($type == "3"){$spaces = $data['macroregiones'];}
        }//dd($data);
        
       
        $consulta_complement = $get_indicador->main_consulta_actividad($data);//dd($spaces);
        $consulta_complement = $get_indicador->get_indicadores_actividad($consulta_complement, $indicador, $type, $spaces, 'ayo');
        /****************Evaluar Influencia**************************/
        //dd($consulta_complement);
        $count = 0;
        $result_influencia = [];
        $raw_influencia = [];
        $param_b = "";
        $indicador = DB::table('indicador')
        ->where('clave', '=', $indicador)
        ->first();
        foreach ($consulta as $row_a) {
            
            $raw_influencia = [];
            foreach ($row_a as $key => $value) {
                //if ($type_influencia =="1") {
                    try {
                        $param_b = $consulta_complement[$count][$key];     
                    } catch (\Exception $e) {
                        $param_b = 0;
                        $value = 0;
                    }
                    
                /*}else{
                    $param_b = $consulta_complement[$count][$b];
                }*/

                if ($key != "space" && $key != "clave") {
                    $params = ["a"=>$value, "b"=>$param_b];
                    if ($indicador) {
                        $result = $invoker->call(array($formula, $indicador->influencia), $params);
                    }else{
                        $result = $invoker->call(array($formula, 'formula_three'), $params);
                    }
                    
                    $raw_influencia = array_merge($raw_influencia, [$key => $result]);    
                }else{
                    $raw_influencia = array_merge($raw_influencia, [$key => $value]);
                }
                
            }
            array_push($result_influencia, $raw_influencia);
            if ($type_influencia == "2") {
                $count++;    
            }
            
        }
        /****************Evaluar Influencia end**************************/

        return ['influencia'=>$result_influencia, 'base'=>$consulta_complement]; 
    }
    public function getPromedio($data, $type){
        $get_indicador = new CensosFunctions();

        $indicadores_request = $data['indicadores'];
        $year_1 = $data['ayo_1'];
        $year_2 = $data['ayo_2'];
        $years = [];
        $cabeceras = [];
        $consulta_promedio = [];
        $dividendo = 0;

        $years_query = DB::table('censos_economicos_completo')
        ->select('ayo')
        ->orderBy('ayo')
        ->distinct()
        ->get();

        
        foreach ($years_query as $one) {
            if (($one->ayo >= $year_1)&&($one->ayo <= $year_2)) {
                $years = array_merge($years,[$one->ayo]);

                $data['ayo'] = $one->ayo;
                $consulta_por_ayo = [];

                if ($type == "1") {
                    $consulta_por_ayo = $get_indicador->main_consulta($data);
                    $consulta_por_ayo = $get_indicador->get_indicadores($consulta_por_ayo, $indicadores_request, 'actividad');
                }elseif ($type == "2") {
                    if ($one->ayo != '2004') {
                        $query_por_ayo = $get_indicador->main_consulta($data);
                        $query_por_ayo = $get_indicador->get_indicadores($query_por_ayo, $indicadores_request, 'actividad');
                        $consulta_por_ayo = $get_indicador->getVariacion($data, $query_por_ayo);
                    }else{
                        $consulta_por_ayo = [];
                    }
                    
                }elseif ($type == "3") {
                    $query_por_ayo = $get_indicador->main_consulta($data);
                    $query_por_ayo = $get_indicador->get_indicadores($query_por_ayo, $indicadores_request, 'actividad');
                    $consulta_por_ayo_prew = $get_indicador->getInfluencia($data, $query_por_ayo);
                    $consulta_por_ayo = $consulta_por_ayo_prew['base'];
                }elseif ($type == "4") {
                    $query_por_ayo = $get_indicador->main_consulta($data);
                    $query_por_ayo = $get_indicador->get_indicadores($query_por_ayo, $indicadores_request, 'actividad');
                    //dd($query_por_ayo);
                    $consulta_por_ayo = $get_indicador->getInfluencia($data, $query_por_ayo);
                    $consulta_por_ayo = $consulta_por_ayo['influencia'];
                    //$consulta_por_ayo = ['consulta'=>$consulta_por_ayo_prew['influencia'], 'cabeceras'=>$consulta_por_ayo_prew['cabeceras']];
                }
                
                
                if (empty($consulta_promedio)) {
                    if (empty($consulta_por_ayo)) {
                        # code...
                    }else{
                        $consulta_promedio = $consulta_por_ayo;
                        //$cabeceras = $consulta_por_ayo['cabeceras'];    
                    }
                    
                }else{//dd($consulta_promedio);
                    $counter = 0;
                    foreach ($consulta_promedio as $raw) {
                        foreach ($raw as $key => $value) {
                            if ($key != 'indicador' && $key != 'clave') {

                                $flag = false;
                                foreach ($consulta_por_ayo[$counter] as $clave => $valor) {
                                    if ($key == $clave) {
                                        $raw[$key] = $value+$consulta_por_ayo[$counter][$key];
                                        $flag = true;
                                        break;
                                    }        
                                }
                                if (!$flag) {
                                            
                                }
                            }
                        }
                        $consulta_promedio[$counter] = $raw;
                        $counter++;
                    }
                }
                $dividendo++;
            }
        }

        $counter = 0;
        foreach ($consulta_promedio as $raw) {
            foreach ($raw as $key => $value) {
                if ($key != 'indicador' && $key != 'clave') {
                    $raw[$key] = $value/$dividendo;
                }
            }
            $consulta_promedio[$counter] = $raw;
            $counter++;
        }
        //return ['consulta'=>$consulta_promedio, 'cabeceras'=>$cabeceras];
        return $consulta_promedio;
    }

    public function sum_region($consulta, $group){
        $count = 0;//dd($consulta);
        $consulta_region = [];
        foreach ($consulta as $row) {
            if ($count == 0) {
                array_push($consulta_region, $row);
                //break;
            }else{//dd($consulta_region);
                $flag = false;
                foreach ($consulta_region as $fila) {
                    if ($fila->$group == $row->$group && $fila->ayo == $row->ayo) {//dd($group);
                        /***********Operacion suma*********/
                        foreach ($row as $key => $value) {
                            if ($key != "id" && $key != "ayo" && $key != "cve_ent" && $key != "cve_mun" && $key != "sector" && $key != "subsector" && $key != "rama" && $key != "subrama" && $key != "actividad" && $key != $group) {
                                $fila->$key = $fila->$key+$row->$key;
                            }
                        }
                        $flag = true;
                        break;
                        //dd($fila);
                    }else{
                        $flag = false;
                    }
                }
                if ($flag == false) {
                    array_push($consulta_region, $row);
                }
            }
            
            $count ++;
        }
        return $consulta_region;
    }
    public function sum_actividad($consulta, $group){
        $count = 0;//dd($consulta);
        $consulta_region = [];
        foreach ($consulta as $row) {
            if ($count == 0) {
                array_push($consulta_region, $row);
                //break;
            }else{//dd($consulta_region);
                $flag = false;
                foreach ($consulta_region as $fila) {
                    if ($fila->$group == $row->$group && $fila->actividad == $row->actividad) {//dd($group);
                        /***********Operacion suma*********/
                        foreach ($row as $key => $value) {
                            if ($key != "id" && $key != "ayo" && $key != "cve_ent" && $key != "cve_mun" && $key != "sector" && $key != "subsector" && $key != "rama" && $key != "subrama" && $key != "actividad" && $key != $group) {
                                $fila->$key = $fila->$key+$row->$key;
                            }
                        }
                        $flag = true;
                        break;
                        //dd($fila);
                    }else{
                        $flag = false;
                    }
                }
                if ($flag == false) {
                    array_push($consulta_region, $row);
                }
            }
            
            $count ++;
        }
        return $consulta_region;
    }
    public function sum($consulta, $group){
        $count = 0;
        $consulta_region = [];
        foreach ($consulta as $raw) {
            if ($count == 0) {
                $raw->cve_mun = $raw->$group;
                array_push($consulta_region, $raw);
            }else{
                $flag = false;
                foreach ($consulta_region as $fila) {
                    if ($fila->$group == $raw->$group && $fila->ayo == $row->ayo) {
                        /***********Operacion suma*********/
                        foreach ($raw as $key => $value) {
                            if ($key != "id" && $key != "ayo" && $key != "cve_ent" && $key != "cve_mun" && $key != "sector" && $key != "subsector" && $key != "rama" && $key != "subrama" && $key != "actividad" && $key != $group) {
                                $fila->$key = $fila->$key+$raw->$key;
                            }elseif ($key == $group) {
                                $fila->cve_mun = $value;
                            }
                        }
                        $flag = true;
                        break;
                    }else{
                        $flag = false;
                    }
                }
                if ($flag == false) {
                    $raw->cve_mun = $raw->$group;
                    array_push($consulta_region, $raw);
                }
            }
            //dd($raw);
            $count ++;
        }
        return $consulta_region;
    }

    public function valoracion_precios_constantes($consulta/*, $entidad, $year*/){//dd($entidad);
        $deflactor_general = DB::table('general_indice')->first();//dd($deflactor);
        $deflactor_pib = [];
        foreach ($consulta as $row) {//dd($row);
            $deflactor_pib = DB::table('pib_indices')
                //->where('cve_ent', '=', $entidad)
                ->where('cve_ent', '=', $row->cve_ent)
                ->where('sector', '=', $row->sector)
                ->where('subsector', '=', $row->sector)
                ->first();
            foreach ($row as $key => $value) {
                if ($key != "id" && $key != "ayo" && $key != "cve_ent" && $key != "cve_mun" && $key != "sector" && $key != "subsector" && $key != "rama" && $key != "subrama" && $key != "actividad" && $key != "cve_region" && $key != "cve_macroregion") {

                    if ($key == "a131a") {
                        //$row->$key = $value/$deflactor_general->$year;
                        $ayo = $row->ayo;
                        $row->$key = $value/$deflactor_general->$ayo;
                    }elseif ($key == "ue" || $key == "a221a" || $key == "q000a" || $key == "j000a" || $key == "k610a" || $key == "k620a" || $key == "k060a" || $key == "a800a") {
                        if ($deflactor_pib) {//dd($deflactor_pib);
                            //$row->$key = $value/$deflactor_pib->$year;
                            $ayo = $row->ayo;
                            $row->$key = $value/$deflactor_pib->$ayo;
                        }else{
                            //$row->$key = $value/$deflactor_general->$year;
                            $ayo = $row->ayo;
                            $row->$key = $value/$deflactor_general->$ayo;
                            
                        }
                    }
                }
            }
        }

        return $consulta;
    }

    /*********get indicadores within dependent variables**************/
    public function get_indicadores($consulta, $indicadores_request, $actividad){
        $get_indicador = new CensosFunctions();
        $response = [];
        $variables = [];
        $indicadores = DB::table('indicador')
        ->whereIn('clave', $indicadores_request)
        ->get();

        foreach ($indicadores as $indicador) {
            array_push($response, $get_indicador->getIndicador($indicador, $consulta, $actividad));

            /****************ADD variables start**********************/
            $in = json_decode($indicador->variables);
            foreach ($in->variables as $key => $value) {   
                if (count($variables) == 0) {
                    $variables = array_merge($variables, [$value]);
                }else{
                    $flag = false;
                    foreach ($variables as $var) {
                        if ($var == $value) {
                            $flag = true;
                        }
                    }
                    if ($flag == false) {
                        $variables = array_merge($variables, [$value]);
                        $concepto = $get_indicador->getVariable($value, $consulta);
                        if (count($concepto) != 0) {
                            array_push($response, $concepto);
                        }
                    }
                }
                
            }

            /****************ADD variables end**********************/
        }
        return $response;
    }


    public function get_indicadores_actividad($consulta, $indicador, $type, $spaces, $actividad){
        $get_indicador = new CensosFunctions();
        $response = [];
        $variables = [];
        $indicador = DB::table('indicador')
        ->where('clave', '=', $indicador)
        ->first();//dd($indicador);
        $space_type = "";
        if ($type == "0") {$space_type = "cve_ent";    
        }elseif($type == "1"){$space_type = "cve_mun";
        }elseif($type == "2"){$space_type = "cve_region";
        }elseif($type == "3"){$space_type = "cve_macroregion";
        }
        foreach ($spaces as $space) {
            array_push($response, $get_indicador->getActividad_Indicador($indicador, $consulta, $space_type, $space, $actividad));
           // dd($get_indicador->getActividad_Indicador($indicador, $consulta, $space_type, $space, $actividad));
            
        }//dd($response);
        return $response;
    }

    public function get_names_columns($response){
        /**********Obtener nombres de columnas******/
        $names_table = [];
        //dd($response);
        foreach ($response[0] as $key => $value) {
            if ($key != 'indicador' && $key != 'clave' && $key != 'space') {
                if (DB::table('diccionario')->where('clave', '=', substr($key,1))->exists()) {
                    $name = DB::table('diccionario')
                    ->where('clave', '=', substr($key,1))
                    ->first();
                    array_push($names_table, ['data'=>$key, 'title'=>$name->nombre]);
                }else{
                    array_push($names_table, ['data'=>$key, 'title'=>substr($key,1)]);
                }
                
            }else{
                if ($key == "indicador") {
                    array_push($names_table, ['data'=>$key, 'title'=>'Indicador']);
                }elseif ($key == "space") {
                    array_push($names_table, ['data'=>$key, 'title'=>'Espacio']);
                }             
            }   
        }
        return $names_table;
    }

    public function main_vocaciones($data){
        $get_indicador = new CensosFunctions();

        $espacio = $data['espacio'];
        $type = $data['actividad'];
        $entidad = $data['entidad'];
        $places_db = [];

        $consulta = [];
        $coeficientes = [];
        $localizacion = [];

        if ($data['periodo'] == "1") {
            $places = [];
            $key_place = '';
            $consulta = $get_indicador->main_consulta_vocacion($data);
            /************Espacio***************************/
            if ($espacio == "0") {
                $key_place = "cve_mun";
                $region = $data['region'];
                $places_db = DB::table('areas_geoestadisticas_municipales')
                    ->where('cve_ent', '=', $entidad)
                    ->where('cve_region', '=', $region)
                    ->select('cve_mun', 'nom_mun as name')
                    ->get();

                $places = [];
                foreach ($places_db as $municipio) {
                    $places = array_merge($places, [$municipio->cve_mun]);
                }
            }elseif ($espacio == "1") {
                $key_place = "cve_region";
                $places = $data['regiones'];
                $places_db = DB::table('regiones_mun')
                    ->where('cve_ent', '=', $entidad)
                    ->select('cve_region', 'nom_region as name')
                    ->get();
            }elseif ($espacio == "2") {
                $key_place = "cve_region";
                $macroregion = $data['macroregion'];
                $places_db = DB::table('regiones_mun')
                    ->where('cve_ent', '=', $entidad)
                    ->where('cve_macroregion', '=', $macroregion)
                    ->select('cve_region', 'nom_region as name')
                    ->get();//dd($macroregiones);
                $places = [];
                foreach ($places_db as $macroregion) {
                    $places = array_merge($places, [$macroregion->cve_region]);
                }//dd($places);
            }elseif ($espacio == "3") {
                $key_place = "cve_macroregion";
                $places = $data['macroregiones'];
                $places_db = DB::table('macroregiones_mun')
                    ->where('cve_ent', '=', $entidad)
                    ->select('cve_macroregion', 'nom_macroregion as name')
                    ->get();
            }
            /**************Acttividades**********************/
            $actividades = [];
            if ($type == "1") {$actividades = $data['sectores'];
            }elseif ($type == "2") {$actividades = $data['subsectores'];
            }elseif ($type == "3") {$actividades = $data['ramas'];
            }
            $consulta = $get_indicador->table_vocaciones($consulta, $actividades, $key_place, $places);
            //dd($consulta);
            $coeficientes = $get_indicador->estimate_qualifiers($consulta);
            //dd($coeficientes);
            $localizacion = $get_indicador->get_vocations($coeficientes);

            $clases = $get_indicador->get_clases($localizacion);

            $names = $get_indicador->get_names_tables_localizacion($localizacion, $key_place, $places_db);
            $cabeceras = $get_indicador->get_names_tables_localizacion($consulta[0], $key_place, $places_db);

        }else{
            //$consulta = $get_indicador->getPromedio($data, '1');
        }

        return  ['consulta'=>$consulta, 
                'coeficientes'=>$coeficientes, 
                'localizacion'=>$localizacion, 
                'clases'=>$clases,
                'names'=>$names,
                'cabeceras'=>$cabeceras];
    } 

    public function table_vocaciones($consulta, $actividades, $key_place, $places){//dd($places);
        $consulta_main = [];
        $row_total = ['actividad' => 'total'];
        $consulta_total = 0;
        foreach ($actividades as $actividad) {
            //$row_main = [];
            $query_name=DB::table('diccionario')
            ->where('clave', '=', $actividad)
            ->first();
            //$row_main = ['actividad' => $actividad];
            $row_main = ['actividad' => $query_name->nombre];
            
            $total = 0;
            foreach ($places as $place) {
                
                $flag = false;
                foreach ($consulta as $row) {//dd($row);
                    if ($row->actividad == $actividad) {
                        if ($row->$key_place == $place) {
                            $key_actividad = $row->$key_place;
                            $row_main = array_merge($row_main, ["a".$key_actividad => $row->a131a]);
                            $total += $row->a131a;

                            $key = "a".$key_actividad;
                            if (array_key_exists($key, $row_total)) {
                                $row_total["a".$key_actividad] += $row->a131a;
                            }else{
                                $row_total["a".$key_actividad] = $row->a131a;//dd($row->a131a);
                            }
                            
                            $flag = true;
                            break;
                        }
                        if (!$flag) {
                            $key = "a".$place;
                            if (array_key_exists($key, $row_total)) {
                                $row_total["a".$place] += 0;
                            }else{
                                $row_total["a".$place] = 0;//dd($row->a131a);
                            }
                            
                            $row_main = array_merge($row_main, ["a".$place => 0]); 
                        }
                    }else{
                        $key = "a".$place;
                        if (array_key_exists($key, $row_total)) {
                            $row_total["a".$place] += 0;
                        }else{
                            $row_total["a".$place] = 0;//dd($row->a131a);
                        }
                        
                        $row_main = array_merge($row_main, ["a".$place => 0]);
                    }
                }
                
            }
            $consulta_total += $total;
            $row_main = array_merge($row_main, ["total" => $total]); 
            array_push($consulta_main, $row_main);
        }
        $row_total = array_merge($row_total, ['total' => $consulta_total]);
        array_push($consulta_main, $row_total);
        return $consulta_main;
    }

    public function estimate_qualifiers($tabla){
        $total = $tabla[count($tabla)-1];
        $coeficientes = [];
        
        foreach ($tabla as $fila) {
            $row = [];
            foreach ($fila as $key => $value) {
                if ($key == 'actividad') {
                    $row = array_merge($row, ['actividad'=>$value]);
                }else{
                    $coeficiente = 0;
                    try {
                        $coeficiente = ($value/$total[$key])/($fila['total']/$total['total']);    
                    } catch (\Exception $e) {
                        $coeficiente = 0;
                    }
                    //$coeficiente = ($value/$total[$key])/($fila['total']/$total['total']);
                    $row = array_merge($row, [''.$key => $coeficiente]);
                }
            }
            array_push($coeficientes, $row);
        }
        return $coeficientes;
    }

    public function get_vocations($coeficientes){//dd($coeficientes);
        $get_indicador = new CensosFunctions();        
        $localizacion = [];
        $counter = 0;
        foreach ($coeficientes as $fila) {
            foreach ($fila as $key => $value) {
                if ($key != 'actividad' && $key != 'total') {
                    if ($counter == 0) {
                        $localizacion[$key] = [];
                    }
                    array_push($localizacion[$key], ['Especializacion_Productiva'=>$fila['actividad'], 'coeficiente'=>$value]);
                    
                }
            }
            $counter++;
        }
        foreach ($localizacion as $place => $value) {
          $localizacion[$place] = $get_indicador->orderMultiDimensionalArray($value, 'coeficiente', true);  
        }
        return $localizacion;
    }

    function orderMultiDimensionalArray ($toOrderArray, $field, $inverse = false) {
        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {
                $position[$key]  = $row[$field];
                $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        }
        else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {     
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }

    public function get_clases($localizacion){
        $clases_localizacion = [];
        $main = [];
        //dd(["Localizacion"=>$localizacion]);
        foreach ($localizacion as $clave =>$valor) {
            $clases = [];
            $main = $valor[0];
            foreach ($valor as $row) {
                if ($row['coeficiente'] > 1 && ($row['coeficiente'] == $main['coeficiente']||$row['coeficiente'] >= $main['coeficiente']-.25)) {
                    array_push($clases, ['Especializacion_Productiva'=>'alto-vocacion', 'coeficiente'=>'alto-vocacion']);
                }elseif ($row['coeficiente'] > 1 && ($row['coeficiente'] < $main['coeficiente']-.25)) {
                    array_push($clases, ['Especializacion_Productiva'=>'predominante-vocacion', 'coeficiente'=>'predominante-vocacion']);
                }elseif ($row['coeficiente'] >= .9 && $row['coeficiente'] < 1) {
                    array_push($clases, ['Especializacion_Productiva'=>'intermedio-vocacion', 'coeficiente'=>'intermedio-vocacion']);
                }elseif ($row['coeficiente'] < .9) {
                    array_push($clases, ['Especializacion_Productiva'=>'bajo-vocacion', 'coeficiente'=>'bajo-vocacion']);
                }else{
                    array_push($clases, ['Especializacion_Productiva'=>'normal-vocacion', 'coeficiente'=>'normal-vocacion']);
                }
            }
            $clases_localizacion[$clave] = $clases;
        }
        return $clases_localizacion;
    }
    public function get_names_tables_localizacion($localizacion, $key_place, $places){
        /***********Generar nombres de las tablas Localizacion*************/
        $name_tables = [];
        foreach ($localizacion as $key => $value) {
            if ($key == 'actividad') {
                array_push($name_tables, ['name'=>$key, 'nickname'=>'Actividad']);
            }elseif ($key == 'total') {
                array_push($name_tables, ['name'=>$key, 'nickname'=>'Total']);
            }else{
                $flag = false;
                foreach ($places as $place) {
                    if ('a'.$place->$key_place == $key) {
                        array_push($name_tables, ['name'=>$key, 'nickname'=>$place->name]);  
                        $flag = true;
                        break;      
                    }
                    
                }
                if (!$flag) {
                    array_push($name_tables, ['name'=>$key, 'nickname'=>'undefined']);
                }
            }
            
        }
        return $name_tables;
    }
}