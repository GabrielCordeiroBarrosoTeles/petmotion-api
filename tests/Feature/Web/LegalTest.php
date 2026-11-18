<?php

namespace Tests\Feature\Web;

use Tests\TestCase;

class LegalTest extends TestCase
{
    public function test_privacy_page_highlights_missing_contact_fields(): void
    {
        config(['petmotion.legal.email' => null, 'petmotion.legal.dpo' => null, 'petmotion.legal.document' => null]);

        $this->get('/privacidade')
            ->assertOk()
            ->assertSee('Política de Privacidade')
            ->assertSee('Lei nº 13.709/2018')
            ->assertSee('[preencher LEGAL_EMAIL]')
            ->assertSee('[preencher LEGAL_DPO_NAME]');
    }

    public function test_privacy_page_uses_configured_contact_and_escapes_it(): void
    {
        config([
            'petmotion.legal.email' => 'dados@petmotion.app',
            'petmotion.legal.dpo' => 'Maria <b>Souza</b>',
            'petmotion.legal.document' => '00.000.000/0001-00',
        ]);

        $this->get('/privacidade')
            ->assertOk()
            ->assertSee('dados@petmotion.app')
            ->assertSee('inscrito sob o nº 00.000.000/0001-00')
            ->assertSee('Maria &lt;b&gt;Souza&lt;/b&gt;', false)
            ->assertDontSee('[preencher LEGAL_EMAIL]');
    }

    public function test_terms_page_with_and_without_city(): void
    {
        config(['petmotion.legal.city' => null, 'petmotion.legal.document' => null]);
        $this->get('/termos')->assertOk()->assertSee('Termos de Uso')->assertSee('não substitui o veterinário')->assertDontSee('à sua escolha');

        config(['petmotion.legal.city' => 'Fortaleza/CE', 'petmotion.legal.document' => '123']);
        $this->get('/termos')->assertOk()->assertSee('foro de Fortaleza/CE, à sua escolha')->assertSee('(nº 123)');
    }
}
