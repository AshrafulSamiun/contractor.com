<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('parcel_storages')) {
            return;
        }

        Schema::table('parcel_storages', function (Blueprint $table) {
            if (!Schema::hasColumn('parcel_storages', 'facility_id')) {
                $table->unsignedBigInteger('facility_id')->nullable()->after('storage_name');
                $table->index('facility_id');
            }
        });

        if (
            Schema::hasTable('facilities')
            && Schema::hasColumn('parcel_storages', 'facility_id')
            && Schema::hasColumn('parcel_storages', 'facility_name')
        ) {
            DB::table('parcel_storages as ps')
                ->join('facilities as f', function ($join) {
                    $join->on('f.user_id', '=', 'ps.user_id')
                        ->on('f.facility_name', '=', 'ps.facility_name');
                })
                ->whereNull('ps.facility_id')
                ->select(['ps.id as storage_id', 'f.id as matched_facility_id'])
                ->orderBy('ps.id')
                ->chunk(500, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('parcel_storages')
                            ->where('id', $row->storage_id)
                            ->update(['facility_id' => $row->matched_facility_id]);
                    }
                });
        }

        Schema::table('parcel_storages', function (Blueprint $table) {
            if (!Schema::hasColumn('parcel_storages', 'facility_id')) {
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
        if (!Schema::hasTable('parcel_storages') || !Schema::hasColumn('parcel_storages', 'facility_id')) {
            return;
        }

        Schema::table('parcel_storages', function (Blueprint $table) {
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

