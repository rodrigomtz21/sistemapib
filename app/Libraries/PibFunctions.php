<?php

namespace App\Libraries;

use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;

class PibFunctions
{

    function consulta_principal($data){//dd($data);
        $base = $data['selectedValoracion'];
        $denominacion = $data['selectedDenominacion'];
        $pib = $data['selectedTipo'];
        //$cabeceras = DB::connection('sgbi')->select('show columns from '.$base);
        
        global $actividad, $cobertura, $manufactura, $entidad, $entidades, $periodos, $entidades_model, $subsector;
        $actividad = $data['selectedActividad'];
        $cobertura = $data['selectedSector'];
        $subsector = $data['selectedSubsector'];
        //dd($subsector);
        $manufactura = $data['selectedCobertura'];
        $periodos = $data['periodos'];
        $entidades = $data['entidades'];
        $regiones = $data['regiones'];

        $espacio = $data['selectedespaciotipo'];
        $entidad = $data['selectedEntidad'];
        $region = $data['selectedRegion'];
        //dd($periodos);
        $cab = array();
        $cab_aux = array();
        $consulta = array(); 
        $consulta_aux = array();
        $consulta_aux_1 = array();
        $tipo = 0;//dd($periodos);
        $entidades_model = array();
        array_unshift($regiones, '0');
        $consulta_denominacion = DB::table('denominacion')->get();
        if ($pib == '2') {//******PIB de las actividades económicas por entidad federativa*******//
            if($espacio == 'region'){//dd($regiones);
                foreach ($regiones as $region) {
                    # code..

                    $entidades_model = array();
                        if($region != "0"){
                            $entidades = DB::table('mge')->select('id', 'cve_ent', 'clave_region')->orderBy('cve_ent')->where('clave_region', '=', $region)->get();//dd($entidades);
                            foreach ($entidades as $entidad) {
                                # code...
                                $entidades_model=array_merge($entidades_model,array($entidad->cve_ent));      
                            }
                            //$entidades_model = $entidades;
                        }else{
                            $entidades_model = ['0'];//dd($entidades_model);
                        }
                        if($cobertura!='Total de la actividad económica')
                        {   //dd($entidades_model);
                            $consulta = DB::table($base)->whereNested(function($query) use ($actividad, $cobertura, $entidades_model)
                            {   //global $actividad, $cobertura, $entidades_model;
                                $query->where('Tipo', '=', $actividad);
                                $query->where('Sector', '=', $cobertura);
                                $query->whereNull('Subsector');
                                $query->whereIn('cve_ent', $entidades_model);
                            })
                            ->orWhere(function($query) use ($actividad, $cobertura, $entidades_model, $subsector)
                            {   //global $actividad, $cobertura, $entidades_model, $subsector;
                                $query->where('Tipo', '=', $actividad);
                                $query->where('Sector', '=', $cobertura);
                                $query->where('Subsector','=', $subsector);
                                $query->whereIn('cve_ent', $entidades_model);
                            })
                            ->orderBy('cve_ent')->get();
                        }else{
                            $consulta = DB::table($base)->where('Tipo', '=', $actividad)->whereIn('cve_ent', $entidades_model)->orderBy('cve_ent')->get();

                        }      
                        $cont_1 = 0;
                        $con_aux = array();
         
                        foreach ($consulta as $con) {
                            foreach ($con as $clave => $valor) {
                                if ($clave == 'cve_ent') {
                                    $con_aux[$clave] = $region;
                                }elseif($clave!='id'&$clave!='Tipo' && $clave!='Sector' && $clave!='Subsector' && $clave!="Actividad_economica"){
                                    //foreach ($periodos as $periodo) {
                                        //if ($clave == $periodo) {
                                            //$con_aux[$key] = (float)str_replace (" ", "", $con->$key);
                                            if($cont_1 ==0){
                                                $con_aux[$clave] = (float)str_replace (" ", "", $valor);
                                            }else{
                                                $con_aux[$clave] += (float)str_replace (" ", "", $valor);    
                                            }
                                             
                                            if ($base == "pib_precios_corrientes" && $denominacion == "2") {
                                                if($cont_1 ==0){
                                                    $con_aux[$clave] = (double)$con_aux[$clave]/$consulta_denominacion[0]->$clave;
                                                }else{
                                                    $con_aux[$clave] += (double)$con_aux[$clave]/$consulta_denominacion[0]->$clave;
                                                }
                                                
                                            }
                                        //}
                                    //}


                                }
                            }
                            $cont_1++;
                        }
                        array_push($consulta_aux, $con_aux);           
                  
                }
                $consulta = $consulta_aux;
            }else{
                if($cobertura!='Total de la actividad económica')
                {   
                    $consulta_aux_1 = DB::table($base)->whereNested(function($query)
                    {   global $actividad, $cobertura, $entidades;
                        $query->where('Tipo', '=', $actividad);
                        $query->where('Sector', '=', $cobertura);
                        $query->whereNull('Subsector');
                        $query->whereIn('cve_ent', $entidades);
                    })
                    ->orWhere(function($query)
                    {   global $actividad, $cobertura, $entidades, $subsector;//dd($subsector);
                        $query->where('Tipo', '=', $actividad);
                        $query->where('Sector', '=', $cobertura);
                        $query->where('Subsector','=', $subsector);
                        $query->whereIn('cve_ent', $entidades);
                    })
                    ->orderBy('cve_ent')->get();
                    //dd($consulta_aux_1);
                }else{
                    $consulta_aux_1 = DB::table($base)->where('Tipo', '=', $actividad)->whereIn('cve_ent', $entidades)->orderBy('cve_ent')->get();

                }
                
                foreach ($consulta_aux_1 as $con) {
                    $con_aux = array();
                    foreach ($con as $key => $value) {
                        if ($key == 'cve_ent') {
                            $con_aux[$key] = $con->$key;
                        }else{
                            //foreach ($periodos as $periodo) {
                                //if ($key == $periodo) {
                                if ($key!='id'&&$key!='cve_ent' && $key!='Tipo' && $key!='Sector' && $key!='Subsector') {
                                    $con_aux[$key] = (float)str_replace (" ", "", $con->$key);
                                }
                                //}
                            //}
                             
                        }
                    }
                    array_push($consulta, $con_aux);
                }
            }
            //dd($consulta);
        }else if($pib == "1"){//********PIB de las entidades federativas por actividad económica********//
            
            $entidades = [$entidad];
            $consult_entidades = [];
            $model_entidades = [];

            if ($espacio == 'region') {
                $consult_entidades = DB::table('mge')->select('id', 'cve_ent')->orderBy('cve_ent')->where('clave_region', '=', $region)->get();//dd($consult_entidades);
                foreach ($consult_entidades as $entidad) {
                    # code...
                    $model_entidades=array_merge($model_entidades,array($entidad->cve_ent));      
                }
                $entidades = $model_entidades;
            }

            if ($manufactura == 'Completo') {
                $consulta_aux = DB::table($base)->whereIn('cve_ent', $entidades)->orderBy('id')->get();
            }elseif ($manufactura == 'Manufactura') {
                $consulta_aux = DB::table($base)->whereIn('cve_ent', $entidades)->where('Sector', '=', '31-33 Industrias manufactureras')->orderBy('id')->get();
            }elseif ($manufactura == 'Sin Manufactura') {
                $consulta_aux = DB::table($base)->whereNested(function($query) use ($entidades){
                    $query->whereIn('cve_ent', $entidades);
                    $query->where('Sector', '!=', '31-33 Industrias manufactureras');
                })->orWhere(function($query) use ($entidades){
                    $query->whereIn('cve_ent', $entidades);
                    $query->where('Sector', '=', '31-33 Industrias manufactureras');
                    $query->where('Subsector', '=', '31-33 Industrias manufactureras');
                })->orWhere(function($query) use ($entidades){
                    $query->whereIn('cve_ent', $entidades);
                    $query->whereNull('Sector');
                })->orderBy('id')->get();
            }
            //dd($consulta_aux);

            foreach ($consulta_aux as $con) {
                $con_aux = array();
                if ($con->Sector==null && $con->Subsector==null) {
                    $title_1 = $con->Tipo;
                }else if ($con->Sector!=null && $con->Subsector==null) {
                    $title_1 = $con->Sector;
                }else if ($con->Sector!=null && $con->Subsector!=null) {
                    $title_1 = $con->Subsector;
                }
                $con_aux = array_merge($con_aux,array('Actividad_economica'=>$title_1));
                foreach ($con as $key => $value) {
                    //$title = $key->Field;
                    if ($key!='id'&&$key!='cve_ent' && $key!='Tipo' && $key!='Sector' && $key!='Subsector') {
                        //foreach ($periodos as $periodo) {
                            //if ($key == $periodo) {
                                $con_aux[$key] = (float)str_replace (" ", "", $con->$key);
                            //}
                        //}
                    }
                }
                array_push($consulta, $con_aux);
            }

            $back_consulta = [];
            if ($espacio == 'region') {
                foreach ($consulta as $actividad) {
                    $flag = true;
                    foreach ($back_consulta as $back_key => $back_value) {
                        if($actividad['Actividad_economica'] == $back_value['Actividad_economica']){
                            foreach ($back_value as $key => $value) {
                                if ($key!='Actividad_economica') {
                                    $back_consulta[$back_key][$key] = $back_consulta[$back_key][$key] + $actividad[$key];
                                }
                            }
                            $flag = false;
                        }
                    }
                    if($flag){
                        array_push($back_consulta, $actividad);
                    }
                    $flag = true;
                }
                $consulta = $back_consulta;
            }
            //dd($consulta);

        }
        return $consulta;
        //return response()->json($consulta);
        //return array('cabeceras'=>$cab, 'consulta'=>$consulta,);
    }

