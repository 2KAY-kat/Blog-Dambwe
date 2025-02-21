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
    });