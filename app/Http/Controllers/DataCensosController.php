<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Libraries\Censos\FormulasFunctions;
use App\Libraries\Censos\CensosFunctions;
use Invoker;
/*use php-di\Cors\ServiceProvider*/

class DataCensosController extends Controller
{
    /*****************Current request start***************************/
    
        public function sectores(Request $request)
        {   
            $year = $request['ayo'];
            $cve_entidad = $request['cve_entidad'];
            $cve_municipio = $request['cve_municipio'];
            $espacio = $request['espacio'];
            $region = $request['region'];
            $macroregion = $request['macroregion'];
            $periodo = $request['periodo'];
            $year_1 = $request['ayo_1'];
            $year_2 = $request['ayo_2'];
            $years = [];
            /************Get years for the average start*******************/
            if ($periodo == "2") {
                $years_query = DB::table('censos_economicos_completo')
                ->select('ayo')
                ->orderBy('ayo')
                ->distinct()
                ->get();
                foreach ($years_query as $one) {
                    if (($one->ayo >= $year_1)&&($one->ayo <= $year_2)) {
                        //array_push($years, $one->ayo);
                        $years = array_merge($years,[$one->ayo]);
                    }
                }
            }
            /************Get years for the average end*******************/

            if ($cve_entidad == '00') {
                $cve_municipio = '00';
            }
            if ($espacio == '1') {
                $sectores=DB::table('censos_economicos_completo')
                ->join('diccionario', 'diccionario.clave', '=', 'sector')
                ->whereNested(function($query) use($cve_entidad, $cve_municipio, $year, $years, $periodo){
                    $query->where('cve_ent', '=', $cve_entidad);
                    $query->where('cve_mun', '=', $cve_municipio);
                    if ($periodo == "1") {
                        $query->where('ayo', '=', $year);
                    }else{
                        $query->whereIn('ayo', $years);
                    }
                    
                    $query->whereNotIn('censos_economicos_completo.sector', ['TN', 'TE', 'TM']);    
                })
                
                ->select('censos_economicos_completo.sector', 'diccionario.nombre')->distinct()->orderBy('sector')->get();
            }elseif ($espacio == '2') {
                $municipios = DB::table('areas_geoestadisticas_municipales')
                    ->where('cve_ent', '=', $cve_entidad)
                    ->where('cve_region', '=', $region)
                    ->get();
                $modelo_municipios = [];
                foreach ($municipios as $raw) {
                    $modelo_municipios = array_merge($modelo_municipios, [$raw->cve_mun]);
                }
                $sectores=DB::table('censos_economicos_completo')
                ->join('diccionario', 'diccionario.clave', '=', 'sector')
                ->whereNested(function($query) use($cve_entidad, $modelo_municipios, $year, $years, $periodo){
                    $query->where('cve_ent', '=', $cve_entidad);
                    $query->whereIn('cve_mun', $modelo_municipios);
                    if ($periodo == "1") {
                        $query->where('ayo', '=', $year);
                    }else{
                        $query->whereIn('ayo', $years);
                    }
                    
                    $query->whereNotIn('censos_economicos_completo.sector', ['TN', 'TE', 'TM']);
                })
                ->select('censos_economicos_completo.sector', 'diccionario.nombre')->distinct()->orderBy('sector')->get();
            }elseif ($espacio == '3') {
                $municipios = DB::table('areas_geoestadisticas_municipales')
                    ->join('regiones_mun', 'regiones_mun.cve_region', '=', 'areas_geoestadisticas_municipales.cve_region')
                    ->where('areas_geoestadisticas_municipales.cve_ent', '=', $cve_entidad)
                    ->where('regiones_mun.cve_macroregion', '=', $macroregion)
                    ->get();
                //dd($municipios);
                $modelo_municipios = [];
                foreach ($municipios as $raw) {
                    $modelo_municipios = array_merge($modelo_municipios, [$raw->cve_mun]);
                }

                $sectores=DB::table('censos_economicos_completo')
                ->join('diccionario', 'diccionario.clave', '=', 'sector')
                ->whereNested(function($query) use($cve_entidad, $modelo_municipios, $year, $years, $periodo){
                    $query->where('cve_ent', '=', $cve_entidad);
                    $query->whereIn('cve_mun', $modelo_municipios);
                    if ($periodo == "1") {
                        $query->where('ayo', '=', $year);
                    }else{
                        $query->whereIn('ayo', $years);//dd($years);
                    }
                    
                    $query->whereNotIn('censos_economicos_completo.sector', ['TN', 'TE', 'TM']);
                })
                ->select('censos_economicos_completo.sector', 'diccionario.nombre')->distinct()->orderBy('sector')->get();
            }

            return response()->json(['sectores' => $sectores], 200);
        }

        public function current_sectors(){
            $sectores=DB::table('censos_economicos_completo')
            ->join('diccionario', 'diccionario.clave', '=', 'sector')
            ->whereNotIn('censos_economicos_completo.sector', ['TN', 'TE', 'TM'])
            ->select('censos_economicos_completo.sector', 'diccionario.nombre')->distinct()->orderBy('sector')->get();

            return response()->json(['sectores' => $sectores], 200);
        }


        public function subsectores(Request $request)
        {   
            $subsectores = [];
            $year = $request['ayo'];
            $cve_sector = $request['cve_sector'];
            $cve_entidad = $request['cve_entidad'];
            $cve_municipio = $request['cve_municipio'];
            $espacio = $request['espacio'];
            $region = $request['region'];
            $macroregion = $request['macroregion'];
            $periodo = $request['periodo'];
            $year_1 = $request['ayo_1'];
            $year_2 = $request['ayo_2'];
            $years = [];
            /************Get years for the average start*******************/
            if ($periodo == "2") {
                $years_query = DB::table('censos_economicos_completo')
                ->select('ayo')
                ->orderBy('ayo')
                ->distinct()
                ->get();
                foreach ($years_query as $one) {
                    if (($one->ayo >= $year_1)&&($one->ayo <= $year_2)) {
                        //array_push($years, $one->ayo);
                        $years = array_merge($years,[$one->ayo]);
                    }
                }
            }
            /************Get years for the average end*******************/
            if ($cve_entidad == '00') {
                $cve_municipio = '00';
            }
            if ($espacio == '1') {
                $subsectores=DB::table('censos_economicos_completo')
                ->join('diccionario', 'diccionario.clave', '=', 'subsector')
                ->whereNested(function($query) use($cve_sector, $cve_entidad, $cve_municipio, $year, $years, $periodo){
                    $query->where('censos_economicos_completo.sector', '=', $cve_sector);
                    $query->where('cve_ent', '=', $cve_entidad);
                    $query->where('cve_mun', '=', $cve_municipio);
                    if ($periodo == "1") {
                        $query->where('ayo', '=', $year);
                    }else{
                        $query->whereIn('ayo', $years);
                    }
                    
                    $query->whereNotIn('censos_economicos_completo.subsector', ['TN', 'TE', 'TM']);
                })
                ->select('censos_economicos_completo.subsector', 'diccionario.nombre')->distinct()->orderBy('subsector')->get();
            }elseif ($espacio == '2') {
                $municipios = DB::table('areas_geoestadisticas_municipales')
                    ->where('cve_ent', '=', $cve_entidad)
                    ->where('cve_region', '=', $region)
                    ->get();
                $modelo_municipios = [];
                foreach ($municipios as $raw) {
                    $modelo_municipios = array_merge($modelo_municipios, [$raw->cve_mun]);
                }
                $subsectores=DB::table('censos_economicos_completo')
                ->join('diccionario', 'diccionario.clave', '=', 'subsector')
                ->whereNested(function($query) use($cve_sector, $cve_entidad, $modelo_municipios, $year, $years, $periodo){
                    $query->where('censos_economicos_completo.sector', '=', $cve_sector);
                    $query->where('cve_ent', '=', $cve_entidad);
                    $query->whereIn('cve_mun', $modelo_municipios);
                    if ($periodo == "1") {
                        $query->where('ayo', '=', $year);
                    }else{
                        $query->whereIn('ayo', $years);
                    }
                    
                    $query->whereNotIn('censos_economicos_completo.subsector', ['TN', 'TE', 'TM']);
                })
                ->select('censos_economicos_completo.subsector', 'diccionario.nombre')->distinct()->orderBy('subsector')->get();
            }elseif ($espacio == '3') {
                $municipios = DB::table('areas_geoestadisticas_municipales')
                    ->join('regiones_mun', 'regiones_mun.cve_region', '=', 'areas_geoestadisticas_municipales.cve_region')
                    ->where('areas_geoestadisticas_municipales.cve_ent', '=', $cve_entidad)
                    ->where('regiones_mun.cve_macroregion', '=', $macroregion)
                    ->get();
                //dd($municipios);
                $modelo_municipios = [];
                foreach ($municipios as $raw) {
                    $modelo_municipios = array_merge($modelo_municipios, [$raw->cve_mun]);
                }

                $subsectores=DB::table('censos_economicos_completo')
                ->join('diccionario', 'diccionario.clave', '=', 'subsector')
                ->whereNested(function($query) use($cve_sector, $cve_entidad, $modelo_municipios, $year, $years, $periodo){
                    $query->where('censos_economicos_completo.sector', '=', $cve_sector);
                    $query->where('cve_ent', '=', $cve_entidad);
                    $query->whereIn('cve_mun', $modelo_municipios);
                    if ($periodo == "1") {
                        $query->where('ayo', '=', $year);
                    }else{
                        $query->whereIn('ayo', $years);//dd($years);
                    }
                    
                    $query->whereNotIn('censos_economicos_completo.subsector', ['TN', 'TE', 'TM']);
                })
                ->select('censos_economicos_completo.subsector', 'diccionario.nombre')->distinct()->orderBy('subsector')->get();
            }
            
            return response()->json(['subsectores' => $subsectores], 200);
        }

