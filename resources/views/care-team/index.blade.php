@extends('layouts.app')

@section('content')
    @if ($mode === 'patient')
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <article class="panel">
                <div class="panel-header">Collega Dottore al Mio Profilo Paziente</div>
                <div class="panel-body space-y-4">
                    <form method="POST" action="{{ route('care-team.attach-doctor') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-sm font-medium text-slate-700" for="doctor_id">Dottore registrato</label>
                            <select class="form-input" name="doctor_id" id="doctor_id" required>
                                <option value="">Seleziona...</option>
                                @foreach ($availableDoctors as $doctor)
                                    <option value="{{ $doctor->id }}">{{ $doctor->name }} ({{ $doctor->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-primary">Collega Dottore</button>
                    </form>

                    <div>
                        <p class="text-sm font-semibold text-slate-700">Dottori gia collegati</p>
                        <ul class="mt-2 space-y-2 text-sm">
                            @forelse ($linkedDoctors as $doctor)
                                <li class="rounded-lg border border-slate-200 px-3 py-2">{{ $doctor->name }} ({{ $doctor->email }})</li>
                            @empty
                                <li class="text-slate-500">Nessun dottore collegato.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </article>

            <article class="panel">
                <div class="panel-header">Collega Familiare al Mio Profilo Paziente</div>
                <div class="panel-body space-y-4">
                    <form method="POST" action="{{ route('care-team.attach-caregiver') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-sm font-medium text-slate-700" for="caregiver_id">Familiare registrato</label>
                            <select class="form-input" name="caregiver_id" id="caregiver_id" required>
                                <option value="">Seleziona...</option>
                                @foreach ($availableCaregivers as $caregiver)
                                    <option value="{{ $caregiver->id }}">{{ $caregiver->name }} ({{ $caregiver->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-primary">Collega Familiare</button>
                    </form>

                    <div>
                        <p class="text-sm font-semibold text-slate-700">Familiari gia collegati</p>
                        <ul class="mt-2 space-y-2 text-sm">
                            @forelse ($linkedCaregivers as $caregiver)
                                <li class="rounded-lg border border-slate-200 px-3 py-2">{{ $caregiver->name }} ({{ $caregiver->email }})</li>
                            @empty
                                <li class="text-slate-500">Nessun familiare collegato.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </article>
        </section>
    @endif

    @if ($mode === 'caregiver')
        <section class="panel">
            <div class="panel-header">Collega il Mio Profilo Familiare a un Paziente</div>
            <div class="panel-body grid grid-cols-1 gap-6 lg:grid-cols-2">
                <form method="POST" action="{{ route('care-team.attach-patient') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-sm font-medium text-slate-700" for="patient_id">Paziente registrato</label>
                        <select class="form-input" name="patient_id" id="patient_id" required>
                            <option value="">Seleziona...</option>
                            @foreach ($availablePatients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->name }} ({{ $patient->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Collega Paziente</button>
                </form>

                <div>
                    <p class="text-sm font-semibold text-slate-700">Pazienti gia collegati</p>
                    <ul class="mt-2 space-y-2 text-sm">
                        @forelse ($linkedPatients as $patient)
                            <li class="rounded-lg border border-slate-200 px-3 py-2">{{ $patient->name }} ({{ $patient->email }})</li>
                        @empty
                            <li class="text-slate-500">Nessun paziente collegato.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </section>
    @endif
@endsection
