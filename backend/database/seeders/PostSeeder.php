<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

// Publicações e comentários de demonstração. Os horários estão no fuso de Brasília.
class PostSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all()->keyBy('username');

        foreach ($this->posts() as $data) {
            $publishedAt = Carbon::parse($data['at'], 'America/Sao_Paulo')->utc();

            $image = null;
            if (isset($data['image'])) {
                $image = 'posts/'.$data['image'];
                Storage::disk('public')->put($image, file_get_contents(database_path('seeders/images/'.$image)));
            }

            $post = Post::factory()->create([
                'user_id' => $users[$data['user']]->id,
                'content' => $data['content'] ?? null,
                'image' => $image,
                'created_at' => $publishedAt,
                'updated_at' => $publishedAt,
            ]);

            foreach ($data['comments'] ?? [] as [$username, $minutesLater, $text]) {
                Comment::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => $users[$username]->id,
                    'content' => $text,
                    'created_at' => $publishedAt->copy()->addMinutes($minutesLater),
                    'updated_at' => $publishedAt->copy()->addMinutes($minutesLater),
                ]);
            }
        }
    }

    private function posts(): array
    {
        return [
            [
                'user' => 'camilarocha',
                'at' => '2026-06-03 08:20',
                'content' => 'Primeira postagem por aqui. Criei uma conta na Varanda pra ver se consigo largar um pouco os grupos de mensagem e conversar com mais calma. Bom dia, gente!',
                'comments' => [
                    ['brunotavares', 25, 'Bem-vinda! Aqui é bem mais tranquilo que grupo de família, prometo.'],
                    ['larifigueiredo', 130, 'Bom dia, Camila! 👋'],
                ],
            ],
            [
                'user' => 'brunotavares',
                'at' => '2026-06-03 19:10',
                'content' => 'Hoje um aluno me perguntou se a Revolução Francesa "cai na prova". Respondi que ela cai em tudo, inclusive na vida. Ele não achou graça.',
                'comments' => [
                    ['ju_mendes', 30, 'Ele vai lembrar dessa frase daqui a dez anos, pode apostar.'],
                    ['alinesouza', 70, 'Eu teria achado graça. Aceita aluna ouvinte?'],
                ],
            ],
            [
                'user' => 'ju_mendes',
                'at' => '2026-06-04 12:35',
                'content' => 'Fui almoçar no centro e passei pelo mesmo prédio de sempre. Dez anos andando por ali e só hoje reparei que as janelas do terceiro andar são diferentes das outras.',
                'image' => 'predio-antigo.jpg',
                'comments' => [
                    ['patilima', 40, 'Que fachada linda. Dá vontade de desenhar.'],
                    ['gustavoprado', 120, 'Qual rua é essa? Quero fotografar com a luz do fim da tarde.'],
                    ['ju_mendes', 150, 'Fica na Riachuelo! Vale ir lá pelas 17h.'],
                ],
            ],
            [
                'user' => 'rafaokada',
                'at' => '2026-06-04 20:50',
                'content' => 'O fermento natural nasceu há doze dias. Já tem nome (Bartolomeu) e já me deu mais trabalho que muita planta.',
                'comments' => [
                    ['camilarocha', 60, 'Bartolomeu é um nome muito digno pra um fermento.'],
                    ['marcosvieira', 180, 'Fermento e horta têm isso em comum: você vira refém deles.'],
                ],
            ],
            [
                'user' => 'larifigueiredo',
                'at' => '2026-06-05 07:15',
                'image' => 'mar-floripa.jpg',
                'comments' => [
                    ['camilarocha', 20, 'Que paz essa foto.'],
                    ['fe_alves', 65, 'Isso é Floripa? Preciso de férias urgente.'],
                ],
            ],
            [
                'user' => 'diegonasc',
                'at' => '2026-06-05 22:40',
                'content' => 'Lembrete pra mim mesmo: se a query está lenta, olha o índice antes de reescrever o sistema inteiro.',
                'comments' => [
                    ['thiagobarros', 15, 'Anotado. Eu ia reescrever o sistema inteiro mesmo.'],
                    ['camilarocha', 40, 'O clássico "na minha máquina, com 10 registros, funciona".'],
                ],
            ],
            [
                'user' => 'patilima',
                'at' => '2026-06-06 10:05',
                'content' => 'Estudo de cor para um livro infantil que estou ilustrando. Ainda não sei qual paleta vai ganhar.',
                'image' => 'aquarela.jpg',
                'comments' => [
                    ['ju_mendes', 55, 'Gosto muito da parte com os verdes e azuis.'],
                    ['alinesouza', 125, 'Já quero esse livro na biblioteca quando sair!'],
                ],
            ],
            [
                'user' => 'camilarocha',
                'at' => '2026-06-06 17:30',
                'content' => 'Alguém aí já usou CSS Modules num projeto grande? Estou gostando de não precisar inventar nome de classe gigante.',
                'comments' => [
                    ['diegonasc', 30, 'Uso no trabalho. O melhor é saber que mexer num componente não quebra outro do outro lado do site.'],
                    ['thiagobarros', 75, 'Eu ainda escrevo tudo num style.css de três mil linhas. Socorro.'],
                ],
            ],
            [
                'user' => 'thiagobarros',
                'at' => '2026-06-07 14:12',
                'content' => 'Prova de Cálculo 2 na sexta. Se eu sumir, já sabem onde me encontrar: biblioteca, segundo andar, mesa perto da tomada.',
                'comments' => [
                    ['diegonasc', 110, 'Boa sorte! Integral por partes é só respirar fundo.'],
                ],
            ],
            [
                'user' => 'fe_alves',
                'at' => '2026-06-07 06:48',
                'content' => 'Primeiros 5 km sem parar! Há dois meses eu não corria nem atrás do ônibus.',
                'comments' => [
                    ['larifigueiredo', 30, 'Que orgulho! Daqui a pouco são 10 km.'],
                    ['marcosvieira', 70, 'Correr atrás do ônibus conta como treino intervalado, viu.'],
                ],
            ],
            [
                'user' => 'gustavoprado',
                'at' => '2026-06-07 19:02',
                'image' => 'skyline-sp.jpg',
                'comments' => [
                    ['patilima', 50, 'Essas cores do céu parecem pintura.'],
                    ['brunotavares', 115, 'São Paulo fica bonita assim de longe e no fim da tarde. Brincadeira. (Ou não.)'],
                ],
            ],
            [
                'user' => 'alinesouza',
                'at' => '2026-06-08 15:40',
                'content' => 'Chegou um lote de doações na biblioteca do bairro. Tinha um livro de receitas de 1974 com anotações a lápis da antiga dona. Tem coisa mais bonita?',
                'comments' => [
                    ['rafaokada', 55, 'Preciso ver essas receitas! Tem bolo de fubá?'],
                    ['alinesouza', 90, 'Tem! Com a observação "menos açúcar, o Zé reclamou". 😂'],
                ],
            ],
            [
                'user' => 'marcosvieira',
                'at' => '2026-06-09 18:55',
                'content' => 'Colhi o primeiro tomate-cereja da varanda. Um. Mas foi o tomate mais gostoso da minha vida.',
                'image' => 'horta.jpg',
                'comments' => [
                    ['fe_alves', 40, 'O primeiro a gente nunca esquece.'],
                    ['rafaokada', 65, 'Merecia uma bruschetta em homenagem.'],
                ],
            ],
            [
                'user' => 'rafaokada',
                'at' => '2026-06-10 09:20',
                'content' => 'Terceira tentativa. Agora sim, o Bartolomeu trabalhou direito.',
                'image' => 'pao.jpg',
                'comments' => [
                    ['camilarocha', 20, 'Olha essa casca! Aceita encomendas?'],
                    ['larifigueiredo', 125, 'Bartolomeu está de parabéns.'],
                    ['marcosvieira', 190, 'Taí a prova de que persistência funciona.'],
                ],
            ],
            [
                'user' => 'brunotavares',
                'at' => '2026-06-10 21:05',
                'content' => 'Domingo tem pedal até a represa. Saída às 7h da praça. Quem quiser ir no ritmo tranquilo, cola junto.',
                'comments' => [
                    ['gustavoprado', 60, 'Se eu acordar a tempo, apareço. Se não, vejo as fotos.'],
                    ['ju_mendes', 170, 'Ritmo tranquilo é comigo. Vou!'],
                ],
            ],
            [
                'user' => 'camilarocha',
                'at' => '2026-06-11 07:40',
                'content' => 'Subimos às 5h pra pegar o nascer do sol. As pernas reclamaram, a vista compensou.',
                'image' => 'trilha-serra.jpg',
                'comments' => [
                    ['patilima', 30, 'Que cores! Posso usar de referência?'],
                    ['camilarocha', 45, 'Pode usar à vontade!'],
                    ['brunotavares', 120, 'Qual trilha é essa? Parece o Pico do Papagaio.'],
                ],
            ],
            [
                'user' => 'larifigueiredo',
                'at' => '2026-06-11 16:25',
                'content' => 'Curiosidade do dia: no cavalo-marinho, é o macho que carrega os filhotes até o nascimento. Pronto, agora vocês sabem.',
                'comments' => [
                    ['fe_alves', 60, 'Vou contar pro Theo hoje à noite, ele vai amar.'],
                    ['diegonasc', 115, 'Informação que eu não sabia que precisava.'],
                ],
            ],
            [
                'user' => 'diegonasc',
                'at' => '2026-06-12 11:10',
                'content' => 'Code review bom não é o que acha mais defeito. É o que deixa o time aprendendo junto.',
                'comments' => [
                    ['camilarocha', 35, 'Isso devia estar escrito na parede de todo time.'],
                    ['thiagobarros', 80, 'Salvei pra reler no meu primeiro estágio.'],
                ],
            ],
            [
                'user' => 'ju_mendes',
                'at' => '2026-06-12 20:30',
                'content' => 'Calçada estreita não é falta de espaço, é escolha. Sempre lembro disso quando alguém diz que "não cabe" uma calçada decente.',
                'comments' => [
                    ['brunotavares', 50, 'Concordo demais. Cidade é decisão política.'],
                ],
            ],
            [
                'user' => 'patilima',
                'at' => '2026-06-13 09:00',
                'image' => 'cafe.jpg',
                'comments' => [
                    ['rafaokada', 30, 'Esse café está com cara de ser bom.'],
                    ['camilarocha', 60, 'Desenho ou foto? Fiquei na dúvida!'],
                    ['patilima', 80, 'Ilustração digital! Fico feliz que confundiu 😊'],
                ],
            ],
            [
                'user' => 'alinesouza',
                'at' => '2026-06-13 14:22',
                'content' => 'Indicação para quem tem pouco tempo para ler: contos. Dá para terminar um inteiro na fila do banco e ainda sair pensando nele.',
                'comments' => [
                    ['camilarocha', 60, 'Alguma indicação de autora brasileira?'],
                    ['alinesouza', 95, 'Clarice e Lygia Fagundes Telles nunca erram. Começa por "Laços de Família".'],
                ],
            ],
            [
                'user' => 'gustavoprado',
                'at' => '2026-06-14 17:48',
                'content' => 'Saí às 6h pra fotografar a Paulista vazia e encontrei metade de São Paulo tendo a mesma ideia.',
                'comments' => [
                    ['ju_mendes', 40, 'Kkkkk o famoso vazio lotado.'],
                ],
            ],
            [
                'user' => 'thiagobarros',
                'at' => '2026-06-15 13:30',
                'content' => 'Passei em Cálculo 2!!! Obrigado a todo mundo que aguentou minhas reclamações nas últimas semanas.',
                'comments' => [
                    ['diegonasc', 20, 'Eu falei! Parabéns, Thiago!'],
                    ['brunotavares', 60, 'Muito bem! Agora descansa um pouco.'],
                ],
            ],
            [
                'user' => 'fe_alves',
                'at' => '2026-06-15 21:10',
                'content' => 'O Theo perguntou por que a lua segue o carro. Expliquei de três jeitos diferentes. Ele concluiu que a lua gosta da gente. Não discuti.',
                'comments' => [
                    ['larifigueiredo', 30, 'O Theo está certo e ninguém me convence do contrário.'],
                    ['alinesouza', 65, 'Que fofura. Isso dava um livro infantil.'],
                ],
            ],
            [
                'user' => 'marcosvieira',
                'at' => '2026-06-16 08:35',
                'content' => 'Dica pra quem quer começar uma horta em apartamento: comece por cebolinha e manjericão. São difíceis de matar e dão ânimo pra continuar.',
                'comments' => [
                    ['fe_alves', 120, 'Anotado! Tenho um manjericão sobrevivendo bravamente aqui.'],
                ],
            ],
            [
                'user' => 'camilarocha',
                'at' => '2026-06-16 20:15',
                'content' => 'Montei meu cantinho de trabalho novo. A planta é de verdade, o código também (infelizmente, com bug).',
                'image' => 'mesa-trabalho.jpg',
                'comments' => [
                    ['diegonasc', 40, 'Bug é só uma funcionalidade que ninguém documentou.'],
                    ['patilima', 70, 'Adorei a plantinha.'],
                ],
            ],
            [
                'user' => 'brunotavares',
                'at' => '2026-06-17 18:40',
                'content' => 'Pedal de domingo: 62 km, duas paradas pra pastel e nenhum pneu furado. Recorde pessoal de sorte.',
                'image' => 'estrada-serra.jpg',
                'comments' => [
                    ['gustavoprado', 60, 'Desculpa, não acordei. Da próxima eu vou!'],
                    ['ju_mendes', 120, 'Que paisagem! E o pastel, estava bom?'],
                    ['brunotavares', 140, 'O de palmito estava excelente.'],
                ],
            ],
            [
                'user' => 'rafaokada',
                'at' => '2026-06-18 12:05',
                'content' => 'Pergunta séria: pão de queijo é pão? A família está dividida desde o almoço.',
                'comments' => [
                    ['camilarocha', 15, 'É pão de queijo. Não precisa ser pão, ele é ele mesmo.'],
                    ['marcosvieira', 40, 'Polêmica desnecessária e eu adoro.'],
                    ['larifigueiredo', 65, 'Cientificamente não sei. Mas é delicioso.'],
                ],
            ],
            [
                'user' => 'larifigueiredo',
                'at' => '2026-06-19 10:40',
                'content' => 'Hoje teve mutirão de limpeza na praia da Armação. Em duas horas tiramos 38 kg de lixo, a maior parte plástico pequeno. Obrigada a quem apareceu!',
                'comments' => [
                    ['fe_alves', 60, 'Parabéns pela iniciativa! Avisa do próximo.'],
                    ['camilarocha', 125, '38 kg é muita coisa. Que trabalho bonito.'],
                ],
            ],
            [
                'user' => 'diegonasc',
                'at' => '2026-06-19 23:05',
                'content' => 'Deploy na sexta à noite não, né. Mas foi. E deu certo. Não façam isso.',
                'comments' => [
                    ['thiagobarros', 30, 'Anotado: não fazer. Mas, se der certo, comemorar.'],
                    ['camilarocha', 540, 'Coragem tem limite, Diego 😅'],
                ],
            ],
            [
                'user' => 'alinesouza',
                'at' => '2026-06-20 11:15',
                'content' => 'Estante da biblioteca comunitária depois da reorganização. Agora por cor dentro de cada gênero, porque ninguém é de ferro.',
                'image' => 'estante.jpg',
                'comments' => [
                    ['patilima', 30, 'Organização por cor é minha linguagem do amor.'],
                    ['brunotavares', 130, 'Passo aí semana que vem pra doar uns livros.'],
                ],
            ],
            [
                'user' => 'ju_mendes',
                'at' => '2026-06-21 16:50',
                'content' => 'Visitei uma casa modernista e fiquei uns vinte minutos olhando só para a escada. Arquiteto é um bicho estranho, eu sei.',
                'comments' => [
                    ['brunotavares', 55, 'Não é estranho. É paixão pelo ofício.'],
                ],
            ],
            [
                'user' => 'gustavoprado',
                'at' => '2026-06-22 22:30',
                'image' => 'lua-lago.jpg',
                'comments' => [
                    ['larifigueiredo', 40, 'Que imagem! Onde foi isso?'],
                    ['gustavoprado', 65, 'Represa Billings, numa noite sem nenhuma nuvem.'],
                ],
            ],
            [
                'user' => 'patilima',
                'at' => '2026-06-23 15:20',
                'content' => 'Fechei o primeiro contrato de ilustração do ano! Um livro infantil sobre um peixinho que tem medo de água. Sim, é isso mesmo.',
                'comments' => [
                    ['alinesouza', 30, 'Parabéns!!! Já tem lugar reservado na estante infantil.'],
                    ['ju_mendes', 60, 'Um peixinho com medo de água é a coisa mais fofa que já ouvi.'],
                    ['larifigueiredo', 120, 'Se precisar de consultoria científica sobre peixes, estou aqui 😄'],
                ],
            ],
            [
                'user' => 'thiagobarros',
                'at' => '2026-06-24 19:45',
                'content' => 'Comecei a estudar Laravel nas férias. Por que ninguém me contou antes que dava para montar uma API inteira tão rápido?',
                'comments' => [
                    ['diegonasc', 20, 'Bem-vindo ao lado PHP da força. Começa pelas migrations e pelo Eloquent.'],
                    ['camilarocha', 65, 'Depois junta com um front em React e você não para mais.'],
                ],
            ],
            [
                'user' => 'camilarocha',
                'at' => '2026-06-25 12:10',
                'content' => 'Dica de acessibilidade que todo mundo deveria seguir: texto alternativo nas imagens. Custa uma frase e faz uma diferença enorme para quem usa leitor de tela.',
                'comments' => [
                    ['alinesouza', 60, 'Obrigada por lembrar disso. Faz muita diferença mesmo.'],
                ],
            ],
            [
                'user' => 'fe_alves',
                'at' => '2026-06-26 07:05',
                'content' => 'Inscrição feita na corrida de 10 km de agosto. Agora não tem mais volta.',
                'comments' => [
                    ['larifigueiredo', 30, 'Vai dar tudo certo! Estarei na torcida.'],
                    ['marcosvieira', 60, 'Agora é treino e paciência. Igual horta.'],
                ],
            ],
            [
                'user' => 'marcosvieira',
                'at' => '2026-06-27 17:30',
                'content' => 'A composteira finalmente parou de cheirar estranho. O segredo era mais folha seca. Sempre é mais folha seca.',
                'comments' => [
                    ['rafaokada', 110, 'Sempre é mais folha seca. Vou levar isso pra vida.'],
                ],
            ],
            [
                'user' => 'brunotavares',
                'at' => '2026-06-28 10:20',
                'content' => 'Terminei de corrigir 140 redações. Vou passar o resto do sábado olhando para uma parede, obrigado.',
                'comments' => [
                    ['alinesouza', 60, 'Merece um café e um livro bem leve.'],
                    ['ju_mendes', 115, 'Descansa! Professor é herói sem capa.'],
                ],
            ],
            [
                'user' => 'rafaokada',
                'at' => '2026-06-29 20:00',
                'content' => 'Janta de hoje: lámen caseiro com caldo que ficou oito horas no fogo. Valeu cada minuto e cada louça.',
                'comments' => [
                    ['camilarocha', 60, 'Quero a receita!'],
                    ['diegonasc', 90, 'Oito horas?? Eu desisto no minuto quinze.'],
                ],
            ],
        ];
    }
}
