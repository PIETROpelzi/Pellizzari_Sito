<?php

namespace App\Http\Controllers;

use App\Models\Dispenser;
use App\Services\MqttPublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MqttCommandController extends Controller
{
    /**
     * Pubblica un comando strutturato sul topic commands/<comando> del dispenser.
     */
    public function __invoke(Request $request, Dispenser $dispenser, MqttPublisher $mqttPublisher): RedirectResponse
    {
        $validated = $request->validate([
            'command' => ['required', 'string', 'max:80'],
            'payload' => ['nullable', 'string', 'max:4000'],
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
                ? 'Comando "' . $validated['command'] . '" inviato al broker MQTT.'
                : 'Broker MQTT non configurato o comando non inviato.',
        );
    }

    /**
     * Pubblica un payload JSON grezzo su un topic completamente arbitrario.
     * Usato per schedule_response e altri topic non-standard.
     */
    public function raw(Request $request, Dispenser $dispenser, MqttPublisher $mqttPublisher): RedirectResponse
    {
        $validated = $request->validate([
            'topic'   => ['required', 'string', 'max:255'],
            'payload' => ['required', 'string', 'max:32000'],
        ]);

        $decoded = json_decode((string) $validated['payload'], true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return back()
                ->withInput()
                ->withErrors(['payload' => 'Il payload deve essere un JSON valido.']);
        }

        $published = $mqttPublisher->publishTo(
            topic: $validated['topic'],
            payload: $decoded,
        );

        return back()->with(
            'status',
            $published
                ? 'Payload pubblicato sul topic "' . $validated['topic'] . '".'
                : 'Broker MQTT non configurato o invio non riuscito.',
        );
    }
}
