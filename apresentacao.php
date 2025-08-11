<?php
    // Verifica se a sessão já está ativa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Função para registrar o acesso à página
    function registrarAcesso() {
        // Aqui você pode implementar um registro de log ou contador de visitas
        $logFile = 'acessos_log.txt';
        $data = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'];
        $mensagem = "Acesso em: $data - IP: $ip\n";
        
        // Escreve no arquivo de log
        file_put_contents($logFile, $mensagem, FILE_APPEND);
        
        // Você também poderia usar um banco de dados
        // insertAcessoDB($data, $ip);
    }

    // Gera token de segurança para próxima página
    function gerarTokenSeguranca() {
        $_SESSION['token'] = bin2hex(random_bytes(32));
        return $_SESSION['token'];
    }

    // Preparar dados para a próxima página
    function prepararProximaPagina() {
        $_SESSION['origem'] = 'pagina_apresentacao';
        $_SESSION['timestamp'] = time();
        
        // Gera um token de segurança
        $token = gerarTokenSeguranca();
        
        // Modifica o link do botão para incluir o token
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const btn = document.querySelector(".btn");
                btn.href = "Site/index.php?token=' . $token . '";
            });
        </script>';
    }

    // Executar funções PHP
    registrarAcesso();
    prepararProximaPagina();
    ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="adminView/assets/images/logo.png" type="image/x-icon">
    <title>Cristais Gold Lar - Bem-vindo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #fff;
            color: #000;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        .container {
            max-width: 1200px;
            text-align: center;
            padding: 30px;
            position: relative;
            z-index: 10;
            opacity: 0;
            transform: translateY(30px);
        }

        .logo {
            font-size: 3rem;
            font-weight: 700;
            color: #F3ba00;
            margin-bottom: 20px;
            letter-spacing: 2px;
            opacity: 0;
        }

        .subtitle {
            font-size: 1.5rem;
            font-weight: 300;
            margin-bottom: 30px;
            opacity: 0;
        }

        .description {
            font-size: 1.1rem;
            line-height: 1.7;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0;
        }
        
        .logo-container {
            width: 180px;
            height: 180px;
            background-color: #f9f9f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 2px solid #F3ba00;
            opacity: 0;
            transform: scale(0.5);
        }
        
        .logo-image {
            width: 95%;
            height: 95%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .btn {
            display: inline-block;
            background-color:  #F3ba00;
            color: #000;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 500;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            opacity: 0;
            transform: scale(0.9);
        }

        .btn:hover {
            background-color: #000;
            color: #fff;
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3);
        }

        .leaf {
            position: absolute;
            background-color: rgba(117, 159, 64, 0.15);
            width: 60px;
            height: 30px;
            border-radius: 50% 50% 0 50%;
            opacity: 0;
            z-index: 1;
        }
        
        .leaf:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50% 50% 0 50%;
            border: 1px solid rgba(117, 159, 64, 0.3);
        }

        .decoration {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,215,0,0.08) 0%, rgba(255,255,255,0) 70%);
        }

        .decoration.top-right {
            top: -150px;
            right: -150px;
        }

        .decoration.bottom-left {
            bottom: -150px;
            left: -150px;
        }

        /* Loader que será escondido após carregar */
        .loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 100;
        }

        .loader-content {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color:  #F3ba00;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <!-- Loader que será animado para desaparecer -->
    <div class="loader">
        <div class="loader-content"></div>
    </div>

    <!-- Elementos decorativos -->
    <div class="decoration top-right"></div>
    <div class="decoration bottom-left"></div>

    <div class="container">
        <div class="logo-container">
            <div class="logo-image">
            <img src="Site/img/logo3.png" alt="Cristais Gold Lar">
            </div>
        </div>
        <h1 class="logo">Cristais Gold Lar</h1>
        <h2 class="subtitle">Seja Bem-Vindo à Nossa Loja</h2>
        <p class="description">
            Descubra a magia dos nossos arranjos de vidro exclusivos. Peças que transformam ambientes 
            e refletem elegância com design único.
        </p>
        <a href="Site/index.php" class="btn">Começar</a>
    </div>

    <!-- Elementos de folhas para decoração -->
    <div id="leaves-container"></div>

    <script>
        // Aguardar o carregamento completo do DOM
        document.addEventListener('DOMContentLoaded', function() {
            // Criar folhas decorativas
            function createLeaves() {
                const container = document.getElementById('leaves-container');
                const numberOfLeaves = 20;
                
                for (let i = 0; i < numberOfLeaves; i++) {
                    const leaf = document.createElement('div');
                    leaf.classList.add('leaf');
                    
                    // Posição aleatória
                    const posX = Math.random() * window.innerWidth;
                    const posY = Math.random() * window.innerHeight;
                    
                    // Tamanho aleatório
                    const size = Math.random() * 40 + 20;
                    const rotationAngle = Math.random() * 360;
                    
                    leaf.style.left = `${posX}px`;
                    leaf.style.top = `${posY}px`;
                    leaf.style.width = `${size}px`;
                    leaf.style.height = `${size/2}px`;
                    leaf.style.transform = `rotate(${rotationAngle}deg)`;
                    
                    container.appendChild(leaf);
                }
            }

            // Iniciar animações GSAP
            function startAnimations() {
                // Esconder o loader
                gsap.to('.loader', {
                    opacity: 0,
                    duration: 1,
                    onComplete: function() {
                        document.querySelector('.loader').style.display = 'none';
                    }
                });

                // Animar container principal
                gsap.to('.container', {
                    opacity: 1,
                    y: 0,
                    duration: 1.2,
                    ease: "power3.out",
                    delay: 0.5
                });
                
                // Animar logo container
                gsap.to('.logo-container', {
                    opacity: 1,
                    scale: 1,
                    duration: 1.8,
                    delay: 0.6,
                    ease: "elastic.out(1, 0.5)",
                    onComplete: function() {
                        // Pequena animação de pulso após entrada
                        gsap.to('.logo-container', {
                            scale: 1.05,
                            duration: 0.7,
                            repeat: 1,
                            yoyo: true,
                            ease: "sine.inOut"
                        });
                    }
                });

                // Animar logo texto
                gsap.to('.logo', {
                    opacity: 1,
                    duration: 1.5,
                    delay: 1.2,
                    ease: "back.out(1.7)"
                });

                // Animar subtítulo
                gsap.to('.subtitle', {
                    opacity: 1,
                    duration: 1.5,
                    delay: 1.5,
                    ease: "power3.out"
                });

                // Animar descrição
                gsap.to('.description', {
                    opacity: 1,
                    duration: 1.5,
                    delay: 1.8,
                    ease: "power2.out"
                });

                // Animar botão
                gsap.to('.btn', {
                    opacity: 1,
                    scale: 1,
                    duration: 1.2,
                    delay: 2.1,
                    ease: "elastic.out(1, 0.3)"
                });

                // Animar folhas
                gsap.to('.leaf', {
                    opacity: 0.7,
                    stagger: 0.1,
                    duration: 2,
                    delay: 0.5,
                    ease: "power2.out"
                });

                // Animação contínua de flutuação para as folhas
                gsap.to('.leaf', {
                    y: "random(-30, 30)",
                    x: "random(-30, 30)",
                    rotation: "random(-20, 20)",
                    duration: "random(4, 8)",
                    repeat: -1,
                    yoyo: true,
                    ease: "sine.inOut",
                    stagger: {
                        each: 0.2,
                        from: "random"
                    }
                });
            }

            // Criar as folhas e iniciar animações
            createLeaves();
            startAnimations();
        });
    </script>


</body>
</html>