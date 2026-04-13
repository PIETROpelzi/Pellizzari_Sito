@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header">Modifica Piano Terapeutico</div>
        <form method="POST" action="{{ route('therapy-plans.update', $therapyPlan) }}" class="panel-body space-y-4">
            @csrf
            @method('PUT')
            @include('therapy-plans._form')
            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('therapy-plans.show', $therapyPlan) }}" class="btn-secondary">Annulla</a>
                <button type="submit" class="btn-primary">Aggiorna Piano</button>
            </div>
        </form>
    </section>
@endsection
