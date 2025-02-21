$(document).ready(function() {
    // Prevent double initialization
    if (window.interactionsInitialized) return;
    window.interactionsInitialized = true;

    // Add comment toggle functionality
    const commentsSection = document.getElementById('comments-section');
    const commentsToggle = document.getElementById('comments-toggle');
    const shareBtn = document.getElementById('share-btn');
    const shareOptions = document.querySelector('.share-options');
    let commentsLoaded = false;

    // Handle comments toggle
    if (commentsToggle && commentsSection) {
        commentsToggle.addEventListener('click', async function() {
            if (commentsSection.style.display === 'none' || !commentsSection.style.display) {
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
    if (shareBtn && shareOptions) {
        shareBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            shareOptions.classList.toggle('show');
        });

        // Close share menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!shareBtn.contains(e.target)) {
                shareOptions.classList.remove('show');
            }
        });
    }

    function updateReactionCounts(postId, counts, state, clickedType) {
        // Update all instances of this post's reactions on the page
        $(`[data-post-id="${postId}"]`).each(function() {
            const button = $(this);
            const type = button.data('action');
            const countSpan = button.find('.interaction-count');
            
            if (type === 'like') {
                countSpan.text(counts.likes_count);
                button.toggleClass('active', state === 'added' && clickedType === 'like');
            } else if (type === 'dislike') {
                countSpan.text(counts.dislikes_count);
                button.toggleClass('active', state === 'added' && clickedType === 'dislike');
            }

            // If this reaction was added, remove active state from opposite reaction
            if (state === 'added') {
                const oppositeType = type === 'like' ? 'dislike' : 'like';
                button.closest('.post__interactions, .post__interactions-bar')
                    .find(`[data-action="${oppositeType}"]`)
                    .removeClass('active');
            }
        });
    }

    // Handle all interaction clicks
    $(document).on('click', '.interaction-item[data-action]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!window.userId) {
            window.location.href = window.ROOT_URL + 'signin.php';
            return;
        }

        const button = $(this);
        const postId = button.data('post-id');
        const type = button.data('action');

        // Disable button temporarily to prevent double-clicks
        button.prop('disabled', true);

        $.ajax({
            url: window.ROOT_URL + 'api/post-interactions.php',
            type: 'POST',
            data: { post_id: postId, type: type },
            dataType: 'json',
            timeout: 5000, // 5 second timeout
            success: function(response) {
                if (response.success) {
                    updateReactionCounts(postId, response.counts, response.state, type);
                } else if (response.error) {
                    if (response.error === 'Please login to interact') {
                        window.location.href = window.ROOT_URL + 'signin.php';
                    } else {
                        console.error('Server error:', response.error);
                    }
                }
            },
            error: function(xhr, status, error) {
                if (status === 'timeout') {
                    console.error('Request timed out');
                } else if (status === 'error') {
                    if (!navigator.onLine) {
                        console.error('No internet connection');
                    } else {
                        console.error('Server error:', error);
                    }
                }
            },
            complete: function() {
                // Re-enable button after request completes
                button.prop('disabled', false);
            }
        });
    });

    // Add network status listener
    window.addEventListener('online', function() {
        console.log('Back online');
        $('.interaction-item').prop('disabled', false);
    });

    window.addEventListener('offline', function() {
        console.log('Gone offline');
        $('.interaction-item').prop('disabled', true);
    });
});