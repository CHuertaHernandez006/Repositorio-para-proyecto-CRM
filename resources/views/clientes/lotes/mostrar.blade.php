@extends('clientes.lotes.base')
@section('lote-titulo', 'Asignar importación')
@section('lote-contenido')
<div class="actions" style="margin-bottom:20px"><a class="btn" href="{{ route('clientes.lotes.index') }}">Todas las importaciones</a></div>
<div class="card"><h2 class="file-name">{{ $registro->nombre_archivo }}</h2><p class="muted">Confirmada el {{ \Carbon\Carbon::parse($registro->created_at)->timezone('America/Mexico_City')->format('Y-m-d H:i:s') }} · Ciudad de México. Registrada por {{ $creador?->name ?? 'Administrador no disponible' }}.</p><small>{{ $registro->importados }} clientes creados originalmente. Los clientes eliminados no participan en las asignaciones.</small></div>
<div class="metrics"><div class="metric">Disponibles<strong>{{ $total }}</strong></div><div class="metric">Sin responsable<strong>{{ $sinAsignar }}</strong></div><div class="metric">Atención individual protegida<strong>{{ $excepciones }}</strong></div><div class="metric">Sin responsable y sin protección<strong>{{ $asignables }}</strong></div></div>
<div class="card"><h2>Asignar el lote</h2>
    <p class="muted">La acción se aplica al lote completo, incluidas otras páginas, aunque hayas filtrado la tabla. Conserva siempre las excepciones individuales.</p>
    @if($operarios->isEmpty())<div class="notice">No hay operarios de Asignación activos y aprobados en tu empresa. Habilita uno en Operarios para poder asignar clientes.</div>@endif
    <form method="POST" action="{{ route('clientes.lotes.asignar', $registro->id_lote) }}" onsubmit="return confirm('¿Aplicar esta asignación al lote completo según la opción elegida? Las excepciones individuales se conservarán.');">
        @csrf @method('PUT')
        <input type="hidden" name="revision" value="{{ $revision }}">
        <div class="grid"><div class="field"><label for="operario-lote">Operario responsable</label><select id="operario-lote" name="id_usuario_responsable" required><option value="">Selecciona un operario</option>@foreach($operarios as $operario)<option value="{{ $operario->id_usuario }}">{{ $operario->name }}</option>@endforeach</select></div>
        <div class="field"><label for="modo-lote">Clientes a modificar</label><select id="modo-lote" name="modo" required><option value="sin_asignar">Solo sin asignar y sin protección ({{ $asignables }})</option><option value="ordinarios">Todos salvo los protegidos ({{ $ordinarios }})</option></select></div></div>
        <button class="btn primary" type="submit" @disabled($operarios->isEmpty() || $ordinarios === 0)>Aplicar al lote</button>
    </form>
</div>
<div class="card"><h2>Clientes de esta importación</h2><p class="muted">Selecciona otro responsable para una atención especial. «Proteger» evita que una asignación masiva cambie esa decisión; también puedes proteger un cliente sin asignar. Desmarca la casilla y guarda para devolverlo al reparto del lote.</p>
    <form method="GET" action="{{ route('clientes.lotes.mostrar', $registro->id_lote) }}"><div class="grid"><div class="field"><label for="buscar-cliente">Buscar cliente</label><input id="buscar-cliente" name="buscar" type="search" maxlength="100" value="{{ $buscar }}" placeholder="Nombre, organización o teléfono"></div><div class="field"><label for="filtro-cliente">Mostrar</label><select id="filtro-cliente" name="filtro"><option value="todos" @selected($filtro === 'todos')>Todos</option><option value="sin_asignar" @selected($filtro === 'sin_asignar')>Sin responsable</option><option value="excepciones" @selected($filtro === 'excepciones')>Atención individual protegida</option></select></div></div><div class="actions"><button class="btn">Filtrar</button><a class="btn" href="{{ route('clientes.lotes.mostrar', $registro->id_lote) }}">Limpiar filtros / actualizar</a></div></form>
    <div class="scroll" style="margin-top:24px" tabindex="0" role="region" aria-label="Clientes del lote"><table><thead><tr><th scope="col">Cliente / contacto</th><th scope="col">Responsable actual</th><th scope="col">Asignación individual</th></tr></thead><tbody>
    @forelse($clientes as $cliente)
        <tr><td><strong>{{ $cliente->nombre }} {{ $cliente->apellido_paterno }}</strong><small>#{{ $cliente->id_cliente }} · Fila {{ $cliente->fila_archivo }} del archivo</small><small>{{ $cliente->organizacion ?: 'Sin organización' }}</small><small>{{ $cliente->telefono_principal ?: 'Sin teléfono' }}</small><small>{{ $cliente->correo ?: 'Sin correo' }}</small></td>
        <td>{{ $responsables->get($cliente->id_usuario_responsable)?->name ?? ($cliente->id_usuario_responsable ? 'Responsable no disponible' : 'Sin asignar') }}<small><span class="tag">{{ $cliente->excepcion ? 'Protegido: atención individual' : 'Participa en reparto masivo' }}</span></small></td>
        <td><form class="row-form" method="POST" action="{{ route('clientes.lotes.individual', ['lote' => $registro->id_lote, 'cliente' => $cliente->id_cliente]) }}">
            @csrf @method('PUT')
            <input type="hidden" name="revision_cliente" value="{{ \App\Services\Clientes\EstadoAsignacionLote::revision([$cliente]) }}">
            <label for="responsable-{{ $cliente->id_cliente }}">Responsable</label><select id="responsable-{{ $cliente->id_cliente }}" name="id_usuario_responsable">
                @if($cliente->id_usuario_responsable && !$operarios->contains('id_usuario', $cliente->id_usuario_responsable))<option value="{{ $cliente->id_usuario_responsable }}" selected disabled>Selecciona otro responsable</option>@endif
                <option value="" @selected(!$cliente->id_usuario_responsable)>Sin asignar</option>@foreach($operarios as $operario)<option value="{{ $operario->id_usuario }}" @selected($cliente->id_usuario_responsable == $operario->id_usuario)>{{ $operario->name }}</option>@endforeach
            </select>
            <input type="hidden" name="excepcion" value="0"><label class="check" for="proteger-{{ $cliente->id_cliente }}"><input id="proteger-{{ $cliente->id_cliente }}" type="checkbox" name="excepcion" value="1" checked>Proteger de asignaciones masivas</label>
            <button class="btn" type="submit">Guardar decisión individual</button>
        </form></td></tr>
    @empty<tr><td colspan="3" class="empty">No hay clientes disponibles con estos filtros.</td></tr>@endforelse
    </tbody></table></div><div style="margin-top:20px">{{ $clientes->links() }}</div>
</div>
<div class="notice">Solo aparecen operarios de Asignación activos y aprobados de tu empresa. Cambiar responsable modifica quién puede atender al cliente; sus llamadas y citas previas conservan su autor y no se transfieren automáticamente.</div>
@endsection
