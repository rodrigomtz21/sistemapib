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
use App\Libraries\pibnacionalFunction;

class ResumenFunctions
{

    function consulta_principal($data){  
        //dd($data);
        $resumen = new ResumenFunctions();
        $cab = [];
        $flag = false;
        $tabla = [];

        $base = $data['selectedValoracion'];
        $actividad = $data['selectedActividad'];
        $cobertura = $data['selectedCovertura'];
        $subsector = $data['selectedSubsector'];
        $entidades = $data['entidades'];
        $denominacion = $data['selectedDenominacion'];
        $periodo = $data['selectedPeriodo'];
        $year = $data['selectedAyo'];
        $year_1 = $data['selectedAyo_1'];
        $year_2 = $data['selectedAyo_2'];
        $variables = $data['variables'];
        $espacio = $data['espacioPeriodo'];

        $region = $data['selectedRegion'];
        $regiones = $data['regiones'];
        //dd($regiones);
        $places = [];
        $places_regiones = [];
        $tabla_participacion = [];
        $places_entidades = DB::table('mge')->select('id', 'nom_ent as nombre', 'cve_ent as key')->get();
        if($espacio == "region"){/**********evaluar con formulas************/
            $tabla_resumen = $resumen->consulta_region($base, $actividad, $cobertura,$subsector, $region, $denominacion);
            //$tabla_participacion = $resumen->consulta_region("pib_precios_constantes_particip", $actividad, $cobertura,$subsector, $region, $denominacion);
            $places = DB::table('mge')->select('id', 'nom_ent as nombre', 'cve_ent as key')->get();
            $places_regiones = DB::table('regiones')->select('id', 'Nombre as nombre', 'clave as key')->get();
        }elseif($espacio == "region_nacional"){/**************Obtener de la base de datos********/
            $tabla_resumen = $resumen->consulta_region_nacional($base, $actividad, $cobertura,$subsector, $regiones, $denominacion);
            $tabla_participacion = $resumen->consulta_region_nacional('pib_precios_constantes_particip_nacional', $actividad, $cobertura,$subsector, $regiones, $denominacion);
            $places = DB::table('regiones')->select('id', 'Nombre as nombre', 'clave as key')->get();
        }elseif ($espacio == "entidad") { /****************Obtener de la base de datos**********/
            $tabla_resumen = $resumen->consulta_nacional($base, $actividad, $cobertura,$subsector, $entidades, $denominacion);
            $tabla_participacion = $resumen->consulta_nacional('pib_precios_constantes_particip_nacional', $actividad, $cobertura,$subsector, $entidades, $denominacion);
            $places = DB::table('mge')->select('id', 'nom_ent as nombre', 'cve_ent as key')->get();
        }
        

        $tabla = $resumen->resumen($tabla_resumen, $tabla_participacion, $variables, $periodo, $year, $year_1, $year_2, $espacio);
        //dd($tabla);
        /*************Cambiar claves por nombres***********/
        $row_count = 0;
        foreach ($tabla['tabla'] as $row) {
            foreach ($row as $key => $value) {
                if($key == 'Entidad' && $key != "0"){
                    if($value != '0'){
                        foreach ($places as $place) {
                            if('a'.$value == 'a'.$place->key){
                                $tabla['tabla'][$row_count]['nombre'] = $place->nombre;
                            }
                        }
                        if($espacio == "region_nacional"){
                            foreach ($places_entidades as $place) {
                                if('a'.$value == 'a'.$place->key){
                                    $tabla['tabla'][$row_count]['nombre'] = $place->nombre;
                                }
                            }   
                        }
                    }else{
                        $tabla['tabla'][$row_count]['nombre'] = 'Nacional';
                        if($espacio == "region"){
                            foreach ($places_regiones as $place) {
                                if($region == $place->key){
                                    $tabla['tabla'][$row_count]['nombre'] = $place->nombre;        
                                }
                            }
                            
                        }
                    }
                }
            }
            $row_count ++;
        }
        /*************Cambiar claves por nombres end***********/    
        return $tabla;
        //return(['consulta'=>$tabla, 'cabeceras'=>$cab]);
    }

    function consulta_principal_mapa($data){  
        $resumen = new ResumenFunctions();
        $cab = [];
        $flag = false;
        $tabla = [];

        $base = $data['selectedValoracion'];
        $actividad = $data['selectedActividad'];
        $cobertura = $data['selectedCovertura'];
        $subsector = $data['selectedSubsector'];
        $entidades = $data['entidades'];
        $denominacion = $data['selectedDenominacion'];
        $periodo = $data['selectedPeriodo'];
        $year = $data['selectedAyo'];
        $year_1 = $data['selectedAyo_1'];
        $year_2 = $data['selectedAyo_2'];
        $variables = $data['variables'];
        $espacio = $data['espacioPeriodo'];

        $region = $data['selectedRegion'];
        $regiones = $data['regiones'];
        $places = [];
        $places_regiones = [];
        $tabla_participacion =[];
        if($espacio == "region"){
            $tabla_resumen = $resumen->consulta_region($base, $actividad, $cobertura,$subsector, $region, $denominacion);
            //$tabla_participacion = $resumen->consulta_region("pib_precios_constantes_particip_nacional", $actividad, $cobertura,$subsector, $region, $denominacion);
        }elseif($espacio == "region_nacional"){
            $tabla_resumen = $resumen->consulta_region_nacional($base, $actividad, $cobertura,$subsector, $regiones, $denominacion);
            $tabla_participacion = $resumen->consulta_region_nacional('pib_precios_constantes_particip_nacional', $actividad, $cobertura,$subsector, $regiones, $denominacion);
        }elseif ($espacio == "entidad") {
            $tabla_resumen = $resumen->consulta_nacional($base, $actividad, $cobertura,$subsector, $entidades, $denominacion);
            $tabla_participacion = $resumen->consulta_nacional('pib_precios_constantes_particip_nacional', $actividad, $cobertura,$subsector, $entidades, $denominacion);
        }

        $tabla = $resumen->resumen($tabla_resumen, $tabla_participacion, $variables, $periodo, $year, $year_1, $year_2, $espacio);
        
        return $tabla;
    }


