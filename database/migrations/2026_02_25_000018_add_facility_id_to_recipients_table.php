<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('recipients')) {
            return;
        }

        Schema::table('recipients', function (Blueprint $table) {
            if (!Schema::hasColumn('recipients', 'facility_id')) {
                $table->unsignedBigInteger('facility_id')->nullable()->after('recipient_types');
                $table->index('facility_id');
            }
        });

        Schema::table('recipients', function (Blueprint $table) {
            if (!Schema::hasColumn('recipients', 'facility_id')) {
                return;
            }

            try {
                $table->foreign('facility_id')->references('id')->on('facilities')->nullOnDelete();
            } catch (\Throwable $exception) {
                // Ignore when FK already exists or db engine does not support this operation.
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('recipients') || !Schema::hasColumn('recipients', 'facility_id')) {
            return;
        }

        Schema::table('recipients', function (Blueprint $table) {
            try {
                $table->dropForeign(['facility_id']);
            } catch (\Throwable $exception) {
                // Ignore when FK does not exist.
            }

            try {
                $table->dropIndex(['facility_id']);
            } catch (\Throwable $exception) {
                // Ignore when index does not exist.
            }

            $table->dropColumn('facility_id');
        });
    }
};
