<?php
namespace App\Services;
use App\Models\{Comision,EmpresaGestora,Incidencia};
use Illuminate\Support\Facades\DB;
class ComisionService {
    /** Llamar dentro de la misma transacción que finaliza la incidencia. */
    public function generar(Incidencia $incidencia):?Comision {
        if($incidencia->estado!=='Finalizada' || !$incidencia->empresa_gestora_id) return null;
        $gestora=EmpresaGestora::findOrFail($incidencia->empresa_gestora_id);
        $base=(float)$incidencia->precio_aplicado;
        $pct=(float)$gestora->porcentaje_comision;
        $importe=round($base*$pct/100,2);
        return Comision::firstOrCreate(['incidencia_id'=>$incidencia->id],[
            'empresa_gestora_id'=>$gestora->id,'precio_base_aplicado'=>$base,
            'porcentaje_aplicado'=>$pct,'importe'=>$importe,
            'fecha_finalizacion'=>$incidencia->finalizada_at??now(),'estado'=>'Pendiente',
        ]);
    }
    /** Misma fuente para los paneles de administrador y de gestora. */
    public function listado(?int $empresaId=null):array {
        $q=Comision::with(['incidencia.comunidad','incidencia.especialidad','gestora'])
           ->whereHas('incidencia',fn($q)=>$q->where('estado','Finalizada'));
        if($empresaId!==null)$q->where('empresa_gestora_id',$empresaId);
        $items=$q->orderByDesc('fecha_finalizacion')->get();
        $totales=$items->groupBy(fn($c)=>$c->fecha_finalizacion?->format('Y-m')??'Sin fecha')
            ->map(fn($g)=>['servicios'=>$g->count(),'importe'=>round($g->sum(fn($c)=>(float)$c->importe),2)]);
        return ['items'=>$items,'totales'=>$totales,'total'=>round($items->sum(fn($c)=>(float)$c->importe),2)];
    }
}
