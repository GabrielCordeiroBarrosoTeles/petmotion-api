<?php

return [
    /*
     * Dados do controlador para as páginas de Privacidade e Termos.
     * Preencha no .env antes de publicar.
     */
    'legal' => [
        'name' => env('LEGAL_NAME', 'PetMotion'),
        'document' => env('LEGAL_DOCUMENT'),            // CPF/CNPJ do responsável
        'email' => env('LEGAL_EMAIL'),                  // e-mail de contato e do encarregado (DPO)
        'dpo' => env('LEGAL_DPO_NAME'),                 // nome do encarregado de dados
        'city' => env('LEGAL_CITY'),                    // cidade/UF para o foro
        'updated_at' => '2026-11-17',
    ],
];
