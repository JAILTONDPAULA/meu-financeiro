<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->foreignId('movimentacao_original_id')->nullable()->constrained('movimentacoes')->cascadeOnDelete();
            $table->decimal('valor', 12, 2);
            $table->date('data_registro')->default(DB::raw('CURRENT_DATE'));
            $table->char('tipo', 1);
            $table->char('flg_credito', 1)->default('N');
            $table->char('recorrente', 1)->default('N');
            $table->unsignedInteger('faturamento_ym')->nullable();
            $table->timestamps();

            $table->index('data_registro');
            $table->index('faturamento_ym');
        });

        DB::statement("ALTER TABLE movimentacoes ADD CONSTRAINT movimentacoes_tipo_check CHECK (tipo IN ('F', 'D'))");
        DB::statement("ALTER TABLE movimentacoes ADD CONSTRAINT movimentacoes_flg_credito_check CHECK (flg_credito IN ('S', 'N'))");
        DB::statement("ALTER TABLE movimentacoes ADD CONSTRAINT movimentacoes_recorrente_check CHECK (recorrente IN ('S', 'N'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
    }
};
