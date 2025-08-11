-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: 186.202.152.117
-- Generation Time: 11-Ago-2025 às 17:01
-- Versão do servidor: 5.7.32-35-log
-- PHP Version: 5.6.40-0+deb8u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `goldlar_2025`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor') DEFAULT 'editor',
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `role`, `reset_token`, `token_expiry`, `created_at`) VALUES
(6, 'Vinicius', '$2y$10$za933mno2Z1AY20mAtqT..FQbhz5EznlhDhFA0d4OkmPDVAVvEN5C', 'admin', NULL, NULL, '2025-05-15 21:10:16'),
(7, 'Vini', '$2y$10$PWghFw9coU4ntQZa57f8LeL24oDPZNDR5VZ17cgpj4xlwaoUODFPq', 'admin', NULL, NULL, '2025-05-19 20:25:41'),
(8, 'MatheusSuporte', '$2y$10$xlLOhXUZ.cQb8WcMqYR6q.Ovw/AG.Zsw3J8ph85hmTybmZ2BDMUgC', 'admin', NULL, NULL, '2025-07-17 02:31:54'),
(9, 'Ingrid', '$2y$10$EOO4SGP0uO34A4NmMkBadeLfobEhIQgZi9moBLzA28aHB0Hl180nO', 'admin', NULL, NULL, '2025-07-17 17:38:52');

-- --------------------------------------------------------

--
-- Estrutura da tabela `bling_configuracoes`
--

