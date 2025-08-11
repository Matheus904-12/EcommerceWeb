document.addEventListener('DOMContentLoaded', () => {
    // Dropdown handling
    const dropdown = document.querySelector('.dropdown');
    if (dropdown) {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const optionsList = dropdown.querySelector('.dropdown-menu');
        const options = dropdown.querySelectorAll('.dropdown-menu li');
        if (toggle && optionsList) {
            toggle.addEventListener('click', () => {
                optionsList.classList.toggle('show');
                toggle.classList.toggle('active');
            });
            options.forEach(option => {
                option.addEventListener('click', () => {
                    options.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');
                    toggle.textContent = option.textContent.trim();
                    optionsList.classList.remove('show');
                    toggle.classList.remove('active');
                });
            });
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (dropdown && !dropdown.contains(e.target)) {
            const optionsList = dropdown.querySelector('.dropdown-menu');
            const toggle = dropdown.querySelector('.dropdown-toggle');
            if (optionsList && toggle) {
                optionsList.classList.remove('show');
                toggle.classList.remove('active');
            }
        }
    });

    // Modal handling for other elements
    const modalTriggers = document.querySelectorAll('.modal-trigger');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-modal-id');
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
            if (modal.classList.contains('active') && !modal.contains(e.target) && !e.target.closest('.modal-trigger')) {
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        });
    });
});