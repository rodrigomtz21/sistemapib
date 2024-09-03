<?php

namespace App\Libraries;

use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;
use App\Libraries\pibFunction;
use App\Libraries\PibFunctions;
use App\Libraries\MapasFunctions;

class PibNacionalFunctions
{

    
    function participacion($data_entidad, $data_nacional, $tipo_periodo, $year_1, $year_2){
        //dd($data_nacional);
        $tabla = array();
        $formula = new PibFunctions();
        $cont = 0;
        /*if($tipo_periodo == 'Promedio'){
            $data_entidad = $formula->promedio(['consulta'=>$data_entidad, 'year_1'=>$year_1, 'year_2'=>$year_2]);
            $data_nacional = $formula->promedio(['consulta'=>$data_nacional, 'year_1'=>$year_1, 'year_2'=>$year_2]);
        }*/
        foreach ($data_nacional as $consulta) {
            $tabla_aux = array();
            foreach ($consulta as $clave => $valor) {
                if ($clave != 'cve_ent' && $clave != 'NOM_ENT' && $clave != 'Actividad_economica') {
                    try {
                        $tabla_aux[$clave] = ($data_entidad[$cont][$clave]/$valor)*100;
                    } catch (Exception $e) {
                        $tabla_aux[$clave] = 0;                   
                    }
                }else{

                    $tabla_aux[$clave] =  $valor;
                }              
            }
            array_push($tabla, $tabla_aux);
            $cont++;
        }
        //return($tabla);
        if($tipo_periodo == 'Ayo'){
            return($tabla);
        }else{
            return($formula->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        }
    }
    function posicion_participacion($base, $consulta_entidad, $consulta_nacional, $year, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel){
        $consulta = [];
        $tabla_participacion = [];

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $funcion = new MapasFunctions();

        $participacion = $valoracion_nacional->participacion($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        //dd($participacion);
        if($tipo_periodo == 'Promedio'){$year='Promedio';}
        foreach ($participacion as $key) {
            $tabla = [];
            $tabla['Actividad_economica'] = $key['Actividad_economica'];
            $tabla[$year] = $key[$year];
            $consulta = $funcion->consulta_x_actividad($base, $key['Actividad_economica'], $region, $nivel);
            $participacion_nacional = $valoracion->participacion($consulta, $tipo_periodo, $year_1, $year_2);
            
            if ($tipo_periodo == 'Promedio') {
                $consulta = $valoracion->promedio(['consulta'=>$consulta, 'year_1'=>$year_1, 'year_2'=>$year_2]);
            }
            $tabla['posicion'] = $valoracion_nacional->posicion($participacion_nacional, $year, $entidad, $consulta, $region, $nivel);   
            array_push($tabla_participacion, $tabla);
        }
        return($tabla_participacion);
    }


    function posicion_variacion($base, $consulta_entidad, $consulta_nacional, $year, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel){
        $consulta = [];
        $tabla_variacion = [];
        
        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $funcion = new MapasFunctions();

        $variacion = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        if($tipo_periodo == 'Promedio'){$year='Promedio';}
        foreach ($variacion as $key) {
            $tabla = [];
            $tabla['Actividad_economica'] = $key['Actividad_economica'];
            $tabla[$year] = $key[$year];

            $consulta = $funcion->consulta_x_actividad($base, $key['Actividad_economica'], $region, $nivel);
            $variacion_nacional = $valoracion->variacion($consulta, $tipo_periodo, $year_1, $year_2);
            if ($tipo_periodo == 'Promedio') {
                $consulta = $valoracion->promedio(['consulta'=>$consulta, 'year_1'=>$year_1, 'year_2'=>$year_2]);
            }
            $tabla['posicion'] = $valoracion_nacional->posicion($variacion_nacional, $year, $entidad, $consulta, $region, $nivel);//dd($consulta);
            array_push($tabla_variacion, $tabla);
        }
        return($tabla_variacion);
    }










    function contribucion_crecimiento_puntos($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2){
        //dd($consulta_nacional);
        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $participacion = [];

        //$variacion = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        $variacion = $valoracion->variacion($consulta_entidad, 'Ayo', $year_1, $year_2);
        $tabla = array(); 
        $cont = 0;
        //$participacion = $valoracion_nacional->participacion($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        $participacion = $valoracion_nacional->participacion($consulta_entidad, $consulta_nacional, 'Ayo', $year_1, $year_2);
        //dd($participacion);
        foreach ($variacion as $consulta) {
            $tabla_aux = array(); 
            $title_aux = "";
            foreach ($consulta as $clave => $valor) {
                if ($clave != 'cve_ent' && $clave != 'NOM_ENT' && $clave != 'Actividad_economica') {
                    if (($title_aux == 'cve_ent' || $title_aux == 'NOM_ENT' || $title_aux == 'Actividad_economica') && $tipo_periodo != 'Promedio') {
                        $tabla_aux[$clave] =  "";
                    }else{
                        //dd($participacion);
                        $tabla_aux[$clave] =  $participacion[$cont][$clave]*$valor/100;
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
            return($valoracion->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        }
        //return($tabla);
    }

    function contribucion_crecimiento_porcentual($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2){
        $cont = 0;//dd($consulta_nacional);
        $tabla = array();
        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();

        //$contribucion_porcentual = $valoracion_nacional->contribucion_crecimiento_puntos($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        //$variacion = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);

        $contribucion_porcentual = $valoracion_nacional->contribucion_crecimiento_puntos($consulta_entidad, $consulta_nacional, 'Ayo', $year_1, $year_2);
        $variacion = $valoracion->variacion($consulta_nacional, 'Ayo', $year_1, $year_2);
        
        foreach ($variacion as $consulta) {
            $tabla_aux = array(); 
            $title_aux = "";
            foreach ($consulta as $clave => $valor) {
                if ($clave != 'cve_ent' && $clave != 'NOM_ENT' && $clave != 'Actividad_economica') {
                    if (($title_aux == 'cve_ent' || $title_aux == 'NOM_ENT' || $title_aux == 'Actividad_economica') && $tipo_periodo != 'Promedio') {
                        $tabla_aux[$clave] =  "";
                    }else{
                        try {
                            $tabla_aux[$clave] =  $contribucion_porcentual[$cont][$clave]/$valor*100;
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
            $cont++;
        }
        if($tipo_periodo == 'Ayo'){
            return($tabla);
        }else{
            return($valoracion->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        }
        //return($tabla);
    }
    function contribucion_crecimiento_porcentual_query($consulta_entidad, $contribucion_entidad_tabla, $consulta_nacional, $tipo_periodo, $year_1, $year_2){
        $cont = 0;//dd($consulta_nacional);
        $tabla = array();
        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();

        //$contribucion_porcentual = $valoracion_nacional->contribucion_crecimiento_puntos($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        //$variacion = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);

        //$contribucion_porcentual = $valoracion_nacional->contribucion_crecimiento_puntos($consulta_entidad, $consulta_nacional, 'Ayo', $year_1, $year_2);
        $contribucion_porcentual = $contribucion_entidad_tabla;//dd(['CP'=>$contribucion_porcentual]);
        /*if($tipo_periodo == 'Promedio'){
            $contribucion_porcentual = $valoracion->promedio(['consulta'=>$contribucion_porcentual, 'year_1'=>$year_1, 'year_2'=>$year_2]);
        }*///dd(['cp_promedio'=>$contribucion_porcentual]);
        $variacion = $valoracion->variacion($consulta_nacional, 'Ayo', $year_1, $year_2);
        //dd(['variacion_nacional'=>$variacion]);
        foreach ($variacion as $consulta) {
            $tabla_aux = array(); 
            $title_aux = "";
            foreach ($consulta as $clave => $valor) {
                if ($clave != 'cve_ent' && $clave != 'NOM_ENT' && $clave != 'Actividad_economica' && $clave != '2003') {
                    if (($title_aux == 'cve_ent' || $title_aux == 'NOM_ENT' || $title_aux == 'Actividad_economica') && $tipo_periodo != 'Promedio') {
                        $tabla_aux[$clave] =  "";
                    }else{
                        try {//dd($valor);
                            $tabla_aux[$clave] =  $contribucion_porcentual[$cont][$clave]/$valor*100;
                        } catch (Exception $e) {
                            $tabla_aux[$clave] =  0.1111;
                        }
                        
                    }                   
                }else{                    
                    $tabla_aux[$clave] =  $valor;
                }
                $title_aux =$clave;
            }
            array_push($tabla, $tabla_aux);
            $cont++;
        }//dd(['cpTabla'=>$tabla]);
        if($tipo_periodo == 'Ayo'){
            return($tabla);
        }else{
            return($valoracion->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        }
        //return($tabla);
    }

    function posicion_contribucion($base, $consulta_entidad, $consulta_nacional, $year, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel){
        $consulta = [];
        $tabla_contribucion = [];
        $cont = 0;

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $funcion = new MapasFunctions();


        $variacion_estatal = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        $variacion_nacional = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);

        $contribucion_puntos = $valoracion_nacional->contribucion_crecimiento_puntos($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        $contribucion_porcentual = $valoracion_nacional->contribucion_crecimiento_porcentual($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        
        if($tipo_periodo == 'Promedio'){$year='Promedio';}
        foreach ($contribucion_puntos as $key) {
            $tabla = [];
            $tabla['Actividad_economica'] = $key['Actividad_economica'];
            $tabla[$year.'_puntos'] = $key[$year];
            $tabla[$year.'_porcentual'] = $contribucion_porcentual[$cont][$year];

            $consulta = $funcion->consulta_x_actividad($base, $key['Actividad_economica'], $region, $nivel);
            $contribucion_nacional = $valoracion->variacion_puntos($consulta, $tipo_periodo, $year_1, $year_2);
            
            if ($tipo_periodo == 'Promedio') {
                $consulta = $valoracion->promedio(['consulta'=>$consulta, 'year_1'=>$year_1, 'year_2'=>$year_2]);
                
            }
            $tabla['posicion'] = $valoracion_nacional->posicion($contribucion_nacional, $year, $entidad, $consulta, $region, $nivel);
            array_push($tabla_contribucion, $tabla);
            $cont++;
        }
        return($tabla_contribucion);
    }
    function posicion_contribucion_query($base, $consulta_entidad, $consulta_nacional, $contribucion_entidad_tabla, $year, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel){
        $consulta = [];
        $tabla_contribucion = [];
        $cont = 0;

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $funcion = new MapasFunctions();


        $variacion_estatal = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        $variacion_nacional = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);
        //dd($year_1);
        //$contribucion_puntos = $valoracion_nacional->contribucion_crecimiento_puntos($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        $contribucion_puntos = $contribucion_entidad_tabla;
        if($tipo_periodo == 'Promedio'){
            $year='Promedio';
            $contribucion_puntos = $valoracion->promedio(['consulta'=>$contribucion_puntos, 'year_1'=>$year_1, 'year_2'=>$year_2]);
        }//dd($contribucion_puntos);
        $contribucion_porcentual = $valoracion_nacional->contribucion_crecimiento_porcentual_query($consulta_entidad, $contribucion_entidad_tabla, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        //dd(['contribucion porcentual'=>$contribucion_porcentual]);
        //if($tipo_periodo == 'Promedio'){$year='Promedio';}
        foreach ($contribucion_puntos as $key) {
            $tabla = [];
            $tabla['Actividad_economica'] = $key['Actividad_economica'];
            $tabla[$year.'_puntos'] = $key[$year];
            $tabla[$year.'_porcentual'] = $contribucion_porcentual[$cont][$year];
            //dd(['region'=>$nivel]);
            $consulta = $funcion->consulta_x_actividad($base, $key['Actividad_economica'], $region, $nivel);
            //dd($consulta);
            //$contribucion_nacional = $valoracion->variacion_puntos($consulta, $tipo_periodo, $year_1, $year_2);
            $contribucion_nacional = $funcion->consulta_x_actividad('pib_precios_constantes_particip_nacional', $key['Actividad_economica'], $region, $nivel);
            //dd($contribucion_nacional);
            if ($tipo_periodo == 'Promedio') {
                $consulta = $valoracion->promedio(['consulta'=>$consulta, 'year_1'=>$year_1, 'year_2'=>$year_2]);
                $contribucion_nacional = $valoracion->promedio(['consulta'=>$contribucion_nacional, 'year_1'=>$year_1, 'year_2'=>$year_2]);
                
            }//dd(['contribucion_nacional'=>$contribucion_nacional]);
            $tabla['posicion'] = $valoracion_nacional->posicion($contribucion_nacional, $year, $entidad, $consulta, $region, $nivel);
            array_push($tabla_contribucion, $tabla);
            $cont++;
        }
        return($tabla_contribucion);
    }






    function diferencia($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2){
        $valoracion_nacional = new PibNacionalFunctions();
        $valoracion = new PibFunctions();

        //$contribucion_porcentual = $valoracion_nacional->contribucion_crecimiento_porcentual($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        //$participacion = $valoracion_nacional->participacion($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        $contribucion_porcentual = $valoracion_nacional->contribucion_crecimiento_porcentual($consulta_entidad, $consulta_nacional, 'Ayo', $year_1, $year_2);
        $participacion = $valoracion_nacional->participacion($consulta_entidad, $consulta_nacional, 'Ayo', $year_1, $year_2);

        $tabla = array(); 
        $cont = 0;
        foreach ($contribucion_porcentual as $consulta) {
            $tabla_aux = array(); 
            $title_aux = "";
            foreach ($consulta as $clave => $valor) {
                if ($clave != 'cve_ent' && $clave != 'NOM_ENT' && $clave != 'Actividad_economica') {
                    if (($title_aux == 'cve_ent' || $title_aux == 'NOM_ENT' || $title_aux == 'Actividad_economica') && $tipo_periodo != 'Promedio') {
                        $tabla_aux[$clave] =  "";
                    }else{
                        $tabla_aux[$clave] =  $valor-$participacion[$cont][$clave];
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
            return($valoracion->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        }
        //return($tabla);
        
    }
    function diferencia_query($consulta_entidad, $consulta_nacional, $contribucion_entidad_tabla, $tipo_periodo, $year_1, $year_2){
        $valoracion_nacional = new PibNacionalFunctions();
        $valoracion = new PibFunctions();

        //$contribucion_porcentual = $valoracion_nacional->contribucion_crecimiento_porcentual($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        //$participacion = $valoracion_nacional->participacion($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        $contribucion_porcentual = $valoracion_nacional->contribucion_crecimiento_porcentual_query($consulta_entidad, $contribucion_entidad_tabla, $consulta_nacional, 'Ayo', $year_1, $year_2);

        $participacion = $valoracion_nacional->participacion($consulta_entidad, $consulta_nacional, 'Ayo', $year_1, $year_2);

        $tabla = array(); 
        $cont = 0;
        foreach ($contribucion_porcentual as $consulta) {
            $tabla_aux = array(); 
            $title_aux = "";
            foreach ($consulta as $clave => $valor) {
                if ($clave != 'cve_ent' && $clave != 'NOM_ENT' && $clave != 'Actividad_economica') {
                    if (($title_aux == 'cve_ent' || $title_aux == 'NOM_ENT' || $title_aux == 'Actividad_economica') && $tipo_periodo != 'Promedio') {
                        $tabla_aux[$clave] =  "";
                    }else{
                        $tabla_aux[$clave] =  $valor-$participacion[$cont][$clave];
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
            return($valoracion->promedio(['consulta'=>$tabla, 'year_1'=>$year_1, 'year_2'=>$year_2]));
        }
        //return($tabla);
        
    }
    function posicion_diferencia($base, $consulta_entidad, $consulta_nacional, $year, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel){
        $consulta = [];
        $tabla_diferencia = [];
        $cont = 0;

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $funcion = new MapasFunctions();
        
        $variacion_estatal = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        $variacion_nacional = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);
        $diferencia = $valoracion_nacional->diferencia($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        
        if($tipo_periodo == 'Promedio'){$year='Promedio';}
        foreach ($diferencia as $key) {
            $tabla = [];
            $tabla['Actividad_economica'] = $key['Actividad_economica'];
            $tabla[$year] = $key[$year];

            $consulta = $funcion->consulta_x_actividad($base, $key['Actividad_economica'], $region, $nivel);
            $contribucion_nacional = $valoracion->eficiencia($consulta, $tipo_periodo, $year_1, $year_2);
                
            if ($tipo_periodo == 'Promedio') {
                $consulta = $valoracion->promedio(['consulta'=>$consulta, 'year_1'=>$year_1, 'year_2'=>$year_2]);
            }
            $tabla['posicion'] = $valoracion_nacional->posicion_2($contribucion_nacional, $year, $entidad, $variacion_estatal[$cont], $variacion_nacional[$cont], $consulta, $region, $nivel);

            array_push($tabla_diferencia, $tabla);
            $cont++;
        }
        return($tabla_diferencia);
    }
    function posicion_diferencia_query($base, $consulta_entidad, $consulta_nacional, $contribucion_entidad_tabla, $year, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel){
        $consulta = [];
        $tabla_diferencia = [];
        $cont = 0;

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $funcion = new MapasFunctions();
        
        $variacion_estatal = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        $variacion_nacional = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);
        $diferencia = $valoracion_nacional->diferencia_query($consulta_entidad, $consulta_nacional, $contribucion_entidad_tabla, $tipo_periodo, $year_1, $year_2);
        //dd($diferencia);
        if($tipo_periodo == 'Promedio'){$year='Promedio';}
        foreach ($diferencia as $key) {
            $tabla = [];
            $tabla['Actividad_economica'] = $key['Actividad_economica'];
            $tabla[$year] = $key[$year];
            //dd(['base'=>$base]);
            $consulta = $funcion->consulta_x_actividad($base, $key['Actividad_economica'], $region, $nivel);
            $contribucion = $funcion->consulta_x_actividad('pib_precios_constantes_particip_nacional', $key['Actividad_economica'], $region, $nivel);
            //dd(['consulta x actividad'=>$contribucion]);
            $contribucion_nacional = $valoracion->eficiencia_query($consulta, $contribucion, $tipo_periodo, $year_1, $year_2);
            //dd(['diferencia'=>$contribucion_nacional]);
            if ($tipo_periodo == 'Promedio') {
                $consulta = $valoracion->promedio(['consulta'=>$consulta, 'year_1'=>$year_1, 'year_2'=>$year_2]);
            }
            $tabla['posicion'] = $valoracion_nacional->posicion_2($contribucion_nacional, $year, $entidad, $variacion_estatal[$cont], $variacion_nacional[$cont], $consulta, $region, $nivel);

            array_push($tabla_diferencia, $tabla);
            $cont++;
        }
        return($tabla_diferencia);
    }

    function posicion($data, $periodo, $entidad, $valores, $region, $nivel){
        if ($nivel == "3" || $nivel == "4") {
            $entidad = $region;
        }
        $posicion = 0;
        $val_entidad = 0; 
        //dd($data);
        foreach ($data as $consulta) {//dd($entidad);
            if($consulta['cve_ent'] == $entidad){
                $val_entidad = $consulta[$periodo];//dd($consulta);
            }
        }
        if ($val_entidad == 0) {
            $posicion = 0;
        }else{
            $cnt = 0;
            foreach ($data as $consulta) {
                if($consulta['cve_ent'] != '0'){/**Evita considerar el valor nacional en el claculo***/
                    if ($valores[$cnt][$periodo] != 0) {/**Evita valores en 0*******/
                        if ($val_entidad < $consulta[$periodo]) {
                            if ($consulta[$periodo]!=0) {
                                    $posicion ++;
                                }
                        }elseif ($val_entidad == $consulta[$periodo]) {
                            $posicion ++;
                        }
                    }
                    
                }
                $cnt++;
            }    
        }
        

        return($posicion);
        
    }

    function posicion_2($data, $periodo, $entidad, $base_entidad, $base_nacional, $valores, $region, $nivel){
        if ($nivel == "3" || $nivel == "4") {
            $entidad = $region;
        }
        $posicion = 0;//dd($data);
        $val_entidad = 0;
        //dd($entidad);   
        //if($periodo == 'Promedio'){$periodo='Promedio';} 
        foreach ($data as $consulta) {
            if($consulta['cve_ent'] == $entidad){
                $val_entidad = $consulta[$periodo];
            }
        }
        //dd(['valor entidad'=>$val_entidad]);
        if ($val_entidad == 0) {
            $posicion = 0;
        }else{
            $cnt = 0;
            foreach ($data as $consulta) {
                if($consulta['cve_ent'] != '0'){
                    if ($valores[$cnt][$periodo] != 0) {
                        if ($base_entidad[$periodo]>=0 && $base_nacional[$periodo]<0) {
                            //if ($val_entidad > $consulta[$periodo]) {
                            if ($val_entidad > $consulta[$periodo]) {
                                if ($consulta[$periodo]!=0) {
                                    $posicion ++;
                                }  
                            }elseif ($val_entidad == $consulta[$periodo]) {
                                $posicion ++;
                            }
                        }elseif ($base_entidad[$periodo]<0 && $base_nacional[$periodo]<0) {
                            if ($val_entidad > $consulta[$periodo]) {
                                if ($consulta[$periodo]!=0) {
                                    $posicion ++;
                                }   
                            }elseif ($val_entidad == $consulta[$periodo]) {
                                $posicion ++;
                            }
                        }else{
                            if ($val_entidad < $consulta[$periodo]) {
                                if ($consulta[$periodo]!=0) {
                                    $posicion ++;
                                } 
                            }elseif ($val_entidad == $consulta[$periodo]) {
                                $posicion ++;
                            }
                        }
                    } 
                }
                $cnt++;
            }
        }
        
        return($posicion);  
    }

    
}
