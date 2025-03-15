document.addEventListener('DOMContentLoaded', function() {
    // Preview uploaded images
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Bio character counter
    const bioTextarea = document.querySelector('textarea[name="bio"]');
    const bioCounter = document.querySelector('.bio-counter');
    if (bioTextarea && bioCounter) {
        bioTextarea.addEventListener('input', function() {
            const count = this.value.length;
            bioCounter.textContent = `${count}/160`;
            bioCounter.style.color = count > 160 ? 'var(--red-color)' : 'var(--color-gray-300)';
            
            // Disable form submission if bio is too long
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.disabled = count > 160;
        });
    }

    // Theme color preview
    const themeColorInput = document.querySelector('input[name="theme_color"]');
    if (themeColorInput) {
        themeColorInput.addEventListener('input', function() {
            document.documentElement.style.setProperty('--color-primary', this.value);
        });
    }

    // Social links validation
    const socialInputs = document.querySelectorAll('.social-input-group input');
    socialInputs.forEach(input => {
        input.addEventListener('input', function() {
            const url = this.value.trim();
            if (url && !isValidUrl(url)) {
                this.setCustomValidity('Please enter a valid URL');
                this.reportValidity();
            } else {
                this.setCustomValidity('');
            }
        });
    });

    // Form validation before submit
    const form = document.querySelector('.edit-profile-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const username = document.querySelector('input[name="username"]').value;
            if (username.length < 3) {
                e.preventDefault();
                alert('Username must be at least 3 characters long');
                return;
            }

            // Validate bio length
            const bio = document.querySelector('textarea[name="bio"]').value;
            if (bio.length > 160) {
                e.preventDefault();
                alert('Bio must not exceed 160 characters');
                return;
            }

            // Validate image sizes
            const avatar = document.querySelector('input[name="avatar"]').files[0];
            const cover = document.querySelector('input[name="cover_photo"]').files[0];
            
            if (avatar && avatar.size > 2000000) {
                e.preventDefault();
                alert('Avatar image must be less than 2MB');
                return;
            }
            
            if (cover && cover.size > 2000000) {
                e.preventDefault();
                alert('Cover image must be less than 2MB');
                return;
            }
        });
    }
});

// URL validation helper
function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}
