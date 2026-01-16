/**
 * NOTIFICATIONS.JS
 * Gestion des notifications
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Marquer une notification comme lue
    document.querySelectorAll('.btn-mark-read').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const notifId = this.dataset.notifId;
            const notifElement = document.getElementById('notif-' + notifId);

            fetch('api/mark-notification-read.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'notif_id=' + notifId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && notifElement) {
                    notifElement.classList.remove('unread');
                    this.remove();
                    
                    const unreadCount = document.querySelectorAll('.notification-item.unread').length;
                    const badge = document.querySelector('.badge-unread');
                    if (unreadCount === 0 && badge) {
                        badge.remove();
                    }
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    });

    // Supprimer une notification
    document.querySelectorAll('.btn-delete-notif').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Supprimer cette notification ?')) return;

            const notifId = this.dataset.notifId;
            const notifElement = document.getElementById('notif-' + notifId);

            fetch('api/delete-notification.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'notif_id=' + notifId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && notifElement) {
                    notifElement.style.opacity = '0';
                    notifElement.style.transform = 'translateX(-20px)';
                    notifElement.style.transition = 'all 0.3s ease';
                    
                    setTimeout(function() {
                        notifElement.remove();
                        const remaining = document.querySelectorAll('.notification-item').length;
                        if (remaining === 0) window.location.reload();
                    }, 300);
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    });
});
