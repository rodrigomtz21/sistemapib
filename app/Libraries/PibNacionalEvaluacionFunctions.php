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
use App\Libraries\MapasFunctions;
use App\Libraries\PibNacionalFunctions;

class PibNacionalEvaluacionFunctions
{

    
    function posicion_valoracion($base, $valores, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel, $promedios){
        $consulta = [];
        $tabla_participacion = [];

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $funcion = new MapasFunctions();
        $valores = $evaluacion_nacional->add_promedios($valores, $promedios);
        foreach ($valores as $raw) {
            $tabla = [];
            $actividad = $raw['Actividad_economica'];
            $consulta = $funcion->consulta_x_actividad($base, $actividad, $region, $nivel);
            $consulta = $evaluacion_nacional->add_promedios($consulta, $promedios);

            foreach ($raw as $key => $value) {
                if ($key == 'Actividad_economica') {
                    $tabla[$key] = $value;    
                    //$actividad = $value;
                }else{
                    $tabla[$key] = $value;
                    
                    $tabla['posicion_'.$key] = $valoracion_nacional->posicion($consulta, $key, $entidad, $consulta, $region, $nivel);   
                    
                }
            }
            array_push($tabla_participacion, $tabla);
        }
        return($tabla_participacion);
    }
    function posicion_participacion($base, $participacion, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel, $promedios){
        $consulta = [];
        $tabla_participacion = [];

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $funcion = new MapasFunctions();

        foreach ($participacion as $raw) {
            $tabla = [];
            $actividad = $raw['Actividad_economica'];
            $consulta = $funcion->consulta_x_actividad($base, $actividad, $region, $nivel);
            //$consulta = $evaluacion_nacional->add_promedios($consulta, $promedios);

            $participacion_nacional = $valoracion->participacion($consulta, $tipo_periodo, $year_1, $year_2);
            $participacion_nacional = $evaluacion_nacional->add_promedios($participacion_nacional, $promedios);

            $consulta = $evaluacion_nacional->add_promedios($consulta, $promedios);
            foreach ($raw as $key => $value) {
                if ($key == 'Actividad_economica') {
                    $tabla[$key] = $value;    
                    //$actividad = $value;
                }else{
                    $tabla[$key] = $value;
                    
                    $tabla['posicion_'.$key] = $valoracion_nacional->posicion($participacion_nacional, $key, $entidad, $consulta, $region, $nivel);   
                    
                }
            }
            array_push($tabla_participacion, $tabla);
        }
        return($tabla_participacion);
    }
    

