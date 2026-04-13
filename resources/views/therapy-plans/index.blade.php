@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header flex items-center justify-between">
            <span>Piani Terapeutici</span>
            <a href="{{ route('therapy-plans.create') }}" class="btn-primary">Nuovo Piano</a>
        </div>
        <div class="panel-body">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="pb-2">Paziente</th>
                            <th class="pb-2">Farmaco</th>
                            <th class="pb-2">Dose</th>
                            <th class="pb-2">Orari</th>
                            <th class="pb-2">Stato</th>
                            <th class="pb-2">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($therapyPlans as $plan)
                            <tr>
                                <td class="py-2">{{ $plan->patient->name ?? '-' }}</td>
                                <td class="py-2">{{ $plan->medicine->name ?? '-' }}</td>
                                <td class="py-2">{{ $plan->dose_amount }} {{ $plan->dose_unit }}</td>
                                <td class="py-2">{{ $plan->schedules->pluck('scheduled_time')->map(fn ($time) => substr((string) $time, 0, 5))->join(', ') }}</td>
                                <td class="py-2">{{ $plan->is_active ? 'Attivo' : 'Non attivo' }}</td>
                                <td class="py-2">
                                    <div class="flex gap-2">
                                        <a href="{{ route('therapy-plans.show', $plan) }}" class="btn-secondary">Apri</a>
                                        <a href="{{ route('therapy-plans.edit', $plan) }}" class="btn-secondary">Modifica</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500">Nessun piano terapeutico.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $therapyPlans->links() }}</div>
        </div>
    </section>
@endsection
