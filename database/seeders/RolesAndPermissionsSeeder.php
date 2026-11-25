<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'empresas.ver', 'empresas.crear', 'empresas.editar', 'empresas.eliminar',
            'leads.ver', 'leads.crear', 'leads.editar', 'leads.eliminar',
            'cotizaciones.ver', 'cotizaciones.crear', 'cotizaciones.editar', 'cotizaciones.eliminar',
            'reportes.ver',
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',
            'productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar',
            'seriales.ver', 'seriales.crear', 'seriales.editar', 'seriales.eliminar',
        ];

        foreach ($permissions as $p) {
            Permission::create(['name' => $p]);
        }

        Role::create(['name' => 'admin'])->givePermissionTo(Permission::all());

        Role::create(['name' => 'vendedor'])->givePermissionTo([
            'leads.ver', 'leads.crear', 'leads.editar', 'leads.eliminar',
            'cotizaciones.ver', 'cotizaciones.crear', 'cotizaciones.editar', 'cotizaciones.eliminar',
            'productos.ver',
            'seriales.ver', 'seriales.crear', 'seriales.editar', 'seriales.eliminar',
            'reportes.ver',
        ]);
    }
}
