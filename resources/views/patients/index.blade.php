@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header flex items-center justify-between">
            <span>Pazienti</span>
            <a href="{{ route('patients.create') }}" class="btn-primary">Nuovo Paziente</a>
        </div>
        <div class="panel-body">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="pb-2">Nome</th>
                            <th class="pb-2">Email</th>
                            <th class="pb-2">Medici/Caregiver</th>
                            <th class="pb-2">Farmaci</th>
                            <th class="pb-2">Piani</th>
                            <th class="pb-2">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($patients as $patient)
                            <tr>
                                <td class="py-2 font-medium text-slate-900">{{ $patient->name }}</td>
                                <td class="py-2">{{ $patient->email }}</td>
                                <td class="py-2">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($patient->careTeamMembers as $member)
                                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs">{{ $member->name }} ({{ $member->pivot->role }})</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-2">{{ $patient->medicines_count }}</td>
                                <td class="py-2">{{ $patient->therapy_plans_count }}</td>
                                <td class="py-2">
                                    <div class="flex gap-2">
                                        <a class="btn-secondary" href="{{ route('patients.show', $patient) }}">Apri</a>
                                        <a class="btn-secondary" href="{{ route('patients.edit', $patient) }}">Modifica</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500">Nessun paziente registrato.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $patients->links() }}</div>
        </div>
    </section>
@endsection
