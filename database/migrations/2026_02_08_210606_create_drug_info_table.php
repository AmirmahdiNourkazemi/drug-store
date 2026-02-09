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
        Schema::create('drug_info', function (Blueprint $table) {
               $table->id('cod');

    $table->unsignedBigInteger('goroh_darmani_detail_cod')->nullable();
    $table->unsignedBigInteger('goroh_daroei_cod')->nullable();
    $table->integer('goroh_farmakologic_cod')->nullable();
    $table->integer('goroh_darmani_cod')->nullable();

    $table->string('nam_fa')->nullable();
    $table->string('nam_en')->nullable();

    $table->text('mavaredmasraf')->nullable();
    $table->text('meghdarmasraf')->nullable();
    $table->text('masrafdarhamelegi')->nullable();
    $table->text('masrafdarshirdehi')->nullable();
    $table->text('manemasraf')->nullable();
    $table->text('avarez')->nullable();
    $table->text('tadakhol')->nullable();
    $table->text('mekanismtasir')->nullable();
    $table->text('nokte')->nullable();
    $table->text('hoshdar')->nullable();
    $table->text('sharayetnegahdari')->nullable();
    $table->text('ashkal_daroei')->nullable();

    $table->foreign('goroh_darmani_detail_cod')
        ->references('cod')->on('goroh_darmani_detail');

    $table->foreign('goroh_daroei_cod')
        ->references('cod')->on('goroh_daroei');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_info');
    }
};
