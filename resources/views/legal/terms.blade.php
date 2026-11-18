@extends('legal.layout')

@section('title', 'Termos de Uso')
@section('description', 'As regras para usar o PetMotion: sua conta, o que o app faz e não faz, e os direitos de cada lado.')

@section('content')
    <div class="summary">
        <h2>Em resumo</h2>
        <ul>
            <li>O PetMotion ajuda você a registrar passeios e cuidados do seu pet. <strong>Ele não substitui o veterinário.</strong></li>
            <li>O alerta de asfalto quente e as distâncias são <strong>estimativas</strong>. Na dúvida, proteja as patinhas.</li>
            <li>Suas fotos e registros continuam sendo seus. Só usamos para fazer o app funcionar.</li>
            <li>Você pode parar de usar e excluir sua conta quando quiser.</li>
        </ul>
    </div>

    <section id="aceite">
        <h2>Aceite</h2>
        <p>Estes Termos de Uso são um acordo entre você e {!! $fill($legal['name'], 'preencher LEGAL_NAME') !!}{{ $legal['document'] ? ' (nº '.$legal['document'].')' : '' }} para uso do aplicativo PetMotion, deste site e dos serviços ligados a eles. Ao criar uma conta ou usar o app, você declara que leu e concorda com estes termos e com a <a href="{{ route('legal.privacy') }}">Política de Privacidade</a>.</p>
        <p>Se não concordar, não use o serviço.</p>
    </section>

    <section id="servico">
        <h2>O que o PetMotion faz</h2>
        <p>O PetMotion é um aplicativo para tutores de cães e gatos que permite:</p>
        <ul>
            <li>registrar passeios com o caminho no mapa, tempo, distância e passos;</li>
            <li>marcar onde o pet fez xixi, cocô ou bebeu água;</li>
            <li>receber uma estimativa da temperatura do asfalto antes de sair;</li>
            <li>guardar vacinas, vermífugos, remédios e consultas, com lembretes;</li>
            <li>acompanhar metas, sequência de dias e conquistas;</li>
            <li>gerar uma imagem do passeio para compartilhar.</li>
        </ul>
        <p>O app está em <strong>fase de demonstração</strong>. Funções podem mudar, ser adicionadas ou retiradas para melhorar o serviço.</p>
    </section>

    <section id="conta">
        <h2>Sua conta</h2>
        <ul>
            <li>Você precisa ter 18 anos ou mais. Menores de idade só podem usar com o consentimento e o acompanhamento dos pais ou responsáveis.</li>
            <li>Informe dados verdadeiros e mantenha seu e-mail atualizado.</li>
            <li>Sua senha é pessoal. Você é responsável pelo que acontece na sua conta, e deve nos avisar se suspeitar de uso indevido.</li>
            <li>Cada conta é de uma pessoa. Você pode cadastrar quantos pets quiser.</li>
        </ul>
    </section>

    <section id="saude">
        <h2>O app não substitui o veterinário</h2>
        <div class="note">O PetMotion é uma ferramenta de organização e incentivo. Ele não faz diagnóstico, não indica tratamento e não substitui a avaliação de um médico-veterinário.</div>
        <ul>
            <li><strong>Asfalto quente</strong>: a temperatura do chão é uma estimativa feita a partir do clima da sua região, e o chão real pode estar mais quente ou mais frio. Antes de sair, encoste a mão no chão por alguns segundos e evite os horários de sol forte.</li>
            <li><strong>Metas de passeio</strong>: a meta diária é escolhida por você. A quantidade ideal de exercício depende da raça, da idade e da saúde do pet, então converse com o veterinário.</li>
            <li><strong>Lembretes de saúde</strong>: dependem das datas que você registra e das notificações estarem ativas no celular. Eles ajudam, mas a responsabilidade pelo calendário de vacinas e remédios continua sendo do tutor.</li>
            <li>Em caso de emergência, procure imediatamente um veterinário.</li>
        </ul>
    </section>

    <section id="precisao">
        <h2>Precisão dos dados</h2>
        <p>Distância, trajeto, ritmo e passos são calculados pelo GPS e pelos sensores do seu celular. A precisão varia conforme o aparelho, o sinal, prédios, árvores e o tempo. Os números servem como referência e podem ter diferenças em relação à realidade.</p>
        <p>O <strong>Modo apresentação</strong>, disponível no Perfil, simula um passeio para demonstração. Os passeios feitos nesse modo não representam um trajeto real.</p>
    </section>

    <section id="uso">
        <h2>Uso correto</h2>
        <p>Ao usar o PetMotion, você se compromete a não:</p>
        <ul>
            <li>usar o app de forma que coloque você, seu pet ou outras pessoas em risco. Por exemplo, mexer no celular enquanto atravessa a rua ou dirige;</li>
            <li>enviar fotos ou textos ilegais, ofensivos ou que violem direitos de terceiros;</li>
            <li>tentar acessar contas, dados ou áreas do sistema que não são suas;</li>
            <li>sobrecarregar, copiar, fazer engenharia reversa ou automatizar o uso do serviço sem autorização;</li>
            <li>usar o app para fins comerciais sem a nossa autorização por escrito.</li>
        </ul>
        <p>Se essas regras forem descumpridas, podemos suspender ou encerrar a conta, avisando você sempre que possível.</p>
    </section>

    <section id="conteudo">
        <h2>Suas fotos e registros</h2>
        <p>As fotos, textos e registros que você coloca no app continuam sendo seus. Você nos dá uma permissão limitada, gratuita e revogável para armazenar, exibir e processar esse conteúdo <strong>apenas para fazer o app funcionar</strong> para você. Por exemplo, mostrar a foto do pet no card do passeio.</p>
        <p>Não usamos suas fotos em publicidade nem as mostramos para outros usuários. Ao excluir o conteúdo ou a conta, essa permissão termina.</p>
    </section>

    <section id="propriedade">
        <h2>Marca e propriedade intelectual</h2>
        <p>O nome PetMotion, a marca, o visual, os textos do app e do site e o código são protegidos por lei. Você pode usar o app normalmente, mas não pode copiar, modificar ou distribuir esses elementos sem autorização. A imagem do passeio que você gera pode ser compartilhada livremente para uso pessoal.</p>
    </section>

    <section id="disponibilidade">
        <h2>Disponibilidade</h2>
        <p>Trabalhamos para manter o PetMotion funcionando, mas ele pode ficar fora do ar para manutenção, atualização ou por falhas de internet, de servidores ou de serviços de terceiros, como o mapa e o clima. Sempre que possível, avisaremos sobre paradas programadas.</p>
    </section>

    <section id="responsabilidade">
        <h2>Responsabilidades</h2>
        <p>Respondemos pelos danos causados por falhas do serviço nos termos do Código de Defesa do Consumidor (Lei nº 8.078/1990). Nenhuma regra destes termos afasta os seus direitos como consumidor.</p>
        <p>Não somos responsáveis por:</p>
        <ul>
            <li>decisões sobre a saúde do pet tomadas só com base no app, sem orientação veterinária;</li>
            <li>acidentes durante o passeio, que é feito sob a guarda e a responsabilidade do tutor;</li>
            <li>problemas causados por celular sem bateria, sem sinal, com permissões desativadas ou com sistema desatualizado;</li>
            <li>o conteúdo e as políticas de apps de terceiros para onde você compartilha seus passeios.</li>
        </ul>
    </section>

    <section id="encerramento">
        <h2>Encerramento</h2>
        <p>Você pode parar de usar o PetMotion a qualquer momento e excluir sua conta em <em>Perfil</em>, <em>Excluir minha conta</em>. A exclusão apaga seus pets, passeios e registros, como explica a <a href="{{ route('legal.privacy') }}">Política de Privacidade</a>.</p>
        <p>Também podemos encerrar o serviço. Nesse caso, avisaremos com antecedência razoável para que você possa salvar o que quiser.</p>
    </section>

    <section id="alteracoes">
        <h2>Mudanças nos termos</h2>
        <p>Podemos atualizar estes termos. A data no topo indica a versão atual. Mudanças importantes serão avisadas por e-mail ou pelo app antes de valerem. Se continuar usando depois disso, você concorda com a nova versão. Se não concordar, pode excluir a conta.</p>
    </section>

    <section id="lei">
        <h2>Lei e foro</h2>
        <p>Estes termos seguem as leis do Brasil. Qualquer disputa poderá ser resolvida no foro do seu domicílio, como garante o Código de Defesa do Consumidor{{ $legal['city'] ? ', ou no foro de '.$legal['city'].', à sua escolha' : '' }}.</p>
        <p>Antes de qualquer medida, fale com a gente pelo e-mail {!! $contact !!}. A maioria das questões se resolve rápido.</p>
    </section>
@endsection
