<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Apaga as imagens de execuções anteriores para o disco ficar igual ao banco.
        Storage::disk('public')->deleteDirectory('avatars');
        Storage::disk('public')->deleteDirectory('posts');

        $this->createUsers();
        $this->createFollows();
        $this->call(PostSeeder::class);
        $this->createLikes();
    }

    private function createUsers(): void
    {
        // [nome, usuário, bio, foto, data de cadastro]. A primeira pessoa é a conta de demonstração.
        $users = [
            ['Camila Rocha', 'camilarocha', 'Desenvolvedora front-end em Recife. Café coado, trilha no fim de semana e fotos tortas de celular.', 'camila.jpg', '2026-06-01 09:12'],
            ['Bruno Tavares', 'brunotavares', 'Professor de história no ensino médio. Pedalo aos domingos e leio sobre cidades.', 'bruno.jpg', '2026-06-01 14:40'],
            ['Juliana Mendes', 'ju_mendes', 'Arquiteta. Gosto de prédio antigo, calçada larga e cidade que dá pra andar a pé.', 'juliana.jpg', '2026-06-02 10:05'],
            ['Rafael Okada', 'rafaokada', 'Cozinheiro amador. Atualmente obcecado por pão de fermentação natural.', null, '2026-06-02 19:22'],
            ['Larissa Figueiredo', 'larifigueiredo', 'Bióloga marinha em Florianópolis. Falo de oceano mais do que deveria 🌊', 'larissa.jpg', '2026-06-03 08:47'],
            ['Diego Nascimento', 'diegonasc', 'Dev back-end, PHP desde 2014. Gosto de código simples e café forte.', 'diego.jpg', '2026-06-03 21:15'],
            ['Patrícia Lima', 'patilima', 'Ilustradora freelancer. Aquarela, caderno de desenho e muito chá.', 'patricia.jpg', '2026-06-04 11:30'],
            ['Thiago Barros', 'thiagobarros', 'Engenharia de Computação na UFPR. Monitor de Cálculo nas horas vagas.', null, '2026-06-05 16:02'],
            ['Fernanda Alves', 'fe_alves', 'Gerente de projetos, mãe do Theo e corredora iniciante.', null, '2026-06-06 07:55'],
            ['Gustavo Prado', 'gustavoprado', 'Fotografia de rua em São Paulo. A câmera mora na mochila.', null, '2026-06-06 22:31'],
            ['Aline Souza', 'alinesouza', 'Bibliotecária. Sempre lendo três livros ao mesmo tempo e terminando nenhum.', 'aline.jpg', '2026-06-08 13:20'],
            ['Marcos Vieira', 'marcosvieira', 'Horta em apartamento, compostagem e muita paciência.', 'marcos.jpg', '2026-06-09 18:44'],
        ];

        foreach ($users as $i => [$name, $username, $bio, $photo, $joinedAt]) {
            if ($photo) {
                Storage::disk('public')->put("avatars/$photo", file_get_contents(database_path("seeders/images/avatars/$photo")));
            }

            $date = Carbon::parse($joinedAt, 'America/Sao_Paulo')->utc();

            User::factory()->create([
                'name' => $name,
                'username' => $username,
                'email' => $i === 0 ? 'demo@varanda.test' : "$username@varanda.test",
                'password' => 'varanda123',
                'bio' => $bio,
                'profile_photo' => $photo ? "avatars/$photo" : null,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    private function createFollows(): void
    {
        // Quem segue => quem é seguido. A conta demo não segue thiagobarros, fe_alves e marcosvieira,
        // para dar para testar o botão "Seguir".
        $follows = [
            'camilarocha' => ['brunotavares', 'ju_mendes', 'rafaokada', 'larifigueiredo', 'diegonasc', 'patilima', 'gustavoprado', 'alinesouza'],
            'brunotavares' => ['camilarocha', 'ju_mendes', 'alinesouza', 'gustavoprado', 'thiagobarros'],
            'ju_mendes' => ['camilarocha', 'brunotavares', 'patilima', 'gustavoprado', 'marcosvieira'],
            'rafaokada' => ['camilarocha', 'larifigueiredo', 'marcosvieira', 'diegonasc'],
            'larifigueiredo' => ['camilarocha', 'rafaokada', 'patilima', 'fe_alves'],
            'diegonasc' => ['camilarocha', 'thiagobarros', 'rafaokada', 'gustavoprado'],
            'patilima' => ['camilarocha', 'ju_mendes', 'alinesouza', 'larifigueiredo', 'gustavoprado'],
            'thiagobarros' => ['camilarocha', 'diegonasc', 'brunotavares'],
            'fe_alves' => ['camilarocha', 'larifigueiredo', 'marcosvieira', 'alinesouza'],
            'gustavoprado' => ['ju_mendes', 'patilima', 'brunotavares'],
            'alinesouza' => ['camilarocha', 'brunotavares', 'patilima', 'fe_alves'],
            'marcosvieira' => ['rafaokada', 'ju_mendes', 'fe_alves', 'larifigueiredo'],
        ];

        foreach ($follows as $follower => $followed) {
            $ids = User::whereIn('username', $followed)->pluck('id');
            User::where('username', $follower)->first()->following()->attach($ids);
        }
    }

    private function createLikes(): void
    {
        // Cada publicação recebe de 2 a 9 curtidas de pessoas aleatórias (menos do próprio autor).
        foreach (Post::all() as $post) {
            $userIds = User::where('id', '!=', $post->user_id)
                ->inRandomOrder()
                ->limit(rand(2, 9))
                ->pluck('id');

            foreach ($userIds as $userId) {
                $post->likes()->create(['user_id' => $userId]);
            }
        }
    }
}
