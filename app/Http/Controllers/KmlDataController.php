<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Zip;
use \ZipArchive;
class KmlDataController extends Controller
{
    //
    public function entidades()
    {
    	//$data = DB::select("SELECT id, ST_AsGeoJSON(st_transform(geom, 4326))::json FROM mge");
    	ini_set('memory_limit', '8192M');



    	$kml = '';
    	//$data = DB::select("SELECT id, ST_AsKML (st_transform (geom, 4326)) FROM mge");
    	$data = DB::select("SELECT id, cve_ent, nom_ent, ST_AsGeoJSON (st_transform (geom, 4326)) FROM mge");
    	//$data = DB::select("SELECT id, AsTopoJSON (st_transform (geom, 4326)) FROM mge");
    	$array_geojson = array("type"=>'FeatureCollection', 'crs'=>array("type"=>"name", "properties"=> array("name"=> "urn:ogc:def:crs:EPSG::102003" )), 'features'=> []);
    	//$geojson = '{"type": "FeatureCollection",'.
		//			'"crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:EPSG::102003" } },'.
		//			'"features": [';
    	foreach ($data as $value) {
    		//dd($value->st_askml);
    		//$element = '{ "type": "Feature", "properties": { "labelrank": 4.000000, "name": "'.$value->nom_ent.'",'.
    		//'"cve_ent": "'.$value->cve_ent.'", "id": "'+$value->id.'"},"geometry":'.$value->st_asgeojson.'},';
    		//$geojson .= $element;
    		$array_element = array("type"=> "Feature", "properties"=> array("labelrank"=> 4.000000, "name"=> $value->nom_ent,"cve_ent"=> $value->cve_ent, "id"=> $value->cve_ent),"geometry"=>json_decode($value->st_asgeojson));
    		array_push($array_geojson['features'], $array_element);
    	}
    	//$geojson .= ']}';
    	//dd(json_decode($geojson));
    	/*$head = '<?xml version="1.0" encoding="UTF-8"?>'.
		'<kml xmlns:atom="http://www.w3.org/2005/Atom" xmlns="http://www.opengis.net/kml/2.2"> <NetworkLink>'.
		'<open>1</open>'.
		'<name>Entidades</name>';
    	$foot = '<Style id="PolyStyle00">'.
				    '<LabelStyle>'.
				      '<color>00000000</color>'.
				      '<scale>0.000000</scale>'.
				    '</LabelStyle>'.
				    '<LineStyle>'.
				      '<color>ff0098e6</color>'.
				      '<width>6.000000</width>'.
				    '</LineStyle>'.
				    '<PolyStyle>'.
				      '<color>00f0f0f0</color>'.
				      '<outline>1</outline>'.
				    '</PolyStyle>'.
				  '</Style>'.
				'</kml>';
    	$content = '';
    	foreach ($data as $value) {
    		//dd($value->st_askml);
    		$content .= '<Placemark id="'.$value->id.'"><name>'.$value->id.'</name>'.$value->st_askml.'</Placemark>';
    	}
    	$kml = $head.$content.$foot;
    	$file = fopen("doc.json", "w");
		fwrite($file, $kml);
		fclose($file);*/
		/*$zipper = new \Chumper\Zipper\Zipper;

		$zipper->make('test.zip')->folder('test')->add('doc.kml');
		$zipper->close();*/
		//$zip = Zip::create("file.zip");
		//$zip->add("doc.kml");
		/*header('Content-Type: application/vnd.google-earth.kmz');
		header('Content-Disposition: attachment; filename="test.kmz"');

		$kmlString="This is your KML string";

		$file = "test.kmz";
		$zip = new ZipArchive();

		if ($zip->open($file, ZIPARCHIVE::CREATE)!==TRUE) {
			exit("cannot open <$file>\n");
		}
		$zip->addFromString("doc.kml", $kml);
		$zip->close();*/
		//echo file_get_contents($file);

    	return response()->json(['kml' => "KML de entidadesss", 'data'=>$array_geojson], 200);
    }
}
