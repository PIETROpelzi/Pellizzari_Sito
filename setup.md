# Setup del progetto Laravel

## 1. Installa le dipendenze PHP

Apri un terminale in `c:\xampp\htdocs\Pellizzari_Sito` e lancia:

```powershell
composer install
```

## 2. Crea il file .env

Se non esiste ancora, copia il file di esempio:

```powershell
copy .env.example .env
```

Poi genera la chiave applicazione:

```powershell
php artisan key:generate
```

## 3. Configura il database

Nel file `.env`, imposta i valori di connessione MySQL:

- `DB_DATABASE=pellizzari_sito`
- `DB_USERNAME=root`
- `DB_PASSWORD=`

Se usi altri valori, aggiorna la configurazione di conseguenza.

## 4. Esegui le migrazioni e il seeding

Per creare le tabelle e popolare i dati demo:

```powershell
php artisan migrate:fresh --seed
```

Se il database esiste già e vuoi solo aggiornare le tabelle:

```powershell
php artisan migrate
php artisan db:seed
```

## 5. Installa le dipendenze JavaScript

In PowerShell usa:

```powershell
npm.cmd install
```

## 6. Compila gli asset

Per build di produzione:

```powershell
npm.cmd run build
```

Per sviluppo con aggiornamenti live:

```powershell
npm.cmd run dev
```

## 7. Avvia il sito

Avvia il server Laravel:

```powershell
php artisan serve
```

Apri il browser su:

```text
http://127.0.0.1:8000
```

## 8. Credenziali demo

Usa queste email e password:

- `admin@smartdispenser.local` / `password`
- `doctor@smartdispenser.local` / `password`
- `caregiver@smartdispenser.local` / `password`
- `patient@smartdispenser.local` / `password`