    function posicion_variacion($base, $consulta_entidad, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel, $promedios){
        $consulta = [];
        $tabla_variacion = [];
        
        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $funcion = new MapasFunctions();

        $variacion = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        $variacion = $evaluacion_nacional->add_promedios($variacion, $promedios);
        
        foreach ($variacion as $raw) {
            $tabla = [];
            $actividad = $raw['Actividad_economica'];
            $consulta = $funcion->consulta_x_actividad($base, $actividad, $region, $nivel);
            
            
            $variacion_nacional = $valoracion->variacion($consulta, $tipo_periodo, $year_1, $year_2);
            $variacion_nacional = $evaluacion_nacional->add_promedios($variacion_nacional, $promedios);

            $consulta = $evaluacion_nacional->add_promedios($consulta, $promedios);
            
            foreach ($raw as $key => $value) {
                if ($key == 'Actividad_economica') {
                    $tabla[$key] = $value; 
                }else{
                    $tabla[$key] = $value;
                    
                    $tabla['posicion_'.$key] = $valoracion_nacional->posicion($variacion_nacional, $key, $entidad, $consulta, $region, $nivel);    
                }
            }
            array_push($tabla_variacion, $tabla);
        }
        return($tabla_variacion);
    }
    function contribucion_crecimiento_puntos($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2, $promedios){
        //dd($consulta_nacional);
        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $participacion = [];

        $variacion = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        //$variacion = $evaluacion_nacional->add_promedios($variacion, $promedios);

        $tabla = array(); 
        $cont = 0;
        //$consulta_entidad = $evaluacion_nacional->add_promedios($consulta_entidad, $promedios);
        //$consulta_nacional = $evaluacion_nacional->add_promedios($consulta_nacional, $promedios);
        $participacion = $valoracion_nacional->participacion($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
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
        return($tabla);
    }

    function contribucion_crecimiento_porcentual($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2, $promedios){
        $cont = 0;//dd($consulta_nacional);
        $tabla = array();
        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();

        $contribucion_porcentual = $evaluacion_nacional->contribucion_crecimiento_puntos($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2, $promedios);

        $variacion = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);
        //$variacion = $evaluacion_nacional->add_promedios($variacion, $promedios);

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
        return($tabla);
    }
    function contribucion_crecimiento_porcentual_query($consulta_entidad, $consulta_nacional, $contribucion_tabla, $tipo_periodo, $year_1, $year_2, $promedios){
        $cont = 0;//dd($consulta_nacional);
        $tabla = array();
        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();

        $contribucion_porcentual = $contribucion_tabla;

        $variacion = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);
        //$variacion = $evaluacion_nacional->add_promedios($variacion, $promedios);

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
        return($tabla);
    }
    function posicion_contribucion_puntos($base, $consulta_entidad, $consulta_nacional, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel, $promedios){
        $consulta = [];
        $tabla_contribucion = [];
        $cont = 0;

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $funcion = new MapasFunctions();


        /*$variacion_estatal = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        $variacion_estatal = $evaluacion_nacional->add_promedios($variacion_estatal, $promedios);
        $variacion_nacional = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);
        $variacion_nacional = $evaluacion_nacional->add_promedios($variacion_nacional, $promedios);*/

        $contribucion_puntos = $evaluacion_nacional->contribucion_crecimiento_puntos($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2, $promedios);
        $contribucion_puntos = $evaluacion_nacional->add_promedios($contribucion_puntos, $promedios);

        foreach ($contribucion_puntos as $raw) {
            $tabla = [];
            $actividad = $raw['Actividad_economica'];
            $consulta = $funcion->consulta_x_actividad($base, $actividad, $region, $nivel);
            $contribucion_nacional = $evaluacion_nacional->variacion_puntos($consulta, $tipo_periodo, $year_1, $year_2, $promedios);
            $contribucion_nacional = $evaluacion_nacional->add_promedios($contribucion_nacional, $promedios);
            $consulta = $evaluacion_nacional->add_promedios($consulta, $promedios);
            foreach ($raw as $key => $value) {
                if ($key == 'Actividad_economica') {
                    $tabla[$key] = $value;    
                    //$actividad = $value;
                }else{
                    $tabla[$key] = $value;
                    
                    
                    $tabla['posicion_'.$key] = $valoracion_nacional->posicion($contribucion_nacional, $key, $entidad, $consulta, $region, $nivel);    
                }
            }
            array_push($tabla_contribucion, $tabla);
        }
        return($tabla_contribucion);
    }

function posicion_contribucion_porcentaje($base, $consulta_entidad, $consulta_nacional, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel, $promedios){
        $consulta = [];
        $tabla_contribucion = [];
        $cont = 0;

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $funcion = new MapasFunctions();


        //$variacion_estatal = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        //$variacion_nacional = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);

        //$contribucion_puntos = $valoracion_nacional->contribucion_crecimiento_puntos($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2, $promedios);
        $contribucion_porcentual = $evaluacion_nacional->contribucion_crecimiento_porcentual($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2, $promedios);
        $contribucion_porcentual = $evaluacion_nacional->add_promedios($contribucion_porcentual, $promedios);
        
        foreach ($contribucion_porcentual as $raw) {
            $tabla = [];
            $actividad = $raw['Actividad_economica'];

            $consulta = $funcion->consulta_x_actividad($base, $actividad, $region, $nivel);
            $contribucion_nacional = $evaluacion_nacional->variacion_puntos($consulta, $tipo_periodo, $year_1, $year_2, $promedios);
            $contribucion_nacional = $evaluacion_nacional->add_promedios($contribucion_nacional, $promedios);

            $consulta = $evaluacion_nacional->add_promedios($consulta, $promedios);
            foreach ($raw as $key => $value) {
                if ($key == 'Actividad_economica') {
                    $tabla[$key] = $value;    
                    //$actividad = $value;
                }else{
                    $tabla[$key] = $value;
                    
                    $tabla['posicion_'.$key] = $valoracion_nacional->posicion($contribucion_nacional, $key, $entidad, $consulta, $region, $nivel);    
                }
            }
            array_push($tabla_contribucion, $tabla);
        }
        return($tabla_contribucion);
    }
    function posicion_contribucion_puntos_query($base, $consulta_entidad, $consulta_nacional, $contribucion_tabla, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel, $promedios){
        $consulta = [];
        $tabla_contribucion = [];
        $cont = 0;

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $funcion = new MapasFunctions();


        /*$variacion_estatal = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        $variacion_estatal = $evaluacion_nacional->add_promedios($variacion_estatal, $promedios);
        $variacion_nacional = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);
        $variacion_nacional = $evaluacion_nacional->add_promedios($variacion_nacional, $promedios);*/

        $contribucion_puntos = $contribucion_tabla;
        $contribucion_puntos = $evaluacion_nacional->add_promedios($contribucion_puntos, $promedios);

        foreach ($contribucion_puntos as $raw) {
            $tabla = [];
            $actividad = $raw['Actividad_economica'];
            $consulta = $funcion->consulta_x_actividad($base, $actividad, $region, $nivel);
            //$contribucion_nacional = $evaluacion_nacional->variacion_puntos($consulta, $tipo_periodo, $year_1, $year_2, $promedios);
            $contribucion_nacional = $funcion->consulta_x_actividad('pib_precios_constantes_particip_nacional', $actividad, $region, $nivel);
            $contribucion_nacional = $evaluacion_nacional->add_promedios($contribucion_nacional, $promedios);
            $consulta = $evaluacion_nacional->add_promedios($consulta, $promedios);
            foreach ($raw as $key => $value) {
                if ($key == 'Actividad_economica') {
                    $tabla[$key] = $value;    
                    //$actividad = $value;
                }else{
                    $tabla[$key] = $value;
                    
                    
                    $tabla['posicion_'.$key] = $valoracion_nacional->posicion($contribucion_nacional, $key, $entidad, $consulta, $region, $nivel);    
                }
            }
            array_push($tabla_contribucion, $tabla);
        }
        return($tabla_contribucion);
    }

