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
        Schema::create('extratos', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('transacao_id')
                ->unique()
                ->constrained('transacoes')
                ->onDelete('restrict');
            
            $table->enum('status', ['pendente', 'concluida', 'falhou'])->default('concluida');
            
            $table->text('descricao')->nullable();
            
            $table->timestamp('posted_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('transacao_id');
            $table->index('status');
            $table->index('posted_at');
        });
        
        DB::statement(
            "ALTER TABLE extratos ADD CONSTRAINT verifica_status CHECK (status IN ('pendente', 'concluida', 'falhou'))"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extratos');
    }
};
