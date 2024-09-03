<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Libraries\Censos\FormulasFunctions;
use App\Libraries\Censos\CensosFunctions;
use Invoker;
/*use php-di\Cors\ServiceProvider*/

class CuestionarioController extends Controller
{
    
    public function cuestionario(){
        $ejes =DB::connection('mysql')->table('ejes')->select('id', 'eje')->distinct()->get();
        //dd($ejes);
        return view('cuestionario')->with(['ejes'=>$ejes]);
    }

}
