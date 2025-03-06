<?php
include 'partials/header.php';
?>

<section class="about-section">
    <div class="container about-container">
        <div class="about-header">
            <h1>About Us</h1>
            <p class="lead">Empowering voices through digital storytelling</p>
        </div>

        <div class="about-content">
            <div class="about-image">
                <img src="images/social-media-concept-composition.jpg" alt="About Dambwe Blog">
            </div>
            
            <div class="about-text">
                <h2>Our Story</h2>
                <p>Welcome to Blog Dambwe, a platform dedicated to sharing meaningful stories and insights. Founded in 2024, we strive to create a space where ideas flourish and conversations matter.</p>
                
                <h2>Our Mission</h2>
                <p>To provide a platform where diverse voices can share their stories, knowledge, and experiences while building a community of engaged readers and writers.</p>

                <div class="about-stats">
                    <div class="stat-item">
                        <h3>1000+</h3>
                        <p>Active Readers</p>
                    </div>
                    <div class="stat-item">
                        <h3>500+</h3>
                        <p>Articles Published</p>
                    </div>
                    <div class="stat-item">
                        <h3>50+</h3>
                        <p>Contributing Writers</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.about-section {
    padding: 6rem 0;
    background: var(--color-bg);
}

.about-container {
    width: var(--container-width-lg);
    margin: 0 auto;
}

.about-header {
    text-align: center;
    margin-bottom: 4rem;
}

.about-header h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--color-white);
}

.about-header .lead {
    color: var(--color-gray-300);
    font-size: 1.2rem;
}

.about-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.about-image img {
    border-radius: var(--card-boder-radius-3);
    box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.2);
}

.about-text h2 {
    color: var(--color-white);
    margin-bottom: 1rem;
}

.about-text p {
    color: var(--color-gray-300);
    margin-bottom: 2rem;
    line-height: 1.7;
}

.about-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    margin-top: 3rem;
}

.stat-item {
    text-align: center;
    padding: 2rem;
    background: var(--color-gray-900);
    border-radius: var(--card-boder-radius-2);
}

.stat-item h3 {
    font-size: 2rem;
    color: var(--color-primary-light);
    margin-bottom: 0.5rem;
}

.stat-item p {
    color: var(--color-gray-300);
    margin: 0;
}

@media screen and (max-width: 1024px) {
    .about-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}

@media screen and (max-width: 600px) {
    .about-header h1 {
        font-size: 2rem;
    }
    
    .about-stats {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>

<?php
include 'partials/footer.php';
?>