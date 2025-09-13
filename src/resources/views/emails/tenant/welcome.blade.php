<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bem-vindo(a)! Defina Sua Senha</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap');
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            background-color: #f4f4f4;
            font-family: 'Poppins', sans-serif;
            color: #444444;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #dddddd;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .email-header {
            background-color: #059669; /* Esmeralda */
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .email-content {
            padding: 30px 40px;
            line-height: 1.7;
            font-size: 16px;
        }
        .email-content .greeting {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .button-container {
            text-align: center;
            padding: 20px 0;
        }
        .button {
            background-color: #10B981; /* Verde mais claro */
            color: #ffffff;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            font-size: 16px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #059669;
        }
        .info {
            font-size: 14px;
            color: #777777;
            text-align: center;
            margin-top: 20px;
        }
        .email-footer {
            background-color: #f8f9fa;
            color: #888888;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid #eeeeee;
        }
        .email-footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Bem-vindo(a)!</h1>
        </div>
        <div class="email-content">
            <p class="greeting">Olá, {{ $userName ?? 'Administrador' }}!</p>
            <p>Sua conta de administrador para o portal da <strong>{{ $tenantName }}</strong> foi criada com sucesso. Para começar, por favor, defina uma senha de acesso clicando no botão abaixo.</p>
            <div class="button-container">
                <a href="{{ $url }}" class="button">Criar Minha Senha</a>
            </div>
            <p class="info">Este link para criação de senha irá expirar em {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutos.</p>
            <p class="info">Se você não esperava este convite, nenhuma ação é necessária.</p>
        </div>
        <div class="email-footer">
            <p>© {{ date('Y') }} {{ $tenantName }}. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