    function resumen($consulta, $tabla_participacion, $variables, $tipo_periodo, $year, $year_1, $year_2, $espacio){  
        $tabla = [];
        $contador = 0;
        $consulta_resumen = [];
        $cabeceras = [];

        $variacion_puntos = [];
        $variacion_porcentual = [];
        $eficiencia = [];

        $resumen = new ResumenFunctions();
        $valoracion = new PibFunctions();

        $participacion = $valoracion->participacion($consulta, $tipo_periodo, $year_1, $year_2);
        $variacion = $valoracion->variacion($consulta, $tipo_periodo, $year_1, $year_2);
        //$variacion_puntos = $valoracion->variacion_puntos($consulta, $tipo_periodo, $year_1, $year_2);
        //$variacion_porcentual = $valoracion->variacion_porcentual($consulta, $tipo_periodo, $year_1, $year_2);
        if ($espacio == 'region') {
            $variacion_puntos = $valoracion->variacion_puntos($consulta, $tipo_periodo, $year_1, $year_2);
            $variacion_porcentual = $valoracion->variacion_porcentual($consulta, $tipo_periodo, $year_1, $year_2);
            $eficiencia = $valoracion->eficiencia($consulta, $tipo_periodo, $year_1, $year_2);
        }else{
            $variacion_puntos = $tabla_participacion; 
            $variacion_porcentual = $valoracion->variacion_porcentual_query($consulta, $tabla_participacion, $tipo_periodo, $year_1, $year_2);
            $eficiencia = $valoracion->eficiencia_query($consulta, $tabla_participacion, $tipo_periodo, $year_1, $year_2);   
        }
        
        

        if($tipo_periodo == 'Promedio'){
            $year = 'Promedio';
            //$participacion = $valoracion->promedio(['consulta'=>$participacion, 'year_1'=>$year_1, 'year_2'=>$year_2]);
            //$variacion = $valoracion->promedio(['consulta'=>$variacion, 'year_1'=>$year_1, 'year_2'=>$year_2]);
            //dd($variacion_puntos);
            $consulta = $valoracion->promedio(['consulta'=>$consulta, 'year_1'=>$year_1, 'year_2'=>$year_2]);
            if ($espacio != 'region') {
                $variacion_puntos = $valoracion->promedio(['consulta'=>$variacion_puntos, 'year_1'=>$year_1, 'year_2'=>$year_2]);
            }
        }
        $participacion = $resumen->posiciones($participacion, $consulta, $year);
        $variacion = $resumen->posiciones($variacion, $consulta, $year);//dd($variacion_puntos);
        $variacion_puntos = $resumen->posiciones($variacion_puntos, $consulta, $year);
        $eficiencia = $resumen->posiciones2($eficiencia, $consulta, $year, $variacion[0][$year]);
        
        array_push($cabeceras, array('nickname'=>'Clave', 'header'=>'Entidad'));
        if ($espacio == 'region') {
            array_push($cabeceras, array('nickname'=>'Región', 'header'=>'nombre'));
        }elseif($espacio == 'region_nacional'){
            array_push($cabeceras, array('nickname'=>'Entidad Federativa o Región', 'header'=>'nombre'));
        }elseif($espacio == 'entidad'){
            array_push($cabeceras, array('nickname'=>'Entidad Federativa', 'header'=>'nombre'));
        }


        array_push($cabeceras, array('nickname'=>'Valor', 'header'=>'Valor'));
        //$contador_cab = 0;
        foreach ($consulta as $con) {
            $con_aux = array();
            //$con_aux_1 = array();
            $con_aux["part"] = $participacion[$contador][$year];
            $con_aux["var"] = $variacion[$contador][$year];
            $con_aux['Entidad'] = $con['cve_ent'];
            $con_aux['Valor'] = (float)$con[$year];

            
            if($variables){
                foreach ($variables as $variable) {
                    $var_id = $variable['id'];
                    $var_title = $variable['title'];
                    if ($var_id == '0') {
                        $con_aux[$var_id] = $participacion[$contador][$year];
                        $con_aux['posicion_1'] = $participacion[$contador]['posicion'];
                        if($contador == 0){
                            array_push($cabeceras, array('nickname'=>$var_title, 'header'=>$var_id));
                            array_push($cabeceras, array('nickname'=>'Posición', 'header'=>'posicion_1'));
                        }
                    }elseif ($var_id == '1') {
                        
                        $con_aux[$var_id] = $variacion[$contador][$year];
                        $con_aux['posicion_2'] = $variacion[$contador]['posicion'];
                        if($contador == 0){
                            array_push($cabeceras, array('nickname'=>$var_title, 'header'=>$var_id));
                            array_push($cabeceras, array('nickname'=>'Posición', 'header'=>'posicion_2'));
                        }
                    }elseif ($var_id == '2') {
                        //dd($variacion_puntos);
                        $con_aux[$var_id.'puntos'] = $variacion_puntos[$contador][$year];
                        $con_aux[$var_id.'porcentaje'] = $variacion_porcentual[$contador][$year];
                        $con_aux['posicion_3'] = $variacion_puntos[$contador]['posicion'];
                        if($contador == 0){
                            array_push($cabeceras, array('nickname'=>$var_title.'(Puntos porcentuales)', 'header'=>$var_id.'puntos'));
                            array_push($cabeceras, array('nickname'=>$var_title.'(En porcentaje)(B)', 'header'=>$var_id.'porcentaje'));
                            array_push($cabeceras, array('nickname'=>'Posición', 'header'=>'posicion_3'));
                        }
                        
                    }elseif ($var_id == '3') {
                        $con_aux[$var_id] = $eficiencia[$contador][$year];
                        $con_aux['posicion_4'] = $eficiencia[$contador]['posicion'];
                        if($contador == 0){
                            array_push($cabeceras, array('nickname'=>$var_title, 'header'=>$var_id));
                            array_push($cabeceras, array('nickname'=>'Posición', 'header'=>'posicion_4'));
                        }
                    }
                    
                }
                
            }
            array_push($consulta_resumen, $con_aux);
            $contador++;
        }//dd(['consulta_resumen'=>$consulta_resumen]);
        return(['tabla'=>$consulta_resumen, 'cabeceras'=>$cabeceras]);
    }
     
