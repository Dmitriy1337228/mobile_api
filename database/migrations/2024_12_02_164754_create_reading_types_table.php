<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reading_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('reading_type');
            $table->string('reading_name', length: 256);
            $table->string('measure_unit', length: 8);
            $table->timestamps();
        });

        DB::table('reading_types')->insert([
            ['reading_type' => 1, 'reading_name' => 'Электричество', 'measure_unit' => 'кВт*ч', 'created_at' => now(), 'updated_at' => now()],
            ['reading_type' => 2, 'reading_name' => 'ХВС', 'measure_unit' => 'м^3', 'created_at' => now(), 'updated_at' => now()],
            ['reading_type' => 3, 'reading_name' => 'ГВС', 'measure_unit' => 'м^3', 'created_at' => now(), 'updated_at' => now()],
            ['reading_type' => 4, 'reading_name' => 'ТКО и проч', 'measure_unit' => 'чел.', 'created_at' => now(), 'updated_at' => now()],
            ['reading_type' => 5, 'reading_name' => 'Отопление', 'measure_unit' => 'Гкал', 'created_at' => now(), 'updated_at' => now()],
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_types');
    }
};
