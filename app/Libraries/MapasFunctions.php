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
use App\Libraries\PibNacionalFunctions;

class MapasFunctions
{

    function consulta_principal($data){
        $nivel = $data['selectedNivel'];
        $formula = new MapasFunctions();
        switch ($nivel) {
            case '1':
                $tabla = $formula->estado($data);              
                break;
            case '2':
                $tabla = $formula->nacional($data);
                break;
            case '3':
                $tabla = $formula->estado($data);              
                break;
            case '4':
                $tabla = $formula->nacional($data);
                break;
            
            default:
                break;
        }
        //dd(['tabla'=>$tabla]);
        return ($tabla);
    }




    function estado($data){  

        global $entidad;
        $manufactura = $data['selectedCobertura'];
        $base = $data['base'];
        $entidad = $data['selectedEntidad'];
        $nivel = $data['selectedNivel'];
        $region = $data['selectedRegion'];

        $periodos = [];
        $funcion = new MapasFunctions();
        //$cab = array();
        $consulta = array();
        $entidades = [$entidad];
        
        $consulta_aux = $funcion->consulta($base, $manufactura, $periodos, $entidades, $nivel, $region);
        $contribucion_tabla = $funcion->consulta('pib_precios_constantes_particip', $manufactura, $periodos, $entidades, $nivel, $region);
        //dd($consulta_aux);
        $consulta = $funcion->mapa($consulta_aux, $contribucion_tabla, $data['selectedAyo'], $data['selectedAyo_1'], $data['selectedAyo_2'], $data['variables'], $data['selectedPeriodo'], $manufactura, $nivel);

        return $consulta;


    }

