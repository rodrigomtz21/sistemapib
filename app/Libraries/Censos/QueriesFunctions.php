<?php

namespace App\Libraries\Censos;
use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;
use Invoker;

class QueriesFunctions
{   
    public function municipio_query($year, $entidad, $municipio, $nivel, $sectores, $subsectores, $ramas, $periodo, $years){
        if ($entidad == '00') {
            $municipio = '00';
        }
        $consulta = DB::table('censos_economicos_completo')
        ->whereNested(function($query) use ($year, $entidad, $municipio, $nivel, $sectores, $subsectores, $ramas, $periodo, $years)
            {   
                $query->where('ayo', '=', $year);
                $query->where('cve_ent', '=', $entidad);
                $query->where('cve_mun', '=', $municipio);
                /****************Nivel: 1=Sector;2=Subsector;3=Rama;*******************/
                if ($nivel == '1') {
                    $query->whereIn('sector', $sectores);
                    $query->whereIn('subsector', $sectores);
                    $query->whereIn('rama', $sectores);
                }elseif ($nivel == '2') {
                    $query->whereIn('subsector', $subsectores);
                    $query->whereIn('rama', $subsectores);
                }elseif ($nivel == '3') {
                    $query->whereIn('rama', $ramas);
                    $query->whereIn('subrama', $ramas);
                }
                
            })
        ->get();

        return $consulta;
    }

    public function municipios_query($year, $entidad, $entidades, $municipio, $nivel, $sector, $subsector, $rama){
        $consulta = DB::table('censos_economicos_completo')
        ->whereNested(function($query) use ($year, $entidad, $entidades, $municipio, $nivel, $sector, $subsector, $rama)
            {   
                $query->where('ayo', '=', $year);
                //$query->whereIn('ayo', $years)
                if ($entidad == $municipio) {
                    $query->whereIn('cve_ent', $entidades);
                    $query->whereIn('cve_mun', $entidades);
                }else{
                    $query->where('cve_ent', '=', $entidad); 
                    $query->where('cve_mun', '!=', $entidad);
                }
                /****************Nivel: 1=Sector;2=Subsector;3=Rama;*******************/
                if ($nivel == '1') {
                    $query->where('sector', '=', $sector);
                    $query->where('subsector', '=', $sector);
                    $query->where('rama', '=', $sector);
                }elseif ($nivel == '2') {
                    $query->where('subsector', '=', $subsector);
                    $query->where('rama', '=', $subsector);
                }elseif ($nivel == '3') {
                    $query->where('rama', '=', $rama);
                    $query->where('subrama', '=', $rama);
                }
                
            })
        ->get();
        return $consulta;
    }

    /***********get just one region*************************/
    public function region_query($year, $entidad, $region, $nivel, $sectores, $subsectores, $ramas){
        
        $consulta = DB::table('censos_economicos_completo')
        ->join('areas_geoestadisticas_municipales', 'censos_economicos_completo.cve_mun', '=', 'areas_geoestadisticas_municipales.cve_mun')
        ->whereNested(function($query) use ($year, $entidad, $region, $nivel, $sectores, $subsectores, $ramas)
            {   
                $query->where('ayo', '=', $year);
                $query->where('censos_economicos_completo.cve_ent', '=', $entidad);
                $query->where('areas_geoestadisticas_municipales.cve_region', '=', $region);
                /****************Nivel: 1=Sector;2=Subsector;3=Rama;*******************/
                if ($nivel == '1') {
                    $query->whereIn('sector', $sectores);
                    $query->whereIn('subsector', $sectores);
                    $query->whereIn('rama', $sectores);
                }elseif ($nivel == '2') {
                    $query->whereIn('subsector', $subsectores);
                    $query->whereIn('rama', $subsectores);
                }elseif ($nivel == '3') {
                    $query->whereIn('rama', $ramas);
                    $query->whereIn('subrama', $ramas);
                }
                
            })
        ->select('censos_economicos_completo.*', 'areas_geoestadisticas_municipales.cve_region')
        ->get();




        return $consulta;
    }

    public function macroregion_query($year, $entidad, $macroregion, $nivel, $sectores, $subsectores, $ramas){
        $consulta = DB::table('censos_economicos_completo')
        ->join('areas_geoestadisticas_municipales', 'censos_economicos_completo.cve_mun', '=', 'areas_geoestadisticas_municipales.cve_mun')
        ->join('regiones_mun', 'regiones_mun.cve_region', '=', 'areas_geoestadisticas_municipales.cve_region')
        ->whereNested(function($query) use ($year, $entidad, $macroregion, $nivel, $sectores, $subsectores, $ramas)
            {   
                $query->where('ayo', '=', $year);
                $query->where('censos_economicos_completo.cve_ent', '=', $entidad);
                $query->where('regiones_mun.cve_macroregion', '=', $macroregion);
                /****************Nivel: 1=Sector;2=Subsector;3=Rama;*******************/
                if ($nivel == '1') {
                    $query->whereIn('sector', $sectores);
                    $query->whereIn('subsector', $sectores);
                    $query->whereIn('rama', $sectores);
                }elseif ($nivel == '2') {
                    $query->whereIn('subsector', $subsectores);
                    $query->whereIn('rama', $subsectores);
                }elseif ($nivel == '3') {
                    $query->whereIn('rama', $ramas);
                    $query->whereIn('subrama', $ramas);
                }
                
            })
        ->select('censos_economicos_completo.*', 'areas_geoestadisticas_municipales.cve_region')
        ->get();
        return $consulta;
    }