CREATE TABLE `bling_configuracoes` (
  `id` int(11) NOT NULL,
  `chave` varchar(50) NOT NULL COMMENT 'Nome da configuração',
  `valor` text NOT NULL COMMENT 'Valor da configuração',
  `descricao` text COMMENT 'Descrição da configuração',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Configurações da integração com o Bling';

--
-- Extraindo dados da tabela `bling_configuracoes`
--

INSERT INTO `bling_configuracoes` (`id`, `chave`, `valor`, `descricao`, `created_at`, `updated_at`) VALUES
(1, 'loja_id', '205510600', 'ID da loja no Bling', '2025-07-31 22:16:41', '2025-07-31 22:16:41'),
(2, 'api_url', 'https://api.bling.com.br/Api/v3', 'URL base da API do Bling', '2025-07-31 22:16:41', '2025-07-31 22:16:41'),
(3, 'default_ncm', '70139110', 'Código NCM padrão para produtos', '2025-07-31 22:16:41', '2025-07-31 22:16:41'),
(4, 'default_cfop', '5102', 'Código CFOP padrão para vendas', '2025-07-31 22:16:41', '2025-07-31 22:16:41');

-- --------------------------------------------------------

--
-- Estrutura da tabela `bling_integration_log`
--

CREATE TABLE `bling_integration_log` (
  `id` int(11) NOT NULL,
  `tipo` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `operacao` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `status` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `id_referencia` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `payload` text COLLATE latin1_general_ci,
  `resposta` text COLLATE latin1_general_ci,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `bling_pedidos_contador`
--

CREATE TABLE `bling_pedidos_contador` (
  `id` int(11) NOT NULL,
  `numero` int(11) NOT NULL DEFAULT '1',
  `ultima_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Extraindo dados da tabela `bling_pedidos_contador`
--

INSERT INTO `bling_pedidos_contador` (`id`, `numero`, `ultima_atualizacao`) VALUES
(1, 1, '2025-07-31 19:38:24');

-- --------------------------------------------------------

--
-- Estrutura da tabela `blog`
--

CREATE TABLE `blog` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `blog_comentarios`
--

CREATE TABLE `blog_comentarios` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `comentario` text NOT NULL,
  `data_comentario` datetime NOT NULL,
  `status` enum('aprovado','pendente','spam') NOT NULL DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `resumo` text NOT NULL,
  `conteudo` longtext NOT NULL,
  `imagem` varchar(255) NOT NULL,
  `data_publicacao` datetime NOT NULL,
  `autor` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `carousel_images`
--

CREATE TABLE `carousel_images` (
  `id` int(11) NOT NULL,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordem` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `carousel_images`
--

INSERT INTO `carousel_images` (`id`, `imagem`, `titulo`, `descricao`, `link`, `ordem`, `status`, `created_at`, `updated_at`) VALUES
(6, 'banner_6877e6198ba69.png', '', '', '', 1, 1, '2025-06-25 22:03:26', '2025-07-16 23:04:30'),
(7, 'banner_6878146bb360c.png', '', '', '', 2, 1, '2025-06-25 22:03:59', '2025-07-16 21:06:51');

-- --------------------------------------------------------

--
-- Estrutura da tabela `carrinho`
--

CREATE TABLE `carrinho` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `carrinho`
--

INSERT INTO `carrinho` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(9, 23, 53, 2, '2025-05-14 20:07:27', '2025-05-14 20:44:35');

-- --------------------------------------------------------

--
-- Estrutura da tabela `checkout_data`
--

CREATE TABLE `checkout_data` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_value` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `checkout_fields`
--

CREATE TABLE `checkout_fields` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_value` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `checkout_fields`
--

INSERT INTO `checkout_fields` (`id`, `session_id`, `field_name`, `field_value`, `created_at`, `updated_at`) VALUES
(1, 'checkout_1743469980715_acst73zb3ym', 'shipping_address', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos - SP', '2025-04-01 23:56:35', '2025-04-03 22:19:10'),
(2, 'checkout_1743469980715_acst73zb3ym', 'shipping_number', '111', '2025-04-01 23:56:35', '2025-04-03 22:19:10'),
(3, 'checkout_1743469980715_acst73zb3ym', 'shipping_complement', '2', '2025-04-01 23:56:35', '2025-04-03 22:19:10'),
(4, 'checkout_1743469980715_acst73zb3ym', 'shipping_cep', '08501-300', '2025-04-01 23:56:35', '2025-04-03 22:19:10'),
(5, 'checkout_1743469980715_acst73zb3ym', 'shipping_phone', '(11) 97166-6817', '2025-04-01 23:56:35', '2025-04-03 22:19:10'),
(6, 'checkout_1743469980715_acst73zb3ym', 'payment_method', 'pix', '2025-04-01 23:56:35', '2025-04-03 22:19:10');

-- --------------------------------------------------------

--
-- Estrutura da tabela `checkout_sessions`
--

CREATE TABLE `checkout_sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `checkout_sessions`
--

INSERT INTO `checkout_sessions` (`id`, `session_id`, `data`, `created_at`, `updated_at`) VALUES
(1, 'checkout_1743469980715_acst73zb3ym', '{\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos - SP\",\"shipping_number\":\"111\",\"shipping_complement\":\"2\",\"shipping_cep\":\"08501-300\",\"shipping_phone\":\"(11) 97166-6817\",\"payment_method\":\"pix\"}', '2025-04-01 23:56:35', '2025-04-03 22:19:10');

-- --------------------------------------------------------

--
-- Estrutura da tabela `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `id_externo_bling` bigint(20) DEFAULT NULL,
  `numeroDocumento` varchar(20) DEFAULT NULL COMMENT 'CPF/CNPJ',
  `endereco` varchar(255) DEFAULT NULL COMMENT 'Endereço',
  `numero` varchar(20) DEFAULT NULL COMMENT 'Número',
  `bairro` varchar(100) DEFAULT NULL COMMENT 'Bairro',
  `municipio` varchar(100) DEFAULT NULL COMMENT 'Município',
  `uf` varchar(2) DEFAULT NULL COMMENT 'UF',
  `cep` varchar(10) DEFAULT NULL COMMENT 'CEP'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_name`, `email`, `phone`, `id_externo_bling`, `numeroDocumento`, `endereco`, `numero`, `bairro`, `municipio`, `uf`, `cep`) VALUES
(1, 'Cliente Teste', 'teste@email.com', '11999999999', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Maria Oliveira', 'maria@email.com', '11988888888', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Carlos Souza', 'carlos@email.com', '11977777777', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'heloisa', 'heloisa@gmail.com', '(11) 99999-6666', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Matheus Lucindo', 'matheuslucindo904@gmail.com', '11971666817', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'Matheus Programador', 'matheusprogramador760@gmail.com', '11971666817', 17564443944, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `entregas`
--

CREATE TABLE `entregas` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `status` enum('pendente','em andamento','entregue','cancelado') DEFAULT 'pendente',
  `data_envio` timestamp NULL DEFAULT NULL,
  `data_entrega` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `favoritos`
--

CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `description` text,
  `category` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `itens_pedido`
--

CREATE TABLE `itens_pedido` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL COMMENT 'Código do produto',
  `ncm` varchar(8) DEFAULT '70139110' COMMENT 'Código NCM do produto',
  `cfop` varchar(4) DEFAULT '5102' COMMENT 'Código CFOP',
  `unidade` varchar(10) DEFAULT 'UN' COMMENT 'Unidade'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `itens_rastreados`
--

CREATE TABLE `itens_rastreados` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `codigo_rastreio` varchar(100) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'processando',
  `data_compra` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `imagem` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `logins`
--

CREATE TABLE `logins` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `logins`
--

INSERT INTO `logins` (`id`, `usuario_id`, `data_hora`, `ip`) VALUES
(21, 26, '2025-05-19 21:12:46', '189.13.162.214'),
(22, 26, '2025-05-20 03:53:02', '189.13.162.214'),
(23, 26, '2025-05-20 03:53:03', '189.13.162.214'),
(24, 29, '2025-05-29 19:43:41', '143.137.249.72'),
(25, 32, '2025-06-26 21:47:17', '177.172.114.220'),
(26, 31, '2025-06-27 17:17:17', '177.172.114.220'),
(27, 34, '2025-06-27 18:38:23', '177.172.114.220'),
(28, 35, '2025-06-27 18:40:28', '177.172.114.220'),
(29, 36, '2025-07-07 15:17:33', '191.39.137.69'),
(30, 36, '2025-07-07 16:21:17', '191.39.137.69'),
(31, 36, '2025-07-07 16:21:53', '191.39.137.69'),
(32, 36, '2025-07-07 16:22:30', '191.39.137.69'),
(33, 36, '2025-07-09 23:02:53', '191.193.196.40'),
(34, 36, '2025-07-10 14:24:31', '201.22.223.15'),
(35, 36, '2025-07-10 14:37:47', '201.22.223.15'),
(36, 36, '2025-07-12 00:40:16', '191.193.196.40'),
(37, 36, '2025-07-12 00:40:17', '191.193.196.40'),
(38, 36, '2025-07-15 19:09:37', '200.162.255.155'),
(39, 36, '2025-07-15 22:03:06', '170.81.201.162'),
(40, 36, '2025-07-15 23:14:51', '191.193.196.40'),
(41, 36, '2025-07-17 11:42:33', '201.47.217.32'),
(42, 36, '2025-07-17 17:51:34', '201.22.220.112'),
(43, 36, '2025-07-18 16:45:07', '186.214.193.152'),
(44, 36, '2025-07-22 22:28:20', '170.81.201.201'),
(45, 36, '2025-07-22 23:15:36', '187.34.108.71'),
(46, 36, '2025-07-31 20:37:55', '189.111.60.158'),
(47, 31, '2025-08-01 02:00:02', '191.13.148.203'),
(48, 31, '2025-08-01 20:25:18', '191.13.148.203'),
(49, 34, '2025-08-02 00:15:21', '191.13.148.203'),
(50, 41, '2025-08-06 23:09:33', '152.244.72.110');

-- --------------------------------------------------------

--
-- Estrutura da tabela `nfe_logs`
--

CREATE TABLE `nfe_logs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `event_type` varchar(50) COLLATE latin1_general_ci NOT NULL COMMENT 'Tipo de evento (emissão, consulta, cancelamento, etc)',
  `status` varchar(50) COLLATE latin1_general_ci NOT NULL COMMENT 'Status da operação (sucesso, erro)',
  `message` text COLLATE latin1_general_ci COMMENT 'Mensagem detalhada do evento',
  `request_data` text COLLATE latin1_general_ci COMMENT 'Dados enviados na requisição',
  `response_data` text COLLATE latin1_general_ci COMMENT 'Dados recebidos na resposta',
  `provider` varchar(20) COLLATE latin1_general_ci DEFAULT 'bling',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Extraindo dados da tabela `nfe_logs`
--

INSERT INTO `nfe_logs` (`id`, `order_id`, `event_type`, `status`, `message`, `request_data`, `response_data`, `provider`, `created_at`) VALUES
(1, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 01:41:24'),
(2, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 01:41:48'),
(3, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 02:29:18'),
(4, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 02:31:33'),
(5, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido. Por favor, verifique os dados e tente novamente.', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 03:40:10'),
(6, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido. Por favor, verifique os dados e tente novamente.', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 03:41:05'),
(7, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido. Por favor, verifique os dados e tente novamente.', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 12:31:52'),
(8, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido. Por favor, verifique os dados e tente novamente.', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 12:32:41'),
(9, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido. Por favor, verifique os dados e tente novamente.', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 12:37:36'),
(10, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido. Por favor, verifique os dados e tente novamente.', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 12:38:25'),
(11, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido. Por favor, verifique os dados e tente novamente.', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 12:39:04'),
(12, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido. Por favor, verifique os dados e tente novamente.', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 12:39:19'),
(13, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido. Por favor, verifique os dados e tente novamente.', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 12:39:36'),
(14, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido. Por favor, verifique os dados e tente novamente.', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-24 21:23:41\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 12:43:25'),
(15, 113, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Erro desconhecido. Por favor, verifique os dados e tente novamente.', '{\"id\":113,\"user_id\":23,\"total\":\"1368.00\",\"subtotal\":\"1440.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"enviado\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG087BBF5F\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-24 21:17:00\",\"updated_at\":\"2025-07-25 12:43:51\",\"visualizado\":1,\"created_at\":\"2025-07-24 21:17:00\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 12:45:31'),
(16, 114, 'emissao', 'erro', 'Erro na API da Betel Tecnologia: Controller class NfeController could not be found.', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'betel', '2025-07-25 13:03:06'),
(17, 114, '', 'error', 'Erro na API da Betel Tecnologia: Controller class V1Controller could not be found.', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 13:37:10'),
(18, 114, '', 'error', 'Erro na API da Betel Tecnologia: Controller class V1Controller could not be found.', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 13:40:13'),
(19, 114, '', 'error', 'Erro na API da Betel Tecnologia: Controller class V1Controller could not be found.', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 13:40:32'),
(20, 114, '', 'error', 'Erro na API da Betel Tecnologia: Controller class V1Controller could not be found.', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 13:44:17'),
(21, 114, '', 'error', 'Erro na API da Betel Tecnologia: Controller class V1Controller could not be found.', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 13:44:27'),
(22, 114, '', 'error', 'Erro na API da Betel Tecnologia: Controller class V1Controller could not be found.', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 13:44:31'),
(23, 114, '', 'error', 'Erro na API da Betel Tecnologia: Controller class V1Controller could not be found.', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 13:44:35'),
(24, 114, '', 'error', 'Erro na API da Betel Tecnologia: Controller class V1Controller could not be found.', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 13:50:07'),
(25, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/v1/notas-fiscais', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:01:51'),
(26, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/v1/notas-fiscais', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:02:02'),
(27, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:21:35'),
(28, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:21:41'),
(29, 114, '', 'error', 'Nenhum endpoint funcionou. Ãšltimo erro: Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/v1/nota-fiscal/emitir. Resposta: {\"devTools\":{\"error\":\"Controller class V1Controller could not be found.\",\"file\":\"\\/usr\\/local\\/lsws\\/api\\/public_html\\/lib\\/Cake\\/Routing\\/Dispatcher.php\",\"messageTemplate\":\"\",\"line\":172},\"error\":\"Con', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:24:19'),
(30, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:24:47'),
(31, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:27:41'),
(32, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:29:37'),
(33, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:29:40'),
(34, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:30:03'),
(35, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:34:25'),
(36, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:51:38'),
(37, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:51:52'),
(38, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:52:25'),
(39, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:53:09'),
(40, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 14:56:20'),
(41, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 15:16:22'),
(42, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 15:16:27'),
(43, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 15:18:42'),
(44, 114, '', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'bling', '2025-07-25 15:18:45'),
(45, 114, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'betel', '2025-07-25 15:45:08');
INSERT INTO `nfe_logs` (`id`, `order_id`, `event_type`, `status`, `message`, `request_data`, `response_data`, `provider`, `created_at`) VALUES
(46, 114, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'betel', '2025-07-25 15:47:30'),
(47, 114, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":114,\"user_id\":23,\"total\":\"101.74\",\"subtotal\":\"90.00\",\"shipping\":\"16.24\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8F2B9F37\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 13:02:35\",\"updated_at\":\"2025-07-25 13:02:57\",\"visualizado\":0,\"created_at\":\"2025-07-25 13:02:35\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":109,\"order_id\":114,\"product_id\":53,\"quantity\":1,\"price_at_purchase\":\"90.00\"}]}', NULL, 'betel', '2025-07-25 15:47:30'),
(48, 115, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":115,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGDAC62B7C\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:26:28\",\"updated_at\":\"2025-07-25 17:26:28\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:26:28\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":110,\"order_id\":115,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":111,\"order_id\":115,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-25 17:27:09'),
(49, 115, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":115,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGDAC62B7C\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:26:28\",\"updated_at\":\"2025-07-25 17:26:28\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:26:28\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":110,\"order_id\":115,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":111,\"order_id\":115,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-25 17:39:17'),
(50, 115, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":115,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGDAC62B7C\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:26:28\",\"updated_at\":\"2025-07-25 17:26:28\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:26:28\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":110,\"order_id\":115,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":111,\"order_id\":115,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-25 17:39:22'),
(51, 115, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":115,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGDAC62B7C\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:26:28\",\"updated_at\":\"2025-07-25 17:26:28\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:26:28\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":110,\"order_id\":115,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":111,\"order_id\":115,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-25 17:39:24'),
(52, 116, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/v1/nfe/emitir', '{\"id\":116,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3E75FB11\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:49:42\",\"updated_at\":\"2025-07-25 17:49:43\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:49:42\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":112,\"order_id\":116,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":113,\"order_id\":116,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-25 17:50:16'),
(53, 116, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/nfe/emitir', '{\"id\":116,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3E75FB11\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:49:42\",\"updated_at\":\"2025-07-25 17:49:43\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:49:42\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":112,\"order_id\":116,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":113,\"order_id\":116,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-25 18:05:55'),
(54, 116, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/nfe/emitir', '{\"id\":116,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3E75FB11\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:49:42\",\"updated_at\":\"2025-07-25 17:49:43\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:49:42\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":112,\"order_id\":116,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":113,\"order_id\":116,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-25 18:06:00'),
(55, 116, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/nfe/emitir', '{\"id\":116,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3E75FB11\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:49:42\",\"updated_at\":\"2025-07-25 17:49:43\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:49:42\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":112,\"order_id\":116,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":113,\"order_id\":116,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-25 18:22:01'),
(56, 116, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/nfe/emitir', '{\"id\":116,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3E75FB11\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:49:42\",\"updated_at\":\"2025-07-25 17:49:43\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:49:42\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":112,\"order_id\":116,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":113,\"order_id\":116,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-25 18:28:09'),
(57, 116, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/nfe/emitir', '{\"id\":116,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3E75FB11\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:49:42\",\"updated_at\":\"2025-07-25 17:49:43\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:49:42\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":112,\"order_id\":116,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":113,\"order_id\":116,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-25 18:32:15'),
(58, 116, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/nfe/emitir', '{\"id\":116,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3E75FB11\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:49:42\",\"updated_at\":\"2025-07-25 17:49:43\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:49:42\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":112,\"order_id\":116,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":113,\"order_id\":116,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-25 18:32:23'),
(59, 116, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/nfe/emitir', '{\"id\":116,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3E75FB11\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:49:42\",\"updated_at\":\"2025-07-25 17:49:43\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:49:42\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":112,\"order_id\":116,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":113,\"order_id\":116,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-27 19:49:27'),
(60, 116, 'emissao', 'error', 'Erro HTTP 404 na requisiÃ§Ã£o para https://api.beteltecnologia.com/nfe/emitir', '{\"id\":116,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3E75FB11\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:49:42\",\"updated_at\":\"2025-07-25 17:49:43\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:49:42\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":112,\"order_id\":116,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":113,\"order_id\":116,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-27 19:50:04'),
(61, 116, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel.', '{\"id\":116,\"user_id\":23,\"total\":\"390.45\",\"subtotal\":\"411.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3E75FB11\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-25 17:49:42\",\"updated_at\":\"2025-07-25 17:49:43\",\"visualizado\":0,\"created_at\":\"2025-07-25 17:49:42\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":112,\"order_id\":116,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"},{\"id\":113,\"order_id\":116,\"product_id\":61,\"quantity\":1,\"price_at_purchase\":\"11.00\"}]}', NULL, 'betel', '2025-07-27 19:55:11'),
(62, 117, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel.', '{\"id\":117,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG44D6986C\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 19:56:16\",\"updated_at\":\"2025-07-27 19:56:16\",\"visualizado\":0,\"created_at\":\"2025-07-27 19:56:16\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":114,\"order_id\":117,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 19:56:41'),
(63, 117, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel.', '{\"id\":117,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG44D6986C\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 19:56:16\",\"updated_at\":\"2025-07-27 19:56:16\",\"visualizado\":0,\"created_at\":\"2025-07-27 19:56:16\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":114,\"order_id\":117,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:36:48'),
(64, 117, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel.', '{\"id\":117,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG44D6986C\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 19:56:16\",\"updated_at\":\"2025-07-27 19:56:16\",\"visualizado\":0,\"created_at\":\"2025-07-27 19:56:16\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":114,\"order_id\":117,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:36:52'),
(65, 121, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel.', '{\"id\":121,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luis Carlos Neves Silva, 111\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3FE970CC\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:38:27\",\"updated_at\":\"2025-07-27 20:38:41\",\"visualizado\":1,\"created_at\":\"2025-07-27 20:38:27\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":116,\"order_id\":121,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:38:46'),
(66, 121, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel.', '{\"id\":121,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luis Carlos Neves Silva, 111\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3FE970CC\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:38:27\",\"updated_at\":\"2025-07-27 20:38:41\",\"visualizado\":1,\"created_at\":\"2025-07-27 20:38:27\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":116,\"order_id\":121,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:39:19'),
(67, 121, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: {\"code\":200,\"status\":\"success\",\"data\":{\"id\":\"49742998\",\"tipo_pessoa\":\"PF\",\"nome\":\"Matheus Lucindo\",\"razao_social\":\"\",\"cnpj\":\"\",\"inscricao_estadual\":\"\",\"inscricao_municipal\":\"\",\"tipo_contribuinte\":\"\",\"responsavel\":\"\",\"cpf\":\"\",\"rg\":\"\",\"data_nascimento\":\"\",\"sexo\":\"\",\"loja_virtual_ativo\":\"0\",\"email_acesso\":\"\",\"telefone\":\"(11)97166-6817\",\"celular\":\"\",\"fax\":\"\",\"email\":\"matheuslucindo904@gmail.com\",\"ativo\":\"1\",\"vendedor_id\":\"971125\",\"nome_vendedor\":\"Vinicius De Melo Oliveira\",\"cadastrado_em\":\"2025-07-27 20:41:47\",\"modificado_em\":\"2025-07-27 20:41:47\",\"contatos\":[],\"enderecos\":[],\"atributos\":[]}}', '{\"id\":121,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luis Carlos Neves Silva, 111\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3FE970CC\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:38:27\",\"updated_at\":\"2025-07-27 20:38:41\",\"visualizado\":1,\"created_at\":\"2025-07-27 20:38:27\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":116,\"order_id\":121,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:41:47'),
(68, 121, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: {\"code\":200,\"status\":\"success\",\"data\":{\"id\":\"49742999\",\"tipo_pessoa\":\"PF\",\"nome\":\"Matheus Lucindo\",\"razao_social\":\"\",\"cnpj\":\"\",\"inscricao_estadual\":\"\",\"inscricao_municipal\":\"\",\"tipo_contribuinte\":\"\",\"responsavel\":\"\",\"cpf\":\"\",\"rg\":\"\",\"data_nascimento\":\"\",\"sexo\":\"\",\"loja_virtual_ativo\":\"0\",\"email_acesso\":\"\",\"telefone\":\"(11)97166-6817\",\"celular\":\"\",\"fax\":\"\",\"email\":\"matheuslucindo904@gmail.com\",\"ativo\":\"1\",\"vendedor_id\":\"971125\",\"nome_vendedor\":\"Vinicius De Melo Oliveira\",\"cadastrado_em\":\"2025-07-27 20:41:59\",\"modificado_em\":\"2025-07-27 20:41:59\",\"contatos\":[],\"enderecos\":[],\"atributos\":[]}}', '{\"id\":121,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luis Carlos Neves Silva, 111\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3FE970CC\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:38:27\",\"updated_at\":\"2025-07-27 20:38:41\",\"visualizado\":1,\"created_at\":\"2025-07-27 20:38:27\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":116,\"order_id\":121,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:41:59'),
(69, 121, 'emissao', 'error', 'Erro HTTP 400 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/vendas', '{\"id\":121,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luis Carlos Neves Silva, 111\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3FE970CC\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:38:27\",\"updated_at\":\"2025-07-27 20:38:41\",\"visualizado\":1,\"created_at\":\"2025-07-27 20:38:27\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":116,\"order_id\":121,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:43:21'),
(70, 121, 'emissao', 'error', 'Erro HTTP 400 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/vendas', '{\"id\":121,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luis Carlos Neves Silva, 111\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3FE970CC\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:38:27\",\"updated_at\":\"2025-07-27 20:38:41\",\"visualizado\":1,\"created_at\":\"2025-07-27 20:38:27\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":116,\"order_id\":121,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:44:33'),
(71, 121, 'emissao', 'error', 'Erro HTTP 400 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/vendas', '{\"id\":121,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luis Carlos Neves Silva, 111\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3FE970CC\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:38:27\",\"updated_at\":\"2025-07-27 20:38:41\",\"visualizado\":1,\"created_at\":\"2025-07-27 20:38:27\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":116,\"order_id\":121,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:45:03'),
(72, 121, 'emissao', 'error', 'Erro HTTP 400 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/vendas', '{\"id\":121,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luis Carlos Neves Silva, 111\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3FE970CC\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:38:27\",\"updated_at\":\"2025-07-27 20:38:41\",\"visualizado\":1,\"created_at\":\"2025-07-27 20:38:27\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":116,\"order_id\":121,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:51:54'),
(73, 121, 'emissao', 'error', 'Erro HTTP 400 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/vendas', '{\"id\":121,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luis Carlos Neves Silva, 111\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG3FE970CC\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:38:27\",\"updated_at\":\"2025-07-27 20:38:41\",\"visualizado\":1,\"created_at\":\"2025-07-27 20:38:27\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":116,\"order_id\":121,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:51:59'),
(74, 122, 'emissao', 'error', 'Erro HTTP 400 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/vendas', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:53:04'),
(75, 122, 'emissao', 'error', 'Erro HTTP 400 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/vendas', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 20:58:57'),
(76, 122, 'emissao', 'error', 'Erro HTTP 400 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/vendas', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:01:07'),
(77, 122, 'emissao', 'error', 'Erro HTTP 400 na requisiÃ§Ã£o para https://api.beteltecnologia.com/api/vendas', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:01:10'),
(78, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:12:39'),
(79, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:15:12'),
(80, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:15:17'),
(81, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:16:46'),
(82, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:16:49'),
(83, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:19:13'),
(84, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:19:16'),
(85, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:21:26'),
(86, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:21:29'),
(87, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:28:22');
INSERT INTO `nfe_logs` (`id`, `order_id`, `event_type`, `status`, `message`, `request_data`, `response_data`, `provider`, `created_at`) VALUES
(88, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:28:30'),
(89, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:30:25'),
(90, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:30:29'),
(91, 122, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":122,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG7C67CC41\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 20:52:51\",\"updated_at\":\"2025-07-27 20:52:51\",\"visualizado\":0,\"created_at\":\"2025-07-27 20:52:51\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":117,\"order_id\":122,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:32:07'),
(92, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:32:30'),
(93, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:35:18'),
(94, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:35:22'),
(95, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:37:43'),
(96, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:37:47'),
(97, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:39:33'),
(98, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:39:41'),
(99, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:39:46'),
(100, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:41:24'),
(101, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:41:29'),
(102, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:41:33'),
(103, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:45:30'),
(104, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:45:34'),
(105, 123, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":123,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CGF5669F0D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:32:03\",\"updated_at\":\"2025-07-27 21:32:03\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:32:03\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":118,\"order_id\":123,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:45:52'),
(106, 124, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":124,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8EF10D01\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:48:48\",\"updated_at\":\"2025-07-27 21:48:48\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:48:48\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"286.547.958-77\",\"telefone\":\"11971666817\",\"items\":[{\"id\":119,\"order_id\":124,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-27 21:49:06'),
(107, 124, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":124,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aguardando_pagamento\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"\",\"card_last4\":null,\"tracking_code\":\"CG8EF10D01\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-27 21:48:48\",\"updated_at\":\"2025-07-27 21:48:48\",\"visualizado\":0,\"created_at\":\"2025-07-27 21:48:48\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":119,\"order_id\":124,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 11:54:34'),
(108, 126, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":126,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG51AFA96D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:09:41\",\"updated_at\":\"2025-07-28 13:09:53\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:09:41\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":121,\"order_id\":126,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:09:58'),
(109, 126, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":126,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG51AFA96D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:09:41\",\"updated_at\":\"2025-07-28 13:09:53\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:09:41\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":121,\"order_id\":126,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:37:26'),
(110, 126, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":126,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG51AFA96D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:09:41\",\"updated_at\":\"2025-07-28 13:09:53\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:09:41\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":121,\"order_id\":126,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:37:38'),
(111, 126, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":126,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG51AFA96D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:09:41\",\"updated_at\":\"2025-07-28 13:09:53\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:09:41\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":121,\"order_id\":126,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:45:30'),
(112, 126, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":126,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG51AFA96D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:09:41\",\"updated_at\":\"2025-07-28 13:09:53\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:09:41\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":121,\"order_id\":126,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:45:33'),
(113, 126, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":126,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG51AFA96D\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:09:41\",\"updated_at\":\"2025-07-28 13:09:53\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:09:41\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":121,\"order_id\":126,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:47:30'),
(114, 127, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":127,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG4C40F97E\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:47:34\",\"updated_at\":\"2025-07-28 13:47:44\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:47:34\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":122,\"order_id\":127,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:47:49'),
(115, 127, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":127,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG4C40F97E\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:47:34\",\"updated_at\":\"2025-07-28 13:47:44\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:47:34\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":122,\"order_id\":127,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:49:46'),
(116, 127, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":127,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG4C40F97E\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:47:34\",\"updated_at\":\"2025-07-28 13:47:44\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:47:34\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":122,\"order_id\":127,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:49:49'),
(117, 127, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":127,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG4C40F97E\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:47:34\",\"updated_at\":\"2025-07-28 13:47:44\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:47:34\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":122,\"order_id\":127,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:52:01'),
(118, 127, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":127,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG4C40F97E\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:47:34\",\"updated_at\":\"2025-07-28 13:47:44\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:47:34\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":122,\"order_id\":127,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:52:04'),
(119, 127, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":127,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG4C40F97E\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:47:34\",\"updated_at\":\"2025-07-28 13:47:44\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:47:34\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":122,\"order_id\":127,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:56:54'),
(120, 127, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":127,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG4C40F97E\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:47:34\",\"updated_at\":\"2025-07-28 13:47:44\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:47:34\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":122,\"order_id\":127,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:56:57'),
(121, 127, 'emissao', 'error', 'NÃ£o foi possÃ­vel obter o ID do cliente na Betel. Resposta: null', '{\"id\":127,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG4C40F97E\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:47:34\",\"updated_at\":\"2025-07-28 13:47:44\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:47:34\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\",\"items\":[{\"id\":122,\"order_id\":127,\"product_id\":54,\"quantity\":1,\"price_at_purchase\":\"400.00\"}]}', NULL, 'betel', '2025-07-28 13:57:17'),
(122, 127, 'emissao', 'erro', 'Erro na API do Bling: NÃ£o encontrado.', '{\"id\":127,\"user_id\":23,\"total\":\"380.00\",\"subtotal\":\"400.00\",\"shipping\":\"0.00\",\"discount\":\"0.00\",\"payment_method\":\"pix\",\"payment_id\":\"\",\"installments\":1,\"status\":\"aceito\",\"shipping_address\":\"Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP\",\"shipping_number\":\"111\",\"shipping_cep\":\"08501-300\",\"shipping_complement\":\"Casa 2\",\"card_last4\":null,\"tracking_code\":\"CG4C40F97E\",\"superfrete_label_id\":null,\"order_date\":\"2025-07-28 13:47:34\",\"updated_at\":\"2025-07-28 13:47:44\",\"visualizado\":0,\"created_at\":\"2025-07-28 13:47:34\",\"nfe_key\":null,\"nfe_number\":null,\"nfe_series\":null,\"nfe_status\":null,\"nfe_issue_date\":null,\"nfe_pdf_url\":null,\"name\":\"Matheus Lucindo\",\"email\":\"matheuslucindo904@gmail.com\",\"cpf\":\"503.092.768-96\",\"telefone\":\"11971666817\"}', '', 'bling', '2025-07-28 17:11:06'),
(123, 131, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"131\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":61,\"descricao\":\"ARRANJO G\",\"detalhes\":\"teste 3\",\"quantidade\":1,\"valor_unitario\":\"11.00\"}]}', '{\"id\":23474192253,\"numero\":\"000131\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null}', 'bling', '2025-07-31 01:41:40'),
(124, 128, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"128\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":54,\"descricao\":\"ARRANJO M\",\"detalhes\":\"\",\"quantidade\":1,\"valor_unitario\":\"400.00\"}]}', '{\"id\":23477822266,\"numero\":\"000128\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 12:03:38'),
(125, 131, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"131\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":61,\"descricao\":\"ARRANJO G\",\"detalhes\":\"teste 3\",\"quantidade\":1,\"valor_unitario\":\"11.00\"}]}', '{\"id\":23477860984,\"numero\":\"000131\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 12:07:25'),
(126, 130, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"130\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":61,\"descricao\":\"ARRANJO G\",\"detalhes\":\"teste 3\",\"quantidade\":1,\"valor_unitario\":\"11.00\"}]}', '{\"id\":23477977634,\"numero\":\"000130\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 12:19:44'),
(127, 127, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"127\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":54,\"descricao\":\"ARRANJO M\",\"detalhes\":\"\",\"quantidade\":1,\"valor_unitario\":\"400.00\"}]}', '{\"id\":23478386771,\"numero\":\"000127\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 13:08:18'),
(128, 105, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"105\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":53,\"descricao\":\"ARRANJO P\",\"detalhes\":\"\",\"quantidade\":1,\"valor_unitario\":\"90.00\"}]}', '{\"id\":23478617639,\"numero\":\"000105\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 13:33:14'),
(129, 108, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"108\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":54,\"descricao\":\"ARRANJO M\",\"detalhes\":\"\",\"quantidade\":1,\"valor_unitario\":\"23.00\"}]}', '{\"id\":23478660615,\"numero\":\"000108\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 13:37:37'),
(130, 125, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"125\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":54,\"descricao\":\"ARRANJO M\",\"detalhes\":\"\",\"quantidade\":2,\"valor_unitario\":\"400.00\"}]}', '{\"id\":23478690048,\"numero\":\"000125\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 13:40:24'),
(131, 112, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"112\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":54,\"descricao\":\"ARRANJO M\",\"detalhes\":\"\",\"quantidade\":1,\"valor_unitario\":\"23.00\"}]}', '{\"id\":23478852076,\"numero\":\"000112\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 13:57:25'),
(132, 129, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"129\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":61,\"descricao\":\"ARRANJO G\",\"detalhes\":\"teste 3\",\"quantidade\":1,\"valor_unitario\":\"11.00\"}]}', '{\"id\":23479006324,\"numero\":\"000129\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 14:13:19'),
(133, 132, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"132\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":61,\"descricao\":\"ARRANJO G\",\"detalhes\":\"teste 3\",\"quantidade\":1,\"valor_unitario\":\"11.00\"}]}', '{\"id\":23479194272,\"numero\":\"000132\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 14:31:37'),
(134, 133, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"133\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":61,\"descricao\":\"ARRANJO G\",\"detalhes\":\"teste 3\",\"quantidade\":1,\"valor_unitario\":\"11.00\"}]}', '{\"id\":23479268188,\"numero\":\"000133\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 14:38:53'),
(135, 134, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"134\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":61,\"descricao\":\"ARRANJO G\",\"detalhes\":\"teste 3\",\"quantidade\":1,\"valor_unitario\":\"11.00\"}]}', '{\"id\":23480664829,\"numero\":\"000134\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 16:53:33'),
(136, 135, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', '{\"id\":\"135\",\"tipo\":\"E\",\"natureza_operacao\":\"Venda de mercadoria\",\"cliente\":{\"nome\":\"Matheus Lucindo\",\"cpf_cnpj\":\"503.092.768-96\",\"email\":\"matheuslucindo904@gmail.com\",\"telefone\":\"11971666817\"},\"itens\":[{\"codigo\":61,\"descricao\":\"ARRANJO G\",\"detalhes\":\"teste 3\",\"quantidade\":1,\"valor_unitario\":\"11.00\"}]}', '{\"id\":23480758688,\"numero\":\"000135\",\"serie\":\"1\",\"data_emissao\":null,\"pdf_url\":null,\"chave_acesso\":null}', 'bling', '2025-07-31 17:02:09'),
(137, 136, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: Invalid key=value pair (missing equal-sign) in Authorization header (hashed with SHA-256 and encoded with Base64): \'qidFMmGmF6HIMJUe7nzCR/7Fb19XK/Ok54Nf7sOXWUY=\'.', '', '', 'bling', '2025-07-31 21:43:08'),
(138, 136, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: Invalid key=value pair (missing equal-sign) in Authorization header (hashed with SHA-256 and encoded with Base64): \'qidFMmGmF6HIMJUe7nzCR/7Fb19XK/Ok54Nf7sOXWUY=\'.', '', '', 'bling', '2025-07-31 22:11:22'),
(139, 136, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: Invalid key=value pair (missing equal-sign) in Authorization header (hashed with SHA-256 and encoded with Base64): \'qidFMmGmF6HIMJUe7nzCR/7Fb19XK/Ok54Nf7sOXWUY=\'.', '', '', 'bling', '2025-07-31 22:30:21'),
(140, 136, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: Invalid key=value pair (missing equal-sign) in Authorization header (hashed with SHA-256 and encoded with Base64): \'0BdAhcK4w3Zbmwylmjpe8+h9YRmAN2rKaYoQJVIkk80=\'.', '', '', 'bling', '2025-07-31 22:32:24'),
(141, 136, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: Authorization header requires \'Credential\' parameter. Authorization header requires \'Signature\' parameter. Authorization header requires \'SignedHeaders\' parameter. Authorization header requires existence of either a \'X-Amz-Date\' or a \'Date\' header. (Hashed with SHA-256 and encoded with Base64) Authorization=BIkZ/NKJ0tgR3HigRE5ajFnVVp6Gc7YN3AwgnfQ/MEI=', '', '', 'bling', '2025-07-31 22:35:32'),
(142, 136, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: Authorization header requires \'Credential\' parameter. Authorization header requires \'Signature\' parameter. Authorization header requires \'SignedHeaders\' parameter. Authorization header requires existence of either a \'X-Amz-Date\' or a \'Date\' header. (Hashed with SHA-256 and encoded with Base64) Authorization=NqE9h5f4KwQV3dPxW8TLKL6XOtI0AgEqSc8IeI6EjoY=', '', '', 'bling', '2025-07-31 22:39:15'),
(143, 136, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: Invalid key=value pair (missing equal-sign) in Authorization header (hashed with SHA-256 and encoded with Base64): \'b4QoE52HWFK3zQnPPkKWLOYTEYiMzPMiBBe/QFKE7t4=\'.', '', '', 'bling', '2025-07-31 22:43:40'),
(144, 136, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: Invalid key=value pair (missing equal-sign) in Authorization header (hashed with SHA-256 and encoded with Base64): \'b4QoE52HWFK3zQnPPkKWLOYTEYiMzPMiBBe/QFKE7t4=\'.', '', '', 'bling', '2025-07-31 22:45:13'),
(145, 136, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: Invalid key=value pair (missing equal-sign) in Authorization header (hashed with SHA-256 and encoded with Base64): \'b4QoE52HWFK3zQnPPkKWLOYTEYiMzPMiBBe/QFKE7t4=\'.', '', '', 'bling', '2025-07-31 22:45:18');
INSERT INTO `nfe_logs` (`id`, `order_id`, `event_type`, `status`, `message`, `request_data`, `response_data`, `provider`, `created_at`) VALUES
(146, 136, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: {\"error\":{\"type\":\"VALIDATION_ERROR\",\"message\":\"N\\u00e3o foi poss\\u00edvel salvar o contato\",\"description\":\"O contato n\\u00e3o pode ser salvo, pois ocorreram problemas em sua valida\\u00e7\\u00e3o.\",\"fields\":[{\"code\":0,\"msg\":\"O CPF j\\u00e1 est\\u00e1 cadastrado no contato Matheus Lucindo\",\"element\":\"cnpj\",\"namespace\":\"CONTATOS\"}]}}', '', '', 'bling', '2025-07-31 22:47:31'),
(147, 137, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: {\"error\":{\"type\":\"VALIDATION_ERROR\",\"message\":\"N\\u00e3o foi poss\\u00edvel salvar o contato\",\"description\":\"O contato n\\u00e3o pode ser salvo, pois ocorreram problemas em sua valida\\u00e7\\u00e3o.\",\"fields\":[{\"code\":0,\"msg\":\"O CPF j\\u00e1 est\\u00e1 cadastrado no contato Matheus Lucindo\",\"element\":\"cnpj\",\"namespace\":\"CONTATOS\"}]}}', '', '', 'bling', '2025-07-31 22:48:48'),
(148, 137, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: {\"error\":{\"type\":\"VALIDATION_ERROR\",\"message\":\"N\\u00e3o foi poss\\u00edvel salvar o contato\",\"description\":\"O contato n\\u00e3o pode ser salvo, pois ocorreram problemas em sua valida\\u00e7\\u00e3o.\",\"fields\":[{\"code\":0,\"msg\":\"O CPF j\\u00e1 est\\u00e1 cadastrado no contato Matheus Lucindo\",\"element\":\"cnpj\",\"namespace\":\"CONTATOS\"}]}}', '', '', 'bling', '2025-07-31 22:54:33'),
(149, 137, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: {\"error\":{\"type\":\"VALIDATION_ERROR\",\"message\":\"N\\u00e3o foi poss\\u00edvel salvar o contato\",\"description\":\"O contato n\\u00e3o pode ser salvo, pois ocorreram problemas em sua valida\\u00e7\\u00e3o.\",\"fields\":[{\"code\":0,\"msg\":\"O CPF j\\u00e1 est\\u00e1 cadastrado no contato Matheus Lucindo\",\"element\":\"cnpj\",\"namespace\":\"CONTATOS\"}]}}', '', '', 'bling', '2025-07-31 22:55:00'),
(150, 137, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: {\"error\":{\"type\":\"VALIDATION_ERROR\",\"message\":\"N\\u00e3o foi poss\\u00edvel salvar o contato\",\"description\":\"O contato n\\u00e3o pode ser salvo, pois ocorreram problemas em sua valida\\u00e7\\u00e3o.\",\"fields\":[{\"code\":0,\"msg\":\"O CPF j\\u00e1 est\\u00e1 cadastrado no contato Matheus Lucindo\",\"element\":\"cnpj\",\"namespace\":\"CONTATOS\"}]}}', '', '', 'bling', '2025-07-31 22:57:21'),
(151, 137, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: {\"error\":{\"type\":\"VALIDATION_ERROR\",\"message\":\"N\\u00e3o foi poss\\u00edvel salvar o contato\",\"description\":\"O contato n\\u00e3o pode ser salvo, pois ocorreram problemas em sua valida\\u00e7\\u00e3o.\",\"fields\":[{\"code\":0,\"msg\":\"O CPF j\\u00e1 est\\u00e1 cadastrado no contato Matheus Lucindo\",\"element\":\"cnpj\",\"namespace\":\"CONTATOS\"}]}}', '', '', 'bling', '2025-07-31 22:59:05'),
(152, 138, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: {\"error\":{\"type\":\"VALIDATION_ERROR\",\"message\":\"N\\u00e3o foi poss\\u00edvel salvar o contato\",\"description\":\"O contato n\\u00e3o pode ser salvo, pois ocorreram problemas em sua valida\\u00e7\\u00e3o.\",\"fields\":[{\"code\":0,\"msg\":\"O CPF j\\u00e1 est\\u00e1 cadastrado no contato Matheus Lucindo\",\"element\":\"cnpj\",\"namespace\":\"CONTATOS\"}]}}', '', '', 'bling', '2025-07-31 23:01:02'),
(153, 139, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: {\"error\":{\"type\":\"VALIDATION_ERROR\",\"message\":\"N\\u00e3o foi poss\\u00edvel salvar o contato\",\"description\":\"O contato n\\u00e3o pode ser salvo, pois ocorreram problemas em sua valida\\u00e7\\u00e3o.\",\"fields\":[{\"code\":0,\"msg\":\"O campo CPF \\u00e9 inv\\u00e1lido\",\"element\":\"cnpj\",\"namespace\":\"CONTATOS\"}]}}', '', '', 'bling', '2025-07-31 23:02:29'),
(154, 139, 'emissao', 'erro', 'Erro ao criar contato no Bling: Erro API Bling: {\"error\":{\"type\":\"VALIDATION_ERROR\",\"message\":\"N\\u00e3o foi poss\\u00edvel salvar o contato\",\"description\":\"O contato n\\u00e3o pode ser salvo, pois ocorreram problemas em sua valida\\u00e7\\u00e3o.\",\"fields\":[{\"code\":0,\"msg\":\"O campo CPF \\u00e9 inv\\u00e1lido\",\"element\":\"cnpj\",\"namespace\":\"CONTATOS\"}]}}', '', '', 'bling', '2025-07-31 23:16:42');

-- --------------------------------------------------------

--
-- Estrutura da tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lida` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `notificacoes`
--

INSERT INTO `notificacoes` (`id`, `usuario_id`, `titulo`, `mensagem`, `data_criacao`, `lida`) VALUES
(59, 23, 'Pedido Confirmado', 'Seu pedido #88 foi confirmado e estÃ¡ em processamento.', '2025-05-12 18:20:33', 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping` decimal(10,2) DEFAULT '0.00',
  `discount` decimal(10,2) DEFAULT '0.00',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installments` tinyint(2) DEFAULT '1',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'processando',
  `shipping_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_cep` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_complement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_last4` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `superfrete_label_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `visualizado` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `nfe_key` varchar(44) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Chave de acesso da NFe',
  `nfe_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número da NFe',
  `nfe_series` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Série da NFe',
  `nfe_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status da NFe (pendente, emitida, cancelada, etc)',
  `nfe_issue_date` datetime DEFAULT NULL COMMENT 'Data de emissão da NFe',
  `nfe_pdf_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL para download do PDF da NFe',
  `bling_pedido_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID do pedido no Bling',
  `bling_numero` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número do pedido no Bling',
  `bling_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status do pedido no Bling',
  `contato_id` bigint(20) DEFAULT NULL COMMENT 'ID do contato Bling',
  `formaPagamento` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Forma de pagamento',
  `parcelas` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Parcelas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `subtotal`, `shipping`, `discount`, `payment_method`, `payment_id`, `installments`, `status`, `shipping_address`, `shipping_number`, `shipping_cep`, `shipping_complement`, `card_last4`, `tracking_code`, `superfrete_label_id`, `order_date`, `updated_at`, `visualizado`, `created_at`, `nfe_key`, `nfe_number`, `nfe_series`, `nfe_status`, `nfe_issue_date`, `nfe_pdf_url`, `bling_pedido_id`, `bling_numero`, `bling_status`, `contato_id`, `formaPagamento`, `parcelas`) VALUES
(85, 23, 68.00, 68.00, 0.00, 0.00, 'pix', NULL, 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG38169C8A', NULL, '2025-05-11 00:05:32', '2025-05-12 13:57:45', 0, '2025-05-10 21:05:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(86, 23, 338.00, 238.00, 100.00, 0.00, 'credit_card', NULL, 1, 'aceito', 'Avenida Cristiano Machado, UniÃ£o, Belo Horizonte, MG', '45', '31160-900', '', NULL, 'CG9AC8D257', NULL, '2025-05-11 00:31:51', '2025-05-12 13:57:49', 0, '2025-05-10 21:31:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(87, 23, 34.00, 34.00, 0.00, 0.00, 'pix', NULL, 1, 'cancelado', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CGDE206552', NULL, '2025-05-12 13:54:31', '2025-07-30 16:00:09', 0, '2025-05-12 10:54:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(88, 23, 116.00, 116.00, 0.00, 0.00, 'pix', NULL, 1, 'aceito', 'Rua Senhor dos Passos, Centro, Rio de Janeiro, RJ', '111', '20061-012', '', NULL, 'CGD5431867', NULL, '2025-05-12 18:20:33', '2025-05-12 18:45:14', 0, '2025-05-12 15:20:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(89, 23, 100.00, 0.00, 100.00, 0.00, 'pix', NULL, 1, 'aceito', 'Rua Senhor dos Passos, Centro, Rio de Janeiro, RJ', '111', '20061-012', '', NULL, 'CG2F08C140', NULL, '2025-05-12 18:23:25', '2025-05-12 18:43:07', 0, '2025-05-12 15:23:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(92, 23, 158.00, 58.00, 100.00, 0.00, 'pix', NULL, 1, 'aceito', 'Rua Conde Lages, Centro, Rio de Janeiro, RJ', '111', '20241-900', '', NULL, 'CG8E38F10E', NULL, '2025-05-12 23:15:13', '2025-05-14 23:39:43', 0, '2025-05-12 20:15:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(93, 23, 174.00, 174.00, 0.00, 0.00, 'credit_card', NULL, 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CGAC67EA25', NULL, '2025-05-14 18:55:34', '2025-05-14 23:39:39', 0, '2025-05-14 15:55:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(94, 23, 23.00, 23.00, 0.00, 0.00, 'credit_card', NULL, 1, 'cancelado', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CGBFADDF5E', NULL, '2025-05-14 22:56:19', '2025-05-14 23:41:22', 0, '2025-05-14 19:56:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(95, 23, 23.00, 23.00, 0.00, 0.00, 'pix', NULL, 1, 'entregue', 'Av. Dr. CÃ¢ndido X. de Almeida e Souza', '200', '08780-911', 'Casa 2', NULL, 'CGEF30B403', NULL, '2025-05-14 23:46:27', '2025-07-16 23:00:16', 1, '2025-05-14 20:46:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(96, 23, 21.85, 23.00, 0.00, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG07869BAC', NULL, '2025-07-17 05:58:10', '2025-07-17 06:00:22', 1, '2025-07-17 02:58:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(97, 23, 21.85, 23.00, 0.00, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG731FEC5C', NULL, '2025-07-17 05:58:11', '2025-07-17 06:00:22', 1, '2025-07-17 02:58:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(98, 23, 23.00, 23.00, 0.00, 0.00, 'pix', '', 0, 'processando', 'ENDEREÃ‡O NÃƒO INFORMADO', 'S/N', '00000-000', '', NULL, 'CG1B84632F', NULL, '2025-07-17 05:58:28', '2025-07-17 06:00:22', 1, '2025-07-17 02:58:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(99, 23, 0.00, 0.00, 0.00, 0.00, 'pix', '', 0, 'aceito', 'ENDEREÃ‡O NÃƒO INFORMADO', 'S/N', '00000-000', '', NULL, 'CG50B915E6', NULL, '2025-07-17 05:58:38', '2025-07-17 06:05:05', 1, '2025-07-17 02:58:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(100, 23, 0.00, 0.00, 0.00, 0.00, 'pix', '', 0, 'em_alerta', 'ENDEREÃ‡O NÃƒO INFORMADO', 'S/N', '00000-000', '', NULL, 'CGA074CA54', NULL, '2025-07-17 05:58:39', '2025-07-31 16:53:35', 1, '2025-07-17 02:58:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(101, 23, 0.00, 0.00, 0.00, 0.00, 'pix', '', 0, 'em_alerta', 'ENDEREÃ‡O NÃƒO INFORMADO', 'S/N', '00000-000', '', NULL, 'CGFABECF0E', NULL, '2025-07-17 06:04:44', '2025-07-31 16:53:32', 1, '2025-07-17 03:04:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(102, 23, 23.00, 23.00, 0.00, 0.00, 'pix', '', 0, 'processando', 'ENDEREÃ‡O NÃƒO INFORMADO', 'S/N', '00000-000', '', NULL, 'CG037E4A4B', NULL, '2025-07-17 06:07:03', '2025-07-17 06:07:14', 1, '2025-07-17 03:07:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(103, 23, 90.00, 90.00, 0.00, 0.00, 'pix', '', 0, 'processando', 'ENDEREÃ‡O NÃƒO INFORMADO', 'S/N', '00000-000', '', NULL, 'CGF93598C5', NULL, '2025-07-17 06:11:31', '2025-07-17 18:59:04', 1, '2025-07-17 03:11:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(104, 23, 0.00, 0.00, 0.00, 0.00, 'pix', '', 0, 'em_alerta', 'ENDEREÃ‡O NÃƒO INFORMADO', 'S/N', '00000-000', '', NULL, 'CGB644E6C3', NULL, '2025-07-17 06:11:38', '2025-07-30 16:00:45', 1, '2025-07-17 03:11:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(105, 23, 90.00, 90.00, 0.00, 0.00, 'pix', '', 0, 'aceito', 'ENDEREÃ‡O NÃƒO INFORMADO', 'S/N', '00000-000', '', NULL, 'CG54014708', NULL, '2025-07-17 06:11:50', '2025-07-31 16:33:14', 1, '2025-07-17 03:11:50', NULL, '000105', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(106, 23, 85.50, 90.00, 0.00, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG2A13782A', NULL, '2025-07-17 06:16:16', '2025-07-17 18:59:04', 1, '2025-07-17 03:16:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(107, 23, 171.00, 180.00, 0.00, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua da Paz, Frei Calixto, Porto Seguro, BA', '111', '45810-972', '', NULL, 'CGB09C2DE5', NULL, '2025-07-17 06:17:31', '2025-07-17 18:59:04', 1, '2025-07-17 03:17:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(108, 23, 21.85, 23.00, 0.00, 0.00, 'pix', '', 1, 'aceito', 'Rua da Paz, Frei Calixto, Porto Seguro, BA', '111', '45810-972', '', NULL, 'CG523B7994', NULL, '2025-07-17 06:31:54', '2025-07-31 16:37:37', 1, '2025-07-17 03:31:54', NULL, '000108', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(109, 23, 0.00, 0.00, 0.00, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua da Paz, Frei Calixto, Porto Seguro, BA', '111', '45810-972', '', NULL, 'CG7AE1F4E6', NULL, '2025-07-17 06:32:06', '2025-07-17 18:59:04', 1, '2025-07-17 03:32:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(110, 23, 21.85, 23.00, 0.00, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua da Paz, Frei Calixto, Porto Seguro, BA', '111', '45810-972', '', NULL, 'CGDF58890F', NULL, '2025-07-17 06:38:26', '2025-07-17 18:59:04', 1, '2025-07-17 03:38:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(111, 23, 10.45, 11.00, 0.00, 0.00, 'pix', '', 1, 'aceito', 'Rua da Paz, Frei Calixto, Porto Seguro, BA', '111', '45810-972', '', NULL, 'CG3AAA4EDF', NULL, '2025-07-17 06:44:26', '2025-07-24 18:47:49', 1, '2025-07-17 03:44:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(112, 23, 118.82, 23.00, 96.97, 0.00, 'pix', '', 1, 'aceito', 'Rua da Paz, Frei Calixto, Porto Seguro, BA', '111', '45810-972', '', NULL, 'CG28E6621E', NULL, '2025-07-17 16:41:25', '2025-07-31 16:57:25', 1, '2025-07-17 13:41:25', NULL, '000112', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(113, 23, 1368.00, 1440.00, 0.00, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG087BBF5F', NULL, '2025-07-25 00:17:00', '2025-07-30 15:59:25', 1, '2025-07-24 21:17:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(114, 23, 101.74, 90.00, 16.24, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG8F2B9F37', NULL, '2025-07-25 16:02:35', '2025-07-25 16:02:57', 0, '2025-07-25 13:02:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(115, 23, 390.45, 411.00, 0.00, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CGDAC62B7C', NULL, '2025-07-25 20:26:28', '2025-07-30 15:59:28', 0, '2025-07-25 17:26:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(116, 23, 390.45, 411.00, 0.00, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG3E75FB11', NULL, '2025-07-25 20:49:42', '2025-07-25 20:49:43', 0, '2025-07-25 17:49:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(117, 23, 380.00, 400.00, 0.00, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG44D6986C', NULL, '2025-07-27 22:56:16', '2025-07-27 23:38:01', 1, '2025-07-27 19:56:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(118, 23, 0.00, 0.00, 0.00, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG4743A3D7', NULL, '2025-07-27 23:37:00', '2025-07-27 23:38:01', 1, '2025-07-27 20:37:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(119, 23, 380.00, 400.00, 0.00, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CGF17B2CFC', NULL, '2025-07-27 23:37:36', '2025-07-27 23:38:01', 1, '2025-07-27 20:37:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(120, 23, 0.00, 0.00, 0.00, 0.00, 'pix', '', 1, 'cancelado', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG2E35A841', NULL, '2025-07-27 23:37:39', '2025-07-30 16:02:37', 1, '2025-07-27 20:37:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(121, 23, 380.00, 400.00, 0.00, 0.00, 'pix', '', 1, 'aceito', 'Rua Luis Carlos Neves Silva, 111', '111', '08501-300', '', NULL, 'CG3FE970CC', NULL, '2025-07-27 23:38:27', '2025-07-27 23:38:41', 1, '2025-07-27 20:38:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(122, 23, 380.00, 400.00, 0.00, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG7C67CC41', NULL, '2025-07-27 23:52:51', '2025-07-30 16:02:19', 1, '2025-07-27 20:52:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(123, 23, 380.00, 400.00, 0.00, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CGF5669F0D', NULL, '2025-07-28 00:32:03', '2025-07-30 16:02:13', 1, '2025-07-27 21:32:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(124, 23, 380.00, 400.00, 0.00, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG8EF10D01', NULL, '2025-07-28 00:48:48', '2025-07-30 16:02:16', 1, '2025-07-27 21:48:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(125, 23, 760.00, 800.00, 0.00, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG206D81D9', NULL, '2025-07-28 16:08:08', '2025-07-31 16:40:24', 1, '2025-07-28 13:08:08', NULL, '000125', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(126, 23, 380.00, 400.00, 0.00, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG51AFA96D', NULL, '2025-07-28 16:09:41', '2025-07-28 16:09:53', 0, '2025-07-28 13:09:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(127, 23, 380.00, 400.00, 0.00, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG4C40F97E', NULL, '2025-07-28 16:47:34', '2025-07-31 16:08:18', 0, '2025-07-28 13:47:34', NULL, '000127', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(128, 23, 380.00, 400.00, 0.00, 0.00, 'pix', '', 1, 'aceito', 'Rua Luis Carlos Neves Silva, 111', '111', '08501-300', '', NULL, 'CG248D2352', NULL, '2025-07-29 17:21:55', '2025-07-31 15:03:38', 1, '2025-07-29 14:21:55', NULL, '000128', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(129, 23, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aceito', 'Rua Luis Carlos Neves Silva, 111', '111', '08501-300', '', NULL, 'CG8D9D204E', NULL, '2025-07-29 21:53:02', '2025-07-31 17:13:19', 0, '2025-07-29 18:53:02', NULL, '000129', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(130, 23, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aceito', 'Rua Luis Carlos Neves Silva, 111', '111', '08501-300', '', NULL, 'CGCA2E8B23', NULL, '2025-07-30 20:29:48', '2025-07-31 15:19:44', 0, '2025-07-30 17:29:48', NULL, '000130', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(131, 23, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG63AB6233', NULL, '2025-07-31 01:47:54', '2025-07-31 04:41:39', 0, '2025-07-30 22:47:54', NULL, '000131', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(132, 23, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG063DEE89', NULL, '2025-07-31 17:29:21', '2025-07-31 20:00:18', 1, '2025-07-31 14:29:21', NULL, '000132', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(133, 23, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG3E0CEAA2', NULL, '2025-07-31 17:38:12', '2025-07-31 17:38:53', 0, '2025-07-31 14:38:12', NULL, '000133', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(134, 23, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGC134C161', NULL, '2025-07-31 19:50:25', '2025-07-31 19:53:33', 0, '2025-07-31 16:50:25', NULL, '000134', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(135, 23, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG4162DF8D', NULL, '2025-07-31 20:01:04', '2025-07-31 20:02:09', 0, '2025-07-31 17:01:04', NULL, '000135', '1', 'autorizada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(136, 23, 35.23, 0.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG3B26EE18', NULL, '2025-07-31 20:10:00', '2025-07-31 21:12:04', 1, '2025-07-31 17:10:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(137, 23, 35.23, 0.00, 35.23, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG8B9F47E4', NULL, '2025-07-31 20:10:42', '2025-08-01 01:48:37', 1, '2025-07-31 17:10:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(138, 31, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG22151B05', NULL, '2025-08-01 02:00:37', '2025-08-01 02:00:53', 0, '2025-07-31 23:00:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(139, 31, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aceito', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG50AD7F61', NULL, '2025-08-01 02:02:14', '2025-08-01 02:02:24', 0, '2025-07-31 23:02:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(140, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG3BD8FA50', NULL, '2025-08-01 03:25:44', '2025-08-01 16:39:32', 1, '2025-08-01 00:25:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(141, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG38BF8781', NULL, '2025-08-01 03:28:35', '2025-08-01 16:39:32', 1, '2025-08-01 00:28:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(142, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG0B5AF033', NULL, '2025-08-01 03:28:59', '2025-08-01 16:39:32', 1, '2025-08-01 00:28:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(143, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG75627E9D', NULL, '2025-08-01 03:39:24', '2025-08-01 16:39:32', 1, '2025-08-01 00:39:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(144, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGB7D937B6', NULL, '2025-08-01 03:39:43', '2025-08-01 16:39:32', 1, '2025-08-01 00:39:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(145, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGDABD3FF3', NULL, '2025-08-01 04:09:51', '2025-08-01 16:39:32', 1, '2025-08-01 01:09:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(146, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG023FE59B', NULL, '2025-08-01 04:13:34', '2025-08-01 16:39:32', 1, '2025-08-01 01:13:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(147, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG848578EF', NULL, '2025-08-01 04:13:35', '2025-08-01 16:39:32', 1, '2025-08-01 01:13:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(148, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGDBE5C447', NULL, '2025-08-01 04:13:39', '2025-08-01 16:39:32', 1, '2025-08-01 01:13:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(149, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG388AE49D', NULL, '2025-08-01 04:14:12', '2025-08-01 16:39:32', 1, '2025-08-01 01:14:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(150, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG895A3271', NULL, '2025-08-01 04:28:50', '2025-08-01 16:39:32', 1, '2025-08-01 01:28:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(151, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', '', NULL, 'CG77A0A388', NULL, '2025-08-01 04:29:00', '2025-08-01 16:39:32', 1, '2025-08-01 01:29:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(152, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG9E7829A9', NULL, '2025-08-01 04:29:28', '2025-08-01 16:39:32', 1, '2025-08-01 01:29:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(153, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG2E4A9B7D', NULL, '2025-08-01 04:33:25', '2025-08-01 16:39:32', 1, '2025-08-01 01:33:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(154, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG3F230E38', NULL, '2025-08-01 04:33:42', '2025-08-01 16:39:32', 1, '2025-08-01 01:33:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(155, 39, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGC9E6D399', NULL, '2025-08-01 04:34:15', '2025-08-01 16:39:32', 1, '2025-08-01 01:34:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(156, 39, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG612AF434', NULL, '2025-08-01 04:36:47', '2025-08-01 16:39:32', 1, '2025-08-01 01:36:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(157, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGE09EE6E1', NULL, '2025-08-01 04:37:25', '2025-08-01 16:39:32', 1, '2025-08-01 01:37:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(158, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG72AD0E9D', NULL, '2025-08-01 04:39:48', '2025-08-01 16:39:32', 1, '2025-08-01 01:39:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(159, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGA7EBEE88', NULL, '2025-08-01 04:40:11', '2025-08-01 16:39:32', 1, '2025-08-01 01:40:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(160, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG092D6D8C', NULL, '2025-08-01 04:45:09', '2025-08-01 16:39:32', 1, '2025-08-01 01:45:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(161, 39, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG19E97498', NULL, '2025-08-01 04:45:54', '2025-08-01 16:39:32', 1, '2025-08-01 01:45:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(162, 39, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGBCEAFAFE', NULL, '2025-08-01 04:49:02', '2025-08-01 16:39:32', 1, '2025-08-01 01:49:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(163, 39, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG828E7E88', NULL, '2025-08-01 04:49:22', '2025-08-01 16:39:32', 1, '2025-08-01 01:49:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(164, 39, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG3F377210', NULL, '2025-08-01 04:57:37', '2025-08-01 16:39:32', 1, '2025-08-01 01:57:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(165, 39, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG308A710B', NULL, '2025-08-01 04:57:59', '2025-08-01 16:39:32', 1, '2025-08-01 01:57:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(166, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG205E009A', NULL, '2025-08-01 13:15:55', '2025-08-01 16:39:32', 1, '2025-08-01 10:15:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(167, 39, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGD26A8FF3', NULL, '2025-08-01 13:16:14', '2025-08-01 16:39:32', 1, '2025-08-01 10:16:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(168, 39, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG415A88E6', NULL, '2025-08-01 13:23:21', '2025-08-01 16:39:32', 1, '2025-08-01 10:23:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(169, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG978D7C09', NULL, '2025-08-01 13:23:51', '2025-08-01 16:39:32', 1, '2025-08-01 10:23:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(170, 39, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG34B034B0', NULL, '2025-08-01 13:38:35', '2025-08-01 16:39:32', 1, '2025-08-01 10:38:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(171, 40, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG159DA469', NULL, '2025-08-01 13:41:16', '2025-08-01 16:39:32', 1, '2025-08-01 10:41:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(172, 40, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG179F2316', NULL, '2025-08-01 14:14:26', '2025-08-01 16:39:32', 1, '2025-08-01 11:14:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(173, 40, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG656A280D', NULL, '2025-08-01 14:15:15', '2025-08-01 16:39:32', 1, '2025-08-01 11:15:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(174, 40, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG7E71DE00', NULL, '2025-08-01 14:24:00', '2025-08-01 16:39:32', 1, '2025-08-01 11:24:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(175, 40, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG6E1ED89E', NULL, '2025-08-01 14:24:34', '2025-08-01 16:39:32', 1, '2025-08-01 11:24:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(176, 40, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG6222E394', NULL, '2025-08-01 14:31:58', '2025-08-01 16:39:32', 1, '2025-08-01 11:31:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(177, 40, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG4F6A4E7D', NULL, '2025-08-01 14:32:38', '2025-08-01 16:39:32', 1, '2025-08-01 11:32:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(178, 40, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG90B350C5', NULL, '2025-08-01 15:10:23', '2025-08-01 16:39:32', 1, '2025-08-01 12:10:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(179, 40, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CGCD625D37', NULL, '2025-08-01 15:10:28', '2025-08-01 16:39:32', 1, '2025-08-01 12:10:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(180, 40, 58.02, 33.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CGECD49F6C', NULL, '2025-08-01 15:11:16', '2025-08-01 16:39:32', 1, '2025-08-01 12:11:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(181, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG60D4DF29', NULL, '2025-08-01 16:10:01', '2025-08-01 16:39:32', 1, '2025-08-01 13:10:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(182, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG8FCC5AC0', NULL, '2025-08-01 16:19:24', '2025-08-01 16:39:32', 1, '2025-08-01 13:19:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(183, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG15EA9775', NULL, '2025-08-01 16:19:54', '2025-08-01 16:39:32', 1, '2025-08-01 13:19:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(184, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG42BD51F9', NULL, '2025-08-01 16:19:56', '2025-08-01 16:39:32', 1, '2025-08-01 13:19:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(185, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CGA9DAB67A', NULL, '2025-08-01 16:23:16', '2025-08-01 16:39:32', 1, '2025-08-01 13:23:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(186, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG1F455728', NULL, '2025-08-01 16:24:14', '2025-08-01 16:39:32', 1, '2025-08-01 13:24:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(187, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CGCF87BB54', NULL, '2025-08-01 16:30:38', '2025-08-01 16:39:32', 1, '2025-08-01 13:30:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(188, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG26264A26', NULL, '2025-08-01 16:31:23', '2025-08-01 16:39:32', 1, '2025-08-01 13:31:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(189, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG496EF45D', NULL, '2025-08-01 16:40:42', '2025-08-01 16:40:42', 0, '2025-08-01 13:40:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(190, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CGE34EA553', NULL, '2025-08-01 16:52:20', '2025-08-01 16:52:20', 0, '2025-08-01 13:52:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(191, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CGDFDBFCEA', NULL, '2025-08-01 16:52:53', '2025-08-01 16:52:53', 0, '2025-08-01 13:52:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(192, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG17AB6DD3', NULL, '2025-08-01 17:32:36', '2025-08-01 17:32:36', 0, '2025-08-01 14:32:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(193, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG16F87AE4', NULL, '2025-08-01 18:08:19', '2025-08-01 18:08:20', 0, '2025-08-01 15:08:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(194, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CGB2B2A047', NULL, '2025-08-01 18:28:11', '2025-08-01 18:28:11', 0, '2025-08-01 15:28:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(195, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG1294F24B', NULL, '2025-08-01 18:54:02', '2025-08-01 18:54:02', 0, '2025-08-01 15:54:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(196, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG38D12027', NULL, '2025-08-01 18:55:13', '2025-08-01 18:55:13', 0, '2025-08-01 15:55:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(197, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG32B74714', NULL, '2025-08-01 18:59:00', '2025-08-01 18:59:00', 0, '2025-08-01 15:59:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(198, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG5CC3FEE7', NULL, '2025-08-01 19:07:35', '2025-08-01 19:07:35', 0, '2025-08-01 16:07:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(199, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CGD8BEEE86', NULL, '2025-08-01 19:26:24', '2025-08-01 19:26:24', 0, '2025-08-01 16:26:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(200, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CG99AFF9D5', NULL, '2025-08-01 19:26:47', '2025-08-01 19:26:48', 0, '2025-08-01 16:26:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(201, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CGF0D7E453', NULL, '2025-08-01 20:07:56', '2025-08-01 20:07:56', 0, '2025-08-01 17:07:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(202, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CGF5759FDD', NULL, '2025-08-01 20:19:33', '2025-08-01 20:19:33', 0, '2025-08-01 17:19:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(203, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '582', '08501-300', 'Casa 2', NULL, 'CGF60B2333', NULL, '2025-08-01 20:20:03', '2025-08-01 20:20:03', 0, '2025-08-01 17:20:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(204, 40, 66.58, 33.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luis Carlos Neves Silva, 111', '582', '08501-300', 'Casa 2', NULL, 'CGEFA31A17', NULL, '2025-08-01 20:22:25', '2025-08-01 20:22:25', 0, '2025-08-01 17:22:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(205, 31, 56.13, 22.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGCF6F5582', NULL, '2025-08-01 23:53:03', '2025-08-01 23:53:03', 0, '2025-08-01 20:53:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(206, 31, 56.13, 22.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG6483FA48', NULL, '2025-08-01 23:57:45', '2025-08-01 23:57:45', 0, '2025-08-01 20:57:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(207, 31, 56.13, 22.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG6FF875C0', NULL, '2025-08-02 00:13:45', '2025-08-02 00:13:45', 0, '2025-08-01 21:13:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(208, 31, 56.13, 22.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG5EE4C0E5', NULL, '2025-08-02 00:14:22', '2025-08-02 00:14:22', 0, '2025-08-01 21:14:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(209, 34, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG3D805F4C', NULL, '2025-08-02 00:15:44', '2025-08-02 00:15:44', 0, '2025-08-01 21:15:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(210, 34, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGCF2CE475', NULL, '2025-08-02 00:25:47', '2025-08-02 00:25:47', 0, '2025-08-01 21:25:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(211, 34, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGAEC8A3E3', NULL, '2025-08-02 00:26:51', '2025-08-02 00:26:51', 0, '2025-08-01 21:26:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(212, 34, 37.12, 11.00, 26.67, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG4826C541', NULL, '2025-08-02 00:45:58', '2025-08-02 00:45:58', 0, '2025-08-01 21:45:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(213, 34, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG3B0D15E5', NULL, '2025-08-02 00:54:26', '2025-08-02 00:54:26', 0, '2025-08-01 21:54:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(214, 34, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG63A481B1', NULL, '2025-08-02 00:57:12', '2025-08-02 00:57:12', 0, '2025-08-01 21:57:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(215, 34, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG93E1CDD5', NULL, '2025-08-02 00:57:38', '2025-08-02 00:57:38', 0, '2025-08-01 21:57:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(216, 34, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG3276564D', NULL, '2025-08-02 00:57:41', '2025-08-02 00:57:41', 0, '2025-08-01 21:57:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(217, 34, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG1D9DD671', NULL, '2025-08-02 00:59:51', '2025-08-02 00:59:51', 0, '2025-08-01 21:59:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(218, 34, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luis Carlos Neves Silva', '111', '08501-300', '', NULL, 'CG8548F6C7', NULL, '2025-08-02 01:00:13', '2025-08-02 01:00:13', 0, '2025-08-01 22:00:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(219, 34, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG1701252B', NULL, '2025-08-02 01:06:13', '2025-08-02 01:06:13', 0, '2025-08-01 22:06:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(220, 34, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CG7184095B', NULL, '2025-08-02 01:11:07', '2025-08-02 01:11:07', 0, '2025-08-01 22:11:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(221, 34, 45.68, 11.00, 35.23, 0.00, 'pix', '', 1, 'aguardando_pagamento', 'Rua Luiz Carlos Neves Silva, Jardim Juliana, Ferraz de Vasconcelos, SP', '111', '08501-300', 'Casa 2', NULL, 'CGF9C79839', NULL, '2025-08-02 01:15:46', '2025-08-02 01:15:46', 0, '2025-08-01 22:15:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_purchase`) VALUES
(88, 88, 53, 2, 58.00),
(89, 89, 53, 3, 58.00),
(92, 92, 53, 1, 58.00),
(93, 93, 53, 3, 58.00),
(94, 94, 54, 1, 23.00),
(95, 95, 54, 1, 23.00),
(96, 96, 54, 1, 23.00),
(97, 97, 54, 1, 23.00),
(98, 98, 54, 1, 23.00),
(99, 102, 54, 1, 23.00),
(100, 103, 53, 1, 90.00),
(101, 105, 53, 1, 90.00),
(102, 106, 53, 1, 90.00),
(103, 107, 53, 2, 90.00),
(104, 108, 54, 1, 23.00),
(105, 110, 54, 1, 23.00),
(106, 111, 61, 1, 11.00),
(107, 112, 54, 1, 23.00),
(108, 113, 53, 16, 90.00),
(109, 114, 53, 1, 90.00),
(110, 115, 54, 1, 400.00),
(111, 115, 61, 1, 11.00),
(112, 116, 54, 1, 400.00),
(113, 116, 61, 1, 11.00),
(114, 117, 54, 1, 400.00),
(115, 119, 54, 1, 400.00),
(116, 121, 54, 1, 400.00),
(117, 122, 54, 1, 400.00),
(118, 123, 54, 1, 400.00),
(119, 124, 54, 1, 400.00),
(120, 125, 54, 2, 400.00),
(121, 126, 54, 1, 400.00),
(122, 127, 54, 1, 400.00),
(123, 128, 54, 1, 400.00),
(124, 129, 61, 1, 11.00),
(125, 130, 61, 1, 11.00),
(126, 131, 61, 1, 11.00),
(127, 132, 61, 1, 11.00),
(128, 133, 61, 1, 11.00),
(129, 134, 61, 1, 11.00),
(130, 135, 61, 1, 11.00),
(131, 138, 61, 1, 11.00),
(132, 139, 61, 1, 11.00),
(133, 140, 61, 1, 11.00),
(134, 141, 61, 1, 11.00),
(135, 142, 61, 1, 11.00),
(136, 143, 61, 1, 11.00),
(137, 144, 61, 1, 11.00),
(138, 145, 61, 1, 11.00),
(139, 146, 61, 1, 11.00),
(140, 147, 61, 1, 11.00),
(141, 148, 61, 1, 11.00),
(142, 149, 61, 1, 11.00),
(143, 150, 61, 1, 11.00),
(144, 151, 61, 1, 11.00),
(145, 152, 61, 1, 11.00),
(146, 153, 61, 1, 11.00),
(147, 154, 61, 1, 11.00),
(148, 155, 61, 1, 11.00),
(149, 156, 61, 1, 11.00),
(150, 157, 61, 1, 11.00),
(151, 158, 61, 1, 11.00),
(152, 159, 61, 1, 11.00),
(153, 160, 61, 1, 11.00),
(154, 161, 61, 1, 11.00),
(155, 162, 61, 1, 11.00),
(156, 163, 61, 1, 11.00),
(157, 164, 61, 1, 11.00),
(158, 165, 61, 1, 11.00),
(159, 166, 61, 1, 11.00),
(160, 167, 61, 1, 11.00),
(161, 168, 61, 1, 11.00),
(162, 169, 61, 1, 11.00),
(163, 170, 61, 1, 11.00),
(164, 171, 61, 1, 11.00),
(165, 172, 61, 1, 11.00),
(166, 173, 61, 1, 11.00),
(167, 174, 61, 1, 11.00),
(168, 175, 61, 1, 11.00),
(169, 176, 61, 1, 11.00),
(170, 177, 61, 1, 11.00),
(171, 178, 61, 1, 11.00),
(172, 179, 61, 1, 11.00),
(173, 180, 61, 3, 11.00),
(174, 181, 61, 3, 11.00),
(175, 182, 61, 3, 11.00),
(176, 183, 61, 3, 11.00),
(177, 184, 61, 3, 11.00),
(178, 185, 61, 3, 11.00),
(179, 186, 61, 3, 11.00),
(180, 187, 61, 3, 11.00),
(181, 188, 61, 3, 11.00),
(182, 189, 61, 3, 11.00),
(183, 190, 61, 3, 11.00),
(184, 191, 61, 3, 11.00),
(185, 192, 61, 3, 11.00),
(186, 193, 61, 3, 11.00),
(187, 194, 61, 3, 11.00),
(188, 195, 61, 3, 11.00),
(189, 196, 61, 3, 11.00),
(190, 197, 61, 3, 11.00),
(191, 198, 61, 3, 11.00),
(192, 199, 61, 3, 11.00),
(193, 200, 61, 3, 11.00),
(194, 201, 61, 3, 11.00),
(195, 202, 61, 3, 11.00),
(196, 203, 61, 3, 11.00),
(197, 204, 61, 3, 11.00),
(198, 205, 61, 2, 11.00),
(199, 206, 61, 2, 11.00),
(200, 207, 61, 2, 11.00),
(201, 208, 61, 2, 11.00),
(202, 209, 61, 1, 11.00),
(203, 210, 61, 1, 11.00),
(204, 211, 61, 1, 11.00),
(205, 212, 61, 1, 11.00),
(206, 213, 61, 1, 11.00),
(207, 214, 61, 1, 11.00),
(208, 215, 61, 1, 11.00),
(209, 216, 61, 1, 11.00),
(210, 217, 61, 1, 11.00),
(211, 218, 61, 1, 11.00),
(212, 219, 61, 1, 11.00),
(213, 220, 61, 1, 11.00),
(214, 221, 61, 1, 11.00);

-- --------------------------------------------------------

--
-- Estrutura da tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method` enum('credit_card','pix') NOT NULL,
  `status` enum('aguardando_pagamento','processando','enviado','entregue','cancelado','finalizado') NOT NULL,
  `shipping_address` varchar(255) NOT NULL,
  `shipping_number` varchar(20) NOT NULL,
  `shipping_cep` varchar(10) NOT NULL,
  `shipping_complement` varchar(100) DEFAULT NULL,
  `card_last4` varchar(4) DEFAULT NULL,
  `tracking_code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text,
  `imagem` varchar(255) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `comprimento` decimal(6,2) DEFAULT '40.00',
  `largura` decimal(6,2) DEFAULT '40.00',
  `altura` decimal(6,2) DEFAULT '35.00',
  `peso` decimal(6,2) DEFAULT '2.00',
  `caixa` enum('P','M','G') DEFAULT 'G',
  `quantidade` int(11) DEFAULT '0',
  `categoria` varchar(255) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `destaque` tinyint(1) NOT NULL DEFAULT '0',
  `desconto` decimal(5,2) DEFAULT NULL COMMENT 'Percentual de desconto (ex.: 20.00 para 20%)',
  `lancamento` tinyint(1) NOT NULL DEFAULT '0',
  `em_alta` tinyint(1) NOT NULL DEFAULT '0',
  `promocao` tinyint(1) NOT NULL DEFAULT '0',
  `ncm` varchar(8) DEFAULT '70139110' COMMENT 'Código NCM do produto',
  `cfop` varchar(4) DEFAULT '5102' COMMENT 'Código CFOP padrão do produto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `imagem`, `preco`, `comprimento`, `largura`, `altura`, `peso`, `caixa`, `quantidade`, `categoria`, `data_cadastro`, `ativo`, `destaque`, `desconto`, `lancamento`, `em_alta`, `promocao`, `ncm`, `cfop`) VALUES
(53, 'ARRANJO P', '', '1753748316_Imagem_do_WhatsApp_de_2025_07_15____s__21.04.18_99d73edd.jpg', 90.00, 22.00, 22.00, 35.00, 2.00, 'P', 0, 'Muranos Color', '2025-05-12 17:01:43', 1, 1, 0.00, 0, 0, 0, '70139110', '5102'),
(54, 'ARRANJO M', '', '1753748306_Imagem_do_WhatsApp_de_2025_07_15____s__21.04.18_99d73edd.jpg', 400.00, 37.00, 37.00, 35.00, 2.00, 'M', 0, 'Arranjos', '2025-05-14 00:59:37', 1, 1, 0.00, 0, 1, 0, '70139110', '5102'),
(61, 'ARRANJO G', 'teste 3', '1753748297_Imagem_do_WhatsApp_de_2025_07_15____s__21.04.18_99d73edd.jpg', 11.00, 40.00, 40.00, 35.00, 2.00, 'G', 0, 'Vasos de Vidro', '2025-06-25 23:43:31', 1, 0, 0.00, 0, 0, 1, '70139110', '5102');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produto_avaliacoes`
--

CREATE TABLE `produto_avaliacoes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `avaliacao` tinyint(4) NOT NULL,
  `ip_usuario` varchar(45) NOT NULL,
  `data_avaliacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `produto_avaliacoes`
--

INSERT INTO `produto_avaliacoes` (`id`, `post_id`, `avaliacao`, `ip_usuario`, `data_avaliacao`) VALUES
(2, 54, 2, '201.0.253.178', '2025-06-19 15:38:44'),
(5, 61, 4, '179.113.177.22', '2025-07-01 19:26:09');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produto_comentarios`
--

CREATE TABLE `produto_comentarios` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_usuario` varchar(45) NOT NULL,
  `comentario` text NOT NULL,
  `data_comentario` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `produto_comentarios`
--

INSERT INTO `produto_comentarios` (`id`, `produto_id`, `user_id`, `ip_usuario`, `comentario`, `data_comentario`) VALUES
(2, 61, 23, '177.196.202.221', 'Produto muito bonito!', '2025-07-15 16:56:54');

-- --------------------------------------------------------

--
-- Estrutura da tabela `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `status` enum('disponível','indisponível') NOT NULL DEFAULT 'disponível',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data_compra` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `saved_cards`
--

CREATE TABLE `saved_cards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card_token` varchar(255) NOT NULL,
  `last_4_digits` varchar(4) NOT NULL,
  `cardholder_name` varchar(255) DEFAULT NULL,
  `card_brand` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `security_events`
--

CREATE TABLE `security_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_type` varchar(100) NOT NULL,
  `context` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_actions_log`
--

CREATE TABLE `user_actions_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `user_actions_log`
--

INSERT INTO `user_actions_log` (`id`, `user_id`, `action`, `details`, `created_at`) VALUES
(31, 23, 'add_favorite', 'Produto ID: 52 adicionado aos favoritos', '2025-05-11 00:04:08'),
(33, 23, 'add_favorite', 'Produto ID: 53 adicionado aos favoritos', '2025-05-12 19:11:18'),
(35, 23, 'remove_favorite', 'Produto ID: 53 removido dos favoritos', '2025-05-12 21:25:06'),
(36, 23, 'add_favorite', 'Produto ID: 53 adicionado aos favoritos', '2025-05-12 21:43:21'),
(37, 23, 'remove_favorite', 'Produto ID: 53 removido dos favoritos', '2025-05-12 22:21:57'),
(38, 23, 'add_favorite', 'Produto ID: 53 adicionado aos favoritos', '2025-05-12 23:31:29'),
(39, 23, 'add_favorite', 'Produto ID: 54 adicionado aos favoritos', '2025-05-14 00:59:44'),
(40, 23, 'remove_favorite', 'Produto ID: 53 removido dos favoritos', '2025-05-14 01:00:39'),
(41, 23, 'add_favorite', 'Produto ID: 53 adicionado aos favoritos', '2025-05-14 01:02:16'),
(42, 23, 'remove_favorite', 'Produto ID: 53 removido dos favoritos', '2025-05-14 01:06:19'),
(43, 23, 'remove_favorite', 'Produto ID: 54 removido dos favoritos', '2025-05-14 01:06:19'),
(44, 23, 'add_favorite', 'Produto ID: 53 adicionado aos favoritos', '2025-05-14 17:44:28'),
(45, 23, 'remove_favorite', 'Produto ID: 53 removido dos favoritos', '2025-05-14 17:57:47'),
(46, 23, 'add_favorite', 'Produto ID: 53 adicionado aos favoritos', '2025-05-14 17:59:21'),
(47, 23, 'add_favorite', 'Produto ID: 54 adicionado aos favoritos', '2025-05-14 17:59:26'),
(48, 23, 'remove_favorite', 'Produto ID: 54 removido dos favoritos', '2025-05-14 19:20:13');

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_activity`
--

CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_cards`
--

CREATE TABLE `user_cards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card_token` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `card_last4` varchar(4) COLLATE latin1_general_ci NOT NULL,
  `card_name` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `card_expiry` varchar(5) COLLATE latin1_general_ci NOT NULL,
  `card_brand` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_cart`
--

CREATE TABLE `user_cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `user_cart`
--

INSERT INTO `user_cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(189, 27, 53, 1, '2025-06-03 13:55:00', '2025-06-03 13:55:00'),
(224, 37, 54, 1, '2025-07-10 14:56:35', '2025-07-10 14:56:35'),
(269, 38, 61, 1, '2025-07-19 17:09:41', '2025-07-19 17:09:41'),
(272, 36, 53, 5, '2025-07-22 23:18:39', '2025-07-22 23:35:29'),
(296, 36, 54, 1, '2025-07-31 20:41:50', '2025-07-31 20:41:50'),
(299, 31, 61, 2, '2025-08-01 02:59:21', '2025-08-01 20:25:24'),
(300, 23, 61, 1, '2025-08-01 03:08:31', '2025-08-01 03:08:31'),
(301, 39, 61, 1, '2025-08-01 03:24:51', '2025-08-01 03:24:51'),
(302, 40, 61, 3, '2025-08-01 13:39:09', '2025-08-01 15:10:42'),
(304, 34, 61, 1, '2025-08-02 00:15:24', '2025-08-02 00:15:24'),
(305, 41, 53, 1, '2025-08-06 23:17:06', '2025-08-06 23:17:06');

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_favorites`
--

CREATE TABLE `user_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `bling_id` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'img/icons/perfil.png',
  `endereco` varchar(255) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `numero_casa` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `otp_secret` varchar(255) DEFAULT NULL,
  `primeira_compra` tinyint(1) NOT NULL DEFAULT '1',
  `id_externo_bling` varchar(50) DEFAULT NULL COMMENT 'ID do contato no Bling'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `bling_id`, `name`, `email`, `password`, `profile_picture`, `endereco`, `cep`, `numero_casa`, `telefone`, `cpf`, `otp_secret`, `primeira_compra`, `id_externo_bling`) VALUES
(23, NULL, 'Matheus Lucindo', 'matheuslucindo904@gmail.com', '$2y$10$Kdmq2UAz4aMFlXvgvTnyk.8wU/.9/6KwDTWBYpIHpm0Kq02zAcVpW', 'https://lh3.googleusercontent.com/a/ACg8ocLEhCYiON6ay8J8aPHAMB0jgybjIOaqsQ4Y6nKo_SBYZZsnhrpCGw=s96-c', 'Rua Luis Carlos Neves Silva, 111', '08501-300', '111', '11971666817', '503.092.768-96', NULL, 0, NULL),
(26, NULL, 'Samuel', 'samuelhenrique084@gmail.com', '$2y$10$.XZGNGp2aRXB0G4387SIl.C7/hIG4bW0XV6Cg9I1LzBAdtMGtCeOC', 'img/icons/perfil.png', 'Rua doutor Antenor Damini', '37704-096', '304', '(35) 99764-4133', NULL, NULL, 1, NULL),
(27, NULL, 'Guh Drum', 'guhdrum@gmail.com', '$2y$10$/Rgf.yV1kgttWwZ5vBaXxeetZ/RFe6W4TJH.58OgD7hgyaGkZAfya', 'https://lh3.googleusercontent.com/a/ACg8ocJoZ80ecv0dozEr6yepHM8RTmCpmyh5rRXdjSGcl65lwWAuqA=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(28, NULL, 'TarcÃ­sio JosÃ© dos Santos', 'tarcisiojosedossantoss@gmail.com', '$2y$10$yI9Fb6Zi5ik6n8ax67nxgebx2wqhERa5K3bRkkVmGcth0.5ERk1Nm', 'https://lh3.googleusercontent.com/a/ACg8ocLlezdeqzK3Z-Chj0d62FYHP1-hh14XpDH73NU6fsd7t1z8zQVBPw=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(29, NULL, 'DANIELE FABIOLA MENDES BUZINA', 'flordeluxoeventos@hotmail.com', '$2y$10$MosP4matspGqZuptG04/h./rNp1e2.D7VdB52V6PwyXSx2eogFXLa', 'img/icons/perfil.png', 'Rua Tapera de Garganta Branca', '86706-686', '154', '(43) 99626-8281', NULL, NULL, 1, NULL),
(30, NULL, 'francisco rocha', 'francisco@gmail.com', '$2y$10$b8Q9sr81dLZ4mpaJFhKPrOjN7E6z.B1iALH6JPYMSvvBO9/SzUOAy', 'img/icons/perfil.png', 'Rua Luis Carlos Neves Silva', '08501-300', '111', '(11) 97166-6817', NULL, NULL, 1, NULL),
(31, NULL, 'heloisa', 'heloisa@gmail.com', '$2y$10$RORS9Qha/L4hlwfe6ahWMOC7/bTkDSnhFJU0Tg/O6/pLVFwWhKsUq', 'img/icons/perfil.png', 'Rua Luis Carlos Neves Silva', '08501-300', '111', '(11) 99999-6666', '111.444.777-35', NULL, 1, '17566128290'),
(32, NULL, 'renata', 'renata@gmail.com', '$2y$10$KxZe2a1BVbvaWzm7kX5jUejxbJEWbNa2wP.znERXRV66rRpNBjJBi', 'img/icons/perfil.png', 'Rua Luis Carlos Neves Silva', '08501-300', '111', '(11) 97166-6817', '290.826.138-32', NULL, 1, NULL),
(33, NULL, '', '', '$2y$10$t.Sweq2PyyS8MwbVmXrbruUMM.uBQ4df3iQZRXkxtVO6v43rhj9sm', 'img/icons/perfil.png', '', '', '', '', NULL, NULL, 1, NULL),
(34, NULL, 'Vanessa', 'vanessa@hotmail.com', '$2y$10$/aompDgUD3IEYMPS3I1Q5Oq4XTXVwL5Cz3UOTEmo02TM8EOZQJQ42', 'img/icons/perfil.png', 'Rua Luis Carlos Neves Silva', '08501-300', '111', '(11) 97166-6817', '503.092.856-96', NULL, 1, '17566165047'),
(35, NULL, 'Bradesco', 'bradesco@gmail.com', '$2y$10$t8N3FvKce88oNnOgIfr6Zux.p7KT0ksINA/kKji.XUNGewfRGBnGu', 'img/icons/perfil.png', 'Rua Luis Carlos Neves Silva', '08501-300', '111', '(11) 97166-6817', '555.555.555-55', NULL, 1, NULL),
(36, NULL, 'Ingrid', 'ingridsantos36@outlook.com', '$2y$10$UCJ5V97GDJDOhV6DVpQtJe7LlfenuzuJqf0CcijnnDqlYwg7tOKnK', 'img/icons/perfil.png', 'Estrada miguel dib jorge ', '08503-000', '605', '(11) 94499-0841', '490.474.848-43', NULL, 1, NULL),
(37, NULL, 'Samuel Mendes', 'samuca_22le@hotmail.com', '$2y$10$9OHhIIfYrjqZBqJo4orTQOr4LKBmF6V1MwT0PwnW2tfgaPrvWKk0y', 'https://lh3.googleusercontent.com/a/ACg8ocLpPAY8G4yKHf35xpcsNGccbOhfWCZIfvDI3VZwRQbof2sOML9A=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(38, NULL, 'Leonardo Natis', 'leoblanconatis@gmail.com', '$2y$10$unHg1yUCAUte9v.nniJb2OJPvP7sd8HNCE9fzWpryhJxcNOHNDz5u', 'https://lh3.googleusercontent.com/a/ACg8ocJwsct2cdlKmyVrDHOFUZvWNhALzO8gRx4Fyp4vQMGiBL9RIQ=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(39, '6', 'Matheus Programador', 'matheusprogramador760@gmail.com', '$2y$10$rxfb/U0qfZGQDvu4LQowcemP7jhvUQJbcaKWEk6ja/myI6sNBvA0.', 'https://lh3.googleusercontent.com/a/ACg8ocLI_3QjSeq8SQM3bmLov0b9sj83qTA-AjDVf_t_XM4dCua8CB8=s96-c', 'Rua Luis Carlos Neves Silva, 111', '08501-300', '111', '11971666817', '292.695.818-88', NULL, 1, '17564479840'),
(40, NULL, 'Matheus Homes', 'matheushomes904@gmail.com', '$2y$10$/fRywtCRsZicrVbqqMVSIOjFKaH7lK5UyzXH1TrQOOxn02x7.jCoi', 'https://lh3.googleusercontent.com/a/ACg8ocJBvDUjEDUMIUho2CHzcl0KvSvabYgDOMHjOH1hCK5U1WBSKSR_=s96-c', 'Rua Luis Carlos Neves Silva, 111', '08501-300', '582', '11971666817', '390.533.447-05', NULL, 1, '17565280286'),
(41, NULL, 'github copilot', 'github@gmail.com', '$2y$10$DTSh5oRlWW/DbnJjIL640ux8rTqVJeU.XuAnt2YT4FTTIYB.sU1Ae', 'img/icons/perfil.png', 'Rua Doutor CÃ¢ndido Xavier de Almeida e Souza, Vila Partenio', '08780-911', '200', '(11) 97166-6817', '295.379.458-06', NULL, 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bling_configuracoes`
--
ALTER TABLE `bling_configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`),
  ADD KEY `idx_chave` (`chave`);

--
-- Indexes for table `bling_integration_log`
--
ALTER TABLE `bling_integration_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bling_pedidos_contador`
--
ALTER TABLE `bling_pedidos_contador`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_comentarios`
--
ALTER TABLE `blog_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `carousel_images`
--
ALTER TABLE `carousel_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `carrinho`
--
ALTER TABLE `carrinho`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`);

--
-- Indexes for table `checkout_data`
--
ALTER TABLE `checkout_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_field_per_session` (`session_id`,`field_name`);

--
-- Indexes for table `checkout_fields`
--
ALTER TABLE `checkout_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_field_per_session` (`session_id`,`field_name`);

--
-- Indexes for table `checkout_sessions`
--
ALTER TABLE `checkout_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`);

--
-- Indexes for table `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `itens_rastreados`
--
ALTER TABLE `itens_rastreados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_produto_id_rast` (`produto_id`);

--
-- Indexes for table `logins`
--
ALTER TABLE `logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `nfe_logs`
--
ALTER TABLE `nfe_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nfe_logs_order_id` (`order_id`),
  ADD KEY `idx_nfe_logs_event_type` (`event_type`),
  ADD KEY `idx_nfe_logs_created_at` (`created_at`),
  ADD KEY `idx_nfe_logs_provider` (`provider`);

--
-- Indexes for table `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_orders_nfe_key` (`nfe_key`),
  ADD KEY `idx_bling_pedido_id` (`bling_pedido_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produto_avaliacoes`
--
ALTER TABLE `produto_avaliacoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_post_ip` (`post_id`,`ip_usuario`);

--
-- Indexes for table `produto_comentarios`
--
ALTER TABLE `produto_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saved_cards`
--
ALTER TABLE `saved_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_saved_cards_user` (`user_id`);

--
-- Indexes for table `security_events`
--
ALTER TABLE `security_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_security_events_type` (`event_type`),
  ADD KEY `idx_security_events_user` (`user_id`);

--
-- Indexes for table `user_actions_log`
--
ALTER TABLE `user_actions_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_activity_lookup` (`user_id`,`ip_address`,`activity_type`,`created_at`);

--
-- Indexes for table `user_cards`
--
ALTER TABLE `user_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_user_sessions_token` (`token`),
  ADD KEY `idx_user_sessions_expires` (`expires_at`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_id_externo_bling` (`id_externo_bling`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `bling_configuracoes`
--
ALTER TABLE `bling_configuracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bling_integration_log`
--
ALTER TABLE `bling_integration_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bling_pedidos_contador`
--
ALTER TABLE `bling_pedidos_contador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blog`
--
ALTER TABLE `blog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_comentarios`
--
ALTER TABLE `blog_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carousel_images`
--
ALTER TABLE `carousel_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `carrinho`
--
ALTER TABLE `carrinho`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `checkout_data`
--
ALTER TABLE `checkout_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checkout_fields`
--
ALTER TABLE `checkout_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `checkout_sessions`
--
ALTER TABLE `checkout_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `entregas`
--
ALTER TABLE `entregas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `itens_pedido`
--
ALTER TABLE `itens_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `itens_rastreados`
--
ALTER TABLE `itens_rastreados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logins`
--
ALTER TABLE `logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `nfe_logs`
--
ALTER TABLE `nfe_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=215;

--
-- AUTO_INCREMENT for table `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `produto_avaliacoes`
--
ALTER TABLE `produto_avaliacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `produto_comentarios`
--
ALTER TABLE `produto_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_cards`
--
ALTER TABLE `saved_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_events`
--
ALTER TABLE `security_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_actions_log`
--
ALTER TABLE `user_actions_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_cards`
--
ALTER TABLE `user_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=306;

--
-- AUTO_INCREMENT for table `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `blog_comentarios`
--
ALTER TABLE `blog_comentarios`
  ADD CONSTRAINT `fk_blog_comentarios_post` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `checkout_fields`
--
ALTER TABLE `checkout_fields`
  ADD CONSTRAINT `checkout_fields_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `checkout_sessions` (`session_id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `entregas`
--
ALTER TABLE `entregas`
  ADD CONSTRAINT `entregas_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD CONSTRAINT `itens_pedido_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itens_pedido_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produtos` (`id`);

--
-- Limitadores para a tabela `itens_rastreados`
--
ALTER TABLE `itens_rastreados`
  ADD CONSTRAINT `itens_rastreados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `logins`
--
ALTER TABLE `logins`
  ADD CONSTRAINT `logins_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `nfe_logs`
--
ALTER TABLE `nfe_logs`
  ADD CONSTRAINT `nfe_logs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD CONSTRAINT `notificacoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produtos` (`id`);

--
-- Limitadores para a tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `produto_avaliacoes`
--
ALTER TABLE `produto_avaliacoes`
  ADD CONSTRAINT `produto_avaliacoes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `produto_comentarios`
--
ALTER TABLE `produto_comentarios`
  ADD CONSTRAINT `produto_comentarios_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `produto_comentarios_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `saved_cards`
--
ALTER TABLE `saved_cards`
  ADD CONSTRAINT `saved_cards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `security_events`
--
ALTER TABLE `security_events`
  ADD CONSTRAINT `security_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `user_actions_log`
--
ALTER TABLE `user_actions_log`
  ADD CONSTRAINT `user_actions_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `user_activity`
--
ALTER TABLE `user_activity`
  ADD CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `user_cards`
--
ALTER TABLE `user_cards`
  ADD CONSTRAINT `user_cards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`);

--
-- Limitadores para a tabela `user_cart`
--
ALTER TABLE `user_cart`
  ADD CONSTRAINT `user_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
