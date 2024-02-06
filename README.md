
# Configuração do Projeto

Este guia fornece instruções para configurar e executar o projeto usando Docker e XAMPP.

1. Subindo o Container Docker
Para iniciar o ambiente de desenvolvimento, execute o seguinte comando:

```bash
docker-compose up
```

2. Ativando o XAMPP
Ao ativar o XAMPP, é necessário garantir que a extensão php_sockets esteja habilitada. Siga os passos abaixo:

Verificar extensões PHP
Execute o seguinte comando para listar as extensões PHP instaladas:

```bash
php -m
```

## Habilitar extensão `php_sockets`

1. Navegue até o diretório de instalação do PHP no seu sistema. No Windows, o caminho pode ser semelhante a `c:\xampp\php`.
2. Acesse o diretório ext em `php\ext\`.
3. Verifique se o arquivo php_sockets.dll está presente nesse diretório.
4. Abra o arquivo `php.ini`, que está localizado na pasta do PHP.
5. Procure por `;extension=php_sockets.dll` no arquivo `php.ini`.
6. Abaixo de algumas extensões, adicione a linha: `extension=php_sockets.dll`.
7. Salve as alterações e reinicie o serviço do Apache no XAMPP.

Para mais informações sobre como habilitar o socket em PHP, consulte [esta postagem no Stack Overflow](https://stackoverflow.com/questions/1361925/how-to-enable-socket-in-php).

# Utilização

Com tudo configurado, você pode capturar mensagens e enviá-las usando os seguintes comandos:

## Capturar Mensagens

Para capturar mensagens, execute:

```bash
php receive.php
```

## Capturar Mensagens

Para capturar mensagens, execute:

```http
http://localhost/microservices/send.php?msg=ola_mundo
```

Substitua ola_mundo pela mensagem que deseja enviar.