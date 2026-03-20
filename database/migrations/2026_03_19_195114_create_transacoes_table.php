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
        Schema::create('transacoes', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('de_user_id')
                ->constrained('users')
                ->onDelete('restrict');
            $table->foreignId('para_user_id')
                ->constrained('users')
                ->onDelete('restrict');
            
            $table->decimal('valor', 19, 2);
            
            $table->enum('status', ['pendente', 'concluida', 'falhou'])->default('pendente');
            $table->enum('tipo', ['credito', 'debito'])->default('credito');
            
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            
            $table->text('motivo_falha')->nullable();
            
            $table->index(['de_user_id', 'created_at']);
            $table->index(['para_user_id', 'created_at']);
            $table->index(['de_user_id', 'status']);
            $table->index(['para_user_id', 'status']);
            $table->index('status');
            $table->index('created_at');
        });
        DB::statement(
            "ALTER TABLE transacoes ADD CONSTRAINT verifica_transferencia_propria CHECK (de_user_id != para_user_id)"
        );
        
        DB::statement(
            "ALTER TABLE transacoes ADD CONSTRAINT verifica_valor_positivo CHECK (valor > 0)"
        );
        
        DB::statement(
            "ALTER TABLE transacoes ADD CONSTRAINT verifica_status_valido CHECK (status IN ('pendente', 'concluida', 'falhou'))"
        );
        
        DB::statement(
            "ALTER TABLE transacoes ADD CONSTRAINT verifica_tipo_valido CHECK (tipo IN ('credito', 'debito'))"
        );
        
        DB::statement(
            "ALTER TABLE transacoes ADD CONSTRAINT verifica_completed_at CHECK (
                (status = 'concluida' AND completed_at IS NOT NULL) OR
                (status != 'concluida')
            )"
        );
        
        DB::statement(
            "ALTER TABLE transacoes ADD CONSTRAINT verifica_motivo_falha CHECK (
                (status = 'falhou' AND motivo_falha IS NOT NULL) OR
                (status != 'falhou')
            )"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacoes');
    }
};
