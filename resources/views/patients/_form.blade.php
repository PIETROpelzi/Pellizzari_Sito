@php
    $isEdit = isset($patient);
    $selectedDoctorIds = collect(old('doctor_ids', $doctorIds ?? []))->map(static fn ($id): int => (int) $id)->all();
    $selectedCaregiverIds = collect(old('caregiver_ids', $caregiverIds ?? []))->map(static fn ($id): int => (int) $id)->all();
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700" for="name">Nome completo</label>
        <input class="form-input" type="text" name="name" id="name" value="{{ old('name', $patient->name ?? '') }}" required>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="email">Email</label>
        <input class="form-input" type="email" name="email" id="email" value="{{ old('email', $patient->email ?? '') }}" required>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="phone">Telefono</label>
        <input class="form-input" type="text" name="phone" id="phone" value="{{ old('phone', $patient->phone ?? '') }}">
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="date_of_birth">Data di nascita</label>
        <input class="form-input" type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', isset($patient) && $patient->date_of_birth ? $patient->date_of_birth->toDateString() : '') }}">
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700" for="address">Indirizzo</label>
        <input class="form-input" type="text" name="address" id="address" value="{{ old('address', $patient->address ?? '') }}">
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="password">Password {{ $isEdit ? '(lascia vuoto per non cambiarla)' : '' }}</label>
        <input class="form-input" type="password" name="password" id="password" {{ $isEdit ? '' : 'required' }}>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="password_confirmation">Conferma Password</label>
        <input class="form-input" type="password" name="password_confirmation" id="password_confirmation" {{ $isEdit ? '' : 'required' }}>
    </div>

    @if ($isEdit)
        <div>
            <label class="text-sm font-medium text-slate-700" for="is_active">Stato account</label>
            <select class="form-input" name="is_active" id="is_active">
                <option value="1" @selected(old('is_active', $patient->is_active) == 1)>Attivo</option>
                <option value="0" @selected(old('is_active', $patient->is_active) == 0)>Disattivo</option>
            </select>
        </div>
    @endif
</div>

<div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="rounded-xl border border-slate-200 p-4">
        <p class="text-sm font-semibold text-slate-700">Dottore assegnato</p>
        <div class="mt-3 space-y-2">
            @if ($canSelectDoctors)
                @forelse ($doctors as $doctor)
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="doctor_ids[]" value="{{ $doctor->id }}"
                            @checked(in_array($doctor->id, $selectedDoctorIds, true))>
                        {{ $doctor->name }}
                    </label>
                @empty
                    <p class="text-sm text-slate-500">Nessun dottore registrato.</p>
                @endforelse
            @else
                <p class="text-sm text-slate-600">
                    Il paziente verra assegnato automaticamente al medico che sta creando/modificando la scheda.
                </p>
            @endif
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 p-4">
        <p class="text-sm font-semibold text-slate-700">Familiari assegnati</p>
        <div class="mt-3 space-y-2">
            @forelse ($caregivers as $caregiver)
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="caregiver_ids[]" value="{{ $caregiver->id }}"
                        @checked(in_array($caregiver->id, $selectedCaregiverIds, true))>
                    <span>{{ $caregiver->name }} <span class="text-xs text-slate-500">({{ $caregiver->email }})</span></span>
                </label>
            @empty
                <p class="text-sm text-slate-500">Nessun familiare registrato. Creane uno dalla sezione Gestione Utenti.</p>
            @endforelse
        </div>
    </div>
</div>
