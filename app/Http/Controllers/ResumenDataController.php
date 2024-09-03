<?php
/**
 *ResumenDataController.php
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
use App\Libraries\ResumenFunctions;
/**
*Clase que contiene los métodos de la funcionalidad del modulo Resumenes de produccion.
*/
class ResumenDataController extends Controller
{
    /**
    *Función que valida los datos recibidos desde el formulario y llama al método consulta_principal() de la clase ResumenFunctions, que retorna una tabla y sus cabeceras construidos con la información proporcionada.
    *@param array $request array de datos recibidos desde el formulario.
    *@method array 
    */
    public function resumen(Request $request)
    {   
    	//dd(['resumen'=>$request]);
    	$data = $request->all();
        //dd(['data'=>$data]);
        $resumen = new ResumenFunctions();
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

        $entidades = [0=>'0'];
        foreach ($data['entidades'] as $entidad) {
            if($entidad['selected']){
                $entidades=array_merge($entidades,array($entidad['id']));    
            }
            
        }

        $regiones = [];
        foreach ($data['regiones'] as $region) {
            if($region['selected']){
                $regiones=array_merge($regiones,array($region['id']));   
            }
            
        }

        $data['variables'] = $variables;
        $data['entidades'] = $entidades;
        
        $tabla = $resumen->consulta_principal($data);
        
        return response()->json($tabla);

        
    }
    /**
    *Función que valida los datos recibidos desde el formulario y llama al método consulta_principal() de la clase ResumenFunctions, que retorna una tabla y sus cabeceras construidos con la información proporcionada, para ser representada en un mapa geografico.
    *@param array $request array de datos recibidos desde el formulario.
    *@method array 
    */
    public function resumen_mapa(Request $request)
    {   
        $data = $request->all();
        $resumen = new ResumenFunctions();
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

        $entidades = [0=>'0'];
        foreach ($data['entidades'] as $entidad) {
            # code...
            if($entidad['selected']){
                $entidades=array_merge($entidades,array($entidad['id'])); 
                //$entidades=array_merge($entidades,array($entidad['nombre_area']));    
            }
            
        }
        $regiones = [];
        foreach ($data['regiones'] as $region) {
            if($region['selected']){
                $regiones=array_merge($regiones,array($region['id']));    
            }
            
        }

        $data['variables'] = $variables;
        $data['entidades'] = $entidades;
        
        $tabla = $resumen->consulta_principal_mapa($data);
        return response()->json($tabla);

        
    }

