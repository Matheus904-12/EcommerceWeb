document.addEventListener('DOMContentLoaded', () => {
    // Dropdown handling
    const dropdown = document.getElementById('custom-dropdown');
    if (dropdown) {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const optionsList = dropdown.querySelector('.dropdown-options');
        const options = dropdown.querySelectorAll('.dropdown-options li');
        if (toggle && optionsList) {
            toggle.addEventListener('click', () => {
                optionsList.classList.toggle('show');
                toggle.classList.toggle('active');
            });
            options.forEach(option => {
                option.addEventListener('click', () => {
                    options.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');
                    toggle.firstChild.textContent = option.textContent.trim();
                    optionsList.classList.remove('show');
                    toggle.classList.remove('active');
                    const redirectUrl = option.getAttribute('data-value');
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }
                });
            });
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (dropdown && !dropdown.contains(e.target)) {
            const optionsList = dropdown.querySelector('.dropdown-options');
            const toggle = dropdown.querySelector('.dropdown-toggle');
            if (optionsList && toggle) {
                optionsList.classList.remove('show');
                toggle.classList.remove('active');
            }
        }
    });

    // Product card modal handling
    const cards = document.querySelectorAll('.product-card');
    cards.forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('.add-to-cart-btn') || e.target.closest('.favorite-icon')) {
                return;
            }
            const modalId = card.getAttribute('data-modal-id');
            if (modalId && modalId !== 'pix-modal') {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'flex';
                    modal.classList.add('active');
                    const closeModalButtons = modal.querySelectorAll('.close-modal');
                    closeModalButtons.forEach(button => {
                        button.addEventListener('click', () => {
                            modal.classList.remove('active');
                            setTimeout(() => {
                                modal.style.display = 'none';
                            }, 300);
                        });
                    });
                }
            }
        });
    });

    // Close modals when clicking outside
    document.addEventListener('click', (e) => {
        const modals = document.querySelectorAll('.modal:not(#pix-modal)');
        modals.forEach(modal => {
            if (modal.classList.contains('active') && !modal.contains(e.target) && !e.target.closest('.product-card')) {
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        });
    });
});