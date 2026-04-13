<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Smart Dispenser 4.0') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <header class="panel mb-6">
            <div class="panel-body flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Smart Dispenser 4.0</p>
                    <h1 class="text-2xl font-bold text-slate-900">Portale Clinico e IoT</h1>
                    <p class="text-sm text-slate-600">Monitoraggio terapia, scorte farmaci e telemetria ambientale.</p>
                </div>
                @auth
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl bg-slate-900 px-3 py-2 text-sm text-white">
                            <p class="font-semibold">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-300">{{ auth()->user()->role?->value }}</p>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-secondary">Esci</button>
                        </form>
                    </div>
                @endauth
            </div>
        </header>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[250px_1fr]">
            @auth
                <aside class="panel h-fit">
                    <div class="panel-header">Navigazione</div>
                    <nav class="panel-body space-y-2 text-sm">
                        <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('dashboard') ? 'bg-sky-100 text-sky-800 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">Dashboard</a>
                        <a href="{{ route('sensor-logs.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('sensor-logs.*') ? 'bg-sky-100 text-sky-800 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">Log Sensori</a>
                        <a href="{{ route('alerts.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('alerts.*') ? 'bg-sky-100 text-sky-800 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">Alert</a>

                        @if (auth()->user()->canManageClinicalData())
                            <hr class="my-3 border-slate-200">
                            <a href="{{ route('patients.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('patients.*') ? 'bg-sky-100 text-sky-800 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">Pazienti</a>
                            <a href="{{ route('medicines.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('medicines.*') ? 'bg-sky-100 text-sky-800 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">Farmaci</a>
                            <a href="{{ route('therapy-plans.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('therapy-plans.*') ? 'bg-sky-100 text-sky-800 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">Piani Terapia</a>
                            <a href="{{ route('dispensers.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('dispensers.*') ? 'bg-sky-100 text-sky-800 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">Dispenser</a>
                        @endif
                    </nav>
                </aside>
            @endauth

            <main class="space-y-6">
                @if (session('status'))
                    <div class="panel border-emerald-200 bg-emerald-50 text-emerald-900">
                        <div class="panel-body py-3 text-sm font-medium">{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="panel border-rose-200 bg-rose-50 text-rose-900">
                        <div class="panel-body py-3 text-sm">
                            <p class="font-semibold">Controlla i dati inseriti:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
