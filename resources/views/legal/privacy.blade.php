@extends('legal.layout')

@section('title', 'Política de Privacidade')
@section('description', 'Como o PetMotion coleta, usa e protege os seus dados e os do seu pet, de acordo com a LGPD.')

@section('content')
    <div class="summary">
        <h2>Em resumo</h2>
        <ul>
            <li>Sua localização só é usada <strong>durante o passeio</strong> e para checar o clima antes de sair. O app não rastreia você em segundo plano.</li>
            <li>Os passeios, as fotos e os registros de saúde do seu pet servem para mostrar o histórico para você. <strong>Não vendemos seus dados</strong> e não mostramos anúncios.</li>
            <li>Se você compartilhar o pet com um <strong>passeador</strong>, você vê o trajeto do passeio dele <strong>ao vivo</strong>, e ele vê só o nome e a foto do pet, nunca a saúde.</li>
            <li>Você pode apagar um passeio, um pet ou a <strong>sua conta inteira</strong> pelo próprio app, quando quiser.</li>
            <li>Dúvidas ou pedidos sobre seus dados: {!! $contact !!}.</li>
        </ul>
    </div>

    <section id="quem-somos">
        <h2>Quem somos</h2>
        <p>Esta política explica como o <strong>PetMotion</strong> (o aplicativo, este site e o painel da equipe) trata dados pessoais, de acordo com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018, a "LGPD").</p>
        <p>O controlador dos dados é {!! $fill($legal['name'], 'preencher LEGAL_NAME') !!}{{ $legal['document'] ? ', inscrito sob o nº '.$legal['document'] : '' }}. O encarregado pelo tratamento de dados (DPO) é {!! $fill($legal['dpo'], 'preencher LEGAL_DPO_NAME') !!}, que pode ser contatado pelo e-mail {!! $contact !!}.</p>
    </section>

    <section id="dados">
        <h2>Quais dados coletamos</h2>
        <p>Coletamos apenas o necessário para o app funcionar. Veja o que é e de onde vem:</p>
        <div class="table">
            <table>
                <thead><tr><th>Dado</th><th>Quando é coletado</th><th>Para quê</th></tr></thead>
                <tbody>
                    <tr><td><strong>Nome, e-mail e senha</strong></td><td>Ao criar a conta</td><td>Identificar você e proteger o acesso. A senha é guardada de forma criptografada e ninguém da equipe consegue vê-la.</td></tr>
                    <tr><td><strong>Dados do pet</strong>: nome, espécie, raça, data de nascimento, peso, meta diária e foto</td><td>Quando você cadastra ou edita um pet</td><td>Montar o perfil do pet, a meta de passeio e o card para compartilhar.</td></tr>
                    <tr><td><strong>Localização durante o passeio</strong>: o caminho percorrido, horário de início e fim, distância</td><td>Somente entre "Começar" e "Finalizar" um passeio, com o app aberto</td><td>Desenhar o trajeto no mapa e calcular distância e ritmo.</td></tr>
                    <tr><td><strong>Marcações do passeio</strong>: xixi, cocô e água, com o local e o horário</td><td>Quando você toca nos botões durante o passeio</td><td>Mostrar no mapa e no histórico, por exemplo para conversar com o veterinário.</td></tr>
                    <tr><td><strong>Passos</strong></td><td>Durante o passeio, pelo sensor de movimento do celular</td><td>Mostrar quantos passos vocês deram.</td></tr>
                    <tr><td><strong>Localização para o clima</strong></td><td>Ao abrir a tela inicial e antes de um passeio</td><td>Consultar a temperatura da sua região e estimar se o asfalto está quente demais.</td></tr>
                    <tr><td><strong>Convite de passeador</strong>: um código de 8 caracteres e o vínculo entre o pet e o passeador</td><td>Quando o pai ou a mãe de pet convida alguém para passear com o pet (o convite vale 48 horas e só pode ser usado uma vez)</td><td>Permitir que o passeador veja o nome e a foto do pet e registre passeios com ele. O passeador não vê registros de saúde.</td></tr>
                    <tr><td><strong>Passeio ao vivo</strong>: posição, tempo, distância e marcações enquanto o passeio acontece</td><td>Somente durante um passeio feito por um passeador, com o app aberto</td><td>Mostrar ao pai ou à mãe do pet, em tempo real, por onde o pet está passeando. Ninguém além de quem cadastrou o pet recebe essa localização.</td></tr>
                    <tr><td><strong>Registros de saúde</strong>: vacinas, vermífugos, remédios, consultas e observações</td><td>Quando você registra um cuidado</td><td>Montar a carteirinha digital e lembrar você da próxima dose.</td></tr>
                    <tr><td><strong>Dados técnicos</strong>: endereço IP e data/hora de acesso</td><td>Em cada uso do app e do site</td><td>Segurança, prevenção de abuso e correção de erros.</td></tr>
                </tbody>
            </table>
        </div>
        <h3>O que não coletamos</h3>
        <ul>
            <li>Não acessamos sua localização com o app fechado ou fora de um passeio.</li>
            <li>Não acessamos seus contatos, mensagens, microfone nem outras fotos além da que você escolher.</li>
            <li>Não usamos ferramentas de publicidade nem de rastreamento de comportamento.</li>
        </ul>
    </section>

    <section id="permissoes">
        <h2>Permissões do celular</h2>
        <p>O app pede permissão ao sistema do celular antes de usar cada recurso, e você pode negar ou revogar a qualquer momento nas configurações do aparelho:</p>
        <ul>
            <li><strong>Localização</strong>: para o trajeto e o clima. Sem ela, você ainda pode usar a carteirinha de saúde.</li>
            <li><strong>Sensor de movimento</strong>: para contar os passos.</li>
            <li><strong>Câmera e fotos</strong>: só quando você escolhe tirar ou selecionar a foto do pet.</li>
            <li><strong>Notificações</strong>: para lembretes de vacina e do passeio em andamento. Os lembretes são criados no próprio celular.</li>
        </ul>
    </section>

    <section id="bases-legais">
        <h2>Por que podemos usar seus dados</h2>
        <p>Cada uso tem uma base legal prevista no art. 7º da LGPD:</p>
        <ul>
            <li><strong>Execução do contrato</strong> (art. 7º, V): conta, pets, passeios, registros de saúde e lembretes são o próprio serviço que você pediu ao se cadastrar.</li>
            <li><strong>Consentimento</strong> (art. 7º, I): uso da localização, da câmera e das notificações, dado por meio das permissões do celular, que você pode retirar quando quiser.</li>
            <li><strong>Legítimo interesse</strong> (art. 7º, IX): segurança da conta, prevenção de fraudes, correção de erros e estatísticas gerais de uso (por exemplo, total de quilômetros da comunidade, sem identificar ninguém).</li>
            <li><strong>Cumprimento de obrigação legal</strong> (art. 7º, II): guarda de registros de acesso exigida pelo Marco Civil da Internet (Lei nº 12.965/2014).</li>
        </ul>
    </section>

    <section id="compartilhamento">
        <h2>Com quem compartilhamos</h2>
        <p><strong>Não vendemos nem alugamos seus dados.</strong> Eles só passam por terceiros quando isso é necessário para o app funcionar:</p>
        <div class="table">
            <table>
                <thead><tr><th>Quem</th><th>O que recebe</th><th>Por quê</th></tr></thead>
                <tbody>
                    <tr><td><strong>Open-Meteo</strong> (serviço de clima)</td><td>Coordenadas aproximadas da sua região</td><td>Informar a temperatura atual. Não recebe seu nome, e-mail nem dados do pet.</td></tr>
                    <tr><td><strong>Apple Maps ou Google Maps</strong></td><td>A área do mapa exibida na tela</td><td>Desenhar o mapa no celular, conforme o sistema do aparelho.</td></tr>
                    <tr><td><strong>Google Fonts e CDNs de bibliotecas</strong></td><td>Endereço IP de quem visita o site</td><td>Carregar as fontes e os recursos visuais do site.</td></tr>
                    <tr><td><strong>Pai ou mãe do pet</strong> (quando um passeador faz o passeio)</td><td>O trajeto, o horário, a distância e as marcações do passeio, ao vivo e depois no histórico</td><td>Acompanhar o passeio do pet. Passeios feitos por você com um pet que alguém compartilhou aparecem para quem cadastrou o pet.</td></tr>
                    <tr><td><strong>Passeador</strong> (quando você compartilha um pet)</td><td>Nome e foto do pet e os passeios que ele mesmo fez</td><td>Passear com o pet. Não recebe registros de saúde nem passeios feitos por outras pessoas.</td></tr>
                    <tr><td><strong>Provedor de hospedagem</strong></td><td>Os dados armazenados no servidor</td><td>Manter o serviço no ar, sob contrato e com dever de sigilo.</td></tr>
                    <tr><td><strong>Equipe autorizada do PetMotion</strong></td><td>Nomes, e-mails, pets e passeios, no painel interno</td><td>Suporte, segurança e acompanhamento do serviço. O acesso é restrito por senha.</td></tr>
                    <tr><td><strong>Autoridades</strong></td><td>O que for exigido</td><td>Somente mediante ordem judicial ou obrigação legal.</td></tr>
                </tbody>
            </table>
        </div>
        <p>Quando você usa o botão <strong>Compartilhar</strong>, a imagem do passeio é enviada para o app que você escolher (Instagram, WhatsApp etc.). A partir daí, vale a política daquele app.</p>
        <h3>Transferência internacional</h3>
        <p>Alguns desses serviços podem processar dados fora do Brasil. Nesses casos, adotamos as salvaguardas previstas no art. 33 da LGPD e enviamos o mínimo necessário.</p>
    </section>

    <section id="guarda">
        <h2>Por quanto tempo guardamos</h2>
        <ul>
            <li><strong>Conta, pets, passeios e saúde</strong>: enquanto sua conta existir. Quando você exclui um passeio ou um pet, ele é apagado na hora.</li>
            <li><strong>Exclusão da conta</strong>: apaga imediatamente todos os seus pets, passeios, marcações e registros de saúde.</li>
            <li><strong>Registros de acesso</strong>: quando a lei exigir (art. 15 do Marco Civil da Internet), guardados por 6 meses e depois descartados.</li>
            <li><strong>Passeio ao vivo</strong>: os dados em tempo real ficam guardados só de forma temporária, por até 3 horas, e 10 minutos depois que o passeio termina. Depois disso são descartados. O passeio finalizado continua no histórico até você apagá-lo.</li>
            <li><strong>Convites</strong>: o código vale 48 horas. Você pode remover um passeador quando quiser.</li>
            <li><strong>Clima</strong>: a consulta fica em cache por 10 minutos, sem identificação do usuário.</li>
        </ul>
    </section>

    <section id="seguranca">
        <h2>Como protegemos</h2>
        <ul>
            <li>Senhas guardadas com criptografia de mão única (hash), sem possibilidade de leitura.</li>
            <li>Acesso ao app por token individual, guardado na área segura do seu celular (Keychain no iPhone, Keystore no Android).</li>
            <li>Comunicação criptografada (HTTPS) no ambiente de produção.</li>
            <li>Cada pessoa só acessa os próprios pets e passeios. O painel interno exige login de administrador.</li>
            <li>Limite de tentativas de login para dificultar ataques.</li>
        </ul>
        <p>Nenhum sistema é 100% invulnerável. Se acontecer um incidente de segurança que possa trazer risco a você, avisaremos você e a Autoridade Nacional de Proteção de Dados (ANPD), como manda o art. 48 da LGPD.</p>
    </section>

    <section id="direitos">
        <h2>Seus direitos</h2>
        <p>Pelo art. 18 da LGPD, você pode pedir a qualquer momento:</p>
        <ul>
            <li>confirmação de que tratamos seus dados e acesso a eles;</li>
            <li>correção de dados incompletos ou errados (a maioria você mesmo edita no app);</li>
            <li>anonimização, bloqueio ou eliminação de dados desnecessários;</li>
            <li>portabilidade dos seus dados para outro serviço;</li>
            <li>eliminação dos dados tratados com base no consentimento;</li>
            <li>informação sobre com quem compartilhamos;</li>
            <li>revogação do consentimento, pelas permissões do celular ou falando com a gente.</li>
        </ul>
        <h3>Como pedir</h3>
        <p><strong>Excluir a conta:</strong> no app, vá em <em>Perfil</em> e toque em <em>Excluir minha conta</em>. Para os outros pedidos, escreva para {!! $contact !!}. Respondemos em até 15 dias. Você também pode reclamar diretamente na ANPD pelo site <a href="https://www.gov.br/anpd" rel="noopener">gov.br/anpd</a>.</p>
    </section>

    <section id="menores">
        <h2>Crianças e adolescentes</h2>
        <p>O PetMotion é feito para maiores de 18 anos. Menores de idade podem usar com o consentimento e o acompanhamento dos pais ou responsáveis, que respondem pela conta, conforme o art. 14 da LGPD.</p>
    </section>

    <section id="cookies">
        <h2>Cookies no site</h2>
        <p>Este site usa apenas <strong>cookies essenciais</strong>: um cookie de sessão e um de proteção contra fraudes em formulários (CSRF). Eles são necessários para o site funcionar e expiram em poucas horas. Não usamos cookies de publicidade nem de análise de comportamento.</p>
    </section>

    <section id="mudancas">
        <h2>Mudanças nesta política</h2>
        <p>Podemos atualizar esta política quando o app ganhar novas funções ou a lei mudar. A data no topo indica a versão atual. Se a mudança for importante, avisaremos por e-mail ou pelo app antes de ela valer.</p>
    </section>

    <section id="contato">
        <h2>Fale com a gente</h2>
        <p>Encarregado de dados: {!! $fill($legal['dpo'], 'preencher LEGAL_DPO_NAME') !!}<br>E-mail: {!! $contact !!}</p>
        <p>Veja também os <a href="{{ route('legal.terms') }}">Termos de uso</a>.</p>
    </section>
@endsection
