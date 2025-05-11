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
        Schema::create('cache_contract', function (Blueprint $table) {
            $table->id(); 
            $table->string('address', 64)->unique(); 
            $table->text('code'); 
            $table->integer('time_checking'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_contract');
    }
};
