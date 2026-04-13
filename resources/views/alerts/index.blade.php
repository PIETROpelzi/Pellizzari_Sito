@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header">Alert</div>
        <div class="panel-body space-y-4">
            <form method="GET" class="flex flex-wrap gap-2">
                <a href="{{ route('alerts.index') }}" class="btn-secondary {{ $status === '' ? 'bg-slate-900 text-white' : '' }}">Tutti</a>
                <a href="{{ route('alerts.index', ['status' => 'open']) }}" class="btn-secondary {{ $status === 'open' ? 'bg-slate-900 text-white' : '' }}">Aperti</a>
                <a href="{{ route('alerts.index', ['status' => 'resolved']) }}" class="btn-secondary {{ $status === 'resolved' ? 'bg-slate-900 text-white' : '' }}">Risolti</a>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="pb-2">Data</th>
                            <th class="pb-2">Paziente</th>
                            <th class="pb-2">Tipo</th>
                            <th class="pb-2">Severita</th>
                            <th class="pb-2">Messaggio</th>
                            <th class="pb-2">Stato</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($alerts as $alert)
                            <tr>
                                <td class="py-2">{{ $alert->triggered_at?->format('d/m/Y H:i') }}</td>
                                <td class="py-2">{{ $alert->patient->name ?? '-' }}</td>
                                <td class="py-2">{{ $alert->type }}</td>
                                <td class="py-2">{{ $alert->severity }}</td>
                                <td class="py-2">{{ $alert->message }}</td>
                                <td class="py-2">
                                    @if ($alert->resolved_at)
                                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Risolto</span>
                                    @elseif (auth()->user()->canManageClinicalData())
                                        <form method="POST" action="{{ route('alerts.resolve', $alert) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-secondary">Segna risolto</button>
                                        </form>
                                    @else
                                        <span class="rounded-full bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-700">Aperto</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500">Nessun alert disponibile.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $alerts->links() }}</div>
        </div>
    </section>
@endsection
