<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Space;
use App\Models\SpaceType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Crear Permisos (nombres en inglés)
        $permissions = [
            'spaces.view',
            'spaces.create',
            'spaces.edit',
            'spaces.delete',
            'reservations.create',
            'reservations.approve',
            'reservations.complete',
            'users.manage',
            'roles.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // 2. Crear Roles y asignar permisos
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $staffRole = Role::create(['name' => 'staff']);
        $staffRole->givePermissionTo([
            'spaces.view',
            'reservations.approve',
            'reservations.complete',
        ]);

        $clientRole = Role::create(['name' => 'client']);
        $clientRole->givePermissionTo([
            'spaces.view',
            'reservations.create',
        ]);

        // 3. Crear Usuarios de Prueba por Rol
        $admin = User::create([
            'name' => 'Administrador Principal',
            'email' => 'admin@coworking.com',
            'password' => 'password123',
        ]);
        $admin->assignRole($adminRole);

        $staff = User::create([
            'name' => 'Recepcionista Staff',
            'email' => 'staff@coworking.com',
            'password' => 'password123',
        ]);
        $staff->assignRole($staffRole);

        $client = User::create([
            'name' => 'Carlos Mendoza',
            'email' => 'cliente@coworking.com',
            'password' => 'password123',
        ]);
        $client->assignRole($clientRole);

        // 4. Crear Tipos de Espacio (datos en español)
        $meetingRoom = SpaceType::create([
            'name' => 'Sala de Reuniones',
            'description' => 'Salas equipadas con proyector, pizarra y videoconferencia.',
        ]);

        $flexDesk = SpaceType::create([
            'name' => 'Escritorio Flexible',
            'description' => 'Puestos individuales en zona compartida de alta velocidad.',
        ]);

        $privateBooth = SpaceType::create([
            'name' => 'Cabina Privada',
            'description' => 'Cabinas insonorizadas para llamadas o trabajo concentrado.',
        ]);

        // 5. Crear Espacios de Ejemplo
        $spaceA = Space::create([
            'space_type_id' => $meetingRoom->id,
            'name' => 'Sala de Reuniones A',
            'floor' => 1,
            'capacity' => 10,
            'price_per_hour' => 45.00,
            'is_active' => true,
        ]);

        $spaceB = Space::create([
            'space_type_id' => $flexDesk->id,
            'name' => 'Escritorio Flexible 01',
            'floor' => 2,
            'capacity' => 1,
            'price_per_hour' => 12.50,
            'is_active' => true,
        ]);

        Space::create([
            'space_type_id' => $privateBooth->id,
            'name' => 'Cabina Privada VIP',
            'floor' => 2,
            'capacity' => 2,
            'price_per_hour' => 25.00,
            'is_active' => true,
        ]);

        // 6. Crear Reservas de Ejemplo
        Reservation::create([
            'user_id' => $client->id,
            'space_id' => $spaceA->id,
            'start_time' => now()->addDay()->setTime(10, 0),
            'end_time' => now()->addDay()->setTime(12, 0),
            'status' => 'confirmed',
            'notes' => 'Cliente solicitó proyector HDMI listo.',
        ]);

        Reservation::create([
            'user_id' => $client->id,
            'space_id' => $spaceB->id,
            'start_time' => now()->addDays(2)->setTime(14, 0),
            'end_time' => now()->addDays(2)->setTime(17, 0),
            'status' => 'pending',
            'notes' => 'Cerca de la ventana principal.',
        ]);
    }
}
