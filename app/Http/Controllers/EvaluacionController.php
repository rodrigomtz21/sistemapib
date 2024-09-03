<?php
/**
 * EvaluacionController.php
 *Clase con metos de consulta de datos 
 *
 * @package      Controllers
 * @category     Consulta
 * @author       Rodrigo Martínez <rodrigo.mtz.hrz@gmail.com>
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Libraries\EvaluacionFunctions;
use App\Libraries\PibFunctions;
/**
*Clase que contiene los métodos de la funcionalidad del modulo Estadísticas.
*/
class EvaluacionController extends Controller
{   
    /**
    *Función que valida los datos recibidos desde el formulario y llama al método main_consulta() de la clase Evaluación, que retorna una tabla y sus cabeceras construidos con la información proporcionada.
    *@param array $request array de datos recibidos desde el formulario.
    *@method array 
    */
    public function main_evaluacion(Request $request)
    {   

    	$data = $request->all();
        $evaluacion = new EvaluacionFunctions();
        /** @var string  $data['selectedValoracion'] Debe contener los valores 1 | 2 */
        if ($data['selectedValoracion']==1) {
            $data['selectedValoracion'] = 'pib_precios_constantes';
        }elseif($data['selectedValoracion']==2){
            $data['selectedValoracion'] = 'pib_precios_corrientes';
        }

        $variables = [];

        foreach ($data['variables'] as $variable) {
            if($variable['selected']){
                array_push($variables, array("id"=>$variable['id'], "title"=>$variable['nombre']));   
            }
            
        }
        /** @var array  $entidades se debe agregar el dato 0 que representa el valor Nacional*/
        $entidades = [0=>'0'];
        foreach ($data['entidades'] as $entidad) {
            if($entidad['selected']){
                $entidades=array_merge($entidades,array($entidad['id']));  
            }
            
        }

        $periodos = [];
        foreach ($data['periodos'] as $periodo) {
            if($periodo['selected']){
                $periodos=array_merge($periodos,array($periodo['id']));   
            }
            
        }

        $data['periodos'] = $periodos;
        $data['variables'] = $variables;
        $data['entidades'] = $entidades;
        
        $tabla = $evaluacion->main_consulta($data);
        return response()->json($tabla);

        
    }
    /**
    *Función que valida los datos recibidos desde el formulario y llama al método main_consulta() de la clase Evaluación, que retorna una tabla y sus cabeceras construidos con la información proporcionada y finalmente genera un archivo excel.
    *@param array $request array de datos recibidos desde el formulario.
    *@method array 
    */
    public function main_evaluacion_excel(Request $request)
    {   

        $data = $request->all();
        $evaluacion = new EvaluacionFunctions();
        $evaluacion_this = new EvaluacionController();
        /** @var string  $data['selectedValoracion'] Debe contener los valores 1 | 2 */
        if ($data['selectedValoracion']==1) {
            $data['selectedValoracion'] = 'pib_precios_constantes';
        }elseif($data['selectedValoracion']==2){
            $data['selectedValoracion'] = 'pib_precios_corrientes';
        }

        $variables = [];

        foreach ($data['variables'] as $variable) {
            if($variable['selected'] == 'true'){
                array_push($variables, array("id"=>$variable['id'], "title"=>$variable['nombre']));   
            }
            
        }
        /** @var array  $entidades se debe agregar el dato 0 que representa el valor Nacional*/
        $entidades = [0=>'0'];
        foreach ($data['entidades'] as $entidad) {
            if($entidad['selected'] == 'true'){
                $entidades=array_merge($entidades,array($entidad['id']));  
            }
            
        }

        $periodos = [];
        foreach ($data['periodos'] as $periodo) {
            if($periodo['selected'] == 'true'){
                $periodos=array_merge($periodos,array($periodo['id']));   
            }
            
        }

        $data['periodos'] = $periodos;
        $data['variables'] = $variables;
        $data['entidades'] = $entidades;
        //$data['regiones'] = $regiones;
        if ($data['flag_promedios'] == 'false') {
            $data['promedios'] = [];
        }
        $tabla = $evaluacion->main_consulta($data);
        
        $data_excel['title_string'] = $data['title_string'];
        $data_excel['valoracion_status'] = true;
        $data_excel['participacion_status'] = false;
        $data_excel['variacion_status'] = false;
        $data_excel['contribucion_status'] = false;
        $data_excel['diferencia_status'] = false;
        /**
         * Generar titulo para la tabla del archivo excel
         */
        $data_excel['headers'] = $tabla['headers'];
        $data_excel['valoracion'] = ['title_valores'=>'Valores',
            'data' => $tabla['estadisticas'],
            'clases' => $evaluacion_this->get_clases_valores($data, $tabla['headers'], $tabla['participacion'])];
            
        foreach ($data['variables'] as $variable) {
            if($variable['id'] == '0'){
                $data_excel['participacion_status'] = true;
                $data_excel['participacion'] = ['title_participacion'=>'Participación (%)(A)',
                    'data' => $tabla['participacion'],
                    'clases' => $evaluacion_this->get_clases_valores($data, $tabla['headers'], $tabla['participacion'])];

            }elseif ($variable['id'] == '1') {
                $data_excel['variacion_status'] = true;
                $data_excel['variacion'] = ['title_variacion'=>'Variación Anual (%)',
                    'data' => $tabla['variacion'],
                    'clases' => $evaluacion_this->get_clases_variacion($data, $tabla['headers'], $tabla['variacion'])];

            }elseif ($variable['id'] == '2') {
                $data_excel['contribucion_status'] = true;
                $data_excel['contribucion'] = ['title_participacion_puntos'=>'Contribución al Crecimiento(Puntos)',
                    'data_participacion_puntos' => $tabla['contribucion_puntos'],
                    'clases_participacion_puntos' => $evaluacion_this->get_clases_contribucion($data, $tabla['headers'], $tabla['contribucion_porcentaje'], $tabla['variacion']),
                    'title_participacion_porcentaje'=>'Contribución al Crecimiento(%)(B)',
                    'data_participacion_porcentaje' => $tabla['contribucion_porcentaje'],
                    'clases_participacion_porcentaje' => $evaluacion_this->get_clases_contribucion($data, $tabla['headers'], $tabla['contribucion_porcentaje'], $tabla['variacion'])];

            }elseif ($variable['id'] == '3') {
                $data_excel['diferencia_status'] = true;
                $data_excel['diferencia'] = ['title_diferencia'=>'Diferencia (B)-(A)(Desempeño global)',
                    'data' => $tabla['diferencia'],
                    'clases' => $evaluacion_this->get_clases_diferencia($data, $tabla['headers'], $tabla['diferencia'], $tabla['variacion'])];
            }
            
        }
        /** 
        *Construir archivo excel 
        *@var string $data['name_file'] Nombre del archivo
        *@var array $data_excel array con los datos y cabeceras utilizados para contruir el archivo
        */
        Excel::create($data['name_file'], function($excel) use ($data_excel) {

            $excel->sheet('Current Stock Report', function($sheet) use ($data_excel) {
                $sheet->loadView('tabla')->with('data',$data_excel);
                $sheet->setOrientation('landscape');
                $sheet->setStyle(array(
                    'font' => array(
                        'name'      =>  'Calibri',
                        'size'      =>  10,
                        'bold'      =>  false
                    )
                ));
                $sheet->setAutoSize(true);
            });

        })->export('xls');
        
    }
    /**
    *Función que evalúa las clases de  acuerdo a los criterios del concepto Participación, para los valores de la tabla valores.
    *@param array $data array de datos recibidos desde el formulario.
    *@param array $headers cabeceras para la tabla valores
    *@param arrya $valores tabla con los valores a evaluar
    *@method array 
    */
    public function get_clases_valores($data, $headers, $valores){
        /**********************Evaluar clases para variable Participación***************/
        $clases_resumen_before = [];         
        $contador = 1;
        foreach ($valores as $fila) {
            $cont = 0; 
            $tabla_resumen_aux = [];
            foreach ($headers as $columna) {
                $title_1 = $columna['header'];   
                $title = $columna['header'];
                if ($contador != 1 && $fila[$title] != 0) {
                    /**
                     * Evaluacion de clases para el concepto Posicion
                     */
                    if (strstr($title, 'posicion')) {
                        if ($fila[$title] >= 1 && $fila[$title] <= 5) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila[$title] <= 10 && $fila[$title] > 5) {$tabla_resumen_aux[$title] = "alto";} 
                        else if ($fila[$title] <= 15 && $fila[$title] > 10) {$tabla_resumen_aux[$title] = "intermedia";}
                        else if ($fila[$title] <= 20 && $fila[$title] > 15) {$tabla_resumen_aux[$title] = "neutro";}
                        else if ($fila[$title] <= 32 && $fila[$title] > 20) {$tabla_resumen_aux[$title] = "negativo";}
                        else{$tabla_resumen_aux[$title] = "normal2";}
                        
                    /**
                     * Omitir los campos Entidad|cve_ent|nombre
                     */
                    }else if($title == 'Entidad' || $title == 'cve_ent' || $title == 'nombre'){
                        $tabla_resumen_aux[$title] = "normal2";
                    }else { 

                        if ($fila[$title] > 9.36) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila[$title] <= 9.36 && $fila[$title] > 6.24) {$tabla_resumen_aux[$title] = "alto";} 
                        else if ($fila[$title] <= 6.24 && $fila[$title] > 3.13) {$tabla_resumen_aux[$title] = "intermedia";}
                        else if ($fila[$title] == 3.13) {$tabla_resumen_aux[$title] = "neutro";}
                        else if ($fila[$title] < 3.13) {$tabla_resumen_aux[$title] = "negativo";}
                    }      
                           
                }else{
                    $tabla_resumen_aux[$title] = "normal2";
                }
                $cont ++;


            }
            $contador ++;
            array_push($clases_resumen_before, $tabla_resumen_aux);
            
        }
        return $clases_resumen_before;
    }
    /**
    *Función que evalúa las clases de  acuerdo a los criterios del concepto Variación, para los valores de la tabla valores.
    *@param array $data array de datos recibidos desde el formulario.
    *@param array $headers cabeceras para la tabla valores
    *@param arrya $valores tabla con los valores a evaluar
    *@method array 
    */
    public function get_clases_variacion($data, $headers, $valores){
       
        $base_tabla = $valores[0];
        $clases_resumen_before = [];         
        $contador =1;
        //for (let fila of this.variacion) {
        foreach ($valores as $fila) {
            $cont = 0; 
            $tabla_resumen_aux = [];
            //for (let columna of this.headers){
            foreach ($headers as $columna) {
                $title_1 = $columna['header'];   
                $title = $columna['header'];
                if ($contador != 1 && $fila[$title] != 0) {
                    if (strstr($title_1, 'posicion')) {
                        if ($fila[$title] >= 1 && $fila[$title] <= 5) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila[$title] <= 10 && $fila[$title] > 5) {$tabla_resumen_aux[$title] = "alto";} 
                        else if ($fila[$title] <= 15 && $fila[$title] > 10) {$tabla_resumen_aux[$title] = "intermedia";}
                        else if ($fila[$title] <= 20 && $fila[$title] > 15) {$tabla_resumen_aux[$title] = "neutro";}
                        else if ($fila[$title] <= 32 && $fila[$title] > 20) {$tabla_resumen_aux[$title] = "negativo";}
                        else{$tabla_resumen_aux[$title] = "normal2";}

                    }else if($title == 'Entidad' || $title == 'cve_ent' || $title == 'nombre'){
                        $tabla_resumen_aux[$title] = "normal2";
                    }else { 
                        if ($fila[$title] >= $base_tabla[$title] && $fila[$title] >= 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila[$title] >= 0 && $fila[$title] < $base_tabla[$title]) {$tabla_resumen_aux[$title] = "neutro";}
                        else if($fila[$title] < 0){$tabla_resumen_aux[$title] = "negativo";}
                    }      
                           
                }else{
                    $tabla_resumen_aux[$title] = "normal2";
                }
                $cont ++;


            }
            $contador ++;
            array_push($clases_resumen_before, $tabla_resumen_aux);
            
        }
        return $clases_resumen_before;
    }
    /**
    *Función que evalúa las clases de  acuerdo a los criterios del concepto Contribución, para los valores de la tabla valores.
    *@param array $data array de datos recibidos desde el formulario.
    *@param array $headers cabeceras para la tabla valores
    *@param arrya $participacion_porcentaje tabla con los valores a evaluar
    *@param arrya $variacion tabla con los valores de variacion
    *@method array 
    */
    public function get_clases_contribucion($data, $headers, $participacion_porcentaje, $variacion){
        $clases_resumen_before = [];         
        $contador =1;
        $base_tabla = $variacion[0];
        //for (let fila of this.participacion_porcentaje) {
        foreach ($participacion_porcentaje as $fila) {
            $cont = 0; 
            $tabla_resumen_aux = [];
            //for (let columna of this.headers){
            foreach ($headers as $columna) {
                $title_1 = $columna['header'];   
                $title = $columna['header'];
                if ($contador != 1 && $fila[$title] != 0) {
                    if (strstr($title_1, 'posicion')) {
                        if ($fila[$title] >= 1 && $fila[$title] <= 5) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila[$title] <= 10 && $fila[$title] > 5) {$tabla_resumen_aux[$title] = "alto";} 
                        else if ($fila[$title] <= 15 && $fila[$title] > 10) {$tabla_resumen_aux[$title] = "intermedia";}
                        else if ($fila[$title] <= 20 && $fila[$title] > 15) {$tabla_resumen_aux[$title] = "neutro";}
                        else if ($fila[$title] <= 32 && $fila[$title] > 20) {$tabla_resumen_aux[$title] = "negativo";}
                        else{$tabla_resumen_aux[$title] = "normal2";}

                    }else if($title == 'Entidad' || $title == 'cve_ent' || $title == 'nombre'){
                        $tabla_resumen_aux[$title] = "normal2";
                    }else { 
                        if($base_tabla[$title]<0 && $fila[$title]>=0){
                            if($fila[$title]*-1 > 9.36) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title]*-1 <= 9.36 && $fila[$title]*-1 > 6.25) {$tabla_resumen_aux[$title] = "alto";}
                            else if($fila[$title]*-1 <= 6.25 && $fila[$title]*-1 > 3.13){$tabla_resumen_aux[$title] = "intermedia";}
                            else if($fila[$title]*-1 == 3.13 ){$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 3.13 ){$tabla_resumen_aux[$title] = "negativo";}
                        }  
                        else if($base_tabla[$title]<0 && $fila[$title]<0){
                            if($fila[$title]*-1 > 9.36) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title]*-1 <= 9.36 && $fila[$title]*-1 > 6.25) {$tabla_resumen_aux[$title] = "alto";}
                            else if($fila[$title]*-1 <= 6.25 && $fila[$title]*-1 > 3.13){$tabla_resumen_aux[$title] = "intermedia";}
                            else if($fila[$title]*-1 == 3.13 ){$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 3.13 ){$tabla_resumen_aux[$title] = "negativo";}
                        }    
                        else{
                            if($fila[$title] > 9.36) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title] <= 9.36 && $fila[$title] > 6.25) {$tabla_resumen_aux[$title] = "alto";}
                            else if($fila[$title] <= 6.25 && $fila[$title] > 3.13){$tabla_resumen_aux[$title] = "intermedia";}
                            else if($fila[$title] == 3.13 ){$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title] < 3.13 ){$tabla_resumen_aux[$title] = "negativo";}
                        }
                    }    
                           
                }else{
                    $tabla_resumen_aux[$title] = "normal2";
                }
                $cont ++;


            }
            $contador ++;
            //clases_resumen_before.push(tabla_resumen_aux);
            array_push($clases_resumen_before, $tabla_resumen_aux);
            
        }
        return $clases_resumen_before;
    }
    /**
    *Función que evalúa las clases de  acuerdo a los criterios del concepto Desempeño global, para los valores de la tabla valores.
    *@param array $data array de datos recibidos desde el formulario.
    *@param array $headers cabeceras para la tabla valores
    *@param arrya $valores tabla con los valores a evaluar
    *@method array 
    */
    public function get_clases_diferencia($data, $headers, $valores, $variacion){
        $clases_resumen_before = [];         
        $contador =1;
        $base_tabla = $variacion[0];
        //for (let fila of this.diferencia) {
        foreach ($valores as $fila) {
            $cont = 0; 
            $tabla_resumen_aux = [];
            //for (let columna of this.headers){
            foreach ($headers as $columna) {
                $title_1 = $columna['header'];   
                $title = $columna['header'];
                if ($contador != 1 && $fila[$title] != 0) {
                    if (strstr($title_1, 'posicion')) {
                        if ($fila[$title] >= 1 && $fila[$title] <= 5) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila[$title] <= 10 && $fila[$title] > 5) {$tabla_resumen_aux[$title] = "alto";} 
                        else if ($fila[$title] <= 15 && $fila[$title] > 10) {$tabla_resumen_aux[$title] = "intermedia";}
                        else if ($fila[$title] <= 20 && $fila[$title] > 15) {$tabla_resumen_aux[$title] = "neutro";}
                        else if ($fila[$title] <= 32 && $fila[$title] > 20) {$tabla_resumen_aux[$title] = "negativo";}
                        else{$tabla_resumen_aux[$title] = "normal2";}

                    }else if($title == 'Entidad' || $title == 'cve_ent' || $title == 'nombre'){
                        $tabla_resumen_aux[$title] = "normal2";
                    }else { 
                        if($base_tabla[$title]<0 && $fila[$title]>=0){
                            if($fila[$title]*-1 > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 0){$tabla_resumen_aux[$title] = "negativo";}
                        }    
                        else if($base_tabla[$title]<0 && $fila[$title]<0 && $fila[$title]>0){
                            if($fila[$title]*-1 > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 0){$tabla_resumen_aux[$title] = "negativo";}
                        }/*************************No invertir signos**************/    
                        else if($base_tabla[$title]<0 && $fila[$title]<0 && $fila[$title]<0){
                            if($fila[$title]*1 > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title]*1 < 0){$tabla_resumen_aux[$title] = "negativo";}
                        }    
                        else{
                            if($fila[$title] > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title] < 0){$tabla_resumen_aux[$title] = "negativo";}
                        }
                    }      
                           
                }else{
                    $tabla_resumen_aux[$title] = "normal2";
                }
                $cont ++;


            }
            $contador ++;
            array_push($clases_resumen_before, $tabla_resumen_aux);
            //clases_resumen_before.push(tabla_resumen_aux);
            
        }
        return $clases_resumen_before;
    }
}
