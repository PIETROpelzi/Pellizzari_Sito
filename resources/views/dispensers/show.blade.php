@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header flex items-center justify-between">
            <span>{{ $dispenser->name }}</span>
            <a href="{{ route('dispensers.edit', $dispenser) }}" class="btn-secondary">Modifica</a>
        </div>
        <div class="panel-body grid grid-cols-1 gap-6 lg:grid-cols-3">
            <article class="rounded-xl border border-slate-200 p-4 text-sm">
                <h3 class="font-semibold text-slate-700">Identita dispositivo</h3>
                <p class="mt-2"><strong>UID:</strong> {{ $dispenser->device_uid }}</p>
                <p><strong>Paziente:</strong> {{ $dispenser->patient->name ?? '-' }}</p>
                <p><strong>Token:</strong> <span class="font-mono text-xs">{{ $dispenser->api_token }}</span></p>
                <p><strong>Topic base:</strong> {{ $dispenser->mqtt_base_topic ?: '-' }}</p>
                <p><strong>Ultimo segnale:</strong> {{ $dispenser->last_seen_at?->format('d/m/Y H:i') ?: '-' }}</p>
            </article>

            <article class="rounded-xl border border-slate-200 p-4 text-sm lg:col-span-2">
                <h3 class="font-semibold text-slate-700">Invio Comando MQTT</h3>
                <form action="{{ route('dispensers.mqtt-command', $dispenser) }}" method="POST" class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
                    @csrf
                    <div>
                        <label class="text-xs uppercase tracking-wider text-slate-500">Comando</label>
                        <input class="form-input" name="command" placeholder="dispense_now" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs uppercase tracking-wider text-slate-500">Payload JSON (opzionale)</label>
                        <input class="form-input" name="payload" placeholder='{"slot":1}'>
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="btn-primary">Invia al Broker</button>
                    </div>
                </form>
            </article>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <article class="panel">
            <div class="panel-header">Ultimi Log Sensori</div>
            <div class="panel-body">
                <ul class="space-y-2 text-sm">
                    @forelse ($dispenser->sensorLogs as $log)
                        <li class="rounded-lg border border-slate-200 px-3 py-2">
                            {{ $log->recorded_at?->format('d/m H:i') }} - {{ $log->temperature }}°C / {{ $log->humidity }}%
                        </li>
                    @empty
                        <li class="text-slate-500">Nessun log.</li>
                    @endforelse
                </ul>
            </div>
        </article>

        <article class="panel">
            <div class="panel-header">Alert Collegati</div>
            <div class="panel-body">
                <ul class="space-y-2 text-sm">
                    @forelse ($dispenser->alerts as $alert)
                        <li class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-rose-700">
                            {{ $alert->triggered_at?->format('d/m H:i') }} - {{ $alert->type }}: {{ $alert->message }}
                        </li>
                    @empty
                        <li class="text-slate-500">Nessun alert.</li>
                    @endforelse
                </ul>
            </div>
        </article>
    </section>
@endsection
