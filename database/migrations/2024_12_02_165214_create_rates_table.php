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
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('reading_type');
            $table->decimal('rate_value', 8, 2);
            $table->timestamps();
        });

        DB::table('rates')->insert([
            ['reading_type' => 1, 'rate_value' => '5.00', 'created_at' => now(), 'updated_at' => now()],
            ['reading_type' => 2, 'rate_value' => '2.00', 'created_at' => now(), 'updated_at' => now()],
            ['reading_type' => 3, 'rate_value' => '3.50', 'created_at' => now(), 'updated_at' => now()],
            ['reading_type' => 4, 'rate_value' => '370.00', 'created_at' => now(), 'updated_at' => now()],
            ['reading_type' => 5, 'rate_value' => '3028.85', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
