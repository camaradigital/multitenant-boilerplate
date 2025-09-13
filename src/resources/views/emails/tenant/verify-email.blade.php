<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de E-mail</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #0D2C2A;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #43DB9E;
        }
        .content {
            padding: 40px;
            line-height: 1.6;
            color: #555;
            text-align: left;
        }
        .content p {
            margin: 0 0 15px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            background-color: #28a745;
            color: #ffffff;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            display: inline-block;
        }
        .footer {
            background-color: #f9f9f9;
            color: #888;
            padding: 20px;
            font-size: 12px;
            text-align: center;
        }
        .footer a {
            color: #28a745;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $tenantName }}</h1>
        </div>
        <div class="content">
            <h2 style="font-size: 20px; color: #333; margin-bottom: 20px;">Olá, {{ $userName }}!</h2>
            <p>Obrigado por se registrar. Por favor, clique no botão abaixo para verificar seu endereço de e-mail e ativar sua conta.</p>

            <div class="button-container">
                <a href="{{ $url }}" class="button" style="color: #ffffff;">Verificar E-mail Agora</a>
            </div>

            <p>Se você não criou uma conta, nenhuma ação adicional é necessária.</p>
            <p>Atenciosamente,<br>Equipe {{ $tenantName }}</p>
            <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
            <p style="font-size: 12px; color: #888;">Se estiver com problemas para clicar no botão "Verificar E-mail Agora", copie e cole a URL abaixo em seu navegador:</p>
            <p style="font-size: 12px; color: #888; word-break: break-all;">{{ $url }}</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $tenantName }}. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
