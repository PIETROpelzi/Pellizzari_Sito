@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header">Nuovo Paziente</div>
        <form method="POST" action="{{ route('patients.store') }}" class="panel-body">
            @csrf
            @include('patients._form')
            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('patients.index') }}" class="btn-secondary">Annulla</a>
                <button type="submit" class="btn-primary">Salva Paziente</button>
            </div>
        </form>
    </section>
@endsection
