<?php

namespace App\Http\Controllers;

use App\Models\Dispenser;
use App\Services\MqttPublisher;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class MqttCommandController extends Controller
{
    public function __invoke(Request $request, Dispenser $dispenser, MqttPublisher $mqttPublisher): RedirectResponse
    {
        $validated = $request->validate([
            'command' => ['required', 'string', 'max:80'],
            'payload' => ['nullable', 'string', 'max:1000'],
        ]);

        $payload = [];
        if (! empty($validated['payload'])) {
            $decoded = json_decode((string) $validated['payload'], true);
            $payload = is_array($decoded) ? $decoded : ['raw' => $validated['payload']];
        }

        $published = $mqttPublisher->publishCommand(
            dispenser: $dispenser,
            command: $validated['command'],
            payload: $payload,
        );

        return back()->with(
            'status',
            $published
                ? 'Comando MQTT inviato al dispenser.'
                : 'Broker MQTT non configurato o comando non inviato.',
        );
    }
}
