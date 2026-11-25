<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class ServicioZonaController extends Controller {
    public function index(){
        // Incluye 'Sin zona' para incidencias particulares; la suma de porcentajes es 100 %.
        $rows=DB::table('incidencias as i')->leftJoin('comunidades as c','c.id','=','i.comunidad_id')
            ->where('i.estado','Finalizada')
            ->selectRaw("COALESCE(NULLIF(c.zona, ''), 'Sin zona') as zona, COUNT(*) as n")
            ->groupByRaw("COALESCE(NULLIF(c.zona, ''), 'Sin zona')")
            ->orderBy('zona')->get();
        $total=(int)$rows->sum('n');
        if($total===0)return response()->json(['data'=>[],'total_servicios_realizados'=>0]);
        $porcentajes=[];$acumulado=0;
        foreach($rows as $i=>$row){
            $p=($i===$rows->count()-1)?round(100-$acumulado,2):round(100*(int)$row->n/$total,2);
            $acumulado+=$p;
            $porcentajes[]=['zona'=>$row->zona,'servicios_realizados'=>(int)$row->n,'porcentaje_total'=>$p];
        }
        return response()->json(['data'=>$porcentajes,'total_servicios_realizados'=>$total]);
    }
}
