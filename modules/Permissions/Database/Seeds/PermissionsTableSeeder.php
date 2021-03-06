<?php

namespace Modules\Permissions\Database\Seeds;

use Nova\Database\ORM\Model;
use Nova\Database\Seeder;

use Modules\Permissions\Traits\ManagePermissionsTrait;


class PermissionsTableSeeder extends Seeder
{
    use ManagePermissionsTrait;


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = array(
            array(
                'name'  => 'Manage the Permissions',
                'slug'  => 'module.permissions.manage',
                'group' => 'permissions',

                'roles' => array(1, 2),
            ),
        );

        $this->createPermissions($permissions);
    }
}
