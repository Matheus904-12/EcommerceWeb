//login.js

const inputs = document.querySelectorAll(".input-field");
const toggle_btn = document.querySelectorAll(".toggle");
const main = document.querySelector("main");
const bullets = document.querySelectorAll(".bullets span");
const images = document.querySelectorAll(".image");

inputs.forEach((inp) => {
    inp.addEventListener("focus", () => {
        inp.classList.add("active");
    });
    inp.addEventListener("blur", () => {
        if (inp.value != "") return;
        inp.classList.remove("active");
    });
});

toggle_btn.forEach((btn) => {
    btn.addEventListener("click", () => {
        main.classList.toggle("sign-up-mode");
    });
});

function moveSlider() {
    let index = this.dataset.value;
    // Não troca mais a imagem, só o texto
    // let currentImage = document.querySelector(`.img-${index}`);
    // images.forEach((img) => img.classList.remove("show"));
    // currentImage.classList.add("show");

    const textSlider = document.querySelector(".text-group");
    textSlider.style.transform = `translateY(${-(index - 1) * 2.2}rem)`;

    bullets.forEach((bull, idx) => bull.classList.remove("active"));
    this.classList.add("active");
}

bullets.forEach((bullet) => {
    bullet.addEventListener("click", moveSlider);
});

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('signup');
    const steps = Array.from(form.getElementsByClassName('step'));
    let currentStep = 0;

    // Adiciona barra de progresso ao formulário
    const progressBar = document.createElement('div');
    progressBar.className = 'progress-bar';
    const progress = document.createElement('div');
    progress.className = 'progress';
    progressBar.appendChild(progress);
    form.insertBefore(progressBar, form.firstChild);

    // Atualiza a barra de progresso
    function updateProgress() {
        const progressPercentage = ((currentStep + 1) / steps.length) * 100;
        progress.style.width = `${progressPercentage}%`;
    }

    // Mostra o passo atual
    function showStep(stepIndex) {
        steps.forEach((step, index) => {
            step.classList.remove('active');
            if (index === stepIndex) {
                step.classList.add('active');
            }
        });
        updateProgress();
    }

    // Valida os campos do passo
    function validateStep(step) {
        const inputs = step.querySelectorAll('input[required]');
        let isValid = true;

        inputs.forEach(input => {
            input.classList.remove('input-error');
            const errorMessage = input.nextElementSibling;
            if (errorMessage && errorMessage.classList.contains('error-message')) {
                errorMessage.remove();
            }

            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('input-error');
                const error = document.createElement('div');
                error.className = 'error-message';
                error.textContent = 'Este campo é obrigatório';
                input.insertAdjacentElement('afterend', error);
            } else if (input.type === 'email' && !validateEmail(input.value)) {
                isValid = false;
                input.classList.add('input-error');
                const error = document.createElement('div');
                error.className = 'error-message';
                error.textContent = 'Email inválido';
                input.insertAdjacentElement('afterend', error);
            } else if (input.name === 'telefone' && !validatePhone(input.value)) {
                isValid = false;
                input.classList.add('input-error');
                const error = document.createElement('div');
                error.className = 'error-message';
                error.textContent = 'Telefone inválido';
                input.insertAdjacentElement('afterend', error);
            }
        });

        return isValid;
    }

    // Validação de email
    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Validação de telefone (exemplo com expressão regular)
    function validatePhone(phone) {
        return /^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/.test(phone);
    }
    

    // Lida com o clique no botão "Próximo"
    form.querySelectorAll('.next').forEach(button => {
        button.addEventListener('click', () => {
            const currentStepElement = steps[currentStep];
            if (validateStep(currentStepElement)) {
                currentStep++;
                showStep(currentStep);
            }
        });
    });

    // Lida com o clique no botão "Voltar"
    form.querySelectorAll('.prev').forEach(button => {
        button.addEventListener('click', () => {
            currentStep--;
            showStep(currentStep);
        });
    });

    // Lida com o envio do formulário
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Desabilita o botão de enviar
        const submitButton = e.submitter;
        submitButton.disabled = true;

        let formData = new FormData(form);

        fetch(form.action, {
            method: form.method,
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 422) { // Erro de validação
                    return response.json().then(data => {
                        // Exibe mensagens de erro na página
                        Object.keys(data.errors).forEach(field => {
                            const input = form.querySelector(`[name="${field}"]`);
                            const error = document.createElement('div');
                            error.className = 'error-message';
                            error.textContent = data.errors[field][0];
                            input.insertAdjacentElement('afterend', error);
                        });
                    });
                } else {
                    throw new Error('Erro na resposta do servidor: ' + response.status);
                }
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (data.otpSent) {
                    currentStep++;
                    showStep(currentStep);
                } else {
                    if (data.show_welcome_modal) {
                        showWelcomeModal();
                    } else {
                        window.location.href = "index.php";
                    }
                }
            } else {
                // Exibe mensagens de erro na página
                showSiteAlert(data.message || 'Erro ao cadastrar. Tente novamente.', 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            if (error.message.startsWith('Erro na resposta do servidor')) {
                showSiteAlert('Erro no servidor. Tente novamente mais tarde.', 'error');
            } else {
                showSiteAlert('Erro ao cadastrar. Verifique sua conexão e tente novamente.', 'error');
            }
        })
        .finally(() => {
            // Reabilita o botão de enviar
            submitButton.disabled = false;
        });
    });

    showStep(currentStep);

    form.querySelectorAll('input').forEach(input => {
        input.addEventListener('focus', () => {
            input.classList.add('active');
        });

        input.addEventListener('blur', () => {
            if (!input.value) {
                input.classList.remove('active');
            }
        });

        // Remover erro ao digitar
        input.addEventListener('input', () => {
            input.classList.remove('input-error');
            const errorMessage = input.nextElementSibling;
            if (errorMessage && errorMessage.classList.contains('error-message')) {
                errorMessage.remove();
            }
        });
    });

    // Máscara automática para CPF
    const cpfInput = document.querySelector('input[name="cpf"]');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 3 && value.length <= 6) {
                value = value.replace(/(\d{3})(\d{1,3})/, '$1.$2');
            } else if (value.length > 6 && value.length <= 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
            } else if (value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
            }
            e.target.value = value.substring(0, 14);
        });
    }

    // Busca de endereço por CEP
    const cepInput = form.querySelector('input[name="cep"]');
    if (cepInput) {
        // Máscara para CEP
        cepInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            e.target.value = value.substring(0, 9);
        });

        // Buscar endereço quando CEP estiver completo
        cepInput.addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                buscarEnderecoPorCep(cep, this);
            }
        });

        // Buscar endereço quando pressionar Enter
        cepInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const cep = this.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    buscarEnderecoPorCep(cep, this);
                }
            }
        });
    }

    // Máscara para telefone
    const telefoneInput = form.querySelector('input[name="telefone"]');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            e.target.value = value.substring(0, 15);
        });
    }

    // Função para buscar endereço por CEP
    function buscarEnderecoPorCep(cep, cepInput) {
        // Mostrar indicador de carregamento
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'cep-loading';
        loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando endereço...';
        loadingIndicator.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 12px;
            color: #666;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        `;

        // Remover indicadores anteriores
        const existingLoading = cepInput.parentElement.querySelector('.cep-loading');
        const existingFeedback = cepInput.parentElement.querySelector('.cep-feedback');
        if (existingLoading) existingLoading.remove();
        if (existingFeedback) existingFeedback.remove();

        // Adicionar indicador de carregamento
        cepInput.parentElement.style.position = 'relative';
        cepInput.parentElement.appendChild(loadingIndicator);

        // Fazer requisição para a API
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                // Remover indicador de carregamento
                loadingIndicator.remove();

                // Criar feedback visual
                const feedback = document.createElement('div');
                feedback.className = 'cep-feedback';
                feedback.style.cssText = `
                    position: absolute;
                    top: 100%;
                    left: 0;
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    padding: 8px 12px;
                    font-size: 12px;
                    z-index: 1000;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    max-width: 300px;
                `;

                if (data.erro) {
                    // CEP não encontrado
                    feedback.innerHTML = `
                        <div style="color: #f44336; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>CEP não encontrado. Verifique e tente novamente.</span>
                        </div>
                    `;
                    feedback.style.borderColor = '#f44336';
                } else {
                    // CEP encontrado
                    const enderecoInput = form.querySelector('input[name="endereco"]');
                    if (enderecoInput) {
                        enderecoInput.value = `${data.logradouro}, ${data.bairro}`;
                    }

                    feedback.innerHTML = `
                        <div style="color: #4CAF50; display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <i class="fas fa-check-circle"></i>
                            <span><strong>Endereço encontrado!</strong></span>
                        </div>
                        <div style="font-size: 11px; color: #666; line-height: 1.4;">
                            <div><strong>Logradouro:</strong> ${data.logradouro}</div>
                            <div><strong>Bairro:</strong> ${data.bairro}</div>
                            <div><strong>Cidade:</strong> ${data.localidade} - ${data.uf}</div>
                        </div>
                    `;
                    feedback.style.borderColor = '#4CAF50';

                    // Mostrar mensagem de sucesso
                    showSiteAlert('Endereço encontrado e preenchido automaticamente!', 'success');
                }

                cepInput.parentElement.appendChild(feedback);

                // Remover feedback após 5 segundos
                setTimeout(() => {
                    if (feedback.parentElement) {
                        feedback.remove();
                    }
                }, 5000);
            })
            .catch(error => {
                console.error('Erro ao buscar CEP:', error);
                loadingIndicator.remove();

                const feedback = document.createElement('div');
                feedback.className = 'cep-feedback';
                feedback.innerHTML = `
                    <div style="color: #f44336; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Erro ao buscar CEP. Tente novamente.</span>
                    </div>
                `;
                feedback.style.cssText = `
                    position: absolute;
                    top: 100%;
                    left: 0;
                    background: #fff;
                    border: 1px solid #f44336;
                    border-radius: 4px;
                    padding: 8px 12px;
                    font-size: 12px;
                    color: #f44336;
                    z-index: 1000;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                `;

                cepInput.parentElement.appendChild(feedback);

                setTimeout(() => {
                    if (feedback.parentElement) {
                        feedback.remove();
                    }
                }, 5000);
            });
    }

    // --- Medidor de força de senha ---
    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[@$!%*?&]/)) strength++;
        return strength;
    }

    const passwordInput = document.getElementById('signup-password');
    const strengthText = document.getElementById('strength-text');
    const strengthIndicator = document.getElementById('strength-indicator');
    if (passwordInput) {
        passwordInput.addEventListener('input', function () {
            const val = passwordInput.value;
            const strength = checkPasswordStrength(val);
            let text = '';
            let color = '';
            if (strength <= 2) {
                text = 'Fraca';
                color = 'red';
            } else if (strength === 3 || strength === 4) {
                text = 'Média';
                color = 'orange';
            } else if (strength === 5) {
                text = 'Forte';
                color = 'green';
            }
            strengthText.textContent = 'Força da senha:';
            strengthIndicator.style.color = color;
            strengthIndicator.textContent = text;
        });
    }

    // --- Validação dos steps ---
    const nextBtns = document.querySelectorAll('.next');
    nextBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            const currentStep = btn.closest('.step');
            let valid = true;
            currentStep.querySelectorAll('input[required]').forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('input-error');
                    valid = false;
                } else {
                    input.classList.remove('input-error');
                }
            });
            if (!valid) {
                e.preventDefault();
                return;
            }
            // Se válido, avança
            const currentStepId = parseInt(currentStep.id.replace('step', ''));
            document.getElementById('step' + currentStepId).classList.remove('active');
            document.getElementById('step' + (currentStepId + 1)).classList.add('active');
        });
    });

    // --- Voltar mantém valores ---
    const prevBtns = document.querySelectorAll('.prev');
    prevBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const currentStep = btn.closest('.step');
            const currentStepId = parseInt(currentStep.id.replace('step', ''));
            document.getElementById('step' + currentStepId).classList.remove('active');
            document.getElementById('step' + (currentStepId - 1)).classList.add('active');
        });
    });

    // --- Carrossel automático ---
    let carouselIndex = 1;
    setInterval(() => {
        carouselIndex = carouselIndex % 3 + 1;
        // Não troca mais a imagem, só o texto
        // document.querySelectorAll('.image').forEach(img => img.classList.remove('show'));
        // document.querySelector('.img-' + carouselIndex).classList.add('show');
        document.querySelectorAll('.bullets span').forEach((bull, idx) => {
            bull.classList.toggle('active', idx === carouselIndex - 1);
        });
        document.querySelector('.text-group').style.transform = `translateY(-${(carouselIndex - 1) * 2.2}rem)`;
    }, 5000);
});

