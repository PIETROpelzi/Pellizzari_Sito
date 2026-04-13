@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header flex items-center justify-between">
            <span>{{ $patient->name }}</span>
            <a href="{{ route('patients.edit', $patient) }}" class="btn-secondary">Modifica</a>
        </div>
        <div class="panel-body grid grid-cols-1 gap-6 lg:grid-cols-3">
            <article class="rounded-xl border border-slate-200 p-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Anagrafica</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div><dt class="font-medium text-slate-700">Email</dt><dd>{{ $patient->email }}</dd></div>
                    <div><dt class="font-medium text-slate-700">Telefono</dt><dd>{{ $patient->phone ?: '-' }}</dd></div>
                    <div><dt class="font-medium text-slate-700">Nascita</dt><dd>{{ $patient->date_of_birth?->format('d/m/Y') ?: '-' }}</dd></div>
                    <div><dt class="font-medium text-slate-700">Stato</dt><dd>{{ $patient->is_active ? 'Attivo' : 'Disattivo' }}</dd></div>
                </dl>
            </article>

            <article class="rounded-xl border border-slate-200 p-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Care Team</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($patient->careTeamMembers as $member)
                        <li class="rounded-lg bg-slate-100 px-3 py-2">{{ $member->name }} ({{ $member->pivot->role }})</li>
                    @empty
                        <li class="text-slate-500">Nessuna assegnazione.</li>
                    @endforelse
                </ul>
            </article>

            <article class="rounded-xl border border-slate-200 p-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Ultimi Alert</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($patient->alerts as $alert)
                        <li class="rounded-lg bg-rose-50 px-3 py-2">
                            <p class="font-medium text-rose-700">{{ $alert->type }}</p>
                            <p class="text-xs text-rose-600">{{ $alert->message }}</p>
                        </li>
                    @empty
                        <li class="text-slate-500">Nessun alert.</li>
                    @endforelse
                </ul>
            </article>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <article class="panel">
            <div class="panel-header">Farmaci</div>
            <div class="panel-body">
                <ul class="space-y-2 text-sm">
                    @forelse ($patient->medicines as $medicine)
                        <li class="rounded-lg border border-slate-200 px-3 py-2">
                            {{ $medicine->name }} - Qta: {{ $medicine->remaining_quantity }}
                        </li>
                    @empty
                        <li class="text-slate-500">Nessun farmaco associato.</li>
                    @endforelse
                </ul>
            </div>
        </article>

        <article class="panel">
            <div class="panel-header">Piani Terapeutici</div>
            <div class="panel-body">
                <ul class="space-y-2 text-sm">
                    @forelse ($patient->therapyPlans as $plan)
                        <li class="rounded-lg border border-slate-200 px-3 py-2">
                            <p class="font-medium">{{ $plan->medicine->name }}</p>
                            <p>{{ $plan->dose_amount }} {{ $plan->dose_unit }} - Orari:
                                {{ $plan->schedules->pluck('scheduled_time')->map(fn ($time) => substr((string) $time, 0, 5))->join(', ') }}
                            </p>
                        </li>
                    @empty
                        <li class="text-slate-500">Nessun piano terapeutico.</li>
                    @endforelse
                </ul>
            </div>
        </article>
    </section>
@endsection
