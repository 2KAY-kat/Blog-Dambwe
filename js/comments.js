$(document).ready(function() {
    const postId = $('input[name="post_id"]').val();

    let currentCommentId = null;
    let currentCommentElement = null;

    function loadComments() {
        $.ajax({
            url: window.ROOT_URL + 'ajax/get_comments.php',
            type: 'GET',
            data: { post_id: postId },
            success: function(response) {
                $('#comments-container').html(response);
            },
            error: function(xhr, status, error) {
                console.error('Error loading comments:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                $('#comments-container').html('<div class="alert alert-danger">Failed to load comments</div>');
            }
        });
    }

    // Handle all comment form submissions (both new comments and replies)
    $(document).on('submit', '.comment-form', function(e) {
        e.preventDefault(); // Prevent default form submission
        const form = $(this);
        
        // Debug log
        console.log('Submitting comment:', {
            post_id: form.find('input[name="post_id"]').val(),
            comment_text: form.find('textarea[name="comment_text"]').val(),
            parent_id: form.find('input[name="parent_id"]').val()
        });

        $.ajax({
            url: window.ROOT_URL + 'ajax/add_comment.php',
            type: 'POST',
            data: {
                post_id: form.find('input[name="post_id"]').val(),
                comment_text: form.find('textarea[name="comment_text"]').val(),
                parent_id: form.find('input[name="parent_id"]').val() || 0
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    form.find('textarea').val('');
                    loadComments();
                    if (form.hasClass('reply-form')) {
                        form.remove();
                    }
                } else {
                    alert(response.message || 'Error adding comment');
                }
            },
            error: function(xhr, status, error) {
                console.error('Comment submission error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                alert('Failed to add comment. Please try again.');
            }
        });
    });

    // Handle reply button clicks
    $(document).on('click', '.reply-btn', function() {
        const commentId = $(this).data('comment-id');
        const replyFormHtml = `
            <form class="comment-form reply-form">
                <input type="hidden" name="post_id" value="${postId}">
                <input type="hidden" name="parent_id" value="${commentId}">
                <textarea name="comment_text" placeholder="Write your reply..." required></textarea>
                <div class="form-buttons">
                    <button type="submit" class="btn">Reply</button>
                    <button type="button" class="btn cancel-reply">Cancel</button>
                </div>
            </form>
        `;
        
        // Remove any existing reply forms
        $('.reply-form').remove();
        
        // Add new reply form
        $(this).closest('.comment').append(replyFormHtml);
    });

    // Handle cancel reply
    $(document).on('click', '.cancel-reply', function() {
        $(this).closest('.reply-form').remove();
    });

    // Toggle replies visibility
    $(document).on('click', '.toggle-replies', function() {
        const repliesContainer = $(this).closest('.comment').find('.comment-replies');
        repliesContainer.toggleClass('show');
        $(this).text(repliesContainer.hasClass('show') ? 'Hide Replies' : 'Show Replies');
    });

    // Add like/dislike functionality
    $(document).on('click', '.like-btn, .dislike-btn', function() {
        const button = $(this);
        const postId = button.data('post-id');
        const action = button.data('action');

        $.ajax({
            url: window.ROOT_URL + 'ajax/handle_like.php',
            type: 'POST',
            data: { 
                post_id: postId,
                action: action
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#like-count-' + postId).text(response.likes);
                    $('#dislike-count-' + postId).text(response.dislikes);
                    
                    // Toggle active class
                    if (action === 'like') {
                        button.toggleClass('active');
                        button.siblings('.dislike-btn').removeClass('active');
                    } else {
                        button.toggleClass('active');
                        button.siblings('.like-btn').removeClass('active');
                    }
                } else {
                    alert(response.message || 'Error processing request');
                }
            },
            error: function(xhr, status, error) {
                console.error('Like/Dislike error:', {xhr, status, error});
                alert('Failed to process request. Please try again.');
            }
        });
    });

    // Handle delete button clicks
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        currentCommentId = $(this).data('comment-id');
        currentCommentElement = $(this).closest('.comment');
        $('#delete-modal').fadeIn(300);
    });

    // Add modal handlers
    $(document).on('click', '.cancel-delete', function() {
        $('#delete-modal').fadeOut(300);
        currentCommentId = null;
        currentCommentElement = null;
    });

    $(document).on('click', '.confirm-delete', function() {
        if (!currentCommentId) return;
        
        $.ajax({
            url: window.ROOT_URL + 'ajax/delete_comment.php',
            type: 'POST',
            data: { comment_id: currentCommentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    currentCommentElement.slideUp(300, function() {
                        $(this).remove();
                        // Reload comments to update reply counts
                        loadComments();
                    });
                } else {
                    alert(response.message || 'Error deleting comment');
                }
                $('#delete-modal').fadeOut(300);
            },
            error: function(xhr, status, error) {
                console.error('Delete comment error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                alert('Failed to delete comment. Please try again.');
                $('#delete-modal').fadeOut(300);
            }
        });
    });

    // Close modal when clicking outside
    $(window).on('click', function(e) {
        if ($(e.target).is('#delete-modal')) {
            $('#delete-modal').fadeOut(300);
            currentCommentId = null;
            currentCommentElement = null;
        }
    });

    // Initial load of comments
    loadComments();
});
