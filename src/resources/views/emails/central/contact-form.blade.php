<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Contato Recebido - Sistema CAC</title>
    <style>
        /* Estilos gerais para garantir a renderização */
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f7;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background-color: #059669; /* Cor principal */
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
        }
        .content {
            padding: 24px;
            color: #334155;
        }
        .content p {
            margin: 0 0 16px;
            line-height: 1.6;
        }
        .details-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .details-table td:first-child {
            font-weight: 600;
            color: #475569;
            width: 150px; /* Largura fixa para os rótulos */
        }
        .message-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 16px;
            margin-top: 8px;
        }
        .message-box p {
            margin: 0;
            white-space: pre-wrap; /* Mantém as quebras de linha da mensagem */
            font-style: italic;
            color: #475569;
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Novo Lead Recebido</h1>
            </div>
            <div class="content">
                <p>Olá, equipe do Sistema CAC!</p>
                <p>Uma nova solicitação de contato foi enviada através do formulário da página de marketing. Seguem os detalhes:</p>

                <table class="details-table">
                    <tr>
                        <td><strong>Câmara:</strong></td>
                        <td>{{ $formData['cityCouncilName'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Contato:</strong></td>
                        <td>{{ $formData['contactPerson'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>E-mail:</strong></td>
                        <td><a href="mailto:{{ $formData['email'] }}" style="color: #059669; text-decoration: none;">{{ $formData['email'] }}</a></td>
                    </tr>
                </table>

                <p style="margin-bottom: 8px;"><strong>Mensagem enviada:</strong></p>
                <div class="message-box">
                    <p>{{ $formData['message'] }}</p>
                </div>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Câmara Digital. E-mail gerado automaticamente.</p>
        </div>
    </div>
</body>
</html>
