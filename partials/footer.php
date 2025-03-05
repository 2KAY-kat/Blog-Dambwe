<?php /*
<div id="cookies">
        <div class="cookie__container">
            <div class="sub__container">
                <div class="cookies">
                    <p>This website uses cookies to ensure you get the best experience on our website. <a href="">More info.</a></p>
                    <button id="cookies__btn">Agreed!</button>
                </div>
            </div>
        </div>
    </div> */?>
<div class="back-to-top">
    <i class="fas fa-arrow-up"></i>
</div>    
<footer>


    <div class="footer__socials">
        <a href="" target="_blank"><i class="fab fa-youtube"></i></a>
        <a href="" target="_blank"><i class="fab fa-facebook"></i></a>
        <a href="" target="_blank"><i class="fab fa-x-twitter"></i></a>
        <a href="" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="" target="_blank"><i class="fab fa-linkedin"></i></a>
    </div>

    
    <div class="container footer__container">
        <article>
            <h4>Categories (Non-Clickable)</h4>
            <ul>
                <li><a class="colored">Food</a></li>
                <li><a class="colored">Travel</a></li>
                <li><a class="colored">Music</a></li>
                <li><a class="colored">Tech / Scie.</a></li>
                <li><a class="colored">Art</a></li>
                <li><a class="colored">Business</a></li>
            </ul>
        </article>

        <article>
            <h4>Support</h4>
            <ul>
                <li><a href="" class="anchor">Online Support</a></li>
                <li><a href="" class="anchor">Call Numbers</a></li>
                <li><a href="" class="anchor">Emails</a></li>
                <li><a href="<?= ROOT_URL ?>web-assets/code-of-conduct.php" class="anchor">Code of Conduct</a></li>
                <li><a href="<?= ROOT_URL ?>web-assets/privacy-policy.php" class="anchor">Privacy Policy</a></li>
            </ul>
        </article>

        <article>
            <h4>Blog</h4>
            <ul>
                <li><a href="" class="anchor">Popular</a></li>
                <li><a href="" class="anchor">Recent</a></li>
                <li><a href="" class="anchor">Repair</a></li>
                <li><a href="" class="anchor">Categories</a></li>
            </ul>
        </article>


        <article>
            <h4>Permalinks</h4>
            <ul>
                <li><a href="<?= ROOT_URL ?>" class="anchor">Home</a></li>
                <li><a href="<?= ROOT_URL ?>blog.php" class="anchor">Blog</a></li>
                <li><a href="<?= ROOT_URL ?>about.php" class="anchor">About</a></li>
                <li><a href="<?= ROOT_URL ?>services.php" class="anchor">Services</a></li>
                <li><a href="<?= ROOT_URL ?>contact.php" class="anchor">Contact</a></li>
            </ul>
        </article>
    </div>

    <div class="footer__copyright">
    <p>Powered By DAMBWEDESIGNS technology</p> |
    <small>Copyright &copy; 2024 - <span class="span">DEV.</span>Query<span class="span">&trade;</span></small>
    </div>

</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="<?= ROOT_URL ?>scripts/main.js"></script>
<script src="<?= ROOT_URL ?>scripts/js__cookie.js"></script>
<script src="<?= ROOT_URL ?>js/follow.js"></script>

<script>
// Back to top functionality
const backToTop = document.querySelector('.back-to-top');


window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
        backToTop.classList.add('visible');
    } else {
        backToTop.classList.remove('visible');
    }
});

backToTop.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>
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