    /**
    *Función que valida los datos recibidos desde el formulario y llama al método consulta_principal() de la clase ResumenFunctions, que retorna una tabla y sus cabeceras construidos con la información proporcionada.
    *@param array $request array de datos recibidos desde el formulario y al final generar un archivo Excel.
    *@method array 
    */
    public function resumen_excel(Request $request)
    {   

        $data = $request->all();
        $resumen = new ResumenFunctions();
        $evaluacion = new ResumenDataController();
        if ($data['selectedValoracion']==1) {
            $data['selectedValoracion'] = 'pib_precios_constantes';
        }elseif($data['selectedValoracion']==2){
            $data['selectedValoracion'] = 'pib_precios_corrientes';
        }
        //dd($data['variables']);
        $variables = [];
        foreach ($data['variables'] as $variable) {
            # code...
            if($variable['selected'] == "true"){
                array_push($variables, array("id"=>$variable['id'], "title"=>$variable['nombre']));  
            }
            
        }

        $entidades = [0=>'0'];
        
        foreach ($data['entidades'] as $entidad) {
            # code...
            if($entidad['selected'] == "true"){
                $entidades=array_merge($entidades,array($entidad['id']));    
            }
            
        }
        $regiones = [];
        foreach ($data['regiones'] as $region) {
            # code...
            if($region['selected'] == "true"){
                $regiones=array_merge($regiones,array($region['id']));     
            }
            
        }

        $data['variables'] = $variables;
        $data['entidades'] = $entidades;
        
        $tabla = $resumen->consulta_principal($data);
        
        //return response()->json($tabla);
        $data_excel['title_string'] = $data['title_string'];
        $data_excel['valoracion_status'] = true;
        $data_excel['participacion_status'] = false;
        $data_excel['variacion_status'] = false;
        $data_excel['contribucion_status'] = false;
        $data_excel['diferencia_status'] = false;

        $data_excel['headers'] = $tabla['cabeceras'];
        $data_excel['valoracion'] = ['title_valores'=>$data['title_valores'],
            'data' => $tabla['tabla'],
            'clases' => $evaluacion->get_data_clases($data, $tabla['cabeceras'], $tabla['tabla'])];
        $data['participacion'] = [];
        $data['variacion'] = [];
        $data['contribucion'] = [];
        $data['diferencia'] = [];
        //dd($data_excel['valoracion']['clases']);
        //return response()->json(["tabla"=>$tabla, 'cabeceras' => $cabeceras]);
        //return $tabla = \View::make('tabla')->with(['data'=>$data_excel]);
        Excel::create($data['name_file'], function($excel) use ($data_excel) {

            $excel->sheet('Resúmen de producción', function($sheet) use ($data_excel) {
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
    *Función que evalúa las clases de  acuerdo a los criterios de todos los conceptos, para los valores de la tabla tabla_resumen.
    *@param array $data array de datos recibidos desde el formulario.
    *@param array $headers cabeceras para la tabla valores
    *@param arrya $tabla_resumen tabla con los valores a evaluar
    *@method array 
    */
    public function get_data_clases($data, $headers, $tabla_resumen){
        $clases_resumen_before = [];
        $base_tabla = $tabla_resumen[0];
        
        $contador =1;
        //for (let fila of this.tabla_resumen) {
        foreach ($tabla_resumen as $fila) {
            $cont = 0; 
            $tabla_resumen_aux = [];
            //for (let columna of this.cabeceras){
            foreach ($headers as $columna) {
                //fila[columna.cabecera]
                $title = $columna['header']; 
                if ($contador != 1 && $fila['Valor'] != 0) {
                    // code...
                    if ($title == 'Valor' || $title == '0') {
                        if ($fila['part'] > 9.36) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila['part'] <= 9.36 && $fila['part'] > 6.24) {$tabla_resumen_aux[$title] = "alto";} 
                        else if ($fila['part'] <= 6.24 && $fila['part'] > 3.13) {$tabla_resumen_aux[$title] = "intermedia";}
                        else if ($fila['part'] == 3.13) {$tabla_resumen_aux[$title] = "neutro";}
                        else if ($fila['part'] < 3.13) {$tabla_resumen_aux[$title] = "negativo";}

                    }else if ($title == 'posicion_1' || $title == 'posicion_2' || $title == 'posicion_3' || $title == 'posicion_4') { 
                        if ($fila[$title] >= 1 && $fila[$title] <= 5) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila[$title] <= 10 && $fila[$title] > 5) {$tabla_resumen_aux[$title] = "alto";} 
                        else if ($fila[$title] <= 15 && $fila[$title] > 10) {$tabla_resumen_aux[$title] = "intermedia";}
                        else if ($fila[$title] <= 20 && $fila[$title] > 15) {$tabla_resumen_aux[$title] = "neutro";}
                        else if ($fila[$title] <= 32 && $fila[$title] > 20) {$tabla_resumen_aux[$title] = "negativo";}
                        else{$tabla_resumen_aux[$title] = "normal2";}
                    } else if($title == '1'){
                        //var $title_aux = this.cabeceras[(cont-1)]['$title'];
                        if ($fila[$title] >= $base_tabla[$title] && $fila[$title] >= 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                        else if ($fila[$title] >= 0 && $fila[$title] < $base_tabla[$title]) {$tabla_resumen_aux[$title] = "neutro";}
                        else if($fila[$title] < 0){$tabla_resumen_aux[$title] = "negativo";}
                    }else if($title == '2puntos'){
                        $title_aux = '2porcentaje';                                
                        //console.log($title);
                        if($base_tabla['var'] < 0 && $fila['var'] >= 0){
                            if($fila[$title_aux]*-1 > 9.36){$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title_aux]*-1 <= 9.36 && $fila[$title_aux]*-1 > 6.25) {$tabla_resumen_aux[$title] = "alto";}
                            else if($fila[$title_aux]*-1 <= 6.25 && $fila[$title_aux]*-1 > 3.13){$tabla_resumen_aux[$title] = "intermedia";}
                            else if($fila[$title_aux]*-1 == 3.13 ){$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title_aux]*-1 < 3.13 ){$tabla_resumen_aux[$title] = "negativo";}
                        }    
                        else if($base_tabla['var']<0 && $fila['var']<0){
                            if($fila[$title_aux]*-1 > 9.36) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title_aux]*-1 <= 9.36 && $fila[$title_aux]*-1 > 6.25){$tabla_resumen_aux[$title] = "alto";}
                            else if($fila[$title_aux]*-1 <= 6.25 && $fila[$title_aux]*-1 > 3.13){$tabla_resumen_aux[$title] = "intermedia";}
                            else if($fila[$title_aux]*-1 == 3.13 ){$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title_aux]*-1 < 3.13 ){$tabla_resumen_aux[$title] = "negativo";}
                        }   
                        else{
                            if($fila[$title_aux] > 9.36) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title_aux] <= 9.36 && $fila[$title_aux] > 6.25) {$tabla_resumen_aux[$title] = "alto";}
                            else if($fila[$title_aux] <= 6.25 && $fila[$title_aux] > 3.13){$tabla_resumen_aux[$title] = "intermedia";}
                            else if($fila[$title_aux] == 3.13 ){$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title_aux] < 3.13 ){$tabla_resumen_aux[$title] = "negativo";}
                            
                        }
                    }else if($title == '2porcentaje'){
                        

                        if($base_tabla['var']<0 && $fila['var']>=0){
                            if($fila[$title]*-1 > 9.36) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title]*-1 <= 9.36 && $fila[$title]*-1 > 6.25) {$tabla_resumen_aux[$title] = "alto";}
                            else if($fila[$title]*-1 <= 6.25 && $fila[$title]*-1 > 3.13){$tabla_resumen_aux[$title] = "intermedia";}
                            else if($fila[$title]*-1 == 3.13 ){$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 3.13 ){$tabla_resumen_aux[$title] = "negativo";}
                        }  
                        else if($base_tabla['var']<0 && $fila['var']<0){
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

                    }else if($title == '3'){
                       
                        if($base_tabla['var']<0 && $fila['var']>=0){
                            if($fila[$title]*-1 > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 0){$tabla_resumen_aux[$title] = "negativo";}
                        }    
                        else if($base_tabla['var']<0 && $fila['var']<0 && $fila[$title]>0){
                            if($fila[$title]*-1 > 0) {$tabla_resumen_aux[$title] = "muy-alto";}
                            else if($fila[$title] == 0) {$tabla_resumen_aux[$title] = "neutro";}
                            else if($fila[$title]*-1 < 0){$tabla_resumen_aux[$title] = "negativo";}
                        }/*************************No invertir signos**************/
                        else if($base_tabla['var']<0 && $fila['var']<0 && $fila[$title]<0){
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
