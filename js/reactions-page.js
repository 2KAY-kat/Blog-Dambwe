document.addEventListener('DOMContentLoaded', function() {

    function isOffline() {
        return !navigator.onLine;
    }
    const tabBtns = document.querySelectorAll('.tab-btn');
    let currentTab = 'likes';

    if (typeof window.postId === 'undefined') {
        console.error('Post ID not found');
        return;
    }

    async function loadReactions(type) {
        const container = document.querySelector(`#${type} .reactions-list`);
        const loader = document.querySelector(`#${type} .loader`);
        
        if (!container || !loader) return;
        
        container.innerHTML = '';
        loader.style.display = 'block';

        try {
            const response = await fetch(`${window.ROOT_URL}api/get-reactions.php?post_id=${window.postId}&type=${type}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();

            if (data.success) {
                const reactions = data.reactions || [];
                if (reactions.length === 0) {
                    container.innerHTML = `
                        <div class="no-reactions">
                            <p>No ${type} for this post yet</p>
                        </div>`;
                } else {
                    const reactionsHtml = reactions.map(user => `
                        <div class="reaction-item">
                            <div class="user-avatar">
                                <img src="${window.ROOT_URL}images/${user.avatar}" 
                                     alt="${user.firstname}"
                                     onerror="this.src='${window.ROOT_URL}images/default-avatar.png'">
                            </div>
                            <div class="user-info">
                                <h4>
                                    <a href="${window.ROOT_URL}author-posts.php?id=${user.id}">
                                        ${user.firstname} ${user.lastname}
                                    </a>
                                </h4>
                                <small>${user.reaction_time}</small>
                            </div>
                        </div>
                    `).join('');
                    
                    container.innerHTML = `
                        <div class="reactions-count">
                            ${data.total_count} ${type} total
                        </div>
                        ${reactionsHtml}
                    `;
                }
            } else {
                throw new Error(data.message || 'Failed to load reactions');
            }
        } catch (error) {
            console.error('Error:', error);
            container.innerHTML = '<p class="error">Failed to load reactions. Please try again later.</p>';
        } finally {
            loader.style.display = 'none';
        }
    }

    // Add online/offline event listeners
    window.addEventListener('online', () => {
        loadReactions(currentTab);
    });

    window.addEventListener('offline', () => {
        const container = document.querySelector(`#${currentTab} .reactions-list`);
        if (container) {
            container.innerHTML = '<p class="error">You are currently offline. Please check your internet connection.</p>';
        }
    });

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.tab;
            if (type === currentTab) return;

            document.querySelector('.tab-btn.active').classList.remove('active');
            btn.classList.add('active');
            
            document.querySelector('.tab-content.active').classList.remove('active');
            document.querySelector(`#${type}`).classList.add('active');
            
            currentTab = type;
            loadReactions(type);
        });
    });

    // Initial load with connection check
    if (!isOffline()) {
        loadReactions('likes');
    } else {
        const container = document.querySelector('#likes .reactions-list');
        if (container) {
            container.innerHTML = '<p class="error">Cannot load reactions while offline. Please check your internet connection.</p>';
        }
    }
});