        public function current_subsectors(Request $request){
            $cve_sector = $request['cve_sector'];
            $subsectores=DB::table('censos_economicos_completo')
            ->join('diccionario', 'diccionario.clave', '=', 'subsector')
            ->where('censos_economicos_completo.sector', '=', $cve_sector)
            ->whereNotIn('censos_economicos_completo.subsector', ['TN', 'TE', 'TM'])
            ->select('censos_economicos_completo.subsector', 'diccionario.nombre')->distinct()->orderBy('subsector')->get();

            return response()->json(['subsectores' => $subsectores], 200);
        }

        public function ramas(Request $request)
        {       
            $year = $request['ayo'];
            $cve_subsector = $request['cve_subsector'];
            $cve_entidad = $request['cve_entidad'];
            $cve_municipio = $request['cve_municipio'];
            $espacio = $request['espacio'];
            $region = $request['region'];
            $macroregion = $request['macroregion'];
            $periodo = $request['periodo'];
            $year_1 = $request['ayo_1'];
            $year_2 = $request['ayo_2'];
            $years = [];
            /************Get years for the average start*******************/
            if ($periodo == "2") {
                $years_query = DB::table('censos_economicos_completo')
                ->select('ayo')
                ->orderBy('ayo')
                ->distinct()
                ->get();
                foreach ($years_query as $one) {
                    if (($one->ayo >= $year_1)&&($one->ayo <= $year_2)) {
                        //array_push($years, $one->ayo);
                        $years = array_merge($years,[$one->ayo]);
                    }
                }
            }
            /************Get years for the average end*******************/

            if ($cve_entidad == '00') {
                $cve_municipio = '00';
            }
            if ($espacio == '1') {
                $ramas=DB::table('censos_economicos_completo')
                ->join('diccionario', 'diccionario.clave', '=', 'rama')
                ->whereNested(function($query) use($cve_subsector, $cve_entidad, $cve_municipio, $year, $years, $periodo){
                    $query->where('censos_economicos_completo.subsector', '=', $cve_subsector);
                    $query->where('cve_ent', '=', $cve_entidad);
                    $query->where('cve_mun', '=', $cve_municipio);
                    if ($periodo == "1") {
                        $query->where('ayo', '=', $year);
                    }else{
                        $query->whereIn('ayo', $years);
                    }
                    
                    $query->whereNotIn('censos_economicos_completo.rama', ['TN', 'TE', 'TM']);
                })
                ->select('censos_economicos_completo.rama', 'diccionario.nombre')->distinct()->orderBy('rama')->get();
            }elseif ($espacio == '2') {
                $municipios = DB::table('areas_geoestadisticas_municipales')
                    ->where('cve_ent', '=', $cve_entidad)
                    ->where('cve_region', '=', $region)
                    ->get();
                $modelo_municipios = [];
                foreach ($municipios as $raw) {
                    $modelo_municipios = array_merge($modelo_municipios, [$raw->cve_mun]);
                }
                $ramas=DB::table('censos_economicos_completo')
                ->join('diccionario', 'diccionario.clave', '=', 'rama')
                ->whereNested(function($query) use($cve_subsector, $cve_entidad, $modelo_municipios, $year, $years, $periodo){
                    $query->where('censos_economicos_completo.subsector', '=', $cve_subsector);
                    $query->where('cve_ent', '=', $cve_entidad);
                    $query->whereIn('cve_mun', $modelo_municipios);
                    if ($periodo == "1") {
                        $query->where('ayo', '=', $year);
                    }else{
                        $query->whereIn('ayo', $years);
                    }
                    
                    $query->whereNotIn('censos_economicos_completo.rama', ['TN', 'TE', 'TM']);
                })
                ->select('censos_economicos_completo.rama', 'diccionario.nombre')->distinct()->orderBy('rama')->get();
            }elseif ($espacio == '3') {
                $municipios = DB::table('areas_geoestadisticas_municipales')
                    ->join('regiones_mun', 'regiones_mun.cve_region', '=', 'areas_geoestadisticas_municipales.cve_region')
                    ->where('areas_geoestadisticas_municipales.cve_ent', '=', $cve_entidad)
                    ->where('regiones_mun.cve_macroregion', '=', $macroregion)
                    ->get();
                //dd($municipios);
                $modelo_municipios = [];
                foreach ($municipios as $raw) {
                    $modelo_municipios = array_merge($modelo_municipios, [$raw->cve_mun]);
                }

                $ramas=DB::table('censos_economicos_completo')
                ->join('diccionario', 'diccionario.clave', '=', 'rama')
                ->whereNested(function($query) use($cve_subsector, $cve_entidad, $modelo_municipios, $year, $years, $periodo){
                    $query->where('censos_economicos_completo.subsector', '=', $cve_subsector);
                    $query->where('cve_ent', '=', $cve_entidad);
                    $query->whereIn('cve_mun', $modelo_municipios);
                    if ($periodo == "1") {
                        $query->where('ayo', '=', $year);
                    }else{
                        $query->whereIn('ayo', $years);
                    }
                    
                    $query->whereNotIn('censos_economicos_completo.rama', ['TN', 'TE', 'TM']);
                })
                ->select('censos_economicos_completo.rama', 'diccionario.nombre')->distinct()->orderBy('rama')->get();
            }
            
            return response()->json(['ramas' => $ramas], 200);
        }

