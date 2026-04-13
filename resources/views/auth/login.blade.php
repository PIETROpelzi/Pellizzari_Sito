<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Smart Dispenser 4.0</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50">
    <div class="mx-auto grid min-h-screen max-w-5xl grid-cols-1 items-center gap-6 px-4 py-10 lg:grid-cols-2">
        <section class="space-y-4">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Smart Dispenser 4.0</p>
            <h1 class="text-4xl font-bold text-slate-900">Controllo terapeutico centralizzato</h1>
            <p class="text-sm leading-6 text-slate-600">
                Accedi al portale per gestire pazienti, prescrizioni digitali, telemetria ambientale e notifiche di sicurezza.
            </p>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                <p class="font-semibold">Credenziali demo (seed):</p>
                <p>admin@smartdispenser.local / password</p>
                <p>doctor@smartdispenser.local / password</p>
                <p>caregiver@smartdispenser.local / password</p>
                <p>patient@smartdispenser.local / password</p>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">Accesso</div>
            <form method="POST" action="{{ route('login.store') }}" class="panel-body space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-medium text-slate-700" for="email">Email</label>
                    <input class="form-input" type="email" name="email" id="email" value="{{ old('email') }}" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700" for="password">Password</label>
                    <input class="form-input" type="password" name="password" id="password" required>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300">
                    Ricordami
                </label>
                <button class="btn-primary w-full" type="submit">Entra nel portale</button>
            </form>
        </section>
    </div>
</body>
</html>
