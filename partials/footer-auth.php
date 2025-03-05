<div class="footer">
    <div class="footer__copyrightt">
    <p>Powered By DAMBWEDESIGNS technology</p> | <small>Copyright &copy; 2024 - <span class="span">DEV.</span>Query<span>&trade;</span></small>
    </div>

</div>

<script src="<?= ROOT_URL ?>scripts/main.js"></script>

<script src="<?= ROOT_URL ?>js/follow.js"></script>
<script src="<?= ROOT_URL ?>scripts/js__cookie.js"></script>

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
        .then((registration) => {
            console.log('Service Worker registered with scope:', registration.scope);
        })
        .catch((error) => {
            console.log('Service Worker registration failed:', error);
        });
    });
}
</script>
</body>
</html>