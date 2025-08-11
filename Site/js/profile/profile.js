document.addEventListener('DOMContentLoaded', function() {

    // Profile section switching using side-menu-item
    const menuItems = document.querySelectorAll('.side-menu-item');
    const sections = document.querySelectorAll('.profile-container');

    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all menu items
            menuItems.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Get the section to show
            const sectionToShow = this.getAttribute('data-section');
            
            // Fade out all sections first
            sections.forEach(section => {
                section.style.opacity = '0';
                setTimeout(() => {
                    section.classList.remove('active');
                    // After fade out, show the selected section
                    if (section.id === sectionToShow) {
                        section.classList.add('active');
                        // Fade in the selected section
                        setTimeout(() => {
                            section.style.opacity = '1';
                        }, 50);
                    }
                }, 300);
            });
        });
    });

    // Create notification system
    const notificationSystem = document.createElement('div');
    notificationSystem.className = 'notification-system';
    document.body.appendChild(notificationSystem);

    // Function to show notifications
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span>${message}</span>
                <button class="notification-close">×</button>
            </div>
        `;
        
        // Add to notification system
        notificationSystem.appendChild(notification);
        
        // Fade in
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Auto remove after 5 seconds
        const timeout = setTimeout(() => {
            removeNotification(notification);
        }, 5000);
        
        // Close button functionality
        notification.querySelector('.notification-close').addEventListener('click', () => {
            clearTimeout(timeout);
            removeNotification(notification);
        });
    }
    
    function removeNotification(notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }



    // Handle password update form
    const senhaForm = document.querySelector('#senha form');
    if (senhaForm) {
        senhaForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const senhaAtual = document.getElementById('senha_atual').value;
            const novaSenha = document.getElementById('nova_senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;
            
            // Basic validation
            if (novaSenha !== confirmarSenha) {
                showSiteAlert('As senhas não coincidem.', 'error');
                return;
            }
            
            // Create form data
            const formData = new FormData();
            formData.append('senha_atual', senhaAtual);
            formData.append('nova_senha', novaSenha);
            formData.append('confirmar_senha', confirmarSenha);
            
            // Send AJAX request
            fetch('includes/profile/atualizar_senha.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes('sucesso')) {
                    showSiteAlert('Senha atualizada com sucesso!', 'success');
                    // Clear form
                    senhaForm.reset();
                } else {
                    showSiteAlert(data, 'error');
                }
            })
            .catch(error => {
                showSiteAlert('Erro ao processar a solicitação.', 'error');
                console.error('Error:', error);
            });
        });
    }

    // Handle user data update form
    const dadosForm = document.querySelector('#meus-dados form');
    if (dadosForm) {
        dadosForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Create form data from all form inputs
            const formData = new FormData(dadosForm);
            
            // Send AJAX request
            fetch('includes/profile/atualizar_dados.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes('sucesso')) {
                    showSiteAlert('Dados atualizados com sucesso!', 'success');
                    
                    // Update displayed username in the header if name was changed
                    const newName = document.getElementById('nome').value;
                    const userDisplayNames = document.querySelectorAll('#username-display');
                    userDisplayNames.forEach(element => {
                        const displayName = newName.length > 16 ? newName.substring(0, 16) + "..." : newName;
                        element.textContent = displayName;
                    });
                    
                    // Update profile picture in navbar and tabbar
                    const profilePictureInput = document.getElementById('profile_picture');
                    if (profilePictureInput.files.length > 0) {
                        const previewSrc = document.querySelector('.profile-picture-preview').src;
                        const profilePics = document.querySelectorAll('#profile-pic, .tabbar a[href="profile.php"] img');
                        profilePics.forEach(img => {
                            img.src = previewSrc;
                        });
                        
                        // Atualizar também na sessão se necessário
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    showSiteAlert(data, 'error');
                }
            })
            .catch(error => {
                showSiteAlert('Erro ao processar a solicitação.', 'error');
                console.error('Error:', error);
            });
        });
    }

    // Toast notification system
    function showToast(message, type = 'success') {
        let toastContainer = document.querySelector('.toast-container');
        
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
            
            if (!document.getElementById('toast-styles')) {
                const toastStyles = document.createElement('style');
                toastStyles.id = 'toast-styles';
                toastStyles.textContent = `
                    .toast-container {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        z-index: 9999;
                        display: flex;
                        flex-direction: column;
                        gap: 10px;
                    }
                    .toast {
                        min-width: 250px;
                        padding: 15px;
                        border-radius: 4px;
                        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                        color: white;
                        font-weight: 500;
                        animation: toast-in 0.3s ease-in-out;
                    }
                    .toast-success {
                        background-color: #4CAF50;
                    }
                    .toast-error {
                        background-color: #F44336;
                    }
                    .toast-warning {
                        background-color: #FF9800;
                    }
                    .toast-info {
                        background-color: #2196F3;
                    }
                    .toast.fade-out {
                        animation: toast-out 0.3s ease-in-out forwards;
                    }
                    @keyframes toast-in {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                    @keyframes toast-out {
                        from { transform: translateX(0); opacity: 1; }
                        to { transform: translateX(100%); opacity: 0; }
                    }
                `;
                document.head.appendChild(toastStyles);
            }
        }
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        toastContainer.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => {
                toast.remove();
                if (toastContainer.children.length === 0) {
                    toastContainer.remove();
                }
            }, 300);
        }, 3000);
    }

    // Preview da foto de perfil
    const profilePictureInput = document.getElementById('profile_picture');
    const profilePicturePreview = document.querySelector('.profile-picture-preview');
    if (profilePictureInput && profilePicturePreview) {
        profilePictureInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Validar tipo de arquivo
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    showSiteAlert('Por favor, envie apenas arquivos de imagem (JPEG, PNG, GIF, WEBP).', 'error');
                    this.value = '';
                    return;
                }
                
                // Validar tamanho (máximo 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showSiteAlert('O arquivo deve ter menos de 5MB.', 'error');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicturePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});