<?php

use Illuminate\Http\Request;
//use ZipArchive;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
    $token = JWTAuth::getToken();
    $user = JWTAuth::toUser($token);

    return $user;
    //return ['name' => 'Rodrigo'];
})->middleware('jwt.auth');

Route::post('/authenticate', [
	'uses' => 'ApiAuthController@authenticate'
]);

Route::post('/register', [
	'uses' => 'ApiAuthController@register'
]);
Route::post('/save', [
	'uses' => 'DataController@save_image'
]);
Route::get('/images', [
	'uses' => 'DataController@images'
]);
Route::get('/truncate_gallery', [
	'uses' => 'DataController@truncate_gallery'
]);
Route::get('/download_gallery', [
	'uses' => 'DataController@download_gallery'
]);

Route::get('/kml_entidades', [
	'uses' => 'KmlDataController@entidades'
]);

Route::post('/tabla_excel', [
	'uses' => 'DataController@tabla_excel'
]);

Route::post('/vocaciones', [
	'uses' => 'VocacionesController@main_vocacion'
]);
Route::post('/vocaciones_excel', [
	'uses' => 'VocacionesController@main_vocacion_excel'
]);

Route::post('/mapa', [
	'uses' => 'MapaDataController@mapa'
]);
Route::post('/mapa_excel', [
	'uses' => 'MapaDataController@mapa_excel'
]);
Route::post('/resumen', [
	'uses' => 'ResumenDataController@resumen'
]);
Route::post('/resumen_excel', [
	'uses' => 'ResumenDataController@resumen_excel'
]);
Route::post('/estadisticas', [
	'uses' => 'EstadisticasController@main_estadisticas'
]);
Route::post('/estadisticas_excel', [
	'uses' => 'EstadisticasController@main_estadisticas_excel'
]);
Route::post('/evaluacion', [
	'uses' => 'EvaluacionController@main_evaluacion'
]);
Route::post('/evaluacion_excel', [
	'uses' => 'EvaluacionController@main_evaluacion_excel'
]);
Route::post('/mapasevaluacion_excel', [
	'uses' => 'MapasEvaluacionController@main_evaluacion_excel'
]);

Route::post('/mapasevaluacion', [
	'uses' => 'MapasEvaluacionController@main_evaluacion'
]);
Route::post('/resumenmapa', [
	'uses' => 'ResumenDataController@resumen_mapa'
]);
Route::post('/global', [
	'uses' => 'MapaDataController@mapa'
]);
/*Route::get('/actividades', [
	'uses' => 'DataController@actividades'
]);*/
Route::post('/actividades', [
	'uses' => 'DataController@actividades'
]);
Route::post('/coverturas', [
	'uses' => 'DataController@coverturas'
]);
Route::post('/subsectores', [
	'uses' => 'DataController@subsectores'
]);
Route::post('/years', [
	'uses' => 'DataController@years'
]);
Route::post('/years_whole', [
	'uses' => 'DataController@years_whole'
]);
Route::get('/entidades', [
	'uses' => 'DataController@entidades'
]);
Route::post('/entidades_by_region', [
	'uses' => 'DataController@entidades_by_region'
]);
Route::get('/regiones', [
	'middleware'=>'cors', 'uses' => 'DataController@regiones'
]);

/**************Censos económicos******************/
Route::get('/index', [
	'uses' => 'CensosController@index'
]);

Route::post('/columnas', [
	'uses' => 'CensosController@columnas'
]);
Route::post('/data_column', [
	'uses' => 'CensosController@data_column'
]);
Route::post('/consult_data', [
	'uses' => 'CensosController@consult_data'
]);

Route::group(['middleware' => 'cors'], function () {
    Route::post("/angular2login", 'UserController@login');
});




/************Censos economicos vocaciones regionales*******************/
/*Route::get('/censos/sectores', [
	'uses' => 'DataCensosController@sectores'
]);*/
Route::post('/censos/sectores', [
	'uses' => 'DataCensosController@sectores'
]);
Route::post('/censos/subsectores', [
	'uses' => 'DataCensosController@subsectores'
]);
Route::post('/censos/ramas', [
	'uses' => 'DataCensosController@ramas'
]);
	
Route::get('/censos/current/sectores', [
	'uses' => 'DataCensosController@current_sectors'
]);
Route::post('/censos/current/subsectores', [
	'uses' => 'DataCensosController@current_subsectors'
]);
Route::post('/censos/current/ramas', [
	'uses' => 'DataCensosController@current_branches'
]);

Route::get('/censos/years', [
	'uses' => 'DataCensosController@years'
]);
Route::get('/censos/entidades', [
	'uses' => 'DataCensosController@entidades'
]);
Route::post('/censos/municipios', [
	'uses' => 'DataCensosController@municipios'
]);
Route::post('/censos/regiones', [
	'uses' => 'DataCensosController@regiones'
]);
Route::post('/censos/macroregiones', [
	'uses' => 'DataCensosController@macroregiones'
]);
Route::get('/censos/indicadores', [
	'uses' => 'DataCensosController@indicadores'
]);
Route::post('/censos/tabla', [
	'uses' => 'DataCensosController@consulta'
]);
Route::post('/censos/tabla_actividad', [
	'uses' => 'DataCensosController@consulta_actividad'
]);
Route::post('/censos/export', [
	'uses' => 'DataCensosController@export'
]);
Route::post('/censos/tabla_por_indicador', [
	'uses' => 'DataCensosController@consulta_por_indicador'
]);
Route::post('/censos/chart_por_indicador', [
	'uses' => 'DataCensosController@chart_por_indicador'
]);
Route::post('/censos/influencia', [
	'uses' => 'DataCensosController@influencia'
]);
Route::post('/censos/influencia_actividad', [
	'uses' => 'DataCensosController@influencia_actividad'
]);
Route::post('/censos/variacion', [
	'uses' => 'DataCensosController@variacion'
]);
Route::post('/censos/variacion_actividad', [
	'uses' => 'DataCensosController@variacion_actividad'
]);
Route::post('/censos/vocaciones', [
	'uses' => 'DataCensosController@vocaciones'
]);
Route::post('/censos/vocaciones/export', [
	'uses' => 'DataCensosController@vocaciones_export'
]);







Route::get('/token', array('middleware' => ['cors', 'jwt.auth'], function() {
    if ( ! $user = \JWTAuth::parseToken()->authenticate() ) {
        return response()->json(['User Not Found'], 404);
    }
 
    $user = \JWTAuth::parseToken()->authenticate();
    $token = \JWTAuth::getToken();
    $newToken = \JWTAuth::refresh($token);
    return response()->json(['email' => $user->email, 'token' => $newToken], 200);
}));



/******************Test****************************/

Route::get('/cuestionario', [
	'uses' => 'CuestionarioController@cuestionario'
]);