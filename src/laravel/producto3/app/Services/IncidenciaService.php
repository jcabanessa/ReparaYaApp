<?php
namespace App\Services;
use App\Models\{Comision,Comunidad,EmpresaGestora,Especialidad,HistorialEstado,Incidencia,Tecnico,User};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class IncidenciaService {
    public function crear(array $data, User $actor): Incidencia {
        $fecha=Carbon::parse($data['fecha_servicio'],config('app.timezone'));
        if ($fecha->lessThanOrEqualTo(now())) throw ValidationException::withMessages(['fecha_servicio'=>'La cita debe ser futura.']);
        if ($data['tipo_urgencia']==='Estándar' && $fecha->lessThan(now()->addHours(48)))
            throw ValidationException::withMessages(['fecha_servicio'=>'Una solicitud estándar requiere al menos 48 horas de antelación.']);
        $clienteId=(int)($data['cliente_id']??$actor->id);
        $cliente=User::findOrFail($clienteId);
        if ($cliente->rol!=='particular') throw ValidationException::withMessages(['cliente_id'=>'Selecciona un cliente particular.']);
        if ($actor->rol==='particular' && $clienteId!==$actor->id) throw new AuthorizationException();
        if (!in_array($actor->rol,['particular','admin','gestora'],true)) throw new AuthorizationException();
        $comunidad=null;$gestora=null;
        if (!empty($data['comunidad_id'])){
            $comunidad=Comunidad::with('gestora')->findOrFail((int)$data['comunidad_id']);
            $gestora=$comunidad->gestora;
        }
        if ($actor->rol==='gestora') {
            if (!$comunidad || (int)$gestora->usuario_id!==(int)$actor->id) throw new AuthorizationException('Comunidad ajena a tu gestora.');
        } elseif ($actor->rol==='particular' && $comunidad) throw new AuthorizationException();
        $especialidad=Especialidad::findOrFail((int)$data['especialidad_id']);
        return DB::transaction(function() use($data,$actor,$clienteId,$fecha,$comunidad,$gestora,$especialidad){
            for ($n=0;$n<25;$n++) {
                $codigo='REP-'.now()->year.'-'.str_pad((string)random_int(0,9999),4,'0',STR_PAD_LEFT);
                // Sin reintentar transacciones abortadas por SQL; comprobar existencia antes de insertar.
                if (Incidencia::where('localizador',$codigo)->exists()) continue;
                try {
                    $inc=Incidencia::create([
                        'localizador'=>$codigo, 'cliente_id'=>$clienteId,'especialidad_id'=>$especialidad->id,
                        'descripcion'=>$data['descripcion'],'direccion'=>$data['direccion'],
                        'fecha_servicio'=>$fecha,'franja_horaria'=>$data['franja_horaria'],
                        'telefono_contacto'=>$data['telefono_contacto'], 'tipo_urgencia'=>$data['tipo_urgencia'],
                        'estado'=>'Pendiente','precio_aplicado'=>$especialidad->precio_base,
                        'comunidad_id'=>$comunidad?->id,'empresa_gestora_id'=>$gestora?->id,
                    ]);
                } catch(QueryException $e) {
                    if ($e->getCode()==='23000') continue;
                    throw $e;
                }
                $this->registrar($inc,null,'Pendiente',$actor,'Creación de incidencia');
                return $inc;
            }
            throw ValidationException::withMessages(['general'=>'No se ha podido generar un localizador único.']);
        });
    }
    public function cancelar(int $id, User $actor):void {
        DB::transaction(function()use($id,$actor){
            $inc=Incidencia::lockForUpdate()->findOrFail($id);
            if (!in_array($inc->estado,['Pendiente','Asignada'],true)) throw ValidationException::withMessages(['estado'=>'No se puede cancelar una incidencia iniciada o cerrada.']);
            if ($actor->rol==='particular') {
                if ((int)$inc->cliente_id!==(int)$actor->id) throw new AuthorizationException();
                if ($inc->fecha_servicio->lessThanOrEqualTo(now()->addHours(48))) throw ValidationException::withMessages(['estado'=>'La cancelación del cliente exige MÁS de 48 horas.']);
            } elseif ($actor->rol==='gestora') {
                $gestora=EmpresaGestora::where('usuario_id',$actor->id)->firstOrFail();
                if ((int)$inc->empresa_gestora_id!==(int)$gestora->id) throw new AuthorizationException();
                if ($inc->fecha_servicio->lessThanOrEqualTo(now()->addHours(48))) throw ValidationException::withMessages(['estado'=>'La cancelación exige MÁS de 48 horas.']);
            } elseif ($actor->rol!=='admin') throw new AuthorizationException();
            $old=$inc->estado;$inc->update(['estado'=>'Cancelada']);$this->registrar($inc,$old,'Cancelada',$actor,'Cancelación autorizada');
        });
    }
    public function asignar(int $id,int $tecnicoId,User $actor):void {
        if ($actor->rol!=='admin') throw new AuthorizationException();
        DB::transaction(function()use($id,$tecnicoId,$actor){
            $inc=Incidencia::lockForUpdate()->findOrFail($id);
            if (!in_array($inc->estado,['Pendiente','Asignada'],true)) throw ValidationException::withMessages(['estado'=>'La incidencia no admite asignación.']);
            $tecnico=Tecnico::findOrFail($tecnicoId);
            if (!$tecnico->disponible || (int)$tecnico->especialidad_id!==(int)$inc->especialidad_id) throw ValidationException::withMessages(['tecnico_id'=>'Técnico no disponible o de otra especialidad.']);
            $old=$inc->estado;$inc->update(['tecnico_id'=>$tecnico->id,'estado'=>'Asignada']);
            $this->registrar($inc,$old,'Asignada',$actor,'Asignado a '.$tecnico->nombre_completo);
        });
    }
    public function avanzar(int $id,User $actor,string $nuevo):void {
        if ($actor->rol!=='tecnico') throw new AuthorizationException();
        DB::transaction(function() use($id,$actor,$nuevo){
            $inc=Incidencia::lockForUpdate()->findOrFail($id);
            $tecnico=Tecnico::where('usuario_id',$actor->id)->firstOrFail();
            if ((int)$inc->tecnico_id!==(int)$tecnico->id) throw new AuthorizationException('Esta incidencia corresponde a otro técnico.');
            $transiciones=['Asignada'=>'En curso','En curso'=>'Finalizada'];
            if (($transiciones[$inc->estado]??null)!==$nuevo) throw ValidationException::withMessages(['estado'=>'Transición de estado prohibida.']);
            $old=$inc->estado;
            $inc->estado=$nuevo;
            if ($nuevo==='Finalizada') $inc->finalizada_at=now();
            $inc->save();
            $this->registrar($inc,$old,$nuevo,$actor,'Cambio realizado por técnico');
            if ($nuevo==='Finalizada') app(ComisionService::class)->generar($inc);
        });
    }
    public function editar(int $id,array $data,User $actor):void {
        if ($actor->rol!=='admin') throw new AuthorizationException();
        DB::transaction(function()use($id,$data,$actor){
            $inc=Incidencia::lockForUpdate()->findOrFail($id);
            if (!in_array($inc->estado,['Pendiente','Asignada'],true)) throw ValidationException::withMessages(['estado'=>'La incidencia iniciada o cerrada está bloqueada.']);
            $fecha=Carbon::parse($data['fecha_servicio']);
            if ($fecha->lessThanOrEqualTo(now())||($data['tipo_urgencia']==='Estándar' && $fecha->lessThan(now()->addHours(48))))
                throw ValidationException::withMessages(['fecha_servicio'=>'La fecha no cumple la antelación requerida.']);
            if ($inc->estado==='Asignada' && (int)$inc->especialidad_id!==(int)$data['especialidad_id'])
                throw ValidationException::withMessages(['especialidad_id'=>'Desasigna primero al técnico.']);
            $inc->update(collect($data)->only(['especialidad_id','descripcion','direccion','fecha_servicio','franja_horaria','telefono_contacto','tipo_urgencia'])->all());
            $this->registrar($inc,$inc->estado,$inc->estado,$actor,'Edición de datos por administrador');
        });
    }
    private function registrar(Incidencia $inc, ?string $desde,string $hasta,User $actor,string $nota):void {
        HistorialEstado::create(['incidencia_id'=>$inc->id,'estado_anterior'=>$desde,'nuevo_estado'=>$hasta,
            'usuario_id'=>$actor->id,'observaciones'=>$nota,'created_at'=>now()]);
    }
}
