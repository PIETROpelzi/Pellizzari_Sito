# Setup Smart Dispenser 4.0 (Laravel + XAMPP)

## 1) Posso clonarlo da GitHub su un altro PC e farlo partire?
Si, assolutamente si.

### Prerequisiti sul nuovo PC
- XAMPP (Apache + MySQL)
- PHP 8.2 (da XAMPP)
- Composer
- Node.js + npm
- Git

### Procedura completa
Apri PowerShell e vai in `C:\xampp\htdocs`:

```powershell
cd C:\xampp\htdocs
git clone <URL_REPO_GITHUB> Pellizzari_Sito
cd .\Pellizzari_Sito
```

Installa dipendenze:

```powershell
composer install
npm.cmd install
```

Crea `.env` e chiave app:

```powershell
copy .env.example .env
php artisan key:generate
```

Configura database in `.env` (valori classici XAMPP):

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pellizzari_sito
DB_USERNAME=root
DB_PASSWORD=
```

Crea il DB da phpMyAdmin o MySQL:

```sql
CREATE DATABASE pellizzari_sito CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Esegui migrazioni + seed:

```powershell
php artisan migrate:fresh --seed
```

Compila assets:

```powershell
npm.cmd run build
```

Avvio locale:

```powershell
php artisan serve
```

Apri:

```text
http://127.0.0.1:8000
```

Credenziali di test: vedi il file `prova credenziali.md`.
Configurazione HiveMQ: vedi il file `hivemq-pronto.md`.

## 2) Come raggiungerlo da web / da altri dispositivi
Dipende da dove vuoi raggiungerlo.

### A) Da altri PC nella stessa rete LAN
Avvia Laravel in bind su tutte le interfacce:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

Poi apri da altro dispositivo:

```text
http://IP_DEL_PC_HOST:8000
```

Esempio: `http://192.168.1.50:8000`

Verifica firewall Windows (porta 8000 aperta).

### B) Da internet (pubblico)
Per sviluppo/test, il modo piu veloce e usare un tunnel:
- Cloudflare Tunnel (consigliato)
- ngrok

Alternativa: port forwarding sul router, ma e meno sicura se non configuri HTTPS, autenticazione forte e hardening.

### C) In produzione reale
Meglio deploy su server/VPS (o Laravel Cloud) con:
- dominio
- HTTPS
- DB sicuro
- queue/scheduler attivi

## 3) Breve spiegazione del progetto
Smart Dispenser 4.0 e una piattaforma IoT clinica per gestione terapia farmacologica.

Componenti principali:
- Laravel 12: pannello web admin/dottore e API
- MySQL: utenti, piani terapia, eventi dose, telemetria, alert
- ESP32 Dispenser: invia telemetria/eventi dose e riceve comandi
- MQTT (es. HiveMQ): canale real-time tra dispositivi, backend e app

Ruoli applicativi:
- Admin
- Doctor
- Patient
- Caregiver (familiari/controllori)

Flussi base:
- Admin registra dottori, familiari e pazienti.
- Doctor registra familiari e pazienti, ma non puo registrare altri dottori.
- Solo Admin puo selezionare il dottore dalla scheda paziente.
- Paziente puo collegare al proprio profilo un dottore registrato e un familiare registrato.
- Familiare puo collegarsi a un paziente registrato.
- Dispenser invia dati (`telemetry`, `dose-logs`) via API/MQTT.
- Laravel salva su DB e genera alert.
- Laravel puo inviare comandi MQTT al dispenser.

Comandi utili:

```powershell
php artisan test --compact
php artisan schedule:work
php artisan device:mqtt-listen
```
