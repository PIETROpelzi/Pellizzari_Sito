@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header">Nuovo Piano Terapeutico</div>
        <form method="POST" action="{{ route('therapy-plans.store') }}" class="panel-body space-y-4">
            @csrf
            @include('therapy-plans._form')
            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('therapy-plans.index') }}" class="btn-secondary">Annulla</a>
                <button type="submit" class="btn-primary">Salva Piano</button>
            </div>
        </form>
    </section>
@endsection
