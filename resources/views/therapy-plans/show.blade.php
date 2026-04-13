@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header flex items-center justify-between">
            <span>Piano #{{ $therapyPlan->id }}</span>
            <a href="{{ route('therapy-plans.edit', $therapyPlan) }}" class="btn-secondary">Modifica</a>
        </div>
        <div class="panel-body grid grid-cols-1 gap-6 lg:grid-cols-3">
            <article class="rounded-xl border border-slate-200 p-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Dati Clinici</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div><dt class="font-medium">Paziente</dt><dd>{{ $therapyPlan->patient->name }}</dd></div>
                    <div><dt class="font-medium">Medico</dt><dd>{{ $therapyPlan->doctor->name }}</dd></div>
                    <div><dt class="font-medium">Farmaco</dt><dd>{{ $therapyPlan->medicine->name }}</dd></div>
                    <div><dt class="font-medium">Dose</dt><dd>{{ $therapyPlan->dose_amount }} {{ $therapyPlan->dose_unit }}</dd></div>
                </dl>
            </article>

            <article class="rounded-xl border border-slate-200 p-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Validita</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div><dt class="font-medium">Inizio</dt><dd>{{ $therapyPlan->starts_on?->format('d/m/Y') }}</dd></div>
                    <div><dt class="font-medium">Fine</dt><dd>{{ $therapyPlan->ends_on?->format('d/m/Y') ?? 'Non definita' }}</dd></div>
                    <div><dt class="font-medium">Stato</dt><dd>{{ $therapyPlan->is_active ? 'Attivo' : 'Non attivo' }}</dd></div>
                </dl>
            </article>

            <article class="rounded-xl border border-slate-200 p-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Orari</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($therapyPlan->schedules as $schedule)
                        <li class="rounded-lg bg-slate-100 px-3 py-2">{{ substr((string) $schedule->scheduled_time, 0, 5) }}</li>
                    @endforeach
                </ul>
            </article>
        </div>

        <div class="panel-body pt-0">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Log Somministrazioni</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="pb-2">Data Evento</th>
                            <th class="pb-2">Stato</th>
                            <th class="pb-2">Origine</th>
                            <th class="pb-2">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($therapyPlan->doseLogs as $log)
                            <tr>
                                <td class="py-2">{{ $log->event_at?->format('d/m/Y H:i') }}</td>
                                <td class="py-2">{{ $log->status }}</td>
                                <td class="py-2">{{ $log->source }}</td>
                                <td class="py-2">{{ $log->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-500">Nessun evento dose.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
