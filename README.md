# EcommerceWeb

Este projeto é uma plataforma de e-commerce desenvolvida em PHP, com integração de APIs externas, painel administrativo, e recursos modernos para gestão de produtos, pedidos e clientes.

## Estrutura do Projeto

- **Site/**: Frontend do e-commerce, páginas de compra, login, perfil, checkout, avaliações, etc.
- **adminView/**: Painel administrativo, controle de produtos, pedidos, clientes, configurações, emissão de notas fiscais, etc.
- **includes/**: Componentes compartilhados (header, footer, scripts, estilos).
- **uploads/**: Imagens de produtos, carrossel, galeria, etc.
- **config/**: Configurações do site e banners.
- **logs/**: Arquivos de log de erros, operações e integrações.
- **vendor/**: Dependências gerenciadas pelo Composer.

## Principais Funcionalidades

- Cadastro e autenticação de clientes e administradores
- Gestão de produtos, estoque e pedidos
- Integração com APIs de frete (SuperFrete, Jadlog, MelhorEnvio)
- Emissão de Nota Fiscal eletrônica (Bling)
- Integração com Google Login
- Sistema de avaliações de produtos
- Carrinho de compras e favoritos
- Painel administrativo completo
- Logs detalhados de operações e erros

## Tecnologias Utilizadas

- **Backend:** PHP 7+, MySQL
- **Frontend:** HTML5, CSS3, JavaScript, TailwindCSS
- **APIs:** Google, Bling, SuperFrete, Jadlog, MelhorEnvio
- **Gerenciador de dependências:** Composer
- **Outros:** PHPMailer, SwiftMailer, dotenv, QR Code

## Instalação

1. Clone o repositório:
   ```sh
   git clone https://github.com/Matheus904-12/EcommerceWeb.git
   ```
2. Instale as dependências PHP:
   ```sh
   composer install
   ```
3. Configure as variáveis de ambiente de banco de dados (`DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` e `DB_DATABASE`) no ambiente PHP/servidor. Use `.env.example` como referência; ele não é carregado automaticamente pelo PHP.
4. Configure `SUPERFRETE_TOKEN` e `JADLOG_TOKEN` no ambiente se for utilizar as integrações de frete.
5. Ajuste as configurações do site em `config_site.json` e `config/banners.json`.
6. Configure as demais integrações usando credenciais no ambiente ou arquivos locais ignorados pelo Git — nunca grave credenciais reais no repositório.
7. Certifique-se de que as permissões de escrita estejam corretas para as pastas `uploads/` e `logs/`.

## Como Executar

- Acesse o diretório do projeto em seu servidor web (ex: XAMPP, WAMP, IIS, Apache).
- O acesso principal do site é feito via `Site/index.php`.
- O painel administrativo está em `adminView/`.

## Testes

- Scripts de teste para APIs de frete e integração estão disponíveis na raiz do projeto (ex: `test_curl.php`, `teste-superfrete.php`, `teste-jadlog.php`).
- Para testar login e autenticação, utilize `login.php` e `processa_login.php`.

## Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

---

**Autor:** Matheus Lucindo dos Santos

Dúvidas, sugestões ou problemas? Entre em contato pelo e-mail configurado em `config_site.json` ou via redes sociais informadas no mesmo arquivo.


## Segurança e credenciais

- Nunca versione tokens, senhas ou chaves reais.
- Configure as variáveis sensíveis no ambiente do servidor; `.env.example` serve apenas como modelo.
- Se uma credencial já foi publicada, revogue-a/renove-a no serviço de origem. Remover o valor do arquivo atual não apaga cópias presentes no histórico do Git.
