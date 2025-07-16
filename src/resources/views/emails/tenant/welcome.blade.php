<!DOCTYPE html>
<html>
<head>
    <title>Bem-vindo(a) ao Portal do Cidadão</title>
</head>
<body>
    <h1>Bem-vindo(a)!</h1>
    <p>Você foi convidado a acessar o nosso portal. Para começar, por favor, crie uma senha clicando no botão abaixo.</p>
    <a href="{{ $url }}" style="background-color: #4CAF50; color: white; padding: 15px 25px; text-align: center; text-decoration: none; display: inline-block;">
        Criar Senha
    </a>
    <p>Este link para criação de senha irá expirar em {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutos.</p>
    <p>Se você não esperava este convite, nenhuma ação é necessária.</p>
</body>
</html>
