@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-header">Nuovo Farmaco</div>
        <form method="POST" action="{{ route('medicines.store') }}" class="panel-body space-y-4">
            @csrf
            @include('medicines._form')
            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('medicines.index') }}" class="btn-secondary">Annulla</a>
                <button type="submit" class="btn-primary">Salva Farmaco</button>
            </div>
        </form>
    </section>
@endsection
