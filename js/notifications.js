let notificationCount = 0;

function updateNotificationCount() {
    fetch(`${ROOT_URL}api/notifications-count.php`)
        .then(response => response.json())
        .then(data => {
            console.log('Notification count response:', data); // Debug log
            const countElement = document.querySelector('.notification-count');
            if (countElement) {
                notificationCount = data.count;
                countElement.textContent = notificationCount > 0 ? notificationCount : '';
                countElement.style.display = notificationCount > 0 ? 'block' : 'none';
            }
        })
        .catch(error => console.error('Error updating notification count:', error));
}

function createNotification(userId, type, message, relatedId) {
    return fetch(`${ROOT_URL}api/create-notification.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: userId,
            type: type,
            message: message,
            related_id: relatedId
        })
    });
}

// Update notification count every 15 seconds (reduced from 30)
setInterval(updateNotificationCount, 15000);

// Initial update
document.addEventListener('DOMContentLoaded', updateNotificationCount);
