@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header flex items-center justify-between">
            <span>Farmaci</span>
            <a href="{{ route('medicines.create') }}" class="btn-primary">Nuovo Farmaco</a>
        </div>
        <div class="panel-body">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="pb-2">Nome</th>
                            <th class="pb-2">Paziente</th>
                            <th class="pb-2">Scorta</th>
                            <th class="pb-2">Piani</th>
                            <th class="pb-2">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($medicines as $medicine)
                            <tr>
                                <td class="py-2 font-medium">{{ $medicine->name }}</td>
                                <td class="py-2">{{ $medicine->patient->name ?? '-' }}</td>
                                <td class="py-2">
                                    <span class="{{ $medicine->isStockLow() ? 'text-rose-700 font-semibold' : '' }}">
                                        {{ $medicine->remaining_quantity }} (soglia {{ $medicine->reorder_threshold }})
                                    </span>
                                </td>
                                <td class="py-2">{{ $medicine->therapy_plans_count }}</td>
                                <td class="py-2">
                                    <div class="flex gap-2">
                                        <a href="{{ route('medicines.show', $medicine) }}" class="btn-secondary">Apri</a>
                                        <a href="{{ route('medicines.edit', $medicine) }}" class="btn-secondary">Modifica</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500">Nessun farmaco disponibile.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $medicines->links() }}</div>
        </div>
    </section>
@endsection
