<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Walk;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Perguntas reais de tutores. Alimentam a seção visível e o FAQPage (JSON-LD),
     * que é o que buscadores e assistentes de IA usam para responder sobre o app.
     */
    public const FAQ = [
        [
            'q' => 'O que é o PetMotion?',
            'a' => 'O PetMotion é um aplicativo de celular para Android e iPhone que registra os passeios do seu cachorro ou gato. Ele mostra o caminho no mapa, conta o tempo e os passos, guarda onde o pet fez xixi, cocô ou bebeu água e lembra você das vacinas e vermífugos.',
        ],
        [
            'q' => 'Preciso comprar uma coleira com GPS ou outro aparelho?',
            'a' => 'Não. O PetMotion usa só o celular que você já leva no passeio. Não tem coleira, rastreador nem mensalidade de aparelho.',
        ],
        [
            'q' => 'Funciona para gato também?',
            'a' => 'Sim. Você pode cadastrar cachorros e gatos na mesma conta. Os lembretes de vacina, vermífugo e remédio funcionam para qualquer pet, e o registro de passeios serve para quem passeia com o gato na guia.',
        ],
        [
            'q' => 'Como o app sabe se o asfalto está quente demais para as patas?',
            'a' => 'Antes do passeio, o PetMotion consulta a temperatura da sua região. Em dia de sol, o asfalto pode ficar até uns 25 °C mais quente que o ar, então o app estima a temperatura do chão e avisa quando é melhor esperar ou ir pela grama. Uma dica que sempre vale: encoste a mão no chão por 7 segundos. Se incomodar você, vai incomodar o seu pet.',
        ],
        [
            'q' => 'Quanto tempo meu cachorro precisa passear por dia?',
            'a' => 'Depende da raça, da idade e da saúde do seu pet, então o ideal é conversar com o veterinário. No PetMotion você define uma meta diária em minutos, e o app mostra quanto falta para fechar o dia.',
        ],
        [
            'q' => 'O app me avisa das vacinas?',
            'a' => 'Sim. Quando você registra uma vacina, vermífugo ou remédio com a data da próxima dose, o celular manda uma notificação na véspera, mesmo com o app fechado.',
        ],
        [
            'q' => 'Quando o PetMotion vai estar disponível?',
            'a' => 'O PetMotion está em fase final de testes e chega em breve para Android e iPhone.',
        ],
    ];

    public function __invoke(): View
    {
        return view('landing', [
            'faq' => self::FAQ,
            'stats' => [
                'km' => (int) round(Walk::sum('distance_meters') / 1000),
                'walks' => Walk::count(),
                'pets' => Pet::count(),
            ],
        ]);
    }

    public function sitemap(): Response
    {
        $updated = optional(Walk::latest('updated_at')->first())->updated_at ?? now();
        $legalDate = config('petmotion.legal.updated_at');

        $pages = [
            [route('landing'), $updated->toAtomString(), 'weekly', '1.0'],
            [route('legal.privacy'), $legalDate, 'yearly', '0.3'],
            [route('legal.terms'), $legalDate, 'yearly', '0.3'],
        ];

        $urls = collect($pages)->map(fn ($p) => <<<URL
          <url>
            <loc>{$p[0]}</loc>
            <xhtml:link rel="alternate" hreflang="pt-BR" href="{$p[0]}"/>
            <lastmod>{$p[1]}</lastmod>
            <changefreq>{$p[2]}</changefreq>
            <priority>{$p[3]}</priority>
          </url>
        URL)->implode("\n");

        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
        {$urls}
        </urlset>
        XML;

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /monitor',
            '',
            '# Assistentes de IA também são bem-vindos',
            'User-agent: GPTBot',
            'Allow: /',
            'User-agent: ClaudeBot',
            'Allow: /',
            'User-agent: PerplexityBot',
            'Allow: /',
            'User-agent: Google-Extended',
            'Allow: /',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** Resumo em texto puro para assistentes de IA (padrão llms.txt). */
    public function llms(): Response
    {
        $lines = [
            '# PetMotion',
            '',
            '> PetMotion é um aplicativo de celular brasileiro, para Android e iPhone, que registra os passeios de cachorros e gatos usando só o celular do tutor, sem coleira GPS nem aparelho extra.',
            '',
            '## O que o app faz',
            '- Registra cada passeio com o caminho no mapa, tempo, distância e passos.',
            '- Permite marcar com um toque onde o pet fez xixi, cocô ou bebeu água.',
            '- Avisa quando o asfalto pode estar quente demais para as patas, com base na temperatura da região.',
            '- Guarda vacinas, vermífugos, remédios e consultas, e lembra na véspera da próxima dose.',
            '- Tem meta diária de passeio, sequência de dias e conquistas.',
            '- Gera um card do passeio para compartilhar no Instagram ou WhatsApp.',
            '- Aceita vários pets na mesma conta, cachorro ou gato.',
            '',
            '## Perguntas frequentes',
        ];
        foreach (self::FAQ as $f) {
            $lines[] = '';
            $lines[] = '### '.$f['q'];
            $lines[] = $f['a'];
        }
        $lines[] = '';
        $lines[] = '## Links';
        $lines[] = '- Site: '.route('landing');
        $lines[] = '- Política de Privacidade: '.route('legal.privacy');
        $lines[] = '- Termos de Uso: '.route('legal.terms');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