    function nacional($data){  
        
        $manufactura = $data['selectedCobertura'];
        $base = $data['base'];
        $entidad = $data['selectedEntidad'];
        $periodos = [];
        $periodo =$data['selectedPeriodo'];
        $entidades_nacional = ['0'];
        $entidades = [$data['selectedEntidad']];
        $nivel = $data['selectedNivel'];
        $region = $data['selectedRegion'];

        $funcion = new MapasFunctions();
        $consulta_entidad = $funcion->consulta($base, $manufactura, $periodos, $entidades, $nivel, $region);
        $consulta_nacional = $funcion->consulta($base, $manufactura, $periodos, $entidades_nacional, '1', $region);

        $contribucion_entidad_tabla = $funcion->consulta('pib_precios_constantes_particip_nacional', $manufactura, $periodos, $entidades, $nivel, $region);
        //dd($consulta_entidad);
        $consulta = array();
        $consulta = $funcion->nacional_mapa($consulta_nacional, $consulta_entidad, $contribucion_entidad_tabla, $data['selectedAyo'], $data['selectedAyo_1'], $data['selectedAyo_2'], $data['variables'], $periodo, $base, $entidad, $region, $nivel, $manufactura); 
        
        //dd(['consulta'=>$consulta]);
        return $consulta;


    }

    
    function nacional_mapa($consulta_nacional, $consulta_entidad, $contribucion_entidad_tabla, $year, $year_1, $year_2, $variables, $periodo, $base, $entidad, $region, $nivel, $manufactura){
        $contador = 0;
        $consulta_mapa = [];   
        //$cab = [];
        $cabeceras = [];

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $funcion = new MapasFunctions();

        $participacion = $valoracion_nacional->posicion_participacion($base, $consulta_entidad, $consulta_nacional, $year, $entidad, $periodo, $year_1, $year_2, $region, $nivel);

        array_push($cabeceras, array('nickname'=>'Concepto', 'header'=>'Concepto'));
        array_push($cabeceras, array('nickname'=>'Valor nacional', 'header'=>'Valor nacional'));
        if ($nivel == '4') {
            array_push($cabeceras, array('nickname'=>'Valor regional', 'header'=>'Valor estatal'));
        }else{
            array_push($cabeceras, array('nickname'=>'Valor estatal', 'header'=>'Valor estatal'));    
        }
        
        foreach ($variables as $variable) {
            $var_id = $variable['id'];
            $var_title = $variable['title'];

            $variacion = $valoracion_nacional->posicion_variacion($base, $consulta_entidad, $consulta_nacional, $year, $entidad, $periodo, $year_1, $year_2, $region, $nivel);
            $variacion_nacional = $valoracion->variacion($consulta_nacional, $periodo, $year_1, $year_2);
            
            if ($var_id == '0') {
                array_push($cabeceras, array('nickname'=>$var_title, 'header'=>$var_id));
                array_push($cabeceras, array('nickname'=>'Posición', 'header'=>'posicion_1'));  
            }elseif ($var_id == '1') {
                array_push($cabeceras, array('nickname'=>$var_title. 'Nacional', 'header'=>$var_id.'nacional'));
                if ($nivel == '4') {
                    array_push($cabeceras, array('nickname'=>$var_title. 'Regional', 'header'=>$var_id.'estatal'));
                }else{
                    array_push($cabeceras, array('nickname'=>$var_title. 'Estatal', 'header'=>$var_id.'estatal'));
                }
                
                array_push($cabeceras, array('nickname'=>'Posición', 'header'=>'posicion_2'));
            }elseif ($var_id == '2') {
                array_push($cabeceras, array('nickname'=>$var_title. '(Puntos porcentuales)', 'header'=>$var_id.'puntos'));
                array_push($cabeceras, array('nickname'=>$var_title. '(En porcentaje)(B)', 'header'=>$var_id.'porcentaje'));
                array_push($cabeceras, array('nickname'=>'Posición', 'header'=>'posicion_3'));

                if ($manufactura == 'Manufactura') {
                    //$contribucion = $valoracion_nacional->posicion_contribucion($base, $consulta_entidad, $consulta_nacional, $year, $entidad, $periodo, $year_1, $year_2, $region, $nivel);
                    $contribucion = $valoracion_nacional->posicion_contribucion_query($base, $consulta_entidad, $consulta_nacional, $contribucion_entidad_tabla, $year, $entidad, $periodo, $year_1, $year_2, $region, $nivel);
                }else{
                    if ($nivel == '4') {
                        $contribucion = $valoracion_nacional->posicion_contribucion($base, $consulta_entidad, $consulta_nacional, $year, $entidad, $periodo, $year_1, $year_2, $region, $nivel);
                    }else{
                        $contribucion = $valoracion_nacional->posicion_contribucion_query($base, $consulta_entidad, $consulta_nacional, $contribucion_entidad_tabla, $year, $entidad, $periodo, $year_1, $year_2, $region, $nivel);
                    }
                    
                }
            //dd($contribucion);
                
            }elseif ($var_id == '3') {
                if ($manufactura == 'Manufactura') {
                    //$diferencia = $valoracion_nacional->posicion_diferencia($base, $consulta_entidad, $consulta_nacional, $year, $entidad, $periodo, $year_1, $year_2, $region, $nivel);
                    $diferencia = $valoracion_nacional->posicion_diferencia_query($base, $consulta_entidad, $consulta_nacional, $contribucion_entidad_tabla, $year, $entidad, $periodo, $year_1, $year_2, $region, $nivel);
                }else{
                    if ($nivel == '4') {
                        $diferencia = $valoracion_nacional->posicion_diferencia($base, $consulta_entidad, $consulta_nacional, $year, $entidad, $periodo, $year_1, $year_2, $region, $nivel);
                    }else{
                        $diferencia = $valoracion_nacional->posicion_diferencia_query($base, $consulta_entidad, $consulta_nacional, $contribucion_entidad_tabla, $year, $entidad, $periodo, $year_1, $year_2, $region, $nivel);
                    }
                    
                }
                array_push($cabeceras, array('nickname'=>$var_title, 'header'=>$var_id));
                array_push($cabeceras, array('nickname'=>'Posición', 'header'=>'posicion_4'));
            }
            
        } 

        if($periodo == 'Promedio'){
            $year='Promedio';
            $consulta_entidad = $valoracion->promedio(['consulta'=>$consulta_entidad, 'year_1'=>$year_1, 'year_2'=>$year_2]);
            $consulta_nacional = $valoracion->promedio(['consulta'=>$consulta_nacional, 'year_1'=>$year_1, 'year_2'=>$year_2]);
        }
        foreach ($consulta_entidad as $con) {
            $con_aux = array();
            
            $con_aux['Concepto'] = $con['Actividad_economica'];
            $con_aux['Valor nacional'] = (float)$consulta_nacional[$contador][$year];
            $con_aux['Valor estatal'] = (float)$con[$year];
            if($variables){
                $con_aux['part'] = $participacion[$contador][$year];
                $con_aux['var_nacional'] = $variacion_nacional[$contador][$year];
                $con_aux['var_estatal'] = $variacion[$contador][$year];
                foreach ($variables as $variable) {
                    $var_id = $variable['id'];
                    $var_title = $variable['title'];

                    if ($var_id == '0') {
                        $con_aux[$var_id] = $participacion[$contador][$year];
                        $con_aux['posicion_1'] = $participacion[$contador]['posicion'];
                    }elseif ($var_id == '1') {
                        $con_aux[$var_id.'nacional'] = $variacion_nacional[$contador][$year];
                        $con_aux[$var_id.'estatal'] = $variacion[$contador][$year];
                        $con_aux['posicion_2'] = $variacion[$contador]['posicion'];
                    }elseif ($var_id == '2') {
                        $con_aux[$var_id.'puntos'] = $contribucion[$contador][$year.'_puntos'];
                        $con_aux[$var_id.'porcentaje'] = $contribucion[$contador][$year.'_porcentual'];
                        $con_aux['posicion_3'] = $contribucion[$contador]['posicion'];
                    }elseif ($var_id == '3') {
                        $con_aux[$var_id] = $diferencia[$contador][$year];
                        $con_aux['posicion_4'] = $diferencia[$contador]['posicion'];
                    }
                    
                } 
            }
            array_push($consulta_mapa, $con_aux);
            $contador++;
        }
        //dd(['consulta_mapa'=>$consulta_mapa]);
        return ['consulta'=>$consulta_mapa, 'headers'=>$cabeceras];
    }
    
