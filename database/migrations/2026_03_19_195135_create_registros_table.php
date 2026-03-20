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
        Schema::create('registros', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('extrato_id')
                ->constrained('extratos')
                ->onDelete('cascade');
            
            $table->foreignId('carteira_id')
                ->constrained('carteiras')
                ->onDelete('restrict');
            
            $table->decimal('debito', 19, 2)->default(0);
            $table->decimal('credito', 19, 2)->default(0);
            
            $table->string('descricao', 255)->nullable();
            
            $table->timestamp('created_at')->useCurrent();
                        
            $table->index('extrato_id');
            $table->index(['carteira_id', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros');
    }
};
