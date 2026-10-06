@if($pagina->hasPages())
<nav class="paging" aria-label="Paginación">
    @if($pagina->onFirstPage())<span class="muted">Anterior</span>@else<a class="btn" href="{{ $pagina->previousPageUrl() }}">← Anterior</a>@endif
    <span class="muted">Página {{ $pagina->currentPage() }} de {{ $pagina->lastPage() }}</span>
    @if($pagina->hasMorePages())<a class="btn" href="{{ $pagina->nextPageUrl() }}">Siguiente →</a>@else<span class="muted">Siguiente</span>@endif
</nav>
@endif