        public function current_branches(Request $request){
            $cve_subsector = $request['cve_subsector'];
            $ramas=DB::table('censos_economicos_completo')
            ->join('diccionario', 'diccionario.clave', '=', 'rama')
            ->where('censos_economicos_completo.subsector', '=', $cve_subsector)
            ->whereNotIn('censos_economicos_completo.rama', ['TN', 'TE', 'TM'])
            ->select('censos_economicos_completo.rama', 'diccionario.nombre')->distinct()->orderBy('rama')->get();

            return response()->json(['ramas' => $ramas], 200);
        }

        public function entidades(){
            $entidades = DB::table('mge')->select('nom_ent', 'cve_ent', 'latitud', 'longitud')->orderBy('cve_ent')->distinct()->get();
            return response()->json(['entidades' => $entidades]);
        }
        public function municipios(Request $request){
            $cve_ent = $request['cve_ent'];
            $municipios = DB::table('areas_geoestadisticas_municipales')
            ->where('cve_ent', '=', $cve_ent)
            ->select('cve_ent', 'cve_mun', 'nom_mun', 'latitud', 'longitud')
            ->orderBy('cve_mun')
            ->distinct()
            ->get();
            return response()->json(['municipios' => $municipios]);
        }
        public function regiones(Request $request){
            $cve_ent = $request['cve_ent'];
            $regiones = DB::table('regiones_mun')
            ->where('cve_ent', '=', $cve_ent)
            ->select('cve_ent', 'cve_region', 'nom_region', 'latitud', 'longitud')
            ->orderBy('cve_region')
            ->distinct()
            ->get();
            return response()->json(['regiones' => $regiones]);
        }
        public function macroregiones(Request $request){
            $cve_ent = $request['cve_ent'];
            $macroregiones = DB::table('macroregiones_mun')
            ->where('cve_ent', '=', $cve_ent)
            ->select('cve_ent', 'cve_macroregion', 'nom_macroregion', 'latitud', 'longitud')
            ->orderBy('cve_macroregion')
            ->distinct()
            ->get();
            return response()->json(['macroregiones' => $macroregiones]);
        }
        public function years(){
            $years = DB::table('censos_economicos_completo')
            ->select('ayo')
            ->orderBy('ayo')
            ->distinct()
            ->get();
            return response()->json(['years' => $years], 200);
        }
        public function indicadores(){
            $indicadores = DB::table('indicador')
            ->select('clave', 'nombre', 'variables')
            ->get();
            return response()->json(['indicadores' => $indicadores], 200);
        }
    /*****************Current request end***************************/


    
    public function consulta(Request $request){
        $get_indicador = new CensosFunctions();
        $data = $request->all();

        $consulta = [];
        $indicadores_request = $data['indicadores'];
        if ($data['periodo'] == "1") {
            $consulta = $get_indicador->main_consulta($data);
            $consulta = $get_indicador->get_indicadores($consulta, $indicadores_request, 'actividad');
            
        }else{
            $consulta = $get_indicador->getPromedio($data, '1');
        }
        $cabeceras = $get_indicador->get_names_columns($consulta);
        return response()->json(['consulta'=>$consulta, 'cabeceras'=>$cabeceras], 200);

    }
    public function consulta_actividad(Request $request){
        $get_indicador = new CensosFunctions();
        $data = $request->all();
        $type = $data['espacio'];
        $indicador = $data['indicador'];

        $consulta = [];
        $spaces = [];
        if ($type == "0") {$spaces = $data['entidades'];    
        }elseif($type == "1"){$spaces = $data['municipios'];
        }elseif($type == "2"){$spaces = $data['regiones'];
        }elseif($type == "3"){$spaces = $data['macroregiones'];}
        
        $consulta = $get_indicador->main_consulta_actividad($data);//dd($consulta);
        $consulta = $get_indicador->get_indicadores_actividad($consulta, $indicador, $type, $spaces, 'ayo');

        //dd($consulta);
        $cabeceras = $get_indicador->get_names_columns($consulta);

        return response()->json(['consulta'=>$consulta, 'cabeceras'=>$cabeceras], 200);

    }