function posicion_contribucion_porcentaje_query($base, $consulta_entidad, $consulta_nacional, $contribucion_tabla, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel, $promedios){
        $consulta = [];
        $tabla_contribucion = [];
        $cont = 0;

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $funcion = new MapasFunctions();


        //$variacion_estatal = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        //$variacion_nacional = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);

        //$contribucion_puntos = $valoracion_nacional->contribucion_crecimiento_puntos($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2, $promedios);
        $contribucion_porcentual = $evaluacion_nacional->contribucion_crecimiento_porcentual_query($consulta_entidad, $consulta_nacional, $contribucion_tabla, $tipo_periodo, $year_1, $year_2, $promedios);
        $contribucion_porcentual = $evaluacion_nacional->add_promedios($contribucion_porcentual, $promedios);
        
        foreach ($contribucion_porcentual as $raw) {
            $tabla = [];
            $actividad = $raw['Actividad_economica'];

            $consulta = $funcion->consulta_x_actividad($base, $actividad, $region, $nivel);
            //$contribucion_nacional = $evaluacion_nacional->variacion_puntos($consulta, $tipo_periodo, $year_1, $year_2, $promedios);
            $contribucion_nacional = $funcion->consulta_x_actividad('pib_precios_constantes_particip_nacional', $actividad, $region, $nivel);
            $contribucion_nacional = $evaluacion_nacional->add_promedios($contribucion_nacional, $promedios);

            $consulta = $evaluacion_nacional->add_promedios($consulta, $promedios);
            foreach ($raw as $key => $value) {
                if ($key == 'Actividad_economica') {
                    $tabla[$key] = $value;    
                    //$actividad = $value;
                }else{
                    $tabla[$key] = $value;
                    
                    $tabla['posicion_'.$key] = $valoracion_nacional->posicion($contribucion_nacional, $key, $entidad, $consulta, $region, $nivel);    
                }
            }
            array_push($tabla_contribucion, $tabla);
        }
        return($tabla_contribucion);
    }

    function diferencia($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2, $promedios){
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $valoracion = new PibFunctions();

        $contribucion_porcentual = $evaluacion_nacional->contribucion_crecimiento_porcentual($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2, $promedios);

        //$consulta_entidad = $evaluacion_nacional->add_promedios($consulta_entidad, $promedios);
        //$consulta_nacional = $evaluacion_nacional->add_promedios($consulta_nacional, $promedios);
        $participacion = $valoracion_nacional->participacion($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        //$participacion = $evaluacion_nacional->add_promedios($participacion, $promedios);

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
        return($tabla);
        
    }
    function diferencia_query($consulta_entidad, $consulta_nacional, $contribucion_tabla, $tipo_periodo, $year_1, $year_2, $promedios){
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $valoracion = new PibFunctions();

        $contribucion_porcentual = $evaluacion_nacional->contribucion_crecimiento_porcentual_query($consulta_entidad, $consulta_nacional, $contribucion_tabla, $tipo_periodo, $year_1, $year_2, $promedios);

        //$consulta_entidad = $evaluacion_nacional->add_promedios($consulta_entidad, $promedios);
        //$consulta_nacional = $evaluacion_nacional->add_promedios($consulta_nacional, $promedios);
        $participacion = $valoracion_nacional->participacion($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2);
        //$participacion = $evaluacion_nacional->add_promedios($participacion, $promedios);

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
        return($tabla);
        
    }
    function posicion_diferencia($base, $consulta_entidad, $consulta_nacional, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel, $promedios){
        $consulta = [];
        $tabla_diferencia = [];
        $cont = 0;

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $funcion = new MapasFunctions();
        
        $variacion_estatal = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        $variacion_estatal = $evaluacion_nacional->add_promedios($variacion_estatal, $promedios);
        $variacion_nacional = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);
        $variacion_nacional = $evaluacion_nacional->add_promedios($variacion_nacional, $promedios);

        $diferencia = $evaluacion_nacional->diferencia($consulta_entidad, $consulta_nacional, $tipo_periodo, $year_1, $year_2, $promedios);
        $diferencia = $evaluacion_nacional->add_promedios($diferencia, $promedios);
        foreach ($diferencia as $raw) {
            
            $tabla = [];
            $actividad = $raw['Actividad_economica'];
            $consulta = $funcion->consulta_x_actividad($base, $actividad, $region, $nivel);
            $contribucion_nacional = $evaluacion_nacional->eficiencia($consulta, $tipo_periodo, $year_1, $year_2, $promedios);
            $contribucion_nacional = $evaluacion_nacional->add_promedios($contribucion_nacional, $promedios);

            $consulta = $evaluacion_nacional->add_promedios($consulta, $promedios);
            foreach ($raw as $key => $value) {
                if ($key == 'Actividad_economica') {
                    $tabla[$key] = $value;    
                    //$actividad = $value;
                }else{
                    $tabla[$key] = $value;
                    
                    $tabla['posicion_'.$key] = $valoracion_nacional->posicion_2($contribucion_nacional, $key, $entidad, $variacion_estatal[$cont], $variacion_nacional[$cont], $consulta, $region, $nivel);    
                }
            }
            array_push($tabla_diferencia, $tabla);
            $cont++;
        }
        return($tabla_diferencia);
    }
    function posicion_diferencia_query($base, $consulta_entidad, $consulta_nacional, $contribucion_tabla, $entidad, $tipo_periodo, $year_1, $year_2, $region, $nivel, $promedios){
        $consulta = [];
        $tabla_diferencia = [];
        $cont = 0;

        $valoracion = new PibFunctions();
        $valoracion_nacional = new PibNacionalFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $funcion = new MapasFunctions();
        
        $variacion_estatal = $valoracion->variacion($consulta_entidad, $tipo_periodo, $year_1, $year_2);
        $variacion_estatal = $evaluacion_nacional->add_promedios($variacion_estatal, $promedios);
        $variacion_nacional = $valoracion->variacion($consulta_nacional, $tipo_periodo, $year_1, $year_2);
        $variacion_nacional = $evaluacion_nacional->add_promedios($variacion_nacional, $promedios);

        $diferencia = $evaluacion_nacional->diferencia_query($consulta_entidad, $consulta_nacional, $contribucion_tabla, $tipo_periodo, $year_1, $year_2, $promedios);
        $diferencia = $evaluacion_nacional->add_promedios($diferencia, $promedios);
        foreach ($diferencia as $raw) {
            
            $tabla = [];
            $actividad = $raw['Actividad_economica'];
            $consulta = $funcion->consulta_x_actividad($base, $actividad, $region, $nivel);
            $contribucion = $funcion->consulta_x_actividad('pib_precios_constantes_particip_nacional', $actividad, $region, $nivel);
            $contribucion_nacional = $evaluacion_nacional->eficiencia_query($consulta, $contribucion, $tipo_periodo, $year_1, $year_2, $promedios);
            //($data, $contribucion_tabla, $tipo_periodo, $year_1, $year_2, $promedios)
            //$contribucion_nacional = $evaluacion_nacional->eficiencia($consulta, $tipo_periodo, $year_1, $year_2, $promedios);
            $contribucion_nacional = $evaluacion_nacional->add_promedios($contribucion_nacional, $promedios);

            $consulta = $evaluacion_nacional->add_promedios($consulta, $promedios);
            foreach ($raw as $key => $value) {
                if ($key == 'Actividad_economica') {
                    $tabla[$key] = $value;    
                    //$actividad = $value;
                }else{
                    $tabla[$key] = $value;
                    
                    $tabla['posicion_'.$key] = $valoracion_nacional->posicion_2($contribucion_nacional, $key, $entidad, $variacion_estatal[$cont], $variacion_nacional[$cont], $consulta, $region, $nivel);    
                }
            }
            array_push($tabla_diferencia, $tabla);
            $cont++;
        }
        return($tabla_diferencia);
    }

    function add_promedios($consulta, $promedios){
        $valoracion = new PibFunctions();
        $estadisticas_promedio = [];
        foreach ($promedios as $promedio) {
            $estadisticas_promedio = $valoracion->promedio(['consulta'=>$consulta,'year_1' => $promedio['year_1'],'year_2' => $promedio['year_2']]);
            $counter = 0;
            foreach ($estadisticas_promedio as $row) {
                $consulta[$counter][$promedio['name']] = $row['Promedio'];
                $counter++;
            }
        }
        return $consulta;
    }











    /***********Funcione PIBFunctions()**************/
    function variacion_puntos($data, $tipo_periodo, $year_1, $year_2, $promedios){
        $formula = new PibFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();


        $variacion = $formula->variacion($data, $tipo_periodo, $year_1, $year_2);
        //$variacion = $evaluacion_nacional->add_promedios($variacion, $promedios);

        //$data = $evaluacion_nacional->add_promedios($data, $promedios);
        $participacion = $formula->participacion($data, $tipo_periodo, $year_1, $year_2);
        //$participacion = $evaluacion_nacional->add_promedios($participacion, $promedios);
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
        //$tabla = $evaluacion_nacional->add_promedios($tabla, $promedios);
        return($tabla);
    }

    function variacion_porcentual($data, $tipo_periodo, $year_1, $year_2, $promedios){
        //$formula = new PibFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $variacion = $evaluacion_nacional->variacion_puntos($data, $tipo_periodo, $year_1, $year_2, $promedios);
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
        //dd(['variacion_porcentual'=>$tabla]);
        return($tabla);
    }
    function variacion_porcentual_query($data, $contribucion_tabla, $tipo_periodo, $year_1, $year_2, $promedios){
        //$formula = new PibFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        //$variacion = $evaluacion_nacional->variacion_puntos($data, $tipo_periodo, $year_1, $year_2, $promedios);
        $variacion = $contribucion_tabla;
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
        //dd(['variacion_porcentual'=>$tabla]);
        return($tabla);
    }
    function eficiencia($data, $tipo_periodo, $year_1, $year_2, $promedios){
        $formula = new PibFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        $variacion = $evaluacion_nacional->variacion_porcentual($data, $tipo_periodo, $year_1, $year_2, $promedios);
        //$variacion = $evaluacion_nacional->add_promedios($variacion, $promedios);

        //$data = $evaluacion_nacional->add_promedios($data, $promedios);
        $participacion = $formula->participacion($data, $tipo_periodo, $year_1, $year_2);
        //$participacion = $evaluacion_nacional->add_promedios($participacion, $promedios);

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
        //dd(['eficiencia'=>$tabla]);
        return($tabla);
    }
    function eficiencia_query($data, $contribucion_tabla, $tipo_periodo, $year_1, $year_2, $promedios){
        $formula = new PibFunctions();
        $evaluacion_nacional = new PibNacionalEvaluacionFunctions();
        //$variacion = $evaluacion_nacional->variacion_porcentual($data, $tipo_periodo, $year_1, $year_2, $promedios);
        $variacion = $evaluacion_nacional->variacion_porcentual_query($data, $contribucion_tabla, $tipo_periodo, $year_1, $year_2, $promedios);

        //$data = $evaluacion_nacional->add_promedios($data, $promedios);
        $participacion = $formula->participacion($data, $tipo_periodo, $year_1, $year_2);
        //$participacion = $evaluacion_nacional->add_promedios($participacion, $promedios);
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
        //dd(['eficiencia'=>$tabla]);
        return($tabla);
    }
}