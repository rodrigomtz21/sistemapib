<?php
/**
 *MapasEvaluacionController.php
 *Clase con metos de consulta de datos 
 *
 * @package      Controllers
 * @category     Consulta
 * @author       Rodrigo Martínez <rodrigo.mtz.hrz@gmail.com>
 */
namespace App\Http\Controllers;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Libraries\MapasEvaluacionFunctions;
use App\Libraries\PibFunctions;
/**
*Clase que contiene los métodos de la funcionalidad del modulo Evaluacion por años.
*/
class MapasEvaluacionController extends Controller
{   
    /**
    *Función que valida los datos recibidos desde el formulario y llama al método main_consulta() de la clase MapasEvaluacionFunctions, que retorna una tabla y sus cabeceras construidos con la información proporcionada.
    *@param array $request array de datos recibidos desde el formulario.
    *@method array 
    */
    public function main_evaluacion(Request $request){ 
        $data = $request->all();
        $evaluacion = new MapasEvaluacionFunctions();
        


        $variables = [];

        foreach ($data['variables'] as $variable) {
            if($variable['selected']){
                array_push($variables, array("id"=>$variable['id'], "title"=>$variable['nombre']));   
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
        
        
        $tabla = $evaluacion->main_consulta($data);
        //dd($tabla);
        return response()->json($tabla);
    }
    /**
    *Función que valida los datos recibidos desde el formulario y llama al método main_consulta() de la clase MapasEvaluacionFunctions, que retorna una tabla y sus cabeceras construidos con la información proporcionada y ala final genera un archivo excel.
    *@param array $request array de datos recibidos desde el formulario.
    *@method array 
    */
    public function main_evaluacion_excel(Request $request){ 
        $data = $request->all();
        //dd($data);
        $evaluacion = new MapasEvaluacionFunctions();
        $evaluacion_this = new MapasEvaluacionController();


        $variables = [];

        foreach ($data['variables'] as $variable) {
            if($variable['selected'] == 'true'){
                array_push($variables, array("id"=>$variable['id'], "title"=>$variable['nombre']));   
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
        
        //dd($data['flag_promedios']);
        if ($data['flag_promedios'] == 'false') {
            $data['promedios'] = [];
        }
        
        $tabla = $evaluacion->main_consulta($data);
        //dd($tabla);
        //dd($evaluacion_this->get_clases_valores($data, $tabla['headers'], $tabla['participacion']));
        $data_excel['title_string'] = $data['title_string'];
        $data_excel['valoracion_status'] = true;
        $data_excel['participacion_status'] = false;
        $data_excel['variacion_status'] = false;
        $data_excel['contribucion_status'] = false;
        $data_excel['diferencia_status'] = false;

        $variacion_nacional = $tabla['variacion_nacional'];
        $tabla_stadisticas_nacional = $tabla['estadisticas_nacional'];
        //dd($tabla['contribucion_porcentaje']);
        $data_excel['headers'] = $tabla['headers'];
        $data_excel['valoracion'] = ['title_valores'=>'Valores',
            'data' => $tabla['estadisticas'],
            'clases' => $evaluacion_this->get_clases_valores($data, $tabla['headers'], $tabla['participacion'])];

        foreach ($data['variables'] as $variable) {
            if($variable['id'] == '0'){
                $data_excel['participacion_status'] = true;
                $data_excel['participacion'] = ['title_participacion'=>$variable['title'],
                    'data' => $tabla['participacion'],
                    'clases' => $evaluacion_this->get_clases_valores($data, $tabla['headers'], $tabla['participacion'])];

            }elseif ($variable['id'] == '1') {
                $data_excel['variacion_status'] = true;
                $data_excel['variacion'] = ['title_variacion'=>$variable['title'],
                    'data' => $tabla['variacion'],
                    'clases' => $evaluacion_this->get_clases_variacion($data, $tabla['headers'], $tabla['variacion'], $tabla_stadisticas_nacional)];

            }elseif ($variable['id'] == '2') {//dd($tabla['contribucion_porcentaje']);
                $data_excel['contribucion_status'] = true;
                $data_excel['contribucion'] = ['title_participacion_puntos'=>$variable['title'].'(Puntos porcentuales)',
                    'data_participacion_puntos' => $tabla['contribucion_puntos'],
                    'clases_participacion_puntos' => $evaluacion_this->get_clases_contribucion($data, $tabla['headers'], $tabla['contribucion_porcentaje'], $tabla['variacion'], $variacion_nacional),
                    'title_participacion_porcentaje'=>$variable['title'].'(En porcentaje)(B)',
                    'data_participacion_porcentaje' => $tabla['contribucion_porcentaje'],
                    'clases_participacion_porcentaje' => $evaluacion_this->get_clases_contribucion($data, $tabla['headers'], $tabla['contribucion_porcentaje'], $tabla['variacion'], $variacion_nacional)];

            }elseif ($variable['id'] == '3') {
                $data_excel['diferencia_status'] = true;
                $data_excel['diferencia'] = ['title_diferencia'=>$variable['title'],
                    'data' => $tabla['diferencia'],
                    'clases' => $evaluacion_this->get_clases_diferencia($data, $tabla['headers'], $tabla['diferencia'], $tabla['variacion'], $variacion_nacional)];
            }
            
        }//dd($data_excel['valoracion']['clases']);
        //return $tabla = \View::make('tabla')->with(['data'=>$data_excel]);
        //return response()->json($tabla);
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
    *Función que valida los datos recibidos desde el formulario y llama al método main_consulta() de la clase MapasEvaluacionFunctions, que retorna una tabla y sus cabeceras construidos con la información proporcionada.
    *@param array $request array de datos recibidos desde el formulario.
    *@param array $headers cabeceras para la tabla valores
    *@param arrya $valores tabla con los valores a evaluar
    *@method array 
    */
    public function get_clases_valores($data, $headers, $valores){
        $start = 0;
        if(($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')){
            $start = 1;
        }else{
            $start = 0;
        }
        /**********************Evaluar clases para variable Participación***************/
        $clases_resumen_before = [];         
        $contador = 1;
        foreach ($valores as $fila) {
            $cont = 0; 
            $tabla_resumen_aux = [];
            foreach ($headers as $columna) {
                $title_1 = $columna['header'];   
                $title = $columna['header'];
                if ($contador != $start && $fila[$title] != 0) {
                    if (strstr($title_1, 'posicion')) {
                        if ($fila[$title] >= 1 && $fila[$title] <= 5) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila[$title] <= 10 && $fila[$title] > 5) {$tabla_resumen_aux[$title] = "alto";} 
                        else if ($fila[$title] <= 15 && $fila[$title] > 10) {$tabla_resumen_aux[$title] = "intermedia";}
                        else if ($fila[$title] <= 20 && $fila[$title] > 15) {$tabla_resumen_aux[$title] = "neutro";}
                        else if ($fila[$title] <= 32 && $fila[$title] > 20) {$tabla_resumen_aux[$title] = "negativo";}
                        else{$tabla_resumen_aux[$title] = "normal2";}

                    }else if($title == 'Actividad_economica'){
                        $tabla_resumen_aux[$title] = "normal2";
                    }else { 

                        if (($data['selectedNivel']  == '1' || $data['selectedNivel']  == '3')&& $data['selectedCobertura']  != 'Manufactura'){
                            if ($fila[$title] > 15.77) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if ($fila[$title] <= 15.77 && $fila[$title] > 10.51) {$tabla_resumen_aux[$title] = "alto";} 
                            else if ($fila[$title] <= 10.51 && $fila[$title] > 5.26) {$tabla_resumen_aux[$title] = "intermedia";}
                            else if ($fila[$title] == 5.26) {$tabla_resumen_aux[$title] = "neutro";}
                            else if ($fila[$title] < 5.26) {$tabla_resumen_aux[$title] = "negativo";}

                        }else if (($data['selectedNivel']  == '1' || $data['selectedNivel']  == '3')&& $data['selectedCobertura']  == 'Manufactura'){
                            if ($fila[$title] > 24.98) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if ($fila[$title] <= 24.98 && $fila[$title] > 16.65) {$tabla_resumen_aux[$title] = "alto";} 
                            else if ($fila[$title] <= 16.65 && $fila[$title] > 8.33) {$tabla_resumen_aux[$title] = "intermedia";}
                            else if ($fila[$title] == 8.33) {$tabla_resumen_aux[$title] = "neutro";}
                            else if ($fila[$title] < 8.33) {$tabla_resumen_aux[$title] = "negativo";}

                        }else if (($data['selectedNivel']  == '2' || $data['selectedNivel']  == '4')){
                            if ($fila[$title] > 9.36) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if ($fila[$title] <= 9.36 && $fila[$title] > 6.25) {$tabla_resumen_aux[$title] = "alto";} 
                            else if ($fila[$title] <= 6.25 && $fila[$title] > 3.13) {$tabla_resumen_aux[$title] = "intermedia";}
                            else if ($fila[$title] == 3.13) {$tabla_resumen_aux[$title] = "neutro";}
                            else if ($fila[$title] < 3.13) {$tabla_resumen_aux[$title] = "negativo";}

                        }
                    }      
                           
                }else{
                    $tabla_resumen_aux[$title] = "normal2";
                }
                $cont ++;


            }
            $contador ++;
            array_push($clases_resumen_before, $tabla_resumen_aux);
            //$clases_resumen_before.push(tabla_resumen_aux);
            
        }
        return $clases_resumen_before;
    }
    /**
    *Función que evalúa las clases de  acuerdo a los criterios del concepto Variación, para los valores de la tabla valores.
    *@param array $data array de datos recibidos desde el formulario.
    *@param array $headers cabeceras para la tabla valores
    *@param arrya $valores tabla con los valores a evaluar
    *@param arrya $tabla_stadisticas_nacional tabla con los valores anivel nacional
    *@method array 
    */
    public function get_clases_variacion($data, $headers, $valores, $tabla_stadisticas_nacional){
        $start = 0;
        if(($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')){
            $start = 1;
        }else{
            $start = 0;
        }


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
                if ($contador != $start && $fila[$title] != 0) {
                    if (strstr($title_1, 'posicion')) {
                        if ($fila[$title] >= 1 && $fila[$title] <= 5) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila[$title] <= 10 && $fila[$title] > 5) {$tabla_resumen_aux[$title] = "alto";} 
                        else if ($fila[$title] <= 15 && $fila[$title] > 10) {$tabla_resumen_aux[$title] = "intermedia";}
                        else if ($fila[$title] <= 20 && $fila[$title] > 15) {$tabla_resumen_aux[$title] = "neutro";}
                        else if ($fila[$title] <= 32 && $fila[$title] > 20) {$tabla_resumen_aux[$title] = "negativo";}
                        else{$tabla_resumen_aux[$title] = "normal2";}

                    }else if($title == 'Actividad_economica'){
                        $tabla_resumen_aux[$title] = "normal2";
                    }else { 
                        if(($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')){
                            if ($fila[$title] >= $base_tabla[$title] && $fila[$title] >= 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if ($fila[$title] >= 0 && $fila[$title] < $base_tabla[$title]) {$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title] < 0){$tabla_resumen_aux[$title] = "negativo";}
                        }else if(($data['selectedNivel'] == '2' || $data['selectedNivel'] == '4')){
                            if ($fila[$title] >= $tabla_stadisticas_nacional[$contador-1][$title] && $fila[$title] >= 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if ($fila[$title] >= 0 && $fila[$title] < $tabla_stadisticas_nacional[$contador-1][$title]) {$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title] < 0){$tabla_resumen_aux[$title] = "negativo";}
                        }
                    }      
                           
                }else{
                    $tabla_resumen_aux[$title] = "normal2";
                }
                $cont ++;


            }
            $contador ++;
            //$clases_resumen_before.push(tabla_resumen_aux);
            array_push($clases_resumen_before, $tabla_resumen_aux);
            
        }
        //this.clases_variacion = clases_resumen_before;
        return $clases_resumen_before;
    }
    /**
    *Función que evalúa las clases de  acuerdo a los criterios del concepto Contribución, para los valores de la tabla valores.
    *@param array $data array de datos recibidos desde el formulario.
    *@param array $headers cabeceras para la tabla valores
    *@param arrya $participacion_porcentaje tabla con los valores de la participacion
    *@param array $variacion tabla con los valores a evaluar
    *@param arrya $variacion_nacional tabla con los valores anivel nacional
    *@method array 
    */
    public function get_clases_contribucion($data, $headers, $participacion_porcentaje, $variacion, $variacion_nacional){
        $start = 0;//dd($variacion_nacional);
        if(($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')){
            $start = 1;
        }else{
            $start = 0;
        }        
        $variacion_nacional_base = [];
        $base_tabla = $variacion[0];
        if(($data['selectedNivel'] == '2' || $data['selectedNivel'] == '4')){
            //$variacion_nacional_base = $variacion_nacional[0];
            $variacion_nacional_base = $variacion_nacional;    
        }
        //dd(['variacion_nacional_base'=>$participacion_porcentaje]);
        $clases_resumen_before = [];         
        $contador =1;
        //for (let $fila of this.participacion_porcentaje) {
        foreach ($participacion_porcentaje as $fila) {
            $cont = 0; 
            $tabla_resumen_aux = [];
            //for (let columna of this.headers){
            foreach ($headers as $columna) {
                $title_1 = $columna['header'];   
                $title = $columna['header'];
                if ($contador != $start && $fila[$title] != 0) {
                    if (strstr($title_1, 'posicion')) {
                        if ($fila[$title] >= 1 && $fila[$title] <= 5) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila[$title] <= 10 && $fila[$title] > 5) {$tabla_resumen_aux[$title] = "alto";} 
                        else if ($fila[$title] <= 15 && $fila[$title] > 10) {$tabla_resumen_aux[$title] = "intermedia";}
                        else if ($fila[$title] <= 20 && $fila[$title] > 15) {$tabla_resumen_aux[$title] = "neutro";}
                        else if ($fila[$title] <= 32 && $fila[$title] > 20) {$tabla_resumen_aux[$title] = "negativo";}
                        else{$tabla_resumen_aux[$title] = "normal2";}

                    }else if($title == 'Actividad_economica'){
                        $tabla_resumen_aux[$title] = "normal2";
                    }else { 
                        if(($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')&& $data['selectedCobertura'] != 'Manufactura'){
                            //var $title_aux = 'Contribución al Crecimiento(%)(B)'; 
                            if($base_tabla[$title] < 0 && $variacion[$contador-1][$title] >= 0){
                                if($fila[$title]*-1 > 15.77){$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title]*-1 <= 15.77 && $fila[$title]*-1 > 10.51) {$tabla_resumen_aux[$title] = "alto";}
                                else if($fila[$title]*-1 <= 10.51 && $fila[$title]*-1 > 5.26){$tabla_resumen_aux[$title] = "intermedia";}
                                else if($fila[$title]*-1 == 5.26 ){$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title]*-1 < 5.26 ){$tabla_resumen_aux[$title] = "negativo";}
                            }    
                            else if($base_tabla[$title]<0 && $variacion[$contador-1][$title]<0){
                                if($fila[$title]*-1 > 15.77) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title]*-1 <= 15.77 && $fila[$title]*-1 > 10.51){$tabla_resumen_aux[$title] = "alto";}
                                else if($fila[$title]*-1 <= 10.51 && $fila[$title]*-1 > 5.26){$tabla_resumen_aux[$title] = "intermedia";}
                                else if($fila[$title]*-1 == 5.26 ){$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title]*-1 < 5.26 ){$tabla_resumen_aux[$title] = "negativo";}
                            }   
                            else{
                                if($fila[$title] > 15.77) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title] <= 15.77 && $fila[$title] > 10.51) {$tabla_resumen_aux[$title] = "alto";}
                                else if($fila[$title] <= 10.51 && $fila[$title] > 5.26){$tabla_resumen_aux[$title] = "intermedia";}
                                else if($fila[$title] == 5.26 ){$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title] < 5.26 ){$tabla_resumen_aux[$title] = "negativo";}
                                
                            }
                        }else if(($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')&& $data['selectedCobertura'] == 'Manufactura'){
                            //var $title_aux = 'Contribución al Crecimiento(%)(B)'; 
                            if($base_tabla[$title] < 0 && $variacion[$contador-1][$title] >= 0){
                                if($fila[$title]*-1 > 24.98){$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title]*-1 <= 24.98 && $fila[$title]*-1 > 16.65) {$tabla_resumen_aux[$title] = "alto";}
                                else if($fila[$title]*-1 <= 16.65 && $fila[$title]*-1 > 8.33){$tabla_resumen_aux[$title] = "intermedia";}
                                else if($fila[$title]*-1 == 8.33 ){$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title]*-1 < 8.33 ){$tabla_resumen_aux[$title] = "negativo";}
                            }    
                            else if($base_tabla[$title]<0 && $variacion[$contador-1][$title]<0){
                                if($fila[$title]*-1 > 24.98) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title]*-1 <= 24.98 && $fila[$title]*-1 > 16.65){$tabla_resumen_aux[$title] = "alto";}
                                else if($fila[$title]*-1 <= 16.65 && $fila[$title]*-1 > 8.33){$tabla_resumen_aux[$title] = "intermedia";}
                                else if($fila[$title]*-1 == 8.33 ){$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title]*-1 < 8.33 ){$tabla_resumen_aux[$title] = "negativo";}
                            }   
                            else{
                                if($fila[$title] > 24.98) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title] <= 24.98 && $fila[$title] > 16.65) {$tabla_resumen_aux[$title] = "alto";}
                                else if($fila[$title] <= 16.65 && $fila[$title] > 8.33){$tabla_resumen_aux[$title] = "intermedia";}
                                else if($fila[$title] == 8.33 ){$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title] < 8.33 ){$tabla_resumen_aux[$title] = "negativo";}
                                
                            }
                        }else if(($data['selectedNivel'] == '2' || $data['selectedNivel'] == '4')){
                            //var $title_aux = 'Contribución al Crecimiento(%)(B)'; 
                            if($variacion_nacional_base[$contador-1][$title] < 0 && $variacion[$contador-1][$title] >= 0){
                                if($fila[$title]*-1 > 9.36){$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title]*-1 <= 9.36 && $fila[$title]*-1 > 6.25) {$tabla_resumen_aux[$title] = "alto";}
                                else if($fila[$title]*-1 <= 6.25 && $fila[$title]*-1 > 3.13){$tabla_resumen_aux[$title] = "intermedia";}
                                else if($fila[$title]*-1 == 3.13 ){$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title]*-1 < 3.13 ){$tabla_resumen_aux[$title] = "negativo";}
                            }    
                            else if($variacion_nacional_base[$contador-1][$title]<0 && $variacion[$contador-1][$title]<0){
                                if($fila[$title]*-1 > 9.36) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title]*-1 <= 9.36 && $fila[$title]*-1 > 6.25){$tabla_resumen_aux[$title] = "alto";}
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
    *@param arrya $variacion tabla con los valores de la variacion
    *@param arrya $variacion_nacional tabla con los valores de la variacion a nivel nacional
    *@method array 
    */
    public function get_clases_diferencia($data, $headers, $valores, $variacion, $variacion_nacional){
        $start = 0;
        if(($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')){
            $start = 1;
        }else{
            $start = 0;
        }
        $variacion_nacional_base = [];
        //$base_tabla = $variacion[0];
        if(($data['selectedNivel'] == '2' || $data['selectedNivel'] == '4')){
            //$variacion_nacional_base = $variacion_nacional[0];
            $variacion_nacional_base = $variacion_nacional;    
        }
        $base_tabla = $variacion[0];
        $clases_resumen_before = [];         
        $contador =1;
        //for (let fila of this.diferencia) {
        foreach ($valores as $fila) {
            $cont = 0; 
            $tabla_resumen_aux = [];
            //for (let columna of this.headers){
            foreach ($headers as $columna) {
                $title_1 = $columna['header'];   
                $title = $columna['header'];
                if ($contador != $start && $fila[$title] != 0) {
                    if (strstr($title_1, 'posicion')) {
                        if ($fila[$title] >= 1 && $fila[$title] <= 5) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila[$title] <= 10 && $fila[$title] > 5) {$tabla_resumen_aux[$title] = "alto";} 
                        else if ($fila[$title] <= 15 && $fila[$title] > 10) {$tabla_resumen_aux[$title] = "intermedia";}
                        else if ($fila[$title] <= 20 && $fila[$title] > 15) {$tabla_resumen_aux[$title] = "neutro";}
                        else if ($fila[$title] <= 32 && $fila[$title] > 20) {$tabla_resumen_aux[$title] = "negativo";}
                        else{$tabla_resumen_aux[$title] = "normal2";}

                    }else if($title == 'Actividad_economica'){
                        $tabla_resumen_aux[$title] = "normal2";
                    }else { 
                        if(($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')){
                            if($base_tabla[$title]<0 && $variacion[$contador-1][$title]>=0){
                                if($fila[$title]*-1 > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title]*-1 < 0){$tabla_resumen_aux[$title] = "negativo";}
                            }    
                            else if($base_tabla[$title]<0 && $variacion[$contador-1][$title]<0 && $fila[$title]>0){
                                if($fila[$title]*-1 > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title]*-1 < 0){$tabla_resumen_aux[$title] = "negativo";}
                            }/*************************No invertir signos**************/  
                            else if($base_tabla[$title]<0 && $variacion[$contador-1][$title]<0 && $fila[$title]<0){
                                if($fila[$title]*1 > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title]*1 < 0){$tabla_resumen_aux[$title] = "negativo";}
                            }    
                            else{
                                if($fila[$title] > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title] < 0){$tabla_resumen_aux[$title] = "negativo";}
                            }
                        }else if(($data['selectedNivel'] == '2' || $data['selectedNivel'] == '4')){
                           
                            if($variacion_nacional_base[$contador-1][$title]<0 && $variacion[$contador-1][$title]>=0){
                                if($fila[$title]*-1 > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title]*-1 < 0){$tabla_resumen_aux[$title] = "negativo";}
                            }    
                            else if($variacion_nacional_base[$contador-1][$title]<0 && $variacion[$contador-1][$title]<0 && $fila[$title]>0){
                                if($fila[$title]*-1 > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title]*-1 < 0){$tabla_resumen_aux[$title] = "negativo";}
                            }/*************************No invertir signos**************/    
                            else if($variacion_nacional_base[$contador-1][$title]<0 && $variacion[$contador-1][$title]<0 && $fila[$title]<0){
                                if($fila[$title]*1 > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title]*1 < 0){$tabla_resumen_aux[$title] = "negativo";}
                            }    
                            else{
                                if($fila[$title] > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                                else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                                else if($fila[$title] < 0){$tabla_resumen_aux[$title] = "negativo";}
                            }
                        }else{
                            $tabla_resumen_aux[$title] = "normal1";
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
}
