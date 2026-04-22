# Prova Credenziali Smart Dispenser 4.0

Password per tutti gli account: `password`

## Admin (1)
- `admin@smartdispenser.local`

## Dottori
- `doctor.marco@smartdispenser.local`
- `doctor.giulia@smartdispenser.local`
- `doctor.luca@smartdispenser.local`

## Pazienti
- `patient.mario@smartdispenser.local`
- `patient.lucia@smartdispenser.local`
- `patient.giovanni@smartdispenser.local`

## Familiari
- `caregiver.anna@smartdispenser.local`
- `caregiver.paolo@smartdispenser.local`
- `caregiver.sara@smartdispenser.local`
- `caregiver.marco@smartdispenser.local`
- `caregiver.elisa@smartdispenser.local`

## Flussi da provare
1. Login admin: apri `Gestione Utenti` e registra un nuovo dottore o familiare.
2. Login dottore: apri `Gestione Utenti` e verifica che puoi registrare solo familiari (non dottori).
3. Login paziente: apri `Collegamenti` e collega un dottore registrato e un familiare registrato.
4. Login familiare: apri `Collegamenti` e collegati a un paziente registrato.
5. Login admin/dottore: crea o modifica un paziente; solo admin puo selezionare manualmente il dottore nel form paziente.
