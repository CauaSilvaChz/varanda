<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'content' => fake()->randomElement([
                'Primeiro dia de sol depois de uma semana inteira de chuva.',
                'Alguém tem indicação de livro curto para ler no ônibus?',
                'Terminei o curso que comecei em março. Valeu a persistência.',
                'O pão de hoje cresceu bem mais que o da semana passada.',
                'Reunião que podia ter sido um e-mail, parte três.',
                'Testando a câmera nova no parque perto de casa.',
            ]),
            'image' => null,
        ];
    }
}
