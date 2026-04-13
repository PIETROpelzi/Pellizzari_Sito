@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header">Nuovo Dispenser</div>
        <form method="POST" action="{{ route('dispensers.store') }}" class="panel-body space-y-4">
            @csrf
            @include('dispensers._form')
            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('dispensers.index') }}" class="btn-secondary">Annulla</a>
                <button type="submit" class="btn-primary">Salva Dispenser</button>
            </div>
        </form>
    </section>
@endsection
