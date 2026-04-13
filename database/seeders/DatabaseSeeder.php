<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Dispenser;
use App\Models\DoseLog;
use App\Models\Medicine;
use App\Models\PatientAssignment;
use App\Models\SensorLog;
use App\Models\TherapyPlan;
use App\Models\TherapyPlanSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Smart Dispenser',
            'email' => 'admin@smartdispenser.local',
            'password' => 'password',
        ]);

        $doctor = User::factory()->doctor()->create([
            'name' => 'Dr. Marco Rossi',
            'email' => 'doctor@smartdispenser.local',
            'password' => 'password',
        ]);

        $caregiver = User::factory()->caregiver()->create([
            'name' => 'Anna Bianchi',
            'email' => 'caregiver@smartdispenser.local',
            'password' => 'password',
        ]);

        $patients = User::factory()->patient()->count(3)->create();

        foreach ($patients as $patient) {
            PatientAssignment::query()->create([
                'patient_id' => $patient->id,
                'member_id' => $doctor->id,
                'assigned_by_id' => $admin->id,
                'role' => 'Doctor',
            ]);

            PatientAssignment::query()->create([
                'patient_id' => $patient->id,
                'member_id' => $caregiver->id,
                'assigned_by_id' => $admin->id,
                'role' => 'Caregiver',
            ]);

            $dispenser = Dispenser::factory()->create([
                'patient_id' => $patient->id,
                'name' => 'Dispenser '.$patient->name,
            ]);

            $medicines = Medicine::factory()->count(2)->create([
                'patient_id' => $patient->id,
                'created_by_id' => $doctor->id,
            ]);

            foreach ($medicines as $medicine) {
                $therapyPlan = TherapyPlan::factory()->create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'medicine_id' => $medicine->id,
                ]);

                TherapyPlanSchedule::query()->create([
                    'therapy_plan_id' => $therapyPlan->id,
                    'scheduled_time' => '08:00',
                    'week_days' => [1, 2, 3, 4, 5, 6, 7],
                    'timezone' => 'Europe/Rome',
                ]);

                TherapyPlanSchedule::query()->create([
                    'therapy_plan_id' => $therapyPlan->id,
                    'scheduled_time' => '20:00',
                    'week_days' => [1, 2, 3, 4, 5, 6, 7],
                    'timezone' => 'Europe/Rome',
                ]);

                DoseLog::factory()->count(10)->create([
                    'patient_id' => $patient->id,
                    'dispenser_id' => $dispenser->id,
                    'therapy_plan_id' => $therapyPlan->id,
                    'medicine_id' => $medicine->id,
                ]);
            }

            SensorLog::factory()->count(30)->create([
                'patient_id' => $patient->id,
                'dispenser_id' => $dispenser->id,
            ]);

            Alert::factory()->count(4)->create([
                'patient_id' => $patient->id,
                'dispenser_id' => $dispenser->id,
            ]);
        }

        User::factory()->patient()->create([
            'name' => 'Paziente Demo',
            'email' => 'patient@smartdispenser.local',
            'password' => 'password',
        ]);
    }
}
