@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header flex items-center justify-between">
            <span>Piano #{{ $therapyPlan->id }}</span>
            <div class="flex items-center gap-2">
                {{-- Pulsante re-invio manuale via MQTT --}}
                <form action="{{ route('therapy-plans.send-mqtt', $therapyPlan) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-secondary text-xs">
                        📡 Invia al Dispenser (MQTT)
                    </button>
                </form>
                <a href="{{ route('therapy-plans.edit', $therapyPlan) }}" class="btn-secondary">Modifica</a>
            </div>
        </div>

        @if (session('status'))
            <div class="mx-6 mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

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

        {{-- Anteprima payload MQTT --}}
        <div class="panel-body pt-0">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Payload MQTT (set_therapy)</h3>
            <pre class="overflow-x-auto rounded-xl border border-slate-200 bg-slate-50 p-4 font-mono text-xs text-slate-700">{{
                json_encode([
                    'therapy_plan_id' => $therapyPlan->id,
                    'medicine'        => $therapyPlan->medicine?->name,
                    'dose_amount'     => (float) $therapyPlan->dose_amount,
                    'dose_unit'       => $therapyPlan->dose_unit,
                    'schedules'       => $therapyPlan->schedules->pluck('scheduled_time')->map(fn($t) => substr((string)$t, 0, 5))->values()->all(),
                    'starts_on'       => $therapyPlan->starts_on?->toDateString(),
                    'ends_on'         => $therapyPlan->ends_on?->toDateString(),
                    'is_active'       => $therapyPlan->is_active,
                    'instructions'    => $therapyPlan->instructions,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            }}</pre>
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
