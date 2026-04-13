@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header">Modifica Paziente</div>
        <form method="POST" action="{{ route('patients.update', $patient) }}" class="panel-body">
            @csrf
            @method('PUT')
            @include('patients._form')
            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('patients.show', $patient) }}" class="btn-secondary">Annulla</a>
                <button type="submit" class="btn-primary">Aggiorna Paziente</button>
            </div>
        </form>
    </section>
@endsection
