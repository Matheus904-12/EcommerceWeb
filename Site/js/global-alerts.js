// =================== ALERTAS CENTRALIZADOS GLOBAIS (NOTIFICAÇÃO CELULAR) ===================

// Função global para mostrar alertas estilizados
function showSiteAlert(message, type = 'success') {
    // Remove alerta existente
    const oldAlert = document.querySelector('.site-alert');
    if (oldAlert) oldAlert.remove();
    
    // Ícones por tipo
    const icons = {
        success: '<span class="alert-icon">✔️</span>',
        error: '<span class="alert-icon">❌</span>',
        warning: '<span class="alert-icon">⚠️</span>',
        info: '<span class="alert-icon">ℹ️</span>'
    };
    
    // Cria alerta
    const alertDiv = document.createElement('div');
    alertDiv.className = `site-alert ${type}`;
    alertDiv.innerHTML = `
        <button class="alert-close" onclick="this.parentElement.classList.remove('show'); setTimeout(()=>this.parentElement.remove(),300)">×</button>
        ${icons[type] || ''}
        <div>${message}</div>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Anima entrada
    setTimeout(() => alertDiv.classList.add('show'), 10);
    
    // Fecha automático após 4s
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 300);
        }
    }, 4000);
}

// Substituir alert() nativo por showSiteAlert
window.alert = function(message) {
    showSiteAlert(message, 'info');
};

// Substituir confirm() nativo por uma versão estilizada
window.confirm = function(message) {
    return new Promise((resolve) => {
        const oldAlert = document.querySelector('.site-alert');
        if (oldAlert) oldAlert.remove();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'site-alert info';
        alertDiv.innerHTML = `
            <span class="alert-icon">❓</span>
            <div style="margin-bottom: 20px;">${message}</div>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button onclick="this.parentElement.parentElement.remove(); resolve(true);" style="background: #4CAF50; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Sim</button>
                <button onclick="this.parentElement.parentElement.remove(); resolve(false);" style="background: #F44336; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Não</button>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.classList.add('show'), 10);
    });
};

// Fim dos alertas centralizados globais 