    function valoracion($data){
        $valoraciones = $data['valoraciones'];
        //dd($data);
        $formula = new pibFunction1();
        
        $participacion = [];
        $variacion = [];
        $contribucion_puntos = [];
        $contribucion_porcentual = [];
        $diferencia = [];

        if($valoraciones){
            //$variables = $data['variables'];
            foreach ($valoraciones as $valoracion) {
                $var_id = $valoracion['id'];
                $var_title = $valoracion['title'];
                if ($var_id == '0') {
                    $participacion = $formula->participacion($data); 
                }elseif ($var_id == '1') {
                    $variacion = $formula->variacion($data);
                }elseif ($var_id == '2') {
                    $contribucion_puntos = $formula->variacion_puntos($data);
                    $contribucion_porcentual = $formula->variacion_porcentual($data);
                }elseif ($var_id == '3') {
                    $diferencia = $formula->eficiencia($data);
                }
                
            } 
        }
        return (['participacion'=>$participacion, 'variacion'=>$variacion, 'contribucion_puntos'=>$contribucion_puntos, 'contribucion_porcentual'=>$contribucion_porcentual, 'diferencia'=>$diferencia]);
    }



    

    function participacion($data, $tipo_periodo, $year_1, $year_2){
        $tabla = array(); //dd($data);
        $formula = new PibFunctions();
        $global = $data[0];
        /*if($tipo_periodo == 'Promedio'){
            $data = $formula->promedio(['consulta'=>$data, 'year_1'=>$year_1, 'year_2'=>$year_2]);
            $global = $data[0];
        }*///dd($global);
        foreach ($data as $consulta) {
            $tabla_aux = array();
            foreach ($consulta as $clave => $valor ) {
                if ($clave != 'id'&&$clave != 'cve_ent' && $clave != 'Actividad_economica') {
                    try {
                        $tabla_aux[$clave] =  ($valor/$global[$clave])*100;
                    } catch (Exception $e) {
                        $tabla_aux[$clave] = 0;
                    }
                }else{
                    $tabla_aux[$clave] =  $valor;
                }              
            }
            array_push($tabla, $tabla_aux);
        }//dd($tabla);
        //return($tabla);
        if($tipo_periodo == 'Ayo'){
            return($tabla);
        }else{
            return($formula->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        }

        
    }
    function variacion($data, $tipo_periodo, $year_1, $year_2){
        $tabla = array();
        $formula = new PibFunctions();
        foreach ($data as $consulta) {
            $tabla_aux = array(); 
            $title_aux = "";
            foreach ($consulta as $clave => $valor) {
                if ($clave != 'cve_ent' && $clave != 'Actividad_economica') {
                    if ($title_aux == 'cve_ent' || $title_aux == 'Actividad_economica') {
                        $tabla_aux[$clave] =  "";
                    }else{
                        try {
                            $tabla_aux[$clave] =  ($valor/$consulta[$title_aux]-1)*100;
                        } catch (Exception $e) {
                            $tabla_aux[$clave] = 0;
                        }    
                    }                   
                }else{                    
                    $tabla_aux[$clave] =  $valor;
                }
                $title_aux =$clave;
            }
            array_push($tabla, $tabla_aux);

        }
        if($tipo_periodo == 'Ayo'){
            return($tabla);
        }else{
            return($formula->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        }
    }
    function variacion_puntos($data, $tipo_periodo, $year_1, $year_2){
        $formula = new PibFunctions();

        //$participacion = $formula->participacion($data, $tipo_periodo, $year_1, $year_2);//dd($participacion);
        //$variacion = $formula->variacion($data, $tipo_periodo, $year_1, $year_2);

        $participacion = $formula->participacion($data, 'Ayo', $year_1, $year_2);//dd($participacion);
        $variacion = $formula->variacion($data, 'Ayo', $year_1, $year_2);
        $tabla = array(); 
        $cont = 0;
        
        foreach ($participacion as $consulta) {
            $tabla_aux = array(); 
            $title_aux = "";
            foreach ($consulta as $clave => $valor) {
                if($clave == 'Promedio'){
                    $title_aux = "";
                }
                if ($clave != 'cve_ent' && $clave != 'Actividad_economica') {
                    if ($title_aux == 'cve_ent' || $title_aux == 'Actividad_economica') {
                        $tabla_aux[$clave] =  "";
                    }else{
                        $tabla_aux[$clave] =  $participacion[$cont][$clave]*$variacion[$cont][$clave]/100;
                    }                   
                }else{                    
                    $tabla_aux[$clave] =  $valor;
                }
                $title_aux =$clave;
            }
            array_push($tabla, $tabla_aux);
            $cont++;
        }
        if($tipo_periodo == 'Ayo'){
            return($tabla);
        }else{
            return($formula->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        }
            //return($tabla);
    }
    

    function variacion_porcentual($data, $tipo_periodo, $year_1, $year_2){
        $formula = new PibFunctions();
        //$variacion = $formula->variacion_puntos($data, $tipo_periodo, $year_1, $year_2);

        $variacion = $formula->variacion_puntos($data, 'Ayo', $year_1, $year_2);
        $general = $variacion[0];
        $tabla = array(); 
        foreach ($variacion as $consulta) {
            $tabla_aux = array(); 
            $title_aux = "";
            foreach ($consulta as $clave => $valor) {
                if($clave == 'Promedio'){
                    $title_aux = "";
                }
                if ($clave != 'cve_ent' && $clave != 'Actividad_economica') {
                    if ($title_aux == 'cve_ent' || $title_aux == 'Actividad_economica') {
                        $tabla_aux[$clave] =  "";
                    }else{
                        try {
                            $tabla_aux[$clave] =  $valor/$general[$clave]*100;
                        } catch (Exception $e) {
                            $tabla_aux[$clave] =  0;
                        }
                        
                    }                   
                }else{                    
                    $tabla_aux[$clave] =  $valor;
                }
                $title_aux =$clave;
            }
            array_push($tabla, $tabla_aux);
        }
        if($tipo_periodo == 'Ayo'){
            return($tabla);
        }else{
            return($formula->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        } 
        //dd(['variacion_porcentual'=>$tabla]);
        //return($tabla);
    }
    function variacion_porcentual_query($data, $contribucion, $tipo_periodo, $year_1, $year_2){
        $formula = new PibFunctions();
        //dd($contribucion);
        //$variacion = $formula->variacion_puntos($data, $tipo_periodo, $year_1, $year_2);

        //$variacion = $formula->variacion_puntos($data, 'Ayo', $year_1, $year_2);
        $variacion = $contribucion;
        $general = $variacion[0];
        $tabla = array(); 
        foreach ($variacion as $consulta) {
            $tabla_aux = array(); 
            $title_aux = "";
            foreach ($consulta as $clave => $valor) {
                if($clave == 'Promedio'){
                    $title_aux = "";
                }
                if ($clave != 'cve_ent' && $clave != 'Actividad_economica') {
                    if ($title_aux == 'cve_ent' || $title_aux == 'Actividad_economica') {
                        $tabla_aux[$clave] =  "";
                    }else{
                        try {
                            $tabla_aux[$clave] =  ($valor/$general[$clave])*100;
                        } catch (Exception $e) {
                            $tabla_aux[$clave] =  0;
                        }
                        
                    }                   
                }else{                    
                    $tabla_aux[$clave] =  $valor;
                }
                $title_aux =$clave;
            }
            array_push($tabla, $tabla_aux);
        }
        if($tipo_periodo == 'Ayo'){
            return($tabla);
        }else{
            return($formula->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        } 
        //dd(['variacion_porcentual'=>$tabla]);
        //return($tabla);
    }

    function eficiencia($data, $tipo_periodo, $year_1, $year_2){
        $formula = new PibFunctions();
        //$variacion = $formula->variacion_porcentual($data, $tipo_periodo, $year_1, $year_2);
        //$participacion = $formula->participacion($data, $tipo_periodo, $year_1, $year_2);

        $variacion = $formula->variacion_porcentual($data, 'Ayo', $year_1, $year_2);
        $participacion = $formula->participacion($data, 'Ayo', $year_1, $year_2);

        $tabla = array(); 
        $cont = 0;
        foreach ($variacion as $consulta) {
            $tabla_aux = array(); 
            $title_aux = "";
            foreach ($consulta as $clave => $valor) {
                if($clave == 'Promedio'){
                    $title_aux = "";
                }
                if ($clave != 'cve_ent' && $clave != 'Actividad_economica') {
                    if ($title_aux == 'cve_ent' || $title_aux == 'Actividad_economica') {
                        $tabla_aux[$clave] =  "";
                    }else{
                        $tabla_aux[$clave] =  $variacion[$cont][$clave]-$participacion[$cont][$clave];
                    }                   
                }else{                    
                    $tabla_aux[$clave] =  $valor;
                }
                $title_aux =$clave;
            }

            array_push($tabla, $tabla_aux);
            $cont++;
        }
        if($tipo_periodo == 'Ayo'){
            return($tabla);
        }else{
            return($formula->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        }
        //dd(['eficiencia'=>$tabla]);
        //return($tabla);
    }


    function eficiencia_query($data, $contribucion, $tipo_periodo, $year_1, $year_2){
        $formula = new PibFunctions();
        //$variacion = $formula->variacion_porcentual($data, $tipo_periodo, $year_1, $year_2);
        //$participacion = $formula->participacion($data, $tipo_periodo, $year_1, $year_2);

        //$variacion = $formula->variacion_porcentual($data, 'Ayo', $year_1, $year_2);
        $variacion = $formula->variacion_porcentual_query($data, $contribucion, 'Ayo', $year_1, $year_2);
        $participacion = $formula->participacion($data, 'Ayo', $year_1, $year_2);

        $tabla = array(); 
        $cont = 0;
        foreach ($variacion as $consulta) {
            $tabla_aux = array(); 
            $title_aux = "";
            foreach ($consulta as $clave => $valor) {
                if($clave == 'Promedio'){
                    $title_aux = "";
                }
                if ($clave != 'cve_ent' && $clave != 'Actividad_economica') {
                    if ($title_aux == 'cve_ent' || $title_aux == 'Actividad_economica') {
                        $tabla_aux[$clave] =  "";
                    }else{
                        $tabla_aux[$clave] =  $variacion[$cont][$clave]-$participacion[$cont][$clave];
                    }                   
                }else{                    
                    $tabla_aux[$clave] =  $valor;
                }
                $title_aux =$clave;
            }

            array_push($tabla, $tabla_aux);
            $cont++;
        }
        if($tipo_periodo == 'Ayo'){
            return($tabla);
        }else{
            return($formula->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        }
        //dd(['eficiencia'=>$tabla]);
        //return($tabla);
    }

    function variacion_puntos_promedio($data, $year_1, $year_2){
        //dd($data);
        $consultas = $data['consulta'];
        $cabeceras = $data['cabeceras'];
        $formula = new pibFunction1();
        $participacion = $formula->participacion($data);//dd($participacion);
        $variacion = $formula->variacion($data);
        $participacion = $formula->one_promedios(['consulta'=>$participacion, 'cabeceras'=>$cabeceras, 'year_1'=>$year_1, 'year_2'=>$year_2]);
        $variacion = $formula->one_promedios(['consulta'=>$variacion, 'cabeceras'=>$cabeceras, 'year_1'=>$year_1, 'year_2'=>$year_2]);
        $tabla = array(); $cont = 0;
        $cabeceras = [0=>['title'=>'cve_ent'], 1=>['title'=>'Promedio']];
        foreach ($participacion as $consulta) {
            $tabla_aux = array(); $title_aux = "";
            foreach ($cabeceras as $cabecera) {
                $title = $cabecera['title'];
                if ($title != 'cve_ent' && $title != 'Actividad_economica') { 
                    
                        $tabla_aux[$title] =  $consulta[$title]*$variacion[$cont][$title]/100;
                                       
                }else{                  
                    $tabla_aux[$title] =  $consulta[$title];
                }
                $title_aux =$title;
            }
            $tabla = array_merge($tabla,[''.$cont => $tabla_aux]);
            $cont++;
        }  
            return($tabla);
        
        //dd($tabla);
    }
    function variacion_porcentual_promedio($data, $year_1, $year_2){
        $formula = new pibFunction1();
        //$cabeceras = $data['cabeceras'];
        $variacion = $formula->variacion_puntos_promedio($data, $year_1, $year_2);
        $cabeceras = [0=>['title'=>'cve_ent'], 1=>['title'=>'Promedio']];
        $general = $variacion[0];
        //dd($general);
        $tabla = array(); $cont = 0;
        foreach ($variacion as $consulta) {
            $tabla_aux = array(); $title_aux = "";
            foreach ($cabeceras as $cabecera) {
                $title = $cabecera['title'];
                if ($title != 'cve_ent' && $title != 'Actividad_economica') {
                    
                        try {
                            $tabla_aux[$title] =  $consulta[$title]/$general[$title]*100;
                        } catch (Exception $e) {
                            $tabla_aux[$title] =  0;
                        }
                        
                                      
                }else{                    
                    $tabla_aux[$title] =  $consulta[$title];
                }
                $title_aux =$title;
            }
            $tabla = array_merge($tabla,[''.$cont => $tabla_aux]);
            $cont++;
        }
        
            return($tabla);
        //dd($tabla);
    }
    function eficiencia_promedio($data, $year_1, $year_2){
        $formula = new pibFunction1();
        $consultas = $data['consulta'];
        $cabeceras = $data['cabeceras'];
        //$variacion = $formula->variacion_porcentual($data);
        $participacion = $formula->participacion($data);
        $participacion = $formula->one_promedios(['consulta'=>$participacion, 'cabeceras'=>$cabeceras, 'year_1'=>$year_1, 'year_2'=>$year_2]);
        $contribucion = $formula->variacion_porcentual_promedio($data, $year_1, $year_2);
        $cabeceras = [0=>['title'=>'cve_ent'], 1=>['title'=>'Promedio']];
        //dd($general);
        $tabla = array(); $cont = 0;
        foreach ($contribucion as $consulta) {
            $tabla_aux = array(); $title_aux = "";
            foreach ($cabeceras as $cabecera) {
                $title = $cabecera['title'];
                if ($title != 'cve_ent' && $title != 'Actividad_economica') {
                    
                        $tabla_aux[$title] =  $consulta[$title]-$participacion[$cont][$title];
                                      
                }else{                    
                    $tabla_aux[$title] =  $consulta[$title];
                }
                $title_aux =$title;
            }
            $tabla = array_merge($tabla,[''.$cont => $tabla_aux]);
            $cont++;
        }
            return($tabla);
        //dd($tabla);
        
    }
    function promedios($data){
        $consulta_aux = array();
        //dd($);
        $prom = (int)$data['no_promedios'];
        //dd($prom);
        for ($i=1; $i <= $prom; $i++) { 
            $year_1 = $data['promedios'][$i-1]['year_pi'];
            $year_2 = $data['promedios'][$i-1]['year_pf'];
            $cont_consulta = 0;
            
            foreach ($data['consulta'] as $consulta) {
                $promedio = 0; $cont = 0;
                foreach ($data['cabeceras'] as $cabecera) {
                    $title = $cabecera['title'];
                    if ($title != 'cve_ent'&&$title != 'Actividad_economica') {
                        if((int)$title >= (int)$year_1&&(int)$title <= (int)$year_2){
                            $promedio += (float)$consulta[$title]; 
                            $cont++;
                        }
                    }
                    //$consulta_aux[$title] = (int)$consulta[$title];
                }
                $data['consulta_base'][$cont_consulta]['Prom. '.$year_1.'-'.$year_2] = $promedio/$cont;
                //$consulta_aux['Prom. '.$year_1.'-'.$year_2] = round($promedio/$cont, 0);
                $cont_consulta++;
            }
            $cabeceras = $data['cabeceras_base'];
            $no_cabeceras = count($cabeceras);
            $data['cabeceras_base'][$no_cabeceras] = ['title' => 'Prom. '.$year_1.'-'.$year_2];
        }
        //dd($data['cabeceras_base']);
        $tabla = ['consulta'=>$data['consulta_base'], 'cabeceras'=>$data['cabeceras_base'], 'tipo'=>$data['tipo']];
        return($tabla);//dd($tabla);
        
    }
    function one_promedios($data){
        $consulta_prom = array();
       // dd($data);
        $title_first = '';
            $year_1 = $data['year_1'];
            $year_2 = $data['year_2'];
            $cont_consulta = 0;
            //dd($data['cabeceras']);
            foreach ($data['consulta'] as $consulta) {
                $consulta_aux = array();
                $promedio = 0; $cont = 0;
                foreach ($data['cabeceras'] as $cabecera) {
                    $title = $cabecera['title'];//dd($title);
                    if ($title !== "Actividad_economica" && $title !== "cve_ent" ) {//dd($title);
                        if((int)$title >= (int)$year_1&&(int)$title <= (int)$year_2){
                            $promedio += (float)$consulta[$title]; 
                            $cont++;
                        }
                        //dd($promedio);
                    }else{
                        $title_first = $title;
                    }
                    
                    
                }
                $consulta_aux[$title_first] = $consulta[$title_first];
                $consulta_aux['Promedio'] = $promedio/$cont;

                $consulta_prom = array_merge($consulta_prom, [''.$cont_consulta=>$consulta_aux]);
                $cont_consulta++;
            }
        return($consulta_prom);//dd($tabla);
        
    }
    function promedio($data){
        $tabla = array();
        $title_first = '';
        $year_1 = $data['year_1'];
        $year_2 = $data['year_2'];

        foreach ($data['consulta'] as $consulta) {
            $consulta_aux = array();
            $promedio = 0; 
            $cont = 0;
            foreach ($consulta as $clave => $valor) {
                if ($clave != "Actividad_economica" && $clave != "cve_ent" ) {
                    if((int)$clave >= (int)$year_1&&(int)$clave <= (int)$year_2){
                        $promedio += (float)$valor; 
                        $cont++;
                    }
                }else{
                    $title_first = $clave;
                }    
            }
            //dd($cont);
            $consulta_aux[$title_first] = $consulta[$title_first];
            $consulta_aux['Promedio'] = $promedio/$cont;    
            
            

            array_push($tabla, $consulta_aux);
        }
        return($tabla);
        
    }

    function all_variables($data){//dd($data);
        $funcion = new pibFunction1();
        $tabla_variables = [];
        $tabla_val = $data['tabla'];
        $cabeceras = $data['cabeceras'];
        $tipo = $data['tipo'];
        $years = $data['promedios'];
        $promedios = $data['no_promedios'];
        $tabla_val_base = $data['consulta_base'];
        $cabeceras_base = $data['cabeceras_base'];
        //dd($promedios);
        $participacion = []; $variacion = []; $contribucion_puntos = []; $contribucion_porcentual = []; $diferencia = []; $cabeceras_promedios = []; 
        if($tabla_val['participacion']){
            $tabla = ['consulta'=>$tabla_val['participacion'], 'cabeceras'=>$cabeceras, 'tipo'=>$tipo, 'promedios'=>$years, 'no_promedios'=>$promedios, 'consulta_base'=>$tabla_val_base['participacion'], 'cabeceras_base'=>$cabeceras_base];
            $participacion = $funcion->promedios($tabla);
            $cabeceras_promedios = $participacion['cabeceras'];//dd($participacion['cabeceras']);
            $participacion = $participacion['consulta']; 
            
        }if ($tabla_val['variacion']) {
            $tabla = ['consulta'=>$tabla_val['variacion'], 'cabeceras'=>$cabeceras, 'tipo'=>$tipo, 'promedios'=>$years, 'no_promedios'=>$promedios, 'consulta_base'=>$tabla_val_base['variacion'], 'cabeceras_base'=>$cabeceras_base];
            $variacion = $funcion->promedios($tabla);
            $cabeceras_promedios = $variacion['cabeceras'];
            $variacion = $variacion['consulta'];
            
        }if ($tabla_val['contribucion_puntos']) {
            //$tabla = ['consulta'=>$tabla_val['contribucion_puntos'], 'cabeceras'=>$cabeceras, 'tipo'=>$tipo, 'promedios'=>$years, 'no_promedios'=>$promedios, 'consulta_base'=>$tabla_val_base['contribucion_puntos'], 'cabeceras_base'=>$cabeceras_base];
            //$contribucion_puntos = $funcion->promedios($tabla);
            $contribucion_puntos = $tabla_val_base['contribucion_puntos']['tabla'];
            //$cabeceras_promedios = $contribucion_puntos['cabeceras'];
            $cabeceras_promedios = $tabla_val_base['contribucion_puntos']['cabeceras'];
            //$contribucion_puntos = $contribucion_puntos['consulta'];
            
        }if ($tabla_val['contribucion_porcentual']) {
            $tabla = ['consulta'=>$tabla_val['contribucion_porcentual'], 'cabeceras'=>$cabeceras, 'tipo'=>$tipo, 'promedios'=>$years, 'no_promedios'=>$promedios, 'consulta_base'=>$tabla_val_base['contribucion_porcentual'], 'cabeceras_base'=>$cabeceras_base];
            //$contribucion_porcentual = $funcion->promedios($tabla);
            //$cabeceras_promedios = $contribucion_porcentual['cabeceras'];
            //$contribucion_porcentual = $contribucion_porcentual['consulta'];
            $contribucion_porcentual = $tabla_val_base['contribucion_porcentual']['tabla'];
            $cabeceras_promedios = $tabla_val_base['contribucion_porcentual']['cabeceras'];
            
        }if ($tabla_val['diferencia']) {
            $tabla = ['consulta'=>$tabla_val['diferencia'], 'cabeceras'=>$cabeceras, 'tipo'=>$tipo, 'promedios'=>$years, 'no_promedios'=>$promedios, 'consulta_base'=>$tabla_val_base['diferencia'], 'cabeceras_base'=>$cabeceras_base];
            //$diferencia = $funcion->promedios($tabla);
            //$cabeceras_promedios = $diferencia['cabeceras'];
            //$diferencia = $diferencia['consulta'];
            $diferencia = $tabla_val_base['diferencia']['tabla'];
            $cabeceras_promedios = $tabla_val_base['diferencia']['cabeceras'];
            
        }
        //dd($diferencia);
        $tabla_val = ['participacion'=>$participacion, 'variacion'=>$variacion, 'contribucion_puntos'=>$contribucion_puntos, 'contribucion_porcentual'=>$contribucion_porcentual, 'diferencia'=>$diferencia];
        //$cabeceras_aux = $participacion['cabeceras'];
       //dd($cabeceras_promedios);
        return(['consulta'=>$tabla_val, 'cabeceras'=>$cabeceras_promedios]);
        
    }

    function ranking($data, $parameters){
        $tabla = array();//dd($datas);
            //foreach ($datas as $data) {dd($data);
                 foreach ($parameters as $parameter){
                    if ($data[$parameter['title']]) {
                        if($parameter['param_1']){
                            $dat = [];
                            foreach ($data[$parameter['title']] as $clave => $fila) {
                                $dat[$clave] = $fila[$parameter['param_1']];
                            }
                            if ($parameter['param_2'] == "asc") {
                                array_multisort($dat, SORT_ASC, $data[$parameter['title']]);
                            }elseif ($parameter['param_2'] == "desc") {
                                array_multisort($dat, SORT_DESC, $data[$parameter['title']]);
                            }
                        }
                    }
                }
            //}
        return($data);
    }
}
