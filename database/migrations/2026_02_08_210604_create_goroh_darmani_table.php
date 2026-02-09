<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('goroh_darmani', function (Blueprint $table) {
                $table->integer('cod')->primary();
    $table->string('nam_fa')->nullable();
    $table->string('nam_en')->nullable();
    $table->string('giyahi_ya_shimiyaei')->nullable();
    $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goroh_darmani');
    }
};