    //Commit
    function mapa($consulta, $contribucion_tabla, $year, $year_1, $year_2, $variables, $tipo_periodo, $manufactura, $nivel){
        $contador = 0;
        $consulta_mapa = [];
        //dd($consulta);
        $cabeceras = [];
        $valoracion = new PibFunctions();
        $participacion = $valoracion->participacion($consulta, $tipo_periodo, $year_1, $year_2);
        $variacion = $valoracion->variacion($consulta, $tipo_periodo, $year_1, $year_2);
        
        if ($manufactura == 'Manufactura') {
            $variacion_puntos = $valoracion->variacion_puntos($consulta, $tipo_periodo, $year_1, $year_2);
            $variacion_porcentual = $valoracion->variacion_porcentual($consulta, $tipo_periodo, $year_1, $year_2);
            $eficiencia = $valoracion->eficiencia($consulta, $tipo_periodo, $year_1, $year_2);     
        }else{
            if ($nivel == '3') {
                $variacion_puntos = $valoracion->variacion_puntos($consulta, $tipo_periodo, $year_1, $year_2);
                $variacion_porcentual = $valoracion->variacion_porcentual($consulta, $tipo_periodo, $year_1, $year_2);
                $eficiencia = $valoracion->eficiencia($consulta, $tipo_periodo, $year_1, $year_2);
            }else{
                $variacion_puntos = $contribucion_tabla;  
                $variacion_porcentual = $valoracion->variacion_porcentual_query($consulta, $contribucion_tabla, $tipo_periodo, $year_1, $year_2);
                $eficiencia = $valoracion->eficiencia_query($consulta, $contribucion_tabla, $tipo_periodo, $year_1, $year_2);
                if($tipo_periodo == 'Promedio'){
                    $variacion_puntos = $valoracion->promedio(['consulta'=>$variacion_puntos, 'year_1'=>$year_1, 'year_2'=>$year_2]);
                }
            }
            
            
            
        }
        
        

        if($tipo_periodo == 'Promedio'){
            $consulta = $valoracion->promedio(['consulta'=>$consulta, 'year_1'=>$year_1, 'year_2'=>$year_2]);
            //dd(['year_1'=>$year_1, 'year_2'=>$year_2]);
            $year = 'Promedio';
        }
        if($variables){
            array_push($cabeceras, array('nickname'=>'Concepto', 'header'=>'Concepto'));
            array_push($cabeceras, array('nickname'=>'Valor', 'header'=>'Valor'));
            foreach ($variables as $variable) {
                $var_id = $variable['id'];
                $var_title = $variable['title'];
                if ($var_id == '0') {
                    array_push($cabeceras, array('nickname'=>$var_title, 'header'=>$var_id));
                }elseif ($var_id == '1') {
                    array_push($cabeceras, array('nickname'=>$var_title, 'header'=>$var_id));
                }elseif ($var_id == '2') {
                    array_push($cabeceras, array('nickname'=>$var_title.'(Puntos porcentuales)', 'header'=>$var_id.'puntos'));
                    array_push($cabeceras, array('nickname'=>$var_title.'(En porcentaje)(B)', 'header'=>$var_id.'porcentaje'));
                }elseif ($var_id == '3') {
                    array_push($cabeceras, array('nickname'=>$var_title, 'header'=>$var_id));
                }
                
            } 
        }
        foreach ($consulta as $con) {
            $con_aux = array();
            //array_push($cabeceras, array('nickname'=>'Concepto', 'cabecera'=>'Concepto'));
            //array_push($cabeceras, array('nickname'=>'Valor', 'cabecera'=>'Valor'));
            $con_aux['Concepto'] = $con['Actividad_economica'];
            $con_aux['Valor'] = (float)$con[$year];
            if($variables){
                $con_aux['part'] = $participacion[$contador][$year];
                $con_aux['var'] = $variacion[$contador][$year];
                foreach ($variables as $variable) {
                    $var_id = $variable['id'];
                    $var_title = $variable['title'];
                    if ($var_id == '0') {
                        //array_push($cabeceras, array('nickname'=>$var_title, 'cabecera'=>$var_id));
                        $con_aux[$var_id] = $participacion[$contador][$year];
                    }elseif ($var_id == '1') {
                        //array_push($cabeceras, array('nickname'=>$var_title, 'cabecera'=>$var_id));
                        $con_aux[$var_id] = $variacion[$contador][$year];
                    }elseif ($var_id == '2') {
                        //array_push($cabeceras, array('nickname'=>$var_title.'(Puntos porcentuales)', 'cabecera'=>$var_id));
                        //array_push($cabeceras, array('nickname'=>$var_title.'(En porcentaje)(B)', 'cabecera'=>$var_id));
                        $con_aux[$var_id.'puntos'] = $variacion_puntos[$contador][$year];
                        $con_aux[$var_id.'porcentaje'] = $variacion_porcentual[$contador][$year];
                    }elseif ($var_id == '3') {
                        //array_push($cabeceras, array('nickname'=>$var_title, 'cabecera'=>$var_id));
                        $con_aux[$var_id] = $eficiencia[$contador][$year];
                    }
                    
                } 
            }
            array_push($consulta_mapa, $con_aux);
            $contador++;
        }
        //dd($consulta_mapa);
        return ['consulta'=>$consulta_mapa, 'headers'=>$cabeceras];
    }