    function consulta_nacional($base, $actividad_this, $cobertura_this, $subsector_this, $entidades_this, $denominacion){
        //$resumen = new resumenFunction();
        global $actividad, $cobertura, $entidades, $subsector;
        $actividad = $actividad_this;
        $cobertura = $cobertura_this;
        $subsector = $subsector_this;
        $entidades = $entidades_this;
        $cont = 0;
        //dd($entidades);
        $cab = [];
        $cab_aux = [];
        $consulta = [];
        $consulta_aux = [];

        if($cobertura!='Total de la actividad económica')
        {   
            $consulta = DB::table($base)->whereNested(function($query)
            {   global $actividad, $cobertura, $entidades;
                $query->where('Tipo', '=', $actividad);
                $query->where('Sector', '=', $cobertura);
                $query->whereNull('Subsector');
                $query->whereIn('cve_ent', $entidades);
            })
            ->orWhere(function($query)
            {   global $actividad, $cobertura, $entidades, $subsector;
                $query->where('Tipo', '=', $actividad);
                $query->where('Sector', '=', $cobertura);
                //$query->where('Subsector','LIKE','%Total%');
                //$query->where('Subsector','=', $cobertura);
                $query->where('Subsector','=', $subsector);
                $query->whereIn('cve_ent', $entidades);
            })
            //joi
            ->orderBy('cve_ent')->get();
        }else{
            $consulta = DB::table($base)->where('Tipo', '=', $actividad)->whereIn('cve_ent', $entidades)->orderBy('cve_ent')->get();

        }  
        //dd(['consulta'=>$consulta]);
        $cont_1 = 0;
        $consulta_denominacion = DB::table('denominacion')->get();
        foreach ($consulta as $con) {
            $con_aux = array();
            foreach ($con as $clave => $valor) {
                //dd($con->cve_ent);
                $con_aux['cve_ent'] = $con->cve_ent;
                if ($clave == 'cve_ent') {
                    //$con_aux[$clave] = $valor;
                }elseif($clave!='id'&$clave!='Tipo' && $clave!='Sector' && $clave!='Subsector' && $clave!="Actividad_economica"){
                    $con_aux[$clave] = (float)str_replace (" ", "", $valor); 
                    if ($base == "pib_precios_corrientes" && $denominacion == "2") {
                        $con_aux[$clave] = (double)$con_aux[$clave]/$consulta_denominacion[0]->$clave;
                    }
                }
            }
            $consulta_aux = array_merge($consulta_aux,[''.$cont_1 => $con_aux]);
            $cont_1++;
        }
        //dd($consulta_aux);
        return $consulta_aux;
    }
    
