<?php

// Só o front-end pode chamar a API pelo navegador.
return [

    'paths' => ['api/*'],

    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],

];