    function consulta($base, $manufactura, $periodos, $entidades, $nivel, $region){
        global $arr_entidades;
        $consulta = [];
        $consult_entidades = [];
        $model_entidades = [];
        $arr_entidades = $entidades;
        if ($nivel == "3" || $nivel == "4") {
            $consult_entidades = DB::table('mge')->select('id', 'cve_ent')->orderBy('cve_ent')->where('clave_region', '=', $region)->get();
            foreach ($consult_entidades as $entidad) {
                # code...
                $model_entidades=array_merge($model_entidades,array($entidad->cve_ent));      
            }
            $arr_entidades = $model_entidades;
        }

        if ($manufactura == 'Completo') {
            $consulta_aux = DB::table($base)->whereIn('cve_ent', $arr_entidades)->orderBy('id')->get();
        }
        elseif ($manufactura == 'Manufactura') {
            $consulta_aux = DB::table($base)->whereIn('cve_ent', $arr_entidades)->where('Sector', '=', '31-33 Industrias manufactureras')->orderBy('id')->get();          
        }elseif ($manufactura == 'Sin Manufactura') {
            //*****Entidad******
            $consulta_aux = DB::table($base)->whereNested(function($query){
                global $arr_entidades;
                $query->whereIn('cve_ent', $arr_entidades);
                $query->where('Sector', '!=', '31-33 Industrias manufactureras');
            })->orWhere(function($query){
                global $arr_entidades;
                $query->whereIn('cve_ent', $arr_entidades);
                $query->where('Sector', '=', '31-33 Industrias manufactureras');
                $query->where('Subsector', '=', '31-33 Industrias manufactureras');
            })->orWhere(function($query){
                global $arr_entidades;
                $query->whereIn('cve_ent', $arr_entidades);
                $query->whereNull('Sector');
            })->orderBy('id')->get();
        }

        foreach ($consulta_aux as $con) {
            //dd($con);
            $con_aux = array();
            if ($con->Sector==null && $con->Subsector==null) {
                $title_1 = $con->Tipo;
            }else if ($con->Sector!=null && $con->Subsector==null) {
                $title_1 = $con->Sector;
            }else if ($con->Sector!=null && $con->Subsector!=null) {
                $title_1 = $con->Subsector;
            }
            $con_aux = array_merge($con_aux,array('Actividad_economica'=>$title_1));
            foreach ($con as $clave => $valor) {
                //$title = $key->column_name;
                //dd($clave);
                if ($clave!='id' && $clave!='cve_ent' && $clave!='NOM_ENT' && $clave!='Tipo' && $clave!='Sector' && $clave!='Subsector') {

                        $con_aux[$clave] = (float)str_replace (" ", "", $valor);                    
                }
            }
            array_push($consulta, $con_aux);
        }

        $back_consulta = [];
        if ($nivel == '3' || $nivel == '4') {
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
        return $consulta;
    }
     
    function consulta_x_actividad($base, $actividad, $region, $nivel){
        global $actividad_this;
        $actividad_this = $actividad;

        $consulta = [];
        $consult_entidades = [];
        if ($nivel == "3" || $nivel == "4") {
            $consult_entidades = DB::table('mge')->select('id', 'cve_ent', 'clave_region')->get();
            //dd($consult_entidades);            
        }
        $consulta_aux = DB::table($base)->whereNested(function($query){
            global $actividad_this;
            $query->where('Tipo', '=', $actividad_this);
            $query->whereNull('Sector');
            $query->whereNull('Subsector');
            //$query->where('cve_ent', '!=', '0' );
        })->orWhere(function($query){
            global $actividad_this;
            $query->where('Sector', '=', $actividad_this);
            $query->whereNull('Subsector');
            //$query->where('cve_ent', '!=', '0' );
        })->orWhere(function($query){
            global $actividad_this;
            $query->where('Subsector', '=', $actividad_this);
            //$query->where('cve_ent', '!=', '0' );
        })->orderBy('id')->get();
        //dd($consulta_aux);
        foreach ($consulta_aux as $con) {
            $con_aux = array();
            foreach ($con as $clave => $valor) {
                if ($clave == 'NOM_ENT' || $clave == 'cve_ent') {
                    $con_aux[$clave] = $valor;
                }else if($clave!='id' && $clave!='Tipo' && $clave!='Sector' && $clave!='Subsector'){

                    $con_aux[$clave] = (float)str_replace (" ", "", $valor); 
                }
            }
            array_push($consulta, $con_aux);
        }//dd($consulta);
        $back_consulta = [];
        if ($nivel == "3" || $nivel == "4") {
            foreach ($consulta as $entidad) {
                $flag = true;
                $cve_region = '';
                foreach ($consult_entidades as $entidad_region) {
                    $aux_cve_ent = $entidad_region->cve_ent;

                    //$entidad_region = json_decode($entidad_region, true);
                    if($entidad['cve_ent'] == $aux_cve_ent){
                        $cve_region = $entidad_region->clave_region;
                        foreach ($back_consulta as $key => $value) {
                            if($value['cve_ent'] == $entidad_region->clave_region){//dd($back_consulta);
                                foreach ($value as $back_key => $back_value) {
                                    if($back_key != 'cve_ent'){
                                        $back_consulta[$key][$back_key] = $back_consulta[$key][$back_key] + $entidad[$back_key];    
                                    }
                                    
                                }                            
                                $flag = false;
                            }
                        }
                    }
                }
                if($flag){
                    /*foreach ($consult_entidades as $entidad_region) {
                        if($entidad['cve_ent'] == )
                    }*/
                    //dd($cve_region);
                    $aux_entidad = $entidad;
                    $aux_entidad['cve_ent'] = $cve_region;
                    //dd($entidad);
                    array_push($back_consulta, $aux_entidad);
                    //dd($back_consulta);
                }
                $flag = true;
            }
            $consulta = $back_consulta;
        }
        //dd($back_consulta);
        return $consulta;
    }
    

}
