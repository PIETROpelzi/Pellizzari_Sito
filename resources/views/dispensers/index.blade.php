@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header flex items-center justify-between">
            <span>Dispenser</span>
            <a href="{{ route('dispensers.create') }}" class="btn-primary">Nuovo Dispenser</a>
        </div>
        <div class="panel-body">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="pb-2">Nome</th>
                            <th class="pb-2">Paziente</th>
                            <th class="pb-2">Device UID</th>
                            <th class="pb-2">Online</th>
                            <th class="pb-2">Ultimo segnale</th>
                            <th class="pb-2">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($dispensers as $dispenser)
                            <tr>
                                <td class="py-2 font-medium">{{ $dispenser->name }}</td>
                                <td class="py-2">{{ $dispenser->patient->name ?? '-' }}</td>
                                <td class="py-2">{{ $dispenser->device_uid }}</td>
                                <td class="py-2">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $dispenser->is_online ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                                        {{ $dispenser->is_online ? 'Online' : 'Offline' }}
                                    </span>
                                </td>
                                <td class="py-2">{{ $dispenser->last_seen_at?->format('d/m H:i') ?? '-' }}</td>
                                <td class="py-2">
                                    <div class="flex gap-2">
                                        <a href="{{ route('dispensers.show', $dispenser) }}" class="btn-secondary">Apri</a>
                                        <a href="{{ route('dispensers.edit', $dispenser) }}" class="btn-secondary">Modifica</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-slate-500">Nessun dispenser registrato.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $dispensers->links() }}</div>
        </div>
    </section>
@endsection