    function consulta_region($base, $actividad_this, $cobertura_this, $subsector_this, $region_this, $denominacion){
        //$resumen = new resumenFunction();
        global $actividad, $cobertura, $entidades_model, $subsector;
        $actividad = $actividad_this;
        $cobertura = $cobertura_this;
        $subsector = $subsector_this;
        $region = $region_this;
        $cont = 0;
        //dd($entidades);
        $cab = [];
        $cab_aux = [];
        $consulta = [];
        $consulta_aux = [];
        $entidades_model = [];
        $entidades = DB::table('mge')->select('id', 'cve_ent')->orderBy('cve_ent')->where('clave_region', '=', $region)->get();
        //dd(['region'=>$entidades]);
        foreach ($entidades as $entidad) {
            # code...
            $entidades_model=array_merge($entidades_model,array($entidad->cve_ent));      
        }
        //dd(['region'=>$entidades_model]);
        if($cobertura!='Total de la actividad económica')
        {   
            $consulta = DB::table($base)->whereNested(function($query)
            {   global $actividad, $cobertura, $entidades_model;
                $query->where('Tipo', '=', $actividad);
                $query->where('Sector', '=', $cobertura);
                $query->whereNull('Subsector');
                $query->whereIn('cve_ent', $entidades_model);
            })
            ->orWhere(function($query)
            {   global $actividad, $cobertura, $entidades_model, $subsector;
                $query->where('Tipo', '=', $actividad);
                $query->where('Sector', '=', $cobertura);
                //$query->where('Subsector','LIKE','%Total%');
                //$query->where('Subsector','=', $cobertura);
                $query->where('Subsector','=', $subsector);
                $query->whereIn('cve_ent', $entidades_model);
            })
            ->orderBy('cve_ent')->get();
        }else{
            $consulta = DB::table($base)->where('Tipo', '=', $actividad)->whereIn('cve_ent', $entidades_model)->orderBy('cve_ent')->get();

        }  
        //dd(['consulta'=>$consulta]);
        $cont_1 = 0;
        $consulta_denominacion = DB::table('denominacion')->get();
        $con_aux = array();
        foreach ($consulta as $con) {
            
            foreach ($con as $clave => $valor) {
                //dd($con->cve_ent);
                $con_aux['cve_ent'] = '0';
                if ($clave == 'cve_ent') {
                    //$con_aux[$clave] = $valor;
                }elseif($clave!='id'&$clave!='Tipo' && $clave!='Sector' && $clave!='Subsector' && $clave!="Actividad_economica"){
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
                }
            }
            //$consulta_aux = array_merge($consulta_aux,[''.$cont_1 => $con_aux]);
            $cont_1++;
        }
        array_push($consulta_aux, $con_aux);
        //$consulta_aux = array_merge($consulta_aux,[''.$cont_1 => $con_aux]);
        //dd(['consulta_aux'=>$con_aux]);
        $cont_1 = 0;
        //$consulta_denominacion = DB::table('denominacion')->get();
        foreach ($consulta as $con) {
            $con_aux = array();
            foreach ($con as $clave => $valor) {
                //dd($con->cve_ent);
                $con_aux['cve_ent'] = $con->cve_ent;
                if ($clave == 'cve_ent') {
                    //$con_aux[$clave] = $valor;
                }elseif($clave!='id'&$clave!='Tipo' && $clave!='Sector' && $clave!='Subsector' && $clave!="Actividad_economica"){
                    $con_aux[$clave] = (float)str_replace (" ", "", $valor); 
                    if ($base == "pib_precios_corrientes" && $denominacion == "2") {
                        $con_aux[$clave] = (double)$con_aux[$clave]/$consulta_denominacion[0]->$clave;
                    }
                }
            }
            $consulta_aux = array_merge($consulta_aux,[''.$cont_1 => $con_aux]);
            $cont_1++;
        }
        //dd($consulta_aux);
        return $consulta_aux;
    }