// Função para logar mensagens no servidor
function logToServer(message) {
    fetch('includes/login/log_js.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message })
    });
}

// Adicionar função para exibir modal de boas-vindas
function showWelcomeModal() {
    const modal = document.createElement('div');
    modal.id = 'welcome-modal';
    modal.className = 'modal';
    modal.style.display = 'flex';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.zIndex = '3000';
    modal.innerHTML = `
        <div class="modal-content" style="max-width:400px;text-align:center;padding:32px 24px;border-radius:16px;background:#fff;box-shadow:0 8px 32px rgba(0,0,0,0.18);">
            <h2>Bem-vindo à Cristais Gold Lar!</h2>
            <p style="margin:18px 0 0 0;font-size:1.1em;">Parabéns, seu cadastro foi realizado com sucesso.<br><b>Sua primeira compra terá 10% de desconto automático!</b></p>
            <button id="close-welcome-modal" style="margin-top:24px;padding:10px 28px;background:#F3BA00;color:#fff;border:none;border-radius:8px;font-size:1em;cursor:pointer;">OK</button>
        </div>
    `;
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    document.getElementById('close-welcome-modal').onclick = function() {
        modal.remove();
        document.body.style.overflow = '';
        window.location.href = "index.php";
    };
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
            document.body.style.overflow = '';
            window.location.href = "index.php";
        }
    });
}