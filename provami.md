Ho implementato la parte web Laravel completa del progetto Smart Dispenser 4.0, con DB, dashboard, ruoli, CRUD clinico e API device.

Cosa c’è adesso

Autenticazione web + ruoli (Admin, Doctor, Patient, Caregiver)
Dashboard con KPI (aderenza, alert, scorte, telemetria)
Gestione pazienti, farmaci, piani terapeutici, dispenser
Log sensori e gestione alert
Endpoint API per ESP32 (plans, telemetry, dose-logs) con token dispositivo
Integrazione MQTT lato web per invio comandi al dispenser
Seeder demo con utenti/test data
Test Pest principali: 7 passed
File principali:

routes/web.php
routes/api.php
app/Http/Controllers/DashboardController.php
app/Http/Controllers/Api/DeviceTelemetryController.php
app/Http/Controllers/Api/DeviceDoseLogController.php
database/seeders/DatabaseSeeder.php
resources/views/layouts/app.blade.php
config/services.php
Come controllare con XAMPP

Avvia Apache e MySQL da XAMPP.
Crea il DB:
CREATE DATABASE pellizzari_sito CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
Verifica .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pellizzari_sito
DB_USERNAME=root
DB_PASSWORD=
Da terminale:
cd C:\xampp\htdocs\Pellizzari_Sito
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
Apri http://127.0.0.1:8000/login.
Credenziali demo

admin@smartdispenser.local / password
doctor@smartdispenser.local / password
caregiver@smartdispenser.local / password
patient@smartdispenser.local / password
Test rapidi API ESP32

Recupera il token da una scheda dispenser nel portale.
Telemetria:
curl -X POST http://127.0.0.1:8000/api/v1/device/telemetry ^
  -H "X-Device-Token: TOKEN_DISPOSITIVO" ^
  -H "Content-Type: application/json" ^
  -d "{\"temperature\":29.4,\"humidity\":72.1,\"battery_level\":80}"
Evento dose:
curl -X POST http://127.0.0.1:8000/api/v1/device/dose-logs ^
  -H "X-Device-Token: TOKEN_DISPOSITIVO" ^
  -H "Content-Type: application/json" ^
  -d "{\"status\":\"Missed\",\"notes\":\"Dose non assunta\"}"
Lettura piano terapeutico:
curl -X GET http://127.0.0.1:8000/api/v1/device/plans ^
  -H "X-Device-Token: TOKEN_DISPOSITIVO"
Nota: qui non ho potuto eseguire migrate --seed su MySQL perché il DB locale non era raggiungibile dall’ambiente, ma i test Laravel/Pest sono passati.