    function consulta_region_nacional($base, $actividad_this, $cobertura_this, $subsector_this, $regiones_this, $denominacion){
        //$resumen = new resumenFunction();
        global $actividad, $cobertura, $entidades_model, $subsector;
        $actividad = $actividad_this;
        $cobertura = $cobertura_this;
        $subsector = $subsector_this;
        $regiones = $regiones_this;
        $cont = 0;
        //dd($entidades);
        $cab = [];
        $cab_aux = [];
        $consulta = [];
        $consulta_aux = [];
        $entidades_model = [];
        $regiones_model = [];

        array_unshift($regiones, array(
          "id"=> "0",
          "nombre_area"=> "Nacional",
          "selected"=> true
        ));
        
        //var_dump(['region'=>$regiones]);
        //dd(['region'=>$regiones_model]);
        $consulta_denominacion = DB::table('denominacion')->get();
        foreach ($regiones as $region) {
            $flag_nacional = false;
            $entidades_model = [];
            if($region['selected'] == "true"){
                if($region['id'] != "0"){
                    $entidades = DB::table('mge')->select('id', 'cve_ent', 'clave_region')->orderBy('cve_ent')->where('clave_region', '=', $region['id'])->get();
                    foreach ($entidades as $entidad) {
                        # code...
                        $entidades_model=array_merge($entidades_model,array($entidad->cve_ent));      
                    }
                }else{
                    $flag_nacional = true;
                    $entidades_model = [0=>'0'];
                }
                if($cobertura!='Total de la actividad económica')
                {   
                    $consulta = DB::table($base)->whereNested(function($query)
                    {   global $actividad, $cobertura, $entidades_model;
                        $query->where('Tipo', '=', $actividad);
                        $query->where('Sector', '=', $cobertura);
                        $query->whereNull('Subsector');
                        $query->whereIn('cve_ent', $entidades_model);
                    })
                    ->orWhere(function($query)
                    {   global $actividad, $cobertura, $entidades_model, $subsector;
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
                            $con_aux[$clave] = $region['id'];
                        }elseif($clave!='id'&$clave!='Tipo' && $clave!='Sector' && $clave!='Subsector' && $clave!="Actividad_economica"){
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
                        }
                    }
                    $cont_1++;
                }
                array_push($consulta_aux, $con_aux); 

                $consulta_aux_entidades = [];
                $cont_1 = 0;
                if (!$flag_nacional) {
                    foreach ($consulta as $con) {
                        $con_aux = array();
                        foreach ($con as $clave => $valor) {
                            //dd($con->cve_ent);
                            $con_aux['cve_ent'] = $con->cve_ent;
                            if ($clave == 'cve_ent') {
                                //$con_aux[$clave] = $valor;
                            }elseif($clave!='id'&$clave!='Tipo' && $clave!='Sector' && $clave!='Subsector' && $clave!="Actividad_economica"){
                                $con_aux[$clave] = (float)str_replace (" ", "", $valor); 
                                if ($base == "pib_precios_corrientes" && $denominacion == "2") {
                                    $con_aux[$clave] = (double)$con_aux[$clave]/$consulta_denominacion[0]->$clave;
                                }
                            }
                        }
                        array_push($consulta_aux, $con_aux);
                        $cont_1++;
                    }  
                    
                }
                       
            }
          
        }
        return $consulta_aux;
    }
    
    
    function posiciones($consulta, $consulta_base, $periodo){
        $dat = [];//dd($consulta);
        $consulta_this = $consulta; $count = 0;//dd($consulta);
        foreach ($consulta as $clave => $fila) {//dd($fila);
            try {
                $dat[$clave] = $fila[$periodo];    
            } catch (Exception $e) {
                //sdd($consulta);
            }
            
            $consulta[$count]['valor'] = $consulta_base[$count][$periodo];
            $count++;
        }//dd($consulta);
        array_multisort($dat, SORT_DESC, $consulta);
       //dd($consulta);
        $poss = []; $cont = 1; $contador = 0;

        foreach ($consulta as $con) {
            $poss_aux = [];
            if ($con['valor'] == '0') {
                $poss_aux["e_".$con['cve_ent']] = ['posicion'=>' '];
            }else{
                if ($con['cve_ent'] == "0" || 'a'.$con['cve_ent'] == "a1" || 'a'.$con['cve_ent'] == "a2" || 'a'.$con['cve_ent'] == "a3" || 'a'.$con['cve_ent'] == "a4" || 'a'.$con['cve_ent'] == "a5") {
                    $poss_aux["e_".$con['cve_ent']] = ['posicion'=>' '];
                }else{
                    $poss_aux["e_".$con['cve_ent']] = ['posicion'=>$cont];
                    $cont++;
                }
            }
            
            $poss = array_merge($poss, $poss_aux); 
            $contador++;
        }//dd($poss);
        $cnt = 0;
        foreach ($consulta_this as $consult) {
            $tamano = count($consult);
            
            $consulta_this[$cnt]['posicion'] = $poss["e_".$consult['cve_ent']]['posicion'];
            
            
            $cnt++;
        }
        //dd($consulta_this);
        return $consulta_this;
    }

    function posiciones2($consulta, $consulta_base, $periodo, $base_nacional){
        $dat = [];//dd($consulta_base);
        $consulta_this = $consulta; $count = 0;
        foreach ($consulta as $clave => $fila) {
            $dat[$clave] = $fila[$periodo];
            $consulta[$count]['valor'] = $consulta_base[$count][$periodo];
            $count++;
        }//dd($consulta);
        if ($base_nacional<0) {
            array_multisort($dat, SORT_ASC, $consulta);
        }else{
            array_multisort($dat, SORT_DESC, $consulta);
        }
        
       //dd($consulta);
        $poss = []; $cont = 1; $contador = 0;
        foreach ($consulta as $con) {
            $poss_aux = [];
            if ($con['valor'] == '0') {
                $poss_aux["e_".$con['cve_ent']] = ['posicion'=>' '];
            }else{
                if ($con['cve_ent'] == "0" || 'a'.$con['cve_ent'] == "a1" || 'a'.$con['cve_ent'] == "a2" || 'a'.$con['cve_ent'] == "a3" || 'a'.$con['cve_ent'] == "a4" || 'a'.$con['cve_ent'] == "a5") {
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
            
            $consulta_this[$cnt]['posicion'] = $poss["e_".$consult['cve_ent']]['posicion'];
            
            
            $cnt++;
        }
        //dd($consulta_this);
        return $consulta_this;
    }

}
