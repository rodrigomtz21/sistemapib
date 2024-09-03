<?php
/**
 *MapaDataController.php
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
use App\Libraries\MapasFunctions;
/**
*Clase que contiene los métodos de la funcionalidad del modulo Mapas de Desempeño Global.
*/
class MapaDataController extends Controller
{   
    /**
    *Función que valida los datos recibidos desde el formulario y llama al método consulta_principal() de la clase MapasFunctions, que retorna una tabla y sus cabeceras construidos con la información proporcionada.
    *@param array $request array de datos recibidos desde el formulario.
    *@method array 
    */
    public function mapa(Request $request){
        
        $funcion = new MapasFunctions();
    	$data = $request->all();
        $variables = [];

        foreach ($data['variables'] as $variable) {
            if($variable['selected']){
                array_push($variables, array("id"=>$variable['id'], "title"=>$variable['nombre']));   
            }
            
        }

        $data['variables'] = $variables;
        $tabla = $funcion->consulta_principal($data);
        $cabeceras = [];
        return response()->json(["tabla"=>$tabla['consulta'], 'cabeceras' => $tabla['headers']]);

    }
    /**
    *Función que valida los datos recibidos desde el formulario y llama al método consulta_principal() de la clase MapasFunctions, que retorna una tabla y sus cabeceras construidos con la información proporcionada y al final genera un archivo Excel
    *@param array $request array de datos recibidos desde el formulario.
    *@method array 
    */
    public function mapa_excel(Request $request){
        $funcion = new MapasFunctions();
        $evaluacion = new MapaDataController();
        $data = $request->all();
        $variables = [];

        foreach ($data['variables'] as $variable) {
            if($variable['selected'] == 'true'){
                array_push($variables, array("id"=>$variable['id'], "title"=>$variable['nombre']));   
            }
            
        }

        $data['variables'] = $variables;
        $tabla = $funcion->consulta_principal($data);
        $cabeceras = [];

        $data_excel['title_string'] = $data['title_string'];
        $data_excel['valoracion_status'] = true;
        $data_excel['participacion_status'] = false;
        $data_excel['variacion_status'] = false;
        $data_excel['contribucion_status'] = false;
        $data_excel['diferencia_status'] = false;

        $data_excel['headers'] = $tabla['headers'];
        $data_excel['valoracion'] = ['title_valores'=>$data['title_valores'],
            'data' => $tabla['consulta'],
            'clases' => $evaluacion->get_data_clases($data, $tabla['headers'], $tabla['consulta'])];
        $data['participacion'] = [];
        $data['variacion'] = [];
        $data['contribucion'] = [];
        $data['diferencia'] = [];
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
    *Función que evalúa las clases de  acuerdo a los criterios de todos los conceptos, para los valores de la tabla valores.
    *@param array $data array de datos recibidos desde el formulario.
    *@param array $headers cabeceras para la tabla valores
    *@param arrya $tabla_global tabla con los valores a evaluar
    *@method array 
    */
    public function get_data_clases($data, $headers, $tabla_global){
        $clases_global_before = [];
        $base_tabla = $tabla_global[0];
        $flag = 0; 
        if ($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3') {
            $flag = 1;
        }else{
            $flag = 0;
        }
        $contador =1;
        foreach ($tabla_global as $fila) {
            $cont = 0; 
            $tabla_global_aux = [];
            foreach ($headers as $columna) {
                $title = $columna['header']; 
                if ($contador != $flag && $fila[$title] != 0) {
                    if ($title == 'Valor'){/****Para Nivel 1 y 3*********/
                        if ($fila['part'] > 15.00) {$tabla_global_aux[$title] = "muy-alto";}
                        else if ($fila['part'] <= 15.00 && $fila['part'] > 10.00) {$tabla_global_aux[$title] = "alto";} 
                        else if ($fila['part'] <= 10.00 && $fila['part'] > 5.00) {$tabla_global_aux[$title] = "intermedia";}
                        else if ($fila['part'] == 5.00) {$tabla_global_aux[$title] = "neutro";}
                        else if ($fila['part'] < 5.00) {$tabla_global_aux[$title] = "negativo";}

                    }else if ($title == '0' && ($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')&& $data['selectedCobertura'] != 'Manufactura'){
                        if ($fila['part'] > 15.00) {$tabla_global_aux[$title] = "muy-alto";}
                        else if ($fila['part'] <= 15.00 && $fila['part'] > 10.00) {$tabla_global_aux[$title] = "alto";} 
                        else if ($fila['part'] <= 10.00 && $fila['part'] > 5.00) {$tabla_global_aux[$title] = "intermedia";}
                        else if ($fila['part'] == 5.00) {$tabla_global_aux[$title] = "neutro";}
                        else if ($fila['part'] < 5.00) {$tabla_global_aux[$title] = "negativo";}

                    }else if ($title == '0' && ($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')&& $data['selectedCobertura'] == 'Manufactura'){
                        if ($fila['part'] > 24.98) {$tabla_global_aux[$title] = "muy-alto";}
                        else if ($fila['part'] <= 24.98 && $fila['part'] > 16.65) {$tabla_global_aux[$title] = "alto";} 
                        else if ($fila['part'] <= 16.65 && $fila['part'] > 8.33) {$tabla_global_aux[$title] = "intermedia";}
                        else if ($fila['part'] == 8.33) {$tabla_global_aux[$title] = "neutro";}
                        else if ($fila['part'] < 8.33) {$tabla_global_aux[$title] = "negativo";}

                    }else if (($title == '0' || $title == 'Valor estatal') && ($data['selectedNivel'] == '2' || $data['selectedNivel'] == '4')){
                        if ($fila['part'] > 9.36) {$tabla_global_aux[$title] = "muy-alto";}
                        else if ($fila['part'] <= 9.36 && $fila['part'] > 6.25) {$tabla_global_aux[$title] = "alto";} 
                        else if ($fila['part'] <= 6.25 && $fila['part'] > 3.13) {$tabla_global_aux[$title] = "intermedia";}
                        else if ($fila['part'] == 3.13) {$tabla_global_aux[$title] = "neutro";}
                        else if ($fila['part'] < 3.13) {$tabla_global_aux[$title] = "negativo";}

                    }/* Colores clases posiciones*////
                    else if ($title == 'posicion_1' || $title == 'posicion_2' || $title == 'posicion_3' || $title == 'posicion_4') { 
                        if ($fila[$title] >= 1 && $fila[$title] <= 5) {$tabla_global_aux[$title] = "muy-alto";}
                        else if ($fila[$title] <= 10 && $fila[$title] > 5) {$tabla_global_aux[$title] = "alto";} 
                        else if ($fila[$title] <= 15 && $fila[$title] > 10) {$tabla_global_aux[$title] = "intermedia";}
                        else if ($fila[$title] <= 20 && $fila[$title] > 15) {$tabla_global_aux[$title] = "neutro";}
                        else if ($fila[$title] <= 32 && $fila[$title] > 20) {$tabla_global_aux[$title] = "negativo";}
                        else{ $tabla_global_aux[$title] = "normal2"; }
                    }else if (($title == 'posicion_1' || $title == 'posicion_2' || $title == 'posicion_3' || $title == 'posicion_4') && $data['selectedNivel'] == '4') { 
                        if ($fila[$title] >= 1 && $fila[$title] <= 5) {$tabla_global_aux[$title] = "muy-alto";}
                        else if ($fila[$title] <= 10 && $fila[$title] > 5) {$tabla_global_aux[$title] = "alto";} 
                        else if ($fila[$title] <= 15 && $fila[$title] > 10) {$tabla_global_aux[$title] = "intermedia";}
                        else if ($fila[$title] <= 20 && $fila[$title] > 15) {$tabla_global_aux[$title] = "neutro";}
                        else if ($fila[$title] <= 32 && $fila[$title] > 20) {$tabla_global_aux[$title] = "negativo";}
                        else{ $tabla_global_aux[$title] = "normal2"; }
                    }/*Colores clases Variacion*/
                    else if($title == '1' && ($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')){
                        if ($fila[$title] >= $base_tabla[$title] && $fila[$title] >= 0) {$tabla_global_aux[$title] = "muy-alto";}
                        else if ($fila[$title] >= 0 && $fila[$title] < $base_tabla[$title]) {$tabla_global_aux[$title] = "neutro";}
                        else if($fila[$title] < 0){$tabla_global_aux[$title] = "negativo";}
                    }else if($title == '1estatal' && ($data['selectedNivel'] == '2' || $data['selectedNivel'] == '4')){
                        //$title_aux = 'Valor nacional';
                        $title_aux = '1nacional';
                        if ($fila[$title] >= $fila[$title_aux] && $fila[$title] >= 0) {$tabla_global_aux[$title] = "muy-alto";}
                        else if ($fila[$title] >= 0 && $fila[$title] < $fila[$title_aux]) {$tabla_global_aux[$title] = "neutro";}
                        else if($fila[$title] < 0){$tabla_global_aux[$title] = "negativo";}
                    }/*Colores clases Contribucion al crecimiento*/
                    else if($title == '2puntos' && ($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')&& $data['selectedCobertura'] != 'Manufactura'){
                        $title_aux = '2porcentaje'; 
                        if($base_tabla['var'] < 0 && $fila['var'] >= 0){
                            if($fila[$title_aux]*-1 > 15.00){$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title_aux]*-1 <= 15.00 && $fila[$title_aux]*-1 > 10.00) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title_aux]*-1 <= 10.00 && $fila[$title_aux]*-1 > 5.00){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title_aux]*-1 == 5.00 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title_aux]*-1 < 5.00 ){$tabla_global_aux[$title] = "negativo";}
                        }    
                        else if($base_tabla['var']<0 && $fila['var']<0){
                            if($fila[$title_aux]*-1 > 15.00) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title_aux]*-1 <= 15.00 && $fila[$title_aux]*-1 > 10.00){$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title_aux]*-1 <= 10.00 && $fila[$title_aux]*-1 > 5.00){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title_aux]*-1 == 5.00 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title_aux]*-1 < 5.00 ){$tabla_global_aux[$title] = "negativo";}
                        }   
                        else{
                            if($fila[$title_aux] > 15.00) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title_aux] <= 15.00 && $fila[$title_aux] > 10.00) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title_aux] <= 10.00 && $fila[$title_aux] > 5.00){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title_aux] == 5.00 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title_aux] < 5.00 ){$tabla_global_aux[$title] = "negativo";}
                            
                        }
                    }else if($title == '2porcentaje' && ($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')&& $data['selectedCobertura'] != 'Manufactura'){
                        

                        if($base_tabla['var']<0 && $fila['var']>=0){
                            if($fila[$title]*-1 > 15.00) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title]*-1 <= 15.00 && $fila[$title]*-1 > 10.00) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title]*-1 <= 10.00 && $fila[$title]*-1 > 5.00){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title]*-1 == 5.00 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 5.00 ){$tabla_global_aux[$title] = "negativo";}
                        }  
                        else if($base_tabla['var']<0 && $fila['var']<0){
                            if($fila[$title]*-1 > 15.00) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title]*-1 <= 15.00 && $fila[$title]*-1 > 10.00) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title]*-1 <= 10.00 && $fila[$title]*-1 > 5.00){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title]*-1 == 5.00 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 5.00 ){$tabla_global_aux[$title] = "negativo";}
                        }    
                        else{
                            if($fila[$title] > 15.00) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title] <= 15.00 && $fila[$title] > 10.00) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title] <= 10.00 && $fila[$title] > 5.00){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title] == 5.00 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title] < 5.00 ){$tabla_global_aux[$title] = "negativo";}
                        }

                    }else if($title == '2puntos' && ($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')&& $data['selectedCobertura'] == 'Manufactura'){
                        $title_aux = '2porcentaje'; 
                        if($base_tabla['var'] < 0 && $fila['var'] >= 0){
                            if($fila[$title_aux]*-1 > 24.98){$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title_aux]*-1 <= 24.98 && $fila[$title_aux]*-1 > 16.65) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title_aux]*-1 <= 16.65 && $fila[$title_aux]*-1 > 8.33){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title_aux]*-1 == 8.33 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title_aux]*-1 < 8.33 ){$tabla_global_aux[$title] = "negativo";}
                        }    
                        else if($base_tabla['var']<0 && $fila['var']<0){
                            if($fila[$title_aux]*-1 > 24.98) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title_aux]*-1 <= 24.98 && $fila[$title_aux]*-1 > 16.65){$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title_aux]*-1 <= 16.65 && $fila[$title_aux]*-1 > 8.33){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title_aux]*-1 == 8.33 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title_aux]*-1 < 8.33 ){$tabla_global_aux[$title] = "negativo";}
                        }   
                        else{
                            if($fila[$title_aux] > 24.98) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title_aux] <= 24.98 && $fila[$title_aux] > 16.65) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title_aux] <= 16.65 && $fila[$title_aux] > 8.33){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title_aux] == 8.33 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title_aux] < 8.33 ){$tabla_global_aux[$title] = "negativo";}
                            
                        }
                    }else if($title == '2porcentaje' && ($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')&& $data['selectedCobertura'] == 'Manufactura'){
                        

                        if($base_tabla['var']<0 && $fila['var']>=0){
                            if($fila[$title]*-1 > 24.98) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title]*-1 <= 24.98 && $fila[$title]*-1 > 16.65) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title]*-1 <= 16.65 && $fila[$title]*-1 > 8.33){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title]*-1 == 8.33 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 8.33 ){$tabla_global_aux[$title] = "negativo";}
                        }  
                        else if($base_tabla['var']<0 && $fila['var']<0){
                            if($fila[$title]*-1 > 24.98) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title]*-1 <= 24.98 && $fila[$title]*-1 > 16.65) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title]*-1 <= 16.65 && $fila[$title]*-1 > 8.33){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title]*-1 == 8.33 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 8.33 ){$tabla_global_aux[$title] = "negativo";}
                        }    
                        else{
                            if($fila[$title] > 24.98) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title] <= 24.98 && $fila[$title] > 16.65) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title] <= 16.65 && $fila[$title] > 8.33){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title] == 8.33 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title] < 8.33 ){$tabla_global_aux[$title] = "negativo";}
                        }

                    }else if($title == '2puntos' && ($data['selectedNivel'] == '2' || $data['selectedNivel'] == '4')){
                        $title_aux = '2porcentaje'; 
                        if($fila['var_nacional'] < 0 && $fila['var_estatal'] >= 0){
                            if($fila[$title_aux]*-1 > 9.36){$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title_aux]*-1 <= 9.36 && $fila[$title_aux]*-1 > 6.25) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title_aux]*-1 <= 6.25 && $fila[$title_aux]*-1 > 3.13){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title_aux]*-1 == 3.13 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title_aux]*-1 < 3.13 ){$tabla_global_aux[$title] = "negativo";}
                        }    
                        else if($fila['var_nacional']<0 && $fila['var_estatal']<0){
                            if($fila[$title_aux]*-1 > 9.36) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title_aux]*-1 <= 9.36 && $fila[$title_aux]*-1 > 6.25){$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title_aux]*-1 <= 6.25 && $fila[$title_aux]*-1 > 3.13){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title_aux]*-1 == 3.13 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title_aux]*-1 < 3.13 ){$tabla_global_aux[$title] = "negativo";}
                        }   
                        else{
                            if($fila[$title_aux] > 9.36) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title_aux] <= 9.36 && $fila[$title_aux] > 6.25) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title_aux] <= 6.25 && $fila[$title_aux] > 3.13){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title_aux] == 3.13 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title_aux] < 3.13 ){$tabla_global_aux[$title] = "negativo";}
                            
                        }
                    }else if($title == '2porcentaje' && ($data['selectedNivel'] == '2' || $data['selectedNivel'] == '4')){
                        

                        if($fila['var_nacional']<0 && $fila['var_estatal']>=0){
                            //dd(['1'=>$fila['Variación Anual (%) Nacional']]);
                            if($fila[$title]*-1 > 9.36) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title]*-1 <= 9.36 && $fila[$title]*-1 > 6.25) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title]*-1 <= 6.25 && $fila[$title]*-1 > 3.13){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title]*-1 == 3.13 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 3.13 ){$tabla_global_aux[$title] = "negativo";}
                        }  
                        else if($fila['var_nacional']<0 && $fila['var_estatal']<0){
                            //dd(['2'=>$title]);
                            if($fila[$title]*-1 > 9.36) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title]*-1 <= 9.36 && $fila[$title]*-1 > 6.25) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title]*-1 <= 6.25 && $fila[$title]*-1 > 3.13){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title]*-1 == 3.13 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 3.13 ){$tabla_global_aux[$title] = "negativo";}
                        }    
                        else{
                            //dd(['3'=>$base_tabla['Variación Anual (%) Nacional']]);
                            if($fila[$title] > 9.36) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title] <= 9.36 && $fila[$title] > 6.25) {$tabla_global_aux[$title] = "alto";}
                            else if($fila[$title] <= 6.25 && $fila[$title] > 3.13){$tabla_global_aux[$title] = "intermedia";}
                            else if($fila[$title] == 3.13 ){$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title] < 3.13 ){$tabla_global_aux[$title] = "negativo";}
                        }

                    }/*colores clases diferencia*/
                    else if($title == '3' && ($data['selectedNivel'] == '1' || $data['selectedNivel'] == '3')){
                       
                        if($base_tabla['var']<0 && $fila['var']>=0){
                            if($fila[$title]*-1 > 0) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 0){$tabla_global_aux[$title] = "negativo";}
                        }    
                        else if($base_tabla['var']<0 && $fila['var']<0 && $fila[$title]>0){
                            if($fila[$title]*-1 > 0) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 0){$tabla_global_aux[$title] = "negativo";}
                        }    
                        else if($base_tabla['var']<0 && $fila['var']<0 && $fila[$title]<0){
                            if($fila[$title]*-1 > 0) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 0){$tabla_global_aux[$title] = "negativo";}
                        }    
                        else{
                            if($fila[$title] > 0) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title] < 0){$tabla_global_aux[$title] = "negativo";}
                        }
                    }else if($title == '3' && ($data['selectedNivel'] == '2' || $data['selectedNivel'] == '4')){
                       
                        if($fila['var_nacional']<0 && $fila['var_estatal']>=0){
                            if($fila[$title]*-1 > 0) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 0){$tabla_global_aux[$title] = "negativo";}
                        }    
                        else if($fila['var_nacional']<0 && $fila['var_estatal']<0 && $fila[$title]>0){
                            if($fila[$title]*-1 > 0) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 0){$tabla_global_aux[$title] = "negativo";}
                        }/*************************No invertir signos**************/    
                        else if($fila['var_nacional']<0 && $fila['var_estatal']<0 && $fila[$title]<0){
                            if($fila[$title]*1 > 0) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title]*1 < 0){$tabla_global_aux[$title] = "negativo";}
                        }    
                        else{
                            if($fila[$title] > 0) {$tabla_global_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_global_aux[$title] = "neutro";}
                            else if($fila[$title] < 0){$tabla_global_aux[$title] = "negativo";}
                        }
                    }else{
                        $tabla_global_aux[$title] = "normal1";
                    }        
                           
                }else{
                    $tabla_global_aux[$title] = "normal2";
                }
                $cont ++;


            }
            $contador ++;

            array_push($clases_global_before, $tabla_global_aux);
            
        }
        return $clases_global_before;
    }
}
