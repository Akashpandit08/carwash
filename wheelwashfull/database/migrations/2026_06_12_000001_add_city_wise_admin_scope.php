<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_cities')) {
            Schema::create('service_cities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('state')->nullable();
                $table->string('status')->default('active');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('service_zones')) {
            Schema::create('service_zones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_city_id')->constrained('service_cities')->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->string('status')->default('active');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['service_city_id', 'slug']);
            });
        }

        $this->addCityColumns('users', 'role');
        $this->addCityColumns('bookings', 'service_id');
        $this->addCityColumns('services', 'category_id');
        $this->addCityColumns('slots', 'date');
        $this->addCityColumns('coupons', 'code');
        $this->addCityColumns('banners', 'title');
        $this->addCityColumns('app_banners', 'title');

        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);
    }

    public function down(): void
    {
        foreach (['app_banners', 'banners', 'coupons', 'slots', 'services', 'bookings', 'users'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'service_zone_id')) {
                    $table->dropConstrainedForeignId('service_zone_id');
                }
                if (Schema::hasColumn($table->getTable(), 'service_city_id')) {
                    $table->dropConstrainedForeignId('service_city_id');
                }
            });
        }

        Schema::dropIfExists('service_zones');
        Schema::dropIfExists('service_cities');
    }

    private function addCityColumns(string $tableName, string $after): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $after) {
            if (! Schema::hasColumn($tableName, 'service_city_id')) {
                $table->foreignId('service_city_id')->nullable()->after($after)->constrained('service_cities')->nullOnDelete();
            }
            if (! Schema::hasColumn($tableName, 'service_zone_id')) {
                $table->foreignId('service_zone_id')->nullable()->after('service_city_id')->constrained('service_zones')->nullOnDelete();
            }
        });
    }
};