    /**********************Queries per Activity***************************/
        public function every_entidades_query($years, $entidades, $nivel, $sector, $subsector, $rama){
            $consulta = DB::table('censos_economicos_completo')
            ->whereNested(function($query) use ($years, $entidades, $nivel, $sector, $subsector, $rama)
                {   ///dd($sector);
                    $query->whereIn('ayo', $years);

                    $query->whereIn('cve_ent', $entidades);
                    $query->whereIn('cve_mun', $entidades);
                    
                    /****************Nivel: 1=Sector;2=Subsector;3=Rama;*******************/
                    if ($nivel == '1') {
                        $query->where('sector', '=', $sector);
                        $query->where('subsector', '=', $sector);
                        $query->where('rama', '=', $sector);
                    }elseif ($nivel == '2') {
                        $query->where('sector', '=', $sector);
                        $query->where('subsector', '=', $subsector);
                        $query->where('rama', '=', $subsector);
                    }elseif ($nivel == '3') {
                        $query->where('sector', '=', $sector);
                        $query->where('subsector', '=', $subsector);
                        $query->where('rama', '=', $rama);
                        $query->where('subrama', '=', $rama);
                    }
                    
                })
            ->get();
            return $consulta;
        }

        public function every_municipios_query($years, $entidad, $municipios, $nivel, $sector, $subsector, $rama){
            $consulta = DB::table('censos_economicos_completo')
            ->whereNested(function($query) use ($years, $entidad, $municipios, $nivel, $sector, $subsector, $rama)
                {   
                    $query->whereIn('ayo', $years);
                    
                    $query->where('cve_ent', '=', $entidad);
                    $query->whereIn('cve_mun', $municipios);
                    
                    /****************Nivel: 1=Sector;2=Subsector;3=Rama;*******************/
                    if ($nivel == '1') {
                        $query->where('sector', '=', $sector);
                        $query->where('subsector', '=', $sector);
                        $query->where('rama', '=', $sector);
                    }elseif ($nivel == '2') {
                        $query->where('sector', '=', $sector);
                        $query->where('subsector', '=', $subsector);
                        $query->where('rama', '=', $subsector);
                    }elseif ($nivel == '3') {
                        $query->where('sector', '=', $sector);
                        $query->where('subsector', '=', $subsector);
                        $query->where('rama', '=', $rama);
                        $query->where('subrama', '=', $rama);
                    }
                    
                })
            ->get();
            return $consulta;
        }

        public function every_region_query($years, $entidad, $regiones, $nivel, $sector, $subsector, $rama){
            //dd($regiones);
            $consulta = DB::table('censos_economicos_completo')
            ->join('areas_geoestadisticas_municipales', 'censos_economicos_completo.cve_mun', '=', 'areas_geoestadisticas_municipales.cve_mun')
            ->whereNested(function($query) use ($years, $entidad, $regiones, $nivel, $sector, $subsector, $rama)
                {   
                    $query->whereIn('censos_economicos_completo.ayo', $years);
                    //$query->where('ayo', '=', '2004');
                    $query->where('censos_economicos_completo.cve_ent', '=', $entidad);
                    $query->whereIn('areas_geoestadisticas_municipales.cve_region', $regiones);
                    /****************Nivel: 1=Sector;2=Subsector;3=Rama;*******************/
                    if ($nivel == '1') {
                        $query->where('sector', '=', $sector);
                        $query->where('subsector', '=', $sector);
                        $query->where('rama', '=', $sector);
                    }elseif ($nivel == '2') {
                        $query->where('sector', '=', $sector);
                        $query->where('subsector', '=', $subsector);
                        $query->where('rama', '=', $subsector);
                    }elseif ($nivel == '3') {
                        $query->where('sector', '=', $sector);
                        $query->where('subsector', '=', $subsector);
                        $query->where('rama', '=', $rama);
                        $query->where('subrama', '=', $rama);
                    }
                    
                })
            //->whereIn('areas_geoestadisticas_municipales.cve_region', $regiones);
            ->distinct()
            ->select('censos_economicos_completo.*', 'areas_geoestadisticas_municipales.cve_region')
            ->get();




            return $consulta;
        }

