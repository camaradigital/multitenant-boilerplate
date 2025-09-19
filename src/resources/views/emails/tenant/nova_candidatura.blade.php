<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nova Candidatura Recebida</title>
    <style>
        /* Estilos Globais e Reset */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            width: 100% !important;
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f2f4f6;
            color: #51545E;
        }
        table {
            border-collapse: collapse;
        }
        td {
            font-family: 'Poppins', Arial, sans-serif;
            font-size: 16px;
        }
        a {
            color: #008a70;
            text-decoration: underline;
        }

        /* Layout Principal */
        .email-wrapper {
            width: 100%;
            margin: 0;
            padding: 0;
            background-color: #f2f4f6;
        }
        .email-content {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Corpo do E-mail */
        .email-body {
            width: 100%;
            margin: 0;
            padding: 0;
        }
        .email-body_inner {
            width: 570px;
            margin: 0 auto;
            padding: 0;
        }
        .content-cell {
            padding: 45px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        /* Cabeçalho */
        .header {
            padding: 25px 0;
            text-align: center;
        }
        .header a {
            font-size: 24px;
            font-weight: 700;
            color: #005a4a;
            text-decoration: none;
        }

        /* Rodapé */
        .footer {
            width: 570px;
            margin: 0 auto;
            padding: 25px 0;
            text-align: center;
        }
        .footer p {
            color: #a8aaaf;
            font-size: 12px;
        }

        /* Componentes */
        h1, h2, h3 {
            color: #00382f;
            margin-top: 0;
            font-weight: 700;
        }
        h1 { font-size: 22px; }
        h2 { font-size: 18px; }
        p {
            margin-top: 0;
            font-size: 16px;
            line-height: 1.625;
            color: #51545E;
        }
        .sub {
            font-size: 13px;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e8e8e8;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-card p {
            margin-bottom: 5px;
        }
        .info-card strong {
            color: #004a3f;
        }
        .quote {
            background-color: #f0fdf4;
            border-left: 5px solid #22c55e;
            padding: 15px;
            margin-top: 15px;
            font-style: italic;
        }

        /* Media Queries */
        @media only screen and (max-width: 600px) {
            .email-body_inner, .footer {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <!-- Cabeçalho -->
                    <tr>
                        <td class="header">
                            <a href="{{ url('/') }}" target="_blank">Câmara Digital</a>
                        </td>
                    </tr>

                    <!-- Corpo do E-mail -->
                    <tr>
                        <td class="email-body" width="100%" cellpadding="0" cellspacing="0">
                            <table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="content-cell">
                                        <h1>Nova Candidatura Recebida</h1>
                                        <p>Olá,</p>
                                        <p>Uma nova candidatura foi submetida para a vaga de <strong>{{ $vaga->titulo }}</strong>. O currículo do candidato encontra-se em anexo para sua análise.</p>

                                        <!-- Card com Detalhes da Vaga -->
                                        <table class="info-card" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td>
                                                    <h2>Detalhes da Vaga</h2>
                                                    <p><strong>Localização:</strong> {{ $vaga->localizacao }}</p>
                                                    <p><strong>Tipo de Contratação:</strong> {{ $vaga->tipo_contratacao }}</p>
                                                    @if($vaga->salario)
                                                        <p><strong>Salário:</strong> R$ {{ number_format($vaga->salario, 2, ',', '.') }}</p>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Card com Informações do Candidato -->
                                        <table class="info-card" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td>
                                                    <h2>Informações do Candidato</h2>
                                                    <p><strong>Nome:</strong> {{ $candidato->name }}</p>
                                                    <p><strong>E-mail:</strong> <a href="mailto:{{ $candidato->email }}">{{ $candidato->email }}</a></p>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Mensagem de Apresentação (se houver) -->
                                        @if($candidatura->mensagem_apresentacao)
                                            <h2>Mensagem de Apresentação</h2>
                                            <div class="quote">
                                                <p>{{ $candidatura->mensagem_apresentacao }}</p>
                                            </div>
                                        @endif

                                        <p>Atenciosamente,<br>Plataforma Câmara Digital</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Rodapé -->
                    <tr>
                        <td>
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="content-cell" align="center">
                                        <p>&copy; {{ date('Y') }} Câmara Digital. Todos os direitos reservados.</p>
                                        <p>Este é um e-mail automático. Por favor, não responda.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

