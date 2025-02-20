document.addEventListener('DOMContentLoaded', function() {
    const followButtons = document.querySelectorAll('.follow-btn');
    
    followButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const authorId = this.dataset.authorId;
            const isInPost = this.closest('.post__author') !== null;
            
            try {
                // Use relative path if ROOT_URL is not defined
                const baseUrl = typeof ROOT_URL !== 'undefined' ? ROOT_URL : '/Blog-Dambwe/';
                const response = await fetch(`${baseUrl}ajax/follow.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `author_id=${authorId}`
                });
                
                const data = await response.json();
                
                if (data.error) {
                    alert(data.error);
                    return;
                }
                
                if (data.status === 'success') {
                    // Update all follow buttons for this author
                    const authorButtons = document.querySelectorAll(`.follow-btn[data-author-id="${authorId}"]`);
                    authorButtons.forEach(btn => {
                        if (data.action === 'follow') {
                            btn.classList.add('following');
                            btn.innerHTML = `<i class="uil uil-user-check"></i>${isInPost ? '<span class="follow-text">Following</span>' : 'Following'}`;
                        } else {
                            btn.classList.remove('following');
                            btn.innerHTML = `<i class="uil uil-user-plus"></i>${isInPost ? '<span class="follow-text">Follow</span>' : 'Follow'}`;
                        }
                    });
                    
                    // Update follower count if on profile/author page
                    const followerCount = document.querySelector('.follower-count');
                    if (followerCount) {
                        followerCount.textContent = data.count;
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    });
});