        public function every_macroregion_query($years, $entidad, $macroregiones, $nivel, $sector, $subsector, $rama){
            $consulta = DB::table('censos_economicos_completo')
            ->join('areas_geoestadisticas_municipales', 'censos_economicos_completo.cve_mun', '=', 'areas_geoestadisticas_municipales.cve_mun')
            ->join('regiones_mun', 'regiones_mun.cve_region', '=', 'areas_geoestadisticas_municipales.cve_region')
            ->whereNested(function($query) use ($years, $entidad, $macroregiones, $nivel, $sector, $subsector, $rama)
                {   
                    $query->whereIn('censos_economicos_completo.ayo', $years);
                    //$query->where('ayo', '=', '2004');
                    $query->where('censos_economicos_completo.cve_ent', '=', $entidad);
                    $query->whereIn('regiones_mun.cve_macroregion', $macroregiones);
                    /****************Nivel: 1=Sector;2=Subsector;3=Rama;*******************/
                    if ($nivel == '1') {
                        $query->where('sector', '=', $sector);
                        $query->where('subsector', '=', $sector);
                        $query->where('rama', '=', $sector);
                    }elseif ($nivel == '2') {
                        $query->where('sector', '=', $sector);
                        $query->where('subsector', '=', $subsector);
                        $query->where('rama', '=', $subsector);
                    }elseif ($nivel == '3') {
                        $query->where('sector', '=', $sector);
                        $query->where('subsector', '=', $subsector);
                        $query->where('rama', '=', $rama);
                        $query->where('subrama', '=', $rama);
                    }
                    
                })
            ->distinct()
            ->select('censos_economicos_completo.*', 'regiones_mun.cve_macroregion')

            ->get();
            return $consulta;
        }

    /************************Vocaciones regionales start*******************/
        public function regiones_vocacion($year, $entidad, $regiones, $nivel, $sectores, $subsectores, $ramas){
            //dd($regiones);
            $consulta = DB::table('censos_economicos_completo')
            ->join('areas_geoestadisticas_municipales', 'censos_economicos_completo.cve_mun', '=', 'areas_geoestadisticas_municipales.cve_mun')
            ->whereNested(function($query) use ($year, $entidad, $regiones, $nivel, $sectores, $subsectores, $ramas)
                {   
                    $query->where('censos_economicos_completo.ayo', '=', $year);
                    //$query->where('ayo', '=', '2004');
                    $query->where('censos_economicos_completo.cve_ent', '=', $entidad);
                    $query->whereIn('areas_geoestadisticas_municipales.cve_region', $regiones);
                    /****************Nivel: 1=Sector;2=Subsector;3=Rama;*******************/
                    if ($nivel == '1') {
                        $query->whereIn('sector', $sectores);
                        $query->whereIn('subsector', $sectores);
                        $query->whereIn('rama', $sectores);
                    }elseif ($nivel == '2') {
                        $query->whereIn('subsector', $subsectores);
                        $query->whereIn('rama', $subsectores);
                    }elseif ($nivel == '3') {
                        $query->whereIn('rama', $ramas);
                        $query->whereIn('subrama', $ramas);
                    }
                    
                })
            //->whereIn('areas_geoestadisticas_municipales.cve_region', $regiones);
            ->distinct()
            ->select('censos_economicos_completo.*', 'areas_geoestadisticas_municipales.cve_region')
            ->get();




            return $consulta;
        }
        public function macroregiones_vocacion($year, $entidad, $macroregiones, $nivel, $sectores, $subsectores, $ramas){
            //dd($regiones);
            $consulta = DB::table('censos_economicos_completo')
            ->join('areas_geoestadisticas_municipales', 'censos_economicos_completo.cve_mun', '=', 'areas_geoestadisticas_municipales.cve_mun')
            ->join('regiones_mun', 'regiones_mun.cve_region', '=', 'areas_geoestadisticas_municipales.cve_region')
            ->whereNested(function($query) use ($year, $entidad, $macroregiones, $nivel, $sectores, $subsectores, $ramas)
                {   
                    $query->where('censos_economicos_completo.ayo', '=', $year);
                    //$query->where('ayo', '=', '2004');
                    $query->where('censos_economicos_completo.cve_ent', '=', $entidad);
                    $query->whereIn('regiones_mun.cve_macroregion', $macroregiones);
                    //$query->whereIn('areas_geoestadisticas_municipales.cve_region', $regiones);
                    /****************Nivel: 1=Sector;2=Subsector;3=Rama;*******************/
                    if ($nivel == '1') {
                        $query->whereIn('sector', $sectores);
                        $query->whereIn('subsector', $sectores);
                        $query->whereIn('rama', $sectores);
                    }elseif ($nivel == '2') {
                        $query->whereIn('subsector', $subsectores);
                        $query->whereIn('rama', $subsectores);
                    }elseif ($nivel == '3') {
                        $query->whereIn('rama', $ramas);
                        $query->whereIn('subrama', $ramas);
                    }
                    
                })
            //->whereIn('areas_geoestadisticas_municipales.cve_region', $regiones);
            ->distinct()
            ->select('censos_economicos_completo.*', 'regiones_mun.cve_macroregion')
            ->get();




            return $consulta;
        }
        

    /************************Vocaciones regionales end*******************/
}
