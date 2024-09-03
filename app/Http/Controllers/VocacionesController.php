<?php
/**
 *VocacionesController.php
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
use Illuminate\Support\Facades\DB;
use App\Libraries\PibFunctions;
/**
*Clase que contiene los métodos de la funcionalidad del modulo Vocaciones regionales.
*/
class VocacionesController extends Controller
{   /**
    *Función que valida los datos recibidos desde el formulario y llama a todos los metodos necesarios para construir la tabla de vocaciones regionales , que retorna una tabla y sus cabeceras construidos con la información proporcionada.
    *@param array $request array de datos recibidos desde el formulario.
    *@method array 
    */
    public function main_vocacion(Request $request)
    {   
        $function = new VocacionesController();
        $pib = new PibFunctions();
    	$data = $request->all();
    	$regiones = DB::table('regiones')->select('Nombre', 'clave')->orderBy('clave')->distinct()->get();
        $entidades = DB::table('mge')->select('id', 'nom_ent', 'cve_ent')->get();

        $tabla = [];
        $table = [];
        $conceptos = [];

    	if($data['selectedNivel'] == "1"){
    		$tabla = $function->consulta($data['selectedCobertura'], 'pib_precios_constantes', $data['selectedRegion']);
            //dd($tabla);
            /***************Crear array conceptos***********/
            $conceptos = $function->get_conceptos($tabla);
            /***************Crear array conceptos end***********/
            
            $table = [];
            if ($data['selectedPeriodo'] == 'Ayo') {
                $table = $function->step_1($conceptos, $tabla, $data['selectedAyo']);
            }else{                                                                                                                                                                                                                                                                          
                $promedio = $function->promedio(['consulta'=>$tabla, 'year_1'=>$data['selectedAyo_1'], 'year_2'=>$data['selectedAyo_2']]);
                $table = $function->step_1($conceptos, $promedio, 'Promedio');
                
            }
    	}else{
            $tabla_conceptos = $function->get_total_regiones($regiones, $data['selectedCobertura']);
            $conceptos = $function->get_conceptos($tabla_conceptos);
            $table = [];
            if ($data['selectedPeriodo'] == 'Ayo') {
                $table = $function->step_1($conceptos, $tabla_conceptos, $data['selectedAyo']);
            }else{
                $promedio = $function->promedio(['consulta'=>$tabla_conceptos, 'year_1'=>$data['selectedAyo_1'], 'year_2'=>$data['selectedAyo_2']]);
                $table = $function->step_1($conceptos, $promedio, 'Promedio');
            }
    	}

        $coeficientes = $function->step_2($table); 
        $localizacion = $function->step_3($coeficientes);
        $clases_localizacion = $function->get_clases($localizacion);

        /***********Generar nombres de las tablas Localizacion*************/
        $name_tables = [];
        foreach ($localizacion as $key => $value) {
            if($data['selectedNivel'] == "1"){
                foreach ($entidades as $entidad) {
                    if ('e_'.$entidad->cve_ent == $key) {
                        array_push($name_tables, ['name'=>$key, 'nickname'=>$entidad->nom_ent]);        
                    }
                }
            }else{
                foreach ($regiones as $region) {
                    if ('e_'.$region->clave == $key) {
                        array_push($name_tables, ['name'=>$key, 'nickname'=>$region->Nombre]);        
                    }
                }
            }
        }
        /***********Generar nombres de las tablas Localizacion end*************/

        /*********Generar headers de tablas************/
        $names_table = [];
        array_push($names_table, ['name'=>'concepto', 'nickname'=>'Concepto']); 
        foreach ($table[0] as $key => $value) {
            if($data['selectedNivel'] == "1"){
                foreach ($entidades as $entidad) {
                    if ('e_'.$entidad->cve_ent == $key) {
                        array_push($names_table, ['name'=>$key, 'nickname'=>$entidad->nom_ent]);        
                    }
                }
            }else{
                foreach ($regiones as $region) {
                    if ('e_'.$region->clave == $key) {
                        array_push($names_table, ['name'=>$key, 'nickname'=>$region->Nombre]);        
                    }
                }
            }
            
        }
        array_push($names_table, ['name'=>'total', 'nickname'=>'Total']); 
        /*********Generar headers de tablas end************/
        
    	return response()->json(['names'=>$name_tables, 'values'=>$localizacion, 'names_consulta'=>$names_table, 'consulta'=>$table, 'coeficientes'=>$coeficientes, 'clases_localizacion'=>$clases_localizacion]);
        
        
    }
    /**
    *Función que valida los datos recibidos desde el formulario y llama a todos los metodos necesarios para construir la tabla de vocaciones regionales , que retorna una tabla y sus cabeceras construidos con la información proporcionada y la final genera un archivo Excel.
    *@param array $request array de datos recibidos desde el formulario.
    *@method array 
    */
    public function main_vocacion_excel(Request $request)
    {   
        $function = new VocacionesController();
        $pib = new PibFunctions();
        $data = $request->all();
        $regiones = DB::table('regiones')->select('Nombre', 'clave')->orderBy('clave')->distinct()->get();
        $entidades = DB::table('mge')->select('id', 'nom_ent', 'cve_ent')->get();

        $tabla = [];
        $table = [];
        $conceptos = [];

        if($data['selectedNivel'] == "1"){
            $tabla = $function->consulta($data['selectedCobertura'], 'pib_precios_constantes', $data['selectedRegion']);
            
            /***************Crear array conceptos***********/
            $conceptos = $function->get_conceptos($tabla);
            /***************Crear array conceptos end***********/
            
            $table = [];
            if ($data['selectedPeriodo'] == 'Ayo') {
                $table = $function->step_1($conceptos, $tabla, $data['selectedAyo']);
            }else{
                $promedio = $function->promedio(['consulta'=>$tabla, 'year_1'=>$data['selectedAyo_1'], 'year_2'=>$data['selectedAyo_2']]);
                $table = $function->step_1($conceptos, $promedio, 'Promedio');
                
            }
        }else{
            $tabla_conceptos = $function->get_total_regiones($regiones, $data['selectedCobertura']);
            $conceptos = $function->get_conceptos($tabla_conceptos);
            $table = [];
            if ($data['selectedPeriodo'] == 'Ayo') {
                $table = $function->step_1($conceptos, $tabla_conceptos, $data['selectedAyo']);
            }else{
                $promedio = $function->promedio(['consulta'=>$tabla_conceptos, 'year_1'=>$data['selectedAyo_1'], 'year_2'=>$data['selectedAyo_2']]);
                $table = $function->step_1($conceptos, $promedio, 'Promedio');
            }
        }

        $coeficientes = $function->step_2($table); 

        $localizacion = $function->step_3($coeficientes);
        $clases_localizacion = $function->get_clases($localizacion);
        //dd($localizacion);
        /***********Generar nombres de las tablas Localizacion*************/
        $name_tables = [];
        foreach ($localizacion as $key => $value) {
            if($data['selectedNivel'] == "1"){
                foreach ($entidades as $entidad) {
                    if ('e_'.$entidad->cve_ent == $key) {
                        array_push($name_tables, ['name'=>$key, 'nickname'=>$entidad->nom_ent]);        
                    }
                }
            }else{
                foreach ($regiones as $region) {
                    if ('e_'.$region->clave == $key) {
                        array_push($name_tables, ['name'=>$key, 'nickname'=>$region->Nombre]);        
                    }
                }
            }
        }
        /***********Generar nombres de las tablas Localizacion end*************/

        /*********Generar headers de tablas************/
        $names_table = [];
        array_push($names_table, ['name'=>'concepto', 'nickname'=>'Concepto']); 
        foreach ($table[0] as $key => $value) {
            if($data['selectedNivel'] == "1"){
                foreach ($entidades as $entidad) {
                    if ('e_'.$entidad->cve_ent == $key) {
                        array_push($names_table, ['name'=>$key, 'nickname'=>$entidad->nom_ent]);        
                    }
                }
            }else{
                foreach ($regiones as $region) {
                    if ('e_'.$region->clave == $key) {
                        array_push($names_table, ['name'=>$key, 'nickname'=>$region->Nombre]);        
                    }
                }
            }
            
        }
        array_push($names_table, ['name'=>'total', 'nickname'=>'Total']); 
        /*********Generar headers de tablas end************/
        $headers =[[
            'header'=>"Especializacion_Productiva",
            'name'=>"Especialización productiva"
        ],[
            'header'=>"coeficiente",
            'name'=>"Coeficiente"
        ]];
        $consulta_table = ['consulta'=>$table, 'headers'=>$names_table];
        $coeficientes_table = ['consulta'=>$coeficientes, 'headers'=>$names_table];
        $localizacion_table = ['consulta'=>$localizacion, 'headers'=>$headers, 'name_tables'=>$name_tables, 'clases'=>$clases_localizacion];
        Excel::create('Vocaciones regionales', function($excel) use ($consulta_table, $coeficientes_table, $localizacion_table) {

            $excel->sheet('consulta', function($sheet) use ($consulta_table) {
                $sheet->loadView('consulta')->with('data',$consulta_table);
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
            $excel->sheet('coeficientes', function($sheet) use ($coeficientes_table) {
                $sheet->loadView('coeficientes')->with('data',$coeficientes_table);
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
            $excel->sheet('Localizacion', function($sheet) use ($localizacion_table) {
                $sheet->loadView('localizacion')->with('data',$localizacion_table);
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

        return response()->json(['status'=>'ok']);

        //return response()->json(['names'=>$name_tables, 'values'=>$localizacion, 'names_consulta'=>$names_table, 'consulta'=>$table, 'coeficientes'=>$coeficientes]);
     
    }
    /**
    *Función que evalua las clases con los criterios de vocaciones regionales para los datos de localizacion.
    *@param array $localizacion array de datos filtrados.
    *@method array 
    */
    public function get_clases($localizacion){
        $clases_localizacion = [];
        $main = [];
        //dd(["Localizacion"=>$localizacion]);
        foreach ($localizacion as $clave =>$valor) {
            $clases = [];
            $main = $valor[0];
            foreach ($valor as $row) {
                if ($row['coeficiente'] > 1 && ($row['coeficiente'] == $main['coeficiente']||$row['coeficiente'] >= $main['coeficiente']-.25)) {
                    array_push($clases, ['Especializacion_Productiva'=>'alto-vocacion', 'coeficiente'=>'alto-vocacion']);
                }elseif ($row['coeficiente'] > 1 && ($row['coeficiente'] < $main['coeficiente']-.25)) {
                    array_push($clases, ['Especializacion_Productiva'=>'predominante-vocacion', 'coeficiente'=>'predominante-vocacion']);
                }elseif ($row['coeficiente'] >= .9 && $row['coeficiente'] < 1) {
                    array_push($clases, ['Especializacion_Productiva'=>'intermedio-vocacion', 'coeficiente'=>'intermedio-vocacion']);
                }elseif ($row['coeficiente'] < .9) {
                    array_push($clases, ['Especializacion_Productiva'=>'bajo-vocacion', 'coeficiente'=>'bajo-vocacion']);
                }else{
                    array_push($clases, ['Especializacion_Productiva'=>'normal-vocacion', 'coeficiente'=>'normal-vocacion']);
                }
            }
            $clases_localizacion[$clave] = $clases;
        }
        return $clases_localizacion;
    }
    /**
    *Función que evalua los datos de acuerdo a los parametros recividos.
    *@param array $covertura define el tipo de valoracion a utilizar.
    *@param string $base es le nombre de la tabla sobre la que se va a consultar
    *@param string $region  el espacio geografico donde se va realizar la consulta
    *@method array 
    */
    public function consulta($covertura, $base, $region){
    	$consulta_aux = [];
        $entidades = [];

        $model_entidades = DB::table('mge')->select('id', 'cve_ent', 'clave_region')->orderBy('cve_ent')->where('clave_region', '=', $region)->get();
        foreach ($model_entidades as $entidad) {
            $entidades=array_merge($entidades,array($entidad->cve_ent));      
        }

    	if ($covertura == 'Completo') {
            $consulta_aux = DB::table($base)->whereIn('cve_ent', $entidades)->orderBy('id')->get();
        }elseif ($covertura == 'Manufactura') {
            $consulta_aux = DB::table($base)->whereIn('cve_ent', $entidades)->where('Sector', '=', '31-33 Industrias manufactureras')->orderBy('id')->get();
        }elseif ($covertura == 'Sin Manufactura') {
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
        }//dd(['cosulta'=>$consulta_aux]);
        $consulta = [];
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
                if ($key!='id' && $key!='Tipo' && $key!='Sector' && $key!='Subsector') {
                    if ($key == 'cve_ent') {
                        $con_aux[$key] = $value;
                    }else{
                        $con_aux[$key] = (float)str_replace (" ", "", $con->$key);    
                    }
                    
                }
            }
            array_push($consulta, $con_aux);
        }//dd($consulta);
        return $consulta;
    }
    /**
    *Función que evalua promedios de acuerdo a los parametros recividos.
    *@param array $data array con los datos a evaluar.
    *@method array 
    */
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
                if ($clave !== "Actividad_economica" && $clave !== "cve_ent" ) {
                    if((int)$clave >= (int)$year_1&&(int)$clave <= (int)$year_2){
                        $promedio += (float)$valor; 
                        $cont++;
                    }
                }else{
                    $title_first = $clave;
                }    
            }
            $consulta_aux['Actividad_economica'] = $consulta['Actividad_economica'];
            $consulta_aux['cve_ent'] = $consulta['cve_ent'];
            $consulta_aux['Promedio'] = $promedio/$cont;

            array_push($tabla, $consulta_aux);
        }
        return($tabla);
        
    }
    /**
    *Función que evalua el primer paso para generar ls vocaciones regionales.
    *@param array $conceptos array de actividades.
    *@param array $tabla es le nombre de la tabla sobre la que se va a consultar
    *@param string $periodo año sobre el se va realizar la consulta.
    *@method array 
    */
    function step_1($conceptos, $tabla, $periodo){
        $table = [];
        $total = [];
        $conter = 0;
        //$total = $tabla[0];
        //$main_total = 0;
        $total = array_merge($total, ['concepto'=>'Total']);
        foreach ($conceptos as $concepto) {
            $row = [];
            $me = 0;
            
            $row = array_merge($row, ['concepto'=>$concepto['concepto']]);
            //array_push($row, ['concepto'=>$concepto['concepto']]);
            foreach ($tabla as $fila) {
                if ($concepto['concepto'] == $fila['Actividad_economica']) {
                    $row = array_merge($row, ['e_'.$fila['cve_ent']=>$fila[$periodo]]);
                    $me += $fila[$periodo];
                    //if ($concepto['concepto'] != 'Total de la actividad económica') {
                        /*if ($conter == 0) {
                            $total['e_'.$fila['cve_ent']] = $fila[$periodo];
                        }else{
                            $total['e_'.$fila['cve_ent']] += $fila[$periodo];
                        }*/
                    //}
                    
                   //array_push($row, [''.$fila['cve_ent']=>$fila[$data['selectedAyo']]]);
                }
            //$conter++;
            }
            //$main_total += $me;
            $row = array_merge($row, ['total'=>$me]);
            if ($conter == 0) {
                $total = $row;
                $total['concepto'] = 'total';
            }else{
                
                array_push($table, $row);
            }
            
            $conter++;
        }
        //$total = array_merge($total, ['total'=>$main_total]);
        array_push($table, $total);
        //dd($table);
        return $table;
    }
    /**
    *Función que evalua coeficientes  para generar ls vocaciones regionales.
    *@param array $tabla array para hacer la evaluacion
    *@method array 
    */
    function step_2($tabla){
        $total = $tabla[count($tabla)-1];
        $coeficientes = [];
        //dd($total);
        foreach ($tabla as $fila) {
            $row = [];
            foreach ($fila as $key => $value) {
                if ($key == 'concepto') {
                    $row = array_merge($row, ['concepto'=>$value]);
                }else{
                    $coeficiente = 0;
                    try {
                        $coeficiente = ($value/$total[$key])/($fila['total']/$total['total']);    
                    } catch (\Exception $e) {
                        $coeficiente = 0;
                    }
                    //$coeficiente = ($value/$total[$key])/($fila['total']/$total['total']);
                    $row = array_merge($row, [''.$key => $coeficiente]);
                }
            }
            array_push($coeficientes, $row);
        }
        return $coeficientes;
        //dd($coeficientes);
    }
    /**
    *Función que organiza coeficientes  para generar las vocaciones regionales.
    *@param array $coeficientes array para hacer la evaluacion
    *@method array 
    */
    function step_3($coeficientes){
        $function = new VocacionesController();
        $localizacion = [];
        $counter = 0;
        foreach ($coeficientes as $fila) {
            foreach ($fila as $key => $value) {
                if ($key != 'concepto' && $key != 'total') {
                    if ($counter == 0) {
                        $localizacion[$key] = [];
                    }
                    array_push($localizacion[$key], ['Especializacion_Productiva'=>$fila['concepto'], 'coeficiente'=>$value]);
                    
                }
            }
            $counter++;
        }
        //uasort($localizacion['e_9'],array($this,'sort_by_orden'));
        foreach ($localizacion as $place => $value) {
          $localizacion[$place] = $function->orderMultiDimensionalArray($value, 'coeficiente', true);  
        }
        //$test = $function->orderMultiDimensionalArray($localizacion['e_9'], 'coeficiente', true);
        return $localizacion;
        //dd($localizacion);
    }

    private static function sort_by_orden ($a, $b) {
        return $a['coeficiente'] - $b['coeficiente'];
    }
    /**
    *Función que obtiene las actividade de la tabla recivida.
    *@param array $tabla array para hacer la evaluacion
    *@method array 
    */
    function get_conceptos($tabla){
        $conceptos = [];
        array_push($conceptos, ['concepto'=>$tabla[0]['Actividad_economica']]);
        $cont = 0;
        foreach ($tabla as $fila) {
            $flag = true;
            if ($cont != 0) {
                foreach ($conceptos as $concepto) {
                    if ($concepto['concepto'] == $fila['Actividad_economica']) {
                       $flag = false;
                    }       
                }
                if ($flag) {
                    array_push($conceptos, ['concepto'=>$fila['Actividad_economica']]);
                }     
            }                     
            $cont++;
        }
        return $conceptos;
    }
    /**
    *Función para calcular los totales de las regiones.
    *@param array $regiones espacio geografico sobre el que se esta trabajando
    *@param string $cobertura es el tipo de valoracion precios constantes | precios corrientes
    *@method array 
    */
    function get_total_regiones($regiones, $cobertura){
        $function = new VocacionesController();
        $tabla_conceptos = [];
        foreach ($regiones as $region) {
            $tabla = $function->consulta($cobertura, 'pib_precios_constantes', $region->clave);  
            //dd($tabla);
            $conceptos = $function->get_conceptos($tabla);

            foreach ($conceptos as $concepto) {
                $query = [];
                $query['Actividad_economica'] = $concepto['concepto'];
                $query['cve_ent'] = $region->clave;
                $cont = 0;
                foreach ($tabla as $fila) {
                    if ($concepto['concepto'] == $fila['Actividad_economica']) {

                        foreach ($fila as $key => $value) {
                            if ($key != 'Actividad_economica' && $key != 'cve_ent') {
                                if($cont == 0){
                                    $query[$key] = (float)str_replace (" ", "", $value);
                                }else{
                                    $query[$key] += (float)str_replace (" ", "", $value);    
                                }
                            }
                        }
                        //dd($query);
                        $cont++;
                    }
                    

                }
                array_push($tabla_conceptos, $query);
            }

                  
        }
        return $tabla_conceptos;
    }
    /**
    *Función para ordenar los valores proporcionados.
    *@param array $toOrderArray array de los valores a ordenar.
    *@param string $filed variable del array que se va a ordenar.
    * @param string $inverse tipo de ordenamiento que se va a realizar
    *@method array 
    */
    function orderMultiDimensionalArray ($toOrderArray, $field, $inverse = false) {
        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {
                $position[$key]  = $row[$field];
                $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        }
        else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {     
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }
}
