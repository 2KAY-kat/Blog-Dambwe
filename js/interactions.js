$(document).ready(function() {
    // Like/Dislike functionality
    $('.like-btn, .dislike-btn').click(function() {
        var postId = $(this).data('post-id');
        var action = $(this).data('action');
        var span = $(this);

        console.log('Post ID:', postId, 'Action:', action); // Debugging

        $.ajax({
            url: 'interactions/handle_like_dislike.php',
            type: 'POST',
            data: {
                post_id: postId,
                action: action
            },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response); // Debugging
                if (response.success) {
                    $('#like-count-' + postId).text(response.likes);
                    $('#dislike-count-' + postId).text(response.dislikes);

                    // Update button styles based on the new state
                    if (response.user_like_value == 1) {
                        span.addClass('active');
                        if (span.siblings('.dislike-btn').length) {
                            span.siblings('.dislike-btn').removeClass('active');
                        }
                    } else if (response.user_like_value == -1) {
                        span.addClass('active');
                        if (span.siblings('.like-btn').length) {
                            span.siblings('.like-btn').removeClass('active');
                        }
                    } else {
                        span.removeClass('active');
                        if (span.siblings('.like-btn').length) {
                            span.siblings('.like-btn').removeClass('active');
                        }
                        if (span.siblings('.dislike-btn').length) {
                            span.siblings('.dislike-btn').removeClass('active');
                        }
                    }
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', xhr, status, error); // Debugging
                console.log('Response Text:', xhr.responseText); // Add this line
                alert('An error occurred.');
            }
        });
    });

    // Comment submission
    $('#comment-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        console.log('Form Data:', formData); // Debugging

        $.ajax({
            url: 'interactions/handle_comment.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response); // Debugging
                if (response.success) {
                    $('#comment-form textarea').val(''); // Clear the textarea
                    loadComments(response.post_id); // Reload comments
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', xhr, status, error); // Debugging
                console.log('Response Text:', xhr.responseText); // Add this line
                alert('An error occurred.');
            }
        });
    });

    // Like comment functionality
    $(document).on('click', '.like-comment-btn', function() {
        var commentId = $(this).data('comment-id');
        var span = $(this);

        console.log('Comment ID:', commentId); // Debugging

        $.ajax({
            url: 'interactions/handle_like_comment.php',
            type: 'POST',
            data: {
                comment_id: commentId
            },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response); // Debugging
                if (response.success) {
                    span.find('.like-count').text(response.likes);
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', xhr, status, error); // Debugging
                console.log('Response Text:', xhr.responseText); // Add this line
                alert('An error occurred.');
            }
        });
    });

    // Load comments on page load
    var postId = $('input[name="post_id"]').val();
    if (postId) {
        loadComments(postId);
    }

    function loadComments(postId) {
        console.log('Loading comments for Post ID:', postId); // Debugging
        $.ajax({
            url: 'interactions/get_comments.php',
            type: 'GET',
            data: {
                post_id: postId
            },
            success: function(response) {
                console.log('Response:', response); // Debugging
                $('#comments-section').html(response);
            },
            error: function(xhr, status, error) {
                console.error('Error:', xhr, status, error); // Debugging
                console.log('Response Text:', xhr.responseText); // Add this line
                alert('Failed to load comments.');
            }
        });
    }
});
