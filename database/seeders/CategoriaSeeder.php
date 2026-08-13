<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Categorias padrão de movimentação.
     */
    public function run(): void
    {
        $categorias = [
            'Saúde' => 'heart-pulse',
            'Mercado' => 'cart-shopping',
            'Carro' => 'car',
            'Moto' => 'motorcycle',
            'Uber' => 'taxi',
            'Lazer' => 'gamepad',
            'Mãe' => 'heart',
            'Família' => 'people-roof',
            'Outros' => 'ellipsis',
            'Helpers' => 'hand-holding-heart',
            'Caixa' => 'cash-register',
            'Trabalho' => 'briefcase',
            'Presentes' => 'gift',
            'Comida' => 'utensils',
            'Comigo' => 'user',
            'Estudo' => 'graduation-cap',
            'Viagens' => 'plane',
            'Combustível' => 'gas-pump',
            'Pagamento' => 'money-bill-wave',
            'Casa' => 'house',
        ];

        foreach ($categorias as $descricao => $icone) {
            Categoria::updateOrCreate(
                ['descricao' => $descricao],
                ['icone' => $icone]
            );
        }
    }
}
