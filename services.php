<?php
include 'partials/header.php';
?>

<section class="services-section">
    <div class="container services-container">
        <div class="services-header">
            <h1>Our Services</h1>
            <p class="lead">Comprehensive solutions for your digital presence</p>
        </div>

        <div class="services-grid">
            <div class="service-card">
                <i class="fas fa-pen-fancy"></i>
                <h3>Content Writing</h3>
                <p>Professional content creation tailored to your needs, from blog posts to technical documentation.</p>
            </div>

            <div class="service-card">
                <i class="fas fa-code"></i>
                <h3>Web Development</h3>
                <p>Custom website development using modern technologies and best practices.</p>
            </div>

            <div class="service-card">
                <i class="fas fa-search"></i>
                <h3>SEO Optimization</h3>
                <p>Improve your online visibility with our comprehensive SEO services.</p>
            </div>

            <div class="service-card">
                <i class="fas fa-bullhorn"></i>
                <h3>Digital Marketing</h3>
                <p>Strategic digital marketing solutions to grow your online presence.</p>
            </div>
        </div>
    </div>
</section>

<style>
.services-section {
    padding: 6rem 0;
    background: var(--color-bg);
}

.services-container {
    width: var(--container-width-lg);
    margin: 0 auto;
}

.services-header {
    text-align: center;
    margin-bottom: 4rem;
}

.services-header h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--color-white);
}

.services-header .lead {
    color: var(--color-gray-300);
    font-size: 1.2rem;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.service-card {
    background: var(--color-gray-900);
    padding: 2rem;
    border-radius: var(--card-boder-radius-3);
    text-align: center;
    transition: var(--transition);
}

.service-card:hover {
    transform: translateY(-0.5rem);
    background: var(--color-primary);
}

.service-card i {
    font-size: 2rem;
    color: var(--color-primary);
    margin-bottom: 1rem;
}

.service-card:hover i {
    color: var(--color-white);
}

.service-card h3 {
    color: var(--color-white);
    margin-bottom: 1rem;
}

.service-card p {
    color: var(--color-gray-300);
    line-height: 1.7;
}

@media screen and (max-width: 1024px) {
    .services-container {
        width: var(--container-width-md);
    }
}

@media screen and (max-width: 600px) {
    .services-header h1 {
        font-size: 2rem;
    }
    
    .services-grid {
        gap: 1rem;
    }
}
</style>

<?php
include 'partials/footer.php';
?>