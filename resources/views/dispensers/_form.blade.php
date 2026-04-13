<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700" for="patient_id">Paziente</label>
        <select class="form-input" name="patient_id" id="patient_id" required>
            <option value="">Seleziona paziente</option>
            @foreach ($patients as $patient)
                <option value="{{ $patient->id }}" @selected(old('patient_id', $dispenser->patient_id ?? '') == $patient->id)>{{ $patient->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="name">Nome dispenser</label>
        <input class="form-input" type="text" name="name" id="name" value="{{ old('name', $dispenser->name ?? '') }}" required>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="device_uid">Device UID ESP32</label>
        <input class="form-input" type="text" name="device_uid" id="device_uid" value="{{ old('device_uid', $dispenser->device_uid ?? '') }}" required>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="api_token">Token API dispositivo</label>
        <input class="form-input" type="text" name="api_token" id="api_token" value="{{ old('api_token', $dispenser->api_token ?? '') }}">
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700" for="mqtt_base_topic">MQTT Base Topic</label>
        <input class="form-input" type="text" name="mqtt_base_topic" id="mqtt_base_topic" value="{{ old('mqtt_base_topic', $dispenser->mqtt_base_topic ?? '') }}" placeholder="smart-dispenser/device-xyz">
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="is_active">Stato</label>
        <select class="form-input" name="is_active" id="is_active">
            <option value="1" @selected(old('is_active', $dispenser->is_active ?? 1) == 1)>Attivo</option>
            <option value="0" @selected(old('is_active', $dispenser->is_active ?? 1) == 0)>Disattivo</option>
        </select>
    </div>
</div>
