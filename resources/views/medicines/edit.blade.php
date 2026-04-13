@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header">Modifica Farmaco</div>
        <form method="POST" action="{{ route('medicines.update', $medicine) }}" class="panel-body space-y-4">
            @csrf
            @method('PUT')
            @include('medicines._form')
            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('medicines.show', $medicine) }}" class="btn-secondary">Annulla</a>
                <button type="submit" class="btn-primary">Aggiorna Farmaco</button>
            </div>
        </form>
    </section>
@endsection
