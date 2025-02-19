document.addEventListener('DOMContentLoaded', () => {
    const skeletonLoader = document.querySelector('.skeleton-loader');
    
    // Hide skeleton loader when page is fully loaded
    window.addEventListener('load', () => {
        skeletonLoader.classList.add('hide');
    });

    // Show skeleton loader before page navigation
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (link && !link.target && !e.ctrlKey && !e.shiftKey) {
            e.preventDefault();
            skeletonLoader.classList.remove('hide');
            setTimeout(() => {
                window.location = link.href;
            }, 300);
        }
    });
});
