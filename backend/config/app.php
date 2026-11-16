<?php

// O Laravel junta este arquivo com a configuração padrão dele, então aqui ficam só as mudanças.
return [

    'name' => env('APP_NAME', 'Varanda'),

    'url' => env('APP_URL', 'http://localhost:8000'),

    // Endereço do front-end Next.js, usado no link do e-mail de redefinição de senha.
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),

    'locale' => 'pt_BR',

    'faker_locale' => 'pt_BR',

];
