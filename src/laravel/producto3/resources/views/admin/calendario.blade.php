@extends('layouts.app')
@section('title','Calendario de incidencias')
@section('content')<div class="heading"><div><p class="eyebrow">Administración</p><h1>Calendario de intervenciones</h1><p class="muted">Urgente en rojo; estándar en azul. El estado aparece también por escrito.</p></div></div>
<div class="card"><div class="calendar-toolbar"><div class="actions"><button id="prev" class="btn secondary" type="button">Anterior</button><button id="today" class="btn secondary" type="button">Hoy</button><button id="next" class="btn secondary" type="button">Siguiente</button></div>
<h2 id="current-date"></h2><div class="actions"><button data-mode="month" class="btn" type="button">Mes</button><button data-mode="week" class="btn secondary" type="button">Semana</button><button data-mode="day" class="btn secondary" type="button">Día</button></div></div>
<div id="calendar" class="calendar-view"></div></div>
<script>
const eventos=@json($eventos);let fecha=new Date(),modo='month';
const pad=n=>String(n).padStart(2,'0');
const sameDay=(a,b)=>a.getFullYear()===b.getFullYear()&&a.getMonth()===b.getMonth()&&a.getDate()===b.getDate();
const entradas=day=>eventos.filter(e=>e.start&&sameDay(new Date(e.start),day));
const celda=(d,extra='')=>{const cards=entradas(d).map(e=>`<a href="${e.url}" class="event ${e.urgencia==='Urgente'?'urgent':'standard'}"><small>${new Date(e.start).toLocaleTimeString('es-ES',{hour:'2-digit',minute:'2-digit'})}</small><span>${safe(e.title)}</span></a>`).join('');return `<div class="day ${extra}"><div class="day-label">${d.getDate()}</div>${cards}</div>`;};
const safe=t=>String(t).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
function paint(){const a=document.getElementById('calendar');const date=document.getElementById('current-date');
 let dates=[];
 if(modo==='month'){const first=new Date(fecha.getFullYear(),fecha.getMonth(),1);const offset=(first.getDay()+6)%7;
 const start=new Date(first);start.setDate(1-offset);for(let i=0;i<42;i++){const d=new Date(start);d.setDate(start.getDate()+i);dates.push(d);}
 date.textContent=fecha.toLocaleDateString('es-ES',{month:'long',year:'numeric'});
 a.innerHTML='<div class="weekday-row">'+['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'].map(x=>`<span>${x}</span>`).join('')+'</div><div class="month-grid">'+dates.map(d=>celda(d,d.getMonth()===fecha.getMonth()?'':'outside')).join('')+'</div>';}
 else {let from=new Date(fecha);if(modo==='week')from.setDate(from.getDate()-((from.getDay()+6)%7));
 const n=modo==='week'?7:1;for(let i=0;i<n;i++){const d=new Date(from);d.setDate(from.getDate()+i);dates.push(d);}
 date.textContent=modo==='day'?fecha.toLocaleDateString('es-ES',{dateStyle:'full'}):'Semana del '+from.toLocaleDateString('es-ES');
 a.innerHTML='<div class="agenda-grid">'+dates.map(d=>`<div class="agenda-day"><b>${d.toLocaleDateString('es-ES',{weekday:'long',day:'numeric',month:'long'})}</b>`+celda(d)+'</div>').join('')+'</div>';}
 document.querySelectorAll('[data-mode]').forEach(b=>b.className='btn '+(b.dataset.mode===modo?'':'secondary'));}
document.querySelectorAll('[data-mode]').forEach(b=>b.addEventListener('click',()=>{modo=b.dataset.mode;paint();}));
for(const [id,n] of [['prev',-1],['next',1]])document.getElementById(id).onclick=()=>{if(modo==='month')fecha.setMonth(fecha.getMonth()+n);else fecha.setDate(fecha.getDate()+n*(modo==='week'?7:1));paint();};
document.getElementById('today').onclick=()=>{fecha=new Date();paint();};paint();
</script>@endsection
