<?php
/**
 *DataController.php
 *Clase con metos de consulta de datos 
 *
 * @package      Controllers
 * @category     Consulta
 * @author       Rodrigo Martínez <rodrigo.mtz.hrz@gmail.com>
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;
/**
*Esta clase contiene los metodos basicos para realizar consultas a la base de datos *economia*.
*/
class DataController extends Controller
{
    /**
    *Función que almacena imágenes dentro de una carpeta del servidor.
    *@param array $request array de datos recividos desde el formulario
    *@method save_image(Request $request) 
    */
    public function save_image(Request $request)
    {   
        $data = $request->all();
        $data = urldecode($data['imageData']);
        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);
        $name = 'gallery/mapa'.rand(0, 100).'.png';
        file_put_contents($name, $data);
        //chmod($name, 0664);
        DB::table('gallery')->insertGetId(
            ['nombre' => $name]
        );
    }
    /**
    *Función  que obtiene array de actividades almacenada en las tablas pib_precios_constantes o pib_precios_corrientes.
    *@param array $request array de datos recividos desde el formulario 
    */
    public function actividades(Request $request)
    {   
        $data = $request->all();
        if ($data['base']==1) {
            $data['base'] = 'pib_precios_constantes';
        }elseif($data['base']==2){
            $data['base'] = 'pib_precios_corrientes';
        }
    	$actividades=DB::table($data['base'])->select('Tipo')->distinct()->orderBy('Tipo')->get();
    	return response()->json(['actividades' => $actividades], 200);
    }
    /**
    *Función  que obtiene array de sectores almacenada en las tablas pib_precios_constantes o pib_precios_corrientes.
    *@param array $request array de datos recividos desde el formulario 
    */
    public function coverturas(Request $request)
    {
    	$data = $request->all();
        if ($data['base']==1) {
            $data['base'] = 'pib_precios_constantes';
        }elseif($data['base']==2){
            $data['base'] = 'pib_precios_corrientes';
        }
    	$coverturas = DB::table($data['base'])->select('Sector')->distinct()->where('Tipo', '=', $data['actividad'])->orderBy('Sector')->get();
        //dd($coverturas);
    	return response()->json(['coverturas' => $coverturas], 200);
    }
    /**
    *Función  que obtiene array de subsectores almacenada en las tablas pib_precios_constantes o pib_precios_corrientes.
    *@param array $request array de datos recividos desde el formulario 
    */
    public function subsectores(Request $request){
        $data = $request->all();
        if ($data['base']==1) {
            $data['base'] = 'pib_precios_constantes';
        }elseif($data['base']==2){
            $data['base'] = 'pib_precios_corrientes';
        }
         $subsectores = DB::table($data['base'])->select('Subsector')->distinct()->where('Sector', '=', $data['covertura'])->orderBy('Subsector')->get();
         return response()->json(['subsectores' => $subsectores], 200);
    }
    /**
    *Función que obtiene array de años, son las cabeceras de las tablas pib_precios_constantes o pib_precios_corrientes exepto 2013.
    *@param array $request array de datos recividos desde el formulario 
    */
    public function years(Request $request){
        $data = $request->all();
        if ($data['base']==1) {
            $data['base'] = 'pib_precios_constantes';
        }elseif($data['base']==2){
            $data['base'] = 'pib_precios_corrientes';
        }
        $years = $cabeceras = DB::select("SELECT column_name FROM information_schema.columns WHERE table_schema = 'public' AND table_name   = '".$data['base']."' AND column_name != 'NOM_ENT' AND column_name != 'Tipo' AND column_name != 'cve_ent' AND column_name != 'id' AND column_name != 'Sector' AND column_name != 'Subsector' AND column_name != '2003'");
        return response()->json(['years' => $years], 200);
    }
    /**
    *Función que obtiene array de años, son las cabeceras de las tablas pib_precios_constantes o pib_precios_corrientes incluyendo 2013
    *@param array $request array de datos recividos desde el formulario 
    */
    public function years_whole(Request $request){
        $data = $request->all();
        if ($data['base']==1) {
            $data['base'] = 'pib_precios_constantes';
        }elseif($data['base']==2){
            $data['base'] = 'pib_precios_corrientes';
        }
        
        $years = $cabeceras = DB::select("SELECT column_name FROM information_schema.columns WHERE table_schema = 'public' AND table_name   = '".$data['base']."' AND column_name != 'NOM_ENT' AND column_name != 'Tipo' AND column_name != 'cve_ent' AND column_name != 'id' AND column_name != 'Sector' AND column_name != 'Subsector'");
         return response()->json(['years' => $years], 200);
    }
    /**
    *Función que obtiene un array con el id y el nombre de las imagenes almacenada en la tabla gallery.
    */
    public function images(){
        $images = DB::table('gallery')->select('id', 'nombre')->get();
        return response()->json(['images' => $images]);
    }
    /**
    *Función que vacia la tabla gallery con el metodo truncate() y ademas elimina todos los archivos de la carpeta gallery/ del servidor. 
    */
    public function truncate_gallery(){
        $entidades = DB::table('gallery')->truncate();
        $files = glob('gallery/*'); // obtiene todos los archivos
        foreach($files as $file){
          if(is_file($file)) // si se trata de un archivo
            unlink($file); // lo elimina
        }
        if (file_exists('gallery.zip')) {
            unlink('gallery.zip');
        }
        
        return response()->json(['truncate' => 'ok']);
    }
    /**
    *Función que comprime la carpeta gallery/ en un archivo de formato zip y despues lo envia al usuario para descargarlo.
    */
    public function download_gallery(){
        $zipper = new \Chumper\Zipper\Zipper;
        $files = glob('gallery/*');
        $zipper->make('gallery.zip')->add($files);

        $zipper->close();
        return response()->download(public_path('gallery.zip'));
    }
    /**
    *Función que obtiene un array con los campos nom_ent, cve_ent, clave_region, latitud y longitud de los datos  almacenados en la tabla mge
    */
    public function entidades(){
        $entidades = DB::table('mge')->select('nom_ent', 'cve_ent', 'clave_region', 'latitud', 'longitud')->orderBy('cve_ent')->distinct()->get();
        return response()->json(['entidades' => $entidades]);
    }
    /**
    *Función que obtiene un array con los campos nom_ent, cve_ent, clave_region de los datos  almacenados en la tabla mge filtrados por región.
    *@param array $request array de datos recividos desde el formulario 
    */
    public function entidades_by_region(Request $request){
        $data = $request->all();
        $entidades = DB::table('mge')->select('id', 'nom_ent', 'cve_ent', 'clave_region')->orderBy('cve_ent')->where('clave_region', '=', $data['id'])->get();
        return response()->json(['entidades' => $entidades]);
    }
    /**
    *Función que obtiene un array con los campos Nombre, clave, latitud y longitud de los datos  almacenados en la tabla regiones.
    */
    public function regiones(){
        $regiones = DB::table('regiones')->select('Nombre', 'clave', 'latitud', 'longitud')->orderBy('clave')->distinct()->get();
        return response()->json(['regiones' => $regiones]);
    }
    /**
    *Función que genera un archivo Excel con los datos almacenados en el parametro $data.
    *@param array $request array de datos necesarios para generar el archivo excel 
    */
    public function tabla_excel(Request $request){
        $data = $request->all();
        
        return $tabla = \View::make('tabla')->with(['data'=>$data]);
    }
}
