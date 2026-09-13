<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Impfungen-Katalog — team-lokal, frei pflegbar. Referenziert von erbrachten Leistungen
 * (encounter_services, catalog_type='vaccination'). Idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vaccinations')) {
            return;
        }

        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('team_id')->index();
            $table->string('catalog_code', 64)->nullable();   // kanonischer Schlüssel (Sync-Grundlage)
            $table->string('code', 64)->nullable();            // Impfstoff-/STIKO-Kürzel
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 16)->default('active');   // active|archived
            $table->unsignedInteger('position')->default(0);
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccinations');
    }
};
