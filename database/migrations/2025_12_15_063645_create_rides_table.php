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
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Penumpang
            $table->foreignId('driver_id')->nullable(); // Driver (nullable saat awal order)
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->decimal('price', 10, 2);
            // Status: pending (cari driver), accepted (dapat driver), completed (selesai), canceled
            $table->enum('status', ['pending', 'accepted', 'completed', 'canceled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
