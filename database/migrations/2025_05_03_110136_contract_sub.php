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
        Schema::create('contract_sub', function (Blueprint $table) {
            $table->string('address', 64)->primary(); 
            $table->text('code'); 
            $table->integer('time_checking'); 
            $table->string('message_on', 32); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_sub');
    }
};
