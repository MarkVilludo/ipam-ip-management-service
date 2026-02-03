<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('activitylog.table_name', 'activity_log');
        $connection = config('activitylog.database_connection');

        $schema = $connection ? Schema::connection($connection) : Schema::getFacadeRoot();
        if ($schema->hasColumn($tableName, 'batch_uuid')) {
            return;
        }

        $builder = $connection ? Schema::connection($connection) : Schema::getFacadeRoot();
        $builder->table($tableName, function (Blueprint $table) {
            $table->uuid('batch_uuid')->nullable()->after('properties');
        });
    }

    public function down(): void
    {
        $tableName = config('activitylog.table_name', 'activity_log');
        $connection = config('activitylog.database_connection');

        $builder = $connection ? Schema::connection($connection) : Schema::getFacadeRoot();
        $builder->table($tableName, function (Blueprint $table) {
            $table->dropColumn('batch_uuid');
        });
    }
};
