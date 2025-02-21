const likeBtn = document.getElementById('like-btn');
const dislikeBtn = document.getElementById('dislike-btn');

document.addEventListener('DOMContentLoaded', function() {
    const commentsSection = document.getElementById('comments-section');
    const commentsCount = document.getElementById('comments-count');
    const commentsToggle = document.getElementById('comments-toggle');
    const shareBtn = document.getElementById('share-btn');
    const shareOptions = document.querySelector('.share-options');
    let commentsLoaded = false;

    // Update comments count on load
    function updateCommentsCount(count) {
        commentsCount.textContent = count;
    }

    // Handle comments toggle with loading indicator
    if (commentsToggle) {
        commentsToggle.addEventListener('click', async function() {
            if (commentsSection.style.display === 'none') {
                commentsSection.style.display = 'block';
                if (!commentsLoaded) {
                    const loader = document.getElementById('comments-loader');
                    if (loader) loader.style.display = 'block';
                    try {
                        await window.loadComments();
                        commentsLoaded = true;
                    } finally {
                        if (loader) loader.style.display = 'none';
                    }
                }
                setTimeout(() => commentsSection.classList.add('expanded'), 10);
            } else {
                commentsSection.classList.remove('expanded');
                setTimeout(() => commentsSection.style.display = 'none', 300);
            }
        });
    }

    // Handle share button
    if (shareBtn) {
        shareBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            shareOptions.classList.toggle('show');
        });
    }

    // Close share menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!shareBtn.contains(e.target)) {
            shareOptions.classList.remove('show');
        }
    });

    // Handle likes/dislikes with persistent state
    [likeBtn, dislikeBtn].forEach(btn => {
        if (!btn) return;
        
        btn.addEventListener('click', async function() {
            if (!this.dataset.postId) return;
            
            try {
                const type = this.id === 'like-btn' ? 'like' : 'dislike';
                const response = await fetch(`${window.ROOT_URL}api/post-interactions.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${this.dataset.postId}&type=${type}`
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.error || 'Failed to update reaction');
                }

                if (data.error) {
                    if (data.error === 'Please login to interact') {
                        window.location.href = `${window.ROOT_URL}signin.php`;
                    } else {
                        alert(data.error);
                    }
                    return;
                }
                
                updateInteractionCounts(data.counts);
                toggleActiveState(this, data.state === 'added');

            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
});

function updateInteractionCounts(counts) {
    console.log('Updating counts:', counts); // Debug log
    const likesCount = document.getElementById('likes-count');
    const dislikesCount = document.getElementById('dislikes-count');
    const commentsCount = document.getElementById('comments-count');
    
    if (likesCount) likesCount.textContent = counts.likes_count || 0;
    if (dislikesCount) dislikesCount.textContent = counts.dislikes_count || 0;
    if (commentsCount) commentsCount.textContent = counts.comments_count || 0;
}

function toggleActiveState(btn, isActive) {
    if (isActive) {
        btn.classList.add('active');
        const otherBtn = btn.id === 'like-btn' ? 
            document.getElementById('dislike-btn') : 
            document.getElementById('like-btn');
        if (otherBtn) otherBtn.classList.remove('active');
    } else {
        btn.classList.remove('active');
    }
}

// Update loadComments function
async function loadComments() {
    try {
        const response = await fetch(`${window.ROOT_URL}api/load-comments.php?post_id=${window.postId}`);
        const data = await response.json();
        
        // Update post reaction counts
        updateInteractionCounts({
            likes_count: data.post_reactions.likes_count,
            dislikes_count: data.post_reactions.dislikes_count,
            comments_count: data.comments.length
        });

        // Update reaction buttons state
        if (data.post_reactions.user_reaction) {
            const btn = data.post_reactions.user_reaction === 'like' ? likeBtn : dislikeBtn;
            toggleActiveState(btn, true);
        }

        // Render comments
        const commentsHtml = data.comments.map(comment => createCommentHTML(comment)).join('');
        document.getElementById('comments-container').innerHTML = commentsHtml;
    } catch (error) {
        console.error('Error loading comments:', error);
    }
}

// Function to create comment HTML
function createCommentHTML(comment) {
    return `
        <div class="comment">
            <p class="comment-author">${comment.author}</p>
            <p class="comment-content">${comment.content}</p>
        </div>
    `;
}

// Make loadComments available globally
window.loadComments = loadComments;
