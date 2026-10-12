<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductRevision;
use App\Models\ProductType;
use App\Models\Role;
use App\Models\UrlRedirect;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class E2ESeeder extends Seeder
{
    public function run(): void
    {
        if (
            ! app()->environment([
                'local',
                'testing',
            ])
        ) {
            throw new RuntimeException(
                'E2ESeeder may only run in local or testing environments.',
            );
        }

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $email = (string) env(
            'E2E_USER_EMAIL',
            'e2e@cim.test',
        );

        $password = (string) env(
            'E2E_USER_PASSWORD',
            'CimE2E-Local-Only-2026!',
        );

        $user = User::query()->updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'name' => 'E2E Super Admin',
                'password' => $password,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $role = Role::query()
            ->where(
                'name',
                Role::SUPER_ADMIN,
            )
            ->firstOrFail();

        $user->roles()->sync([
            $role->id,
        ]);

        DB::transaction(
            function () use ($user): void {
                $products = Product::query()
                    ->where(
                        'created_by',
                        $user->id,
                    )
                    ->pluck('id');

                if ($products->isNotEmpty()) {
                    UrlRedirect::query()
                        ->where(
                            'entity_type',
                            Product::REDIRECT_TYPE,
                        )
                        ->whereIn(
                            'entity_id',
                            $products,
                        )
                        ->delete();

                    ProductRevision::query()
                        ->whereIn(
                            'product_id',
                            $products,
                        )
                        ->delete();

                    Product::query()
                        ->whereIn(
                            'id',
                            $products,
                        )
                        ->delete();
                }
            },
        );

        ProductCategory::query()->updateOrCreate(
            [
                'slug' => 'e2e-category',
            ],
            [
                'name' => 'E2E Category',
                'description' => 'E2E test category',
                'sort_order' => 0,
                'is_active' => true,
            ],
        );

        Brand::query()->updateOrCreate(
            [
                'slug' => 'e2e-brand',
            ],
            [
                'name' => 'E2E Brand',
                'is_active' => true,
            ],
        );

        ProductType::query()->updateOrCreate(
            [
                'slug' => 'e2e-type',
            ],
            [
                'name' => 'E2E Type',
                'is_active' => true,
            ],
        );
    }
}
