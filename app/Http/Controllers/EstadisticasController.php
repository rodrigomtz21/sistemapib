<?php
/**
 * EstadisticasController.php
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
use App\Libraries\EstadisticasFunctions;
use App\Libraries\PibFunctions;
/**
*Clase que contiene los metodos de la funcionalidad del modulo Estadisticas.
*/
class EstadisticasController extends Controller
{
    /**
    *Función que valida los datos recividos desde el formulario y llama al metodo main_consulta() que retorna una tabla y suscabeceras contruida con la informacion proprcionada.
    *@param array $request array de datos recividos desde el formulario.
    */
    public function main_estadisticas(Request $request)
    {   

    	$data = $request->all();
        $estadistica = new EstadisticasFunctions();
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

        $regiones = [];
        foreach ($data['regiones'] as $region) {
            if($region['selected']){
                $regiones=array_merge($regiones,array($region['id']));   
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
        $data['regiones'] = $regiones;
        
        $tabla = $estadistica->main_consulta($data);
        
        return response()->json($tabla);

        
    }
    /**
    *Función que valida los datos recividos desde el formulario y llama al metodo main_consulta() que retorna una tabla y sus cabeceras contruida con la informacion proporcionada y despues genera un archivo excel con el resultado de la consulta.
    *@param array $request array de datos recividos desde el formulario.
    */
    public function main_estadisticas_excel(Request $request)
    {   

        $data = $request->all();
        $estadistica = new EstadisticasFunctions();
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

        $regiones = [];
        foreach ($data['regiones'] as $region) {
            if($region['selected'] == 'true'){
                $regiones=array_merge($regiones,array($region['id']));   
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
        $data['regiones'] = $regiones;
        if ($data['flag_promedios'] == 'false') {
            $data['promedios'] = [];
        }
        $tabla = $estadistica->main_consulta($data);
       

        $data_excel['title_string'] = $data['title_string'];
        $data_excel['valoracion_status'] = true;
        $data_excel['participacion_status'] = false;
        $data_excel['variacion_status'] = false;
        $data_excel['contribucion_status'] = false;
        $data_excel['diferencia_status'] = false;


        $data_excel['headers'] = $tabla['headers'];
        $data_excel['valoracion'] = ['title_valores'=>'Valores',
            'data' => $tabla['estadisticas']];

        foreach ($data['variables'] as $variable) {
            if($variable['id'] == '0'){
                $data_excel['participacion_status'] = true;
                $data_excel['participacion'] = ['title_participacion'=>'Participación (%)(A)',
                    'data' => $tabla['participacion']];

            }elseif ($variable['id'] == '1') {
                $data_excel['variacion_status'] = true;
                $data_excel['variacion'] = ['title_variacion'=>'Variación Anual (%)',
                    'data' => $tabla['variacion']];

            }elseif ($variable['id'] == '2') {
                $data_excel['contribucion_status'] = true;
                $data_excel['contribucion'] = ['title_participacion_puntos'=>'Contribución al Crecimiento(Puntos)',
                    'data_participacion_puntos' => $tabla['contribucion_puntos'],
                    'title_participacion_porcentaje'=>'Contribución al Crecimiento(%)(B)',
                    'data_participacion_porcentaje' => $tabla['contribucion_porcentaje']];

            }elseif ($variable['id'] == '3') {
                $data_excel['diferencia_status'] = true;
                $data_excel['diferencia'] = ['title_diferencia'=>'Diferencia (B)-(A)(Desempeño global)',
                    'data' => $tabla['diferencia']];
            }
            
        }
        /** 
        *Construir archivo excel 
        *@var string $data['name_file'] Nombre del archivo
        *@var array $data_excel array con los datos y cabeceras utilizados para contruir el archivo
        */
        Excel::create($data['name_file'], function($excel) use ($data_excel) {

            $excel->sheet('Current Stock Report', function($sheet) use ($data_excel) {
                $sheet->loadView('estadisticas')->with('data',$data_excel);
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
}