    public function export(Request $request){
        $get_indicador = new CensosFunctions();

        $data = $request->all();
        $indicadores_request = $data['indicadores'];
        try {
            $sectores = $data['sectores'];
        } catch (\Exception $e) {
            $data['sectores'] = [];
        }
        try {
            $subsectores = $data['subsectores'];
        } catch (\Exception $e) {
            $data['subsectores'] = [];
        }
        try {
            $ramas = $data['ramas'];
        } catch (\Exception $e) {
            $data['ramas'] = [];
        }
        $influencia = [];
        $variable_influencia = $data['influencia'];


        $consulta = $get_indicador->main_consulta($data);
        $consulta = $get_indicador->get_indicadores($consulta, $indicadores_request, 'actividad');
        $cabeceras = $get_indicador->get_names_columns($consulta);
        if ($data['periodo'] == "1") {
            $variacion = $get_indicador->getVariacion($data, $consulta);
        }else{
            $variacion = $get_indicador->getPromedio($data, '2');
        }
        $cabeceras_variacion = $get_indicador->get_names_columns($variacion);
        if ($variable_influencia != "") {   
            if ($data['periodo'] == '1') {
                $influencia = $get_indicador->getInfluencia($data, $consulta);

                $base = $influencia['base'];
                $influencia = $influencia['influencia'];
            }else{
                $base = $get_indicador->getPromedio($data, '3');
                $influencia = $get_indicador->getPromedio($data, '4');

                $base = ['consulta'=>$base, 'cabeceras'=>$get_indicador->get_names_columns($base)];
                $influencia = ['consulta'=>$influencia, 'cabeceras'=>$get_indicador->get_names_columns($influencia)];
                //$consulta_influencia['base'] = $consulta_base;
                //$influencia = $consulta_influencia;
            }
        }
        
        if ($data['periodo'] == "2") {
            $consulta = $get_indicador->getPromedio($data, '1');
        }
        $consulta = ['consulta'=>$consulta, 'cabeceras'=>$cabeceras];
        $variacion = ['consulta'=>$variacion, 'cabeceras'=>$cabeceras_variacion];


        $consulta['title'] = $data['title_indicadores'];
        $variacion['title'] = $data['title_variacion'];
        
        $base['title'] = $data['title_base'];
        $influencia['title'] = $data['title_influencia'];

        Excel::create('Indicadores censos económicos', function($excel) use ($consulta, $variacion, $variable_influencia, $influencia, $base) {

            $excel->sheet('indicadores', function($sheet) use ($consulta) {
                $sheet->loadView('indicadores')->with('data',$consulta);
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
            $excel->sheet('variacion', function($sheet) use ($variacion) {
                $sheet->loadView('indicadores')->with('data',$variacion);
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
            if ($variable_influencia != "") {
                $excel->sheet('indicadores base', function($sheet) use ($influencia) {
                    $sheet->loadView('indicadores')->with('data',$base);
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
                $excel->sheet('influencia', function($sheet) use ($influencia, $tilte_influencia) {
                    $sheet->loadView('indicadores')->with('data', $influencia);
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
            }
            

        })->export('xls');
        /**********Obtener nombres de columnas end******/
        return response()->json(['status'=>'ok']);
        //return response()->json($consulta, 200);

    }
    public function influencia(Request $request){
        $get_indicador = new CensosFunctions();
        $data = $request->all();
        $influencia = [];
        $indicadores_request = $data['indicadores'];

        if ($data['periodo'] == '1') {
            $consulta = $get_indicador->main_consulta($data);
            $consulta = $get_indicador->get_indicadores($consulta, $indicadores_request, 'actividad');

            $influencia = $get_indicador->getInfluencia($data, $consulta);//dd($influencia);
            $base = ['consulta'=>$influencia['base'], 'cabeceras'=>$get_indicador->get_names_columns($influencia['base'])];
            $influencia = ['consulta'=>$influencia['influencia'], 'cabeceras'=>$get_indicador->get_names_columns($influencia['influencia'])];
            
        }else{
            $base = $get_indicador->getPromedio($data, '3');
            //dd($base);
            $influencia = $get_indicador->getPromedio($data, '4');//dd($influencia);
            $influencia = ['consulta'=>$influencia, 'cabeceras'=>$get_indicador->get_names_columns($influencia)];
            $base = ['consulta'=>$base, 'cabeceras'=>$get_indicador->get_names_columns($base)];
            //$consulta_influencia['base'] = $consulta_base;
            //$influencia = $consulta_influencia;
        }
        
               
        return response()->json(['influencia'=>$influencia, 'base'=>$base], 200);
    }

    public function influencia_actividad(Request $request){
        $get_indicador = new CensosFunctions();
        $data = $request->all();
        $influencia = [];

        $type = $data['espacio'];
        $indicador = $data['indicador'];

        $spaces = [];
        if ($type == "0") {$spaces = $data['entidades'];    
        }elseif($type == "1"){$spaces = $data['municipios'];
        }elseif($type == "2"){$spaces = $data['regiones'];
        }elseif($type == "3"){$spaces = $data['macroregiones'];}
       
        $consulta = $get_indicador->main_consulta_actividad($data);
        $consulta = $get_indicador->get_indicadores_actividad($consulta, $indicador, $type, $spaces, 'ayo');

        $influencia = $get_indicador->getInfluencia_actividad($data, $consulta);

        $base = ['consulta'=>$influencia['base'], 'cabeceras'=>$get_indicador->get_names_columns($influencia['base'])];
        $influencia = ['consulta'=>$influencia['influencia'], 'cabeceras'=>$get_indicador->get_names_columns($influencia['influencia'])];
        
               
        return response()->json(['influencia'=>$influencia, 'base'=>$base], 200);
    }

    public function variacion(Request $request){
        $get_indicador = new CensosFunctions();
        $data = $request->all();
        $variacion = [];
        $indicadores_request = $data['indicadores'];
        
        if ($data['periodo'] == "1") {
            $consulta = $get_indicador->main_consulta($data);
            $consulta = $get_indicador->get_indicadores($consulta, $indicadores_request, 'actividad');
            $variacion = $get_indicador->getVariacion($data, $consulta);
        }else{
            $variacion = $get_indicador->getPromedio($data, '2');
        }
        $variacion = ['consulta'=>$variacion, 'cabeceras'=>$get_indicador->get_names_columns($variacion)];
        return response()->json($variacion, 200);

    }
    public function variacion_actividad(Request $request){
        $get_indicador = new CensosFunctions();
        $data = $request->all();

        $variacion = [];
        $type = $data['espacio'];
        $indicador = $data['indicador'];
        
        $spaces = [];
        if ($type == "0") {$spaces = $data['entidades'];    
        }elseif($type == "1"){$spaces = $data['municipios'];
        }elseif($type == "2"){$spaces = $data['regiones'];
        }elseif($type == "3"){$spaces = $data['macroregiones'];}
       
        $consulta = $get_indicador->main_consulta_actividad($data);
        $consulta = $get_indicador->get_indicadores_actividad($consulta, $indicador, $type, $spaces, 'ayo');
        $variacion = $get_indicador->getVariacion_actividad($data, $consulta);
        //dd($variacion);
        
        $variacion = ['consulta'=>$variacion, 'cabeceras'=>$get_indicador->get_names_columns($consulta)];
        return response()->json($variacion, 200);

    }
    public function consulta_por_indicador(Request $request){
        $get_indicador = new CensosFunctions();
        $data = $request->all();
        $indicador_request = $data['indicador'];

        $response = [];
        
        
        
        $consulta = $get_indicador->main_consulta_indicador($data);
        //dd($consulta);
        /**********Evaluar Indicador start***********************/
            $indicador = DB::table('indicador')
            ->where('clave', '=', $indicador_request)
            ->first();
            array_push($response, $get_indicador->getIndicador($indicador, $consulta, 'cve_mun'));
            /*array_push($response, $get_indicador->getUniqueIndicador($indicador, $consulta));*/
        /**********Evaluar Indicador end***********************/


        /**********Obtener nombres de columnas******/
            $names_table = [];
            $natural_breakes = [];//dd($response[0]);
            foreach ($response[0] as $key => $value) {
                /**********Get natural braks****************/
                if ($key != 'indicador' && $key != 'clave') {
                    $natural_breakes = array_merge($natural_breakes, [$value]);
                }
                /***********get name columns*************/
                array_push($names_table, ['data'=>$key, 'title'=>$key]); 
                
            }
        /**********Obtener nombres de columnas******/

        return response()->json(['consulta' => $response, 'cabeceras'=>$names_table, 'breakes'=>$natural_breakes], 200);

    }
    /*************** check***********************/
    public function chart_por_indicador(Request $request){
        $get_indicador = new CensosFunctions();
        $data = $request->all();
        $indicador_request = $data['indicador'];

        $response = [];
        
        
        $consulta = $get_indicador->main_consulta_indicador($data);
        
        /******************Evaluar Inicador start**************************/
            $indicador = DB::table('indicador')
            ->where('clave', '=', $indicador_request)
            ->first();

            $response = $get_indicador->getChartIndicador($indicador, $consulta);
        /******************Evaluar Inicador end**************************/

        
        $count =0;
        foreach ($response as $value) {
            if ($data['municipio'] == $data['entidad']) {
                $espacio = DB::table('mge')
                ->where('cve_ent', '=', $value['key'])
                ->first();
                $response[$count]['name'] = $espacio->nom_ent;
            }else{
                $espacio = DB::table('areas_geoestadisticas_municipales')
                ->where('cve_mun', '=', $value['key'])
                ->where('cve_ent', '=', $data['entidad'])
                ->first();
                $response[$count]['name'] = $espacio->nom_mun;
            }
            $count++;
        }
        
        return response()->json(['consulta' => $response], 200);

    }
    
    /************************Vocaciones regionales start******************/
        public function vocaciones(Request $request){
            $get_indicador = new CensosFunctions();
            $data = $request->all();
            
            $vocaciones = $get_indicador->main_vocaciones($data);

            return response()->json($vocaciones, 200);
        }

        public function vocaciones_export(Request $request){
            $get_indicador = new CensosFunctions();
            $data = $request->all();
            try {
                $sectores = $data['sectores'];
            } catch (\Exception $e) {
                $data['sectores'] = [];
            }
            try {
                $subsectores = $data['subsectores'];
            } catch (\Exception $e) {
                $data['subsectores'] = [];
            }
            try {
                $ramas = $data['ramas'];
            } catch (\Exception $e) {
                $data['ramas'] = [];
            }
            try {
                $regiones = $data['regiones'];
            } catch (\Exception $e) {
                $data['regiones'] = [];
            }
            try {
                $macroregiones = $data['macroregiones'];
            } catch (\Exception $e) {
                $data['macroregiones'] = [];
            }
            $vocaciones = $get_indicador->main_vocaciones($data);

            /************Create excel file***************************/
            $headers =[[
                'header'=>"Especializacion_Productiva",
                'name'=>"Especialización productiva"
            ],[
                'header'=>"coeficiente",
                'name'=>"Coeficiente"
            ]];
            $consulta_table = ['consulta'=>$vocaciones['consulta'], 'headers'=>$vocaciones['cabeceras']];
            $coeficientes_table = ['consulta'=>$vocaciones['coeficientes'], 'headers'=>$vocaciones['cabeceras']];
            $localizacion_table = ['consulta'=>$vocaciones['localizacion'], 'headers'=>$headers, 'name_tables'=>$vocaciones['names'], 'clases'=>$vocaciones['clases']];
            
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

            /*return response()->json(['consulta'=>$consulta, 
                                    'coeficientes'=>$coeficientes, 
                                    'localizacion'=>$localizacion, 
                                    'clases'=>$clases,
                                    'names'=>$names,
                                    'cabeceras'=>$cabeceras]
                                , 200);*/
        }
    /************************Vocaciones regionales start******************/

}
