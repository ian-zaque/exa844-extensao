<?php

use Illuminate\Database\Seeder;
use App\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(!Role::first()){
            $role = new Role();
            $role->role = 'adm';
            $role->created_at = now();
            $role->updated_at = now();
            $role->deleted_at = null;
            $role->save();

            $role = new Role();
            $role->role = 'normal';
            $role->created_at = now();
            $role->updated_at = now();
            $role->deleted_at = null;
            $role->save();
        }
    }
}
