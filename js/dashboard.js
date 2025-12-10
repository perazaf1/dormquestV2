// Dashboard JS: confirm actions
function confirmAction(form) {
    return confirm('Confirmer cette action ?');
}
function confirmCancel() {
    return confirm('Voulez-vous vraiment annuler cette candidature ?');
}

document.addEventListener('DOMContentLoaded', function() {
    // Optionally enhance buttons later
});

// Auto-dismiss alerts and message modal handling
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(a => {
        setTimeout(() => {
            a.style.transition = 'opacity 0.4s, transform 0.4s';
            a.style.opacity = '0';
            a.style.transform = 'translateY(-8px)';
            setTimeout(() => a.remove(), 400);
        }, 5000);
    });

    // View message buttons
    document.querySelectorAll('.view-message').forEach(btn => {
        btn.addEventListener('click', function() {
            const message = this.getAttribute('data-message') || '';
            const student = this.getAttribute('data-student') || '';
            const annonce = this.getAttribute('data-annonce') || '';
            showMessageModal(student, annonce, message);
        });
    });

    // modal close
    const modal = document.getElementById('messageModal');
    if (modal) {
        modal.querySelector('.modal__close').addEventListener('click', hideMessageModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) hideMessageModal();
        });
    }
});

function showMessageModal(student, annonce, message) {
    const modal = document.getElementById('messageModal');
    if (!modal) return;
    modal.querySelector('.modal__title').textContent = (student ? student + ' — ' : '') + (annonce ? annonce : 'Message');
    modal.querySelector('.modal__body').textContent = message || '(Aucun message)';
    modal.classList.add('active');
}

function hideMessageModal() {
    const modal = document.getElementById('messageModal');
    if (!modal) return;
    modal.classList.remove('active');
}
