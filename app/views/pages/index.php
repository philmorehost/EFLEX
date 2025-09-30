<?php require_once APP_ROOT . '/app/views/includes/header.php'; ?>

<div class="landing-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1><?php echo htmlspecialchars($data['content']['hero_title'] ?? 'The Better, Smarter, and Faster Way To Pay Bills'); ?></h1>
            <p><?php echo htmlspecialchars($data['content']['hero_subtitle'] ?? 'Join millions of people who use our platform to pay bills, buy airtime, data, and manage their finances.'); ?></p>
            <div class="hero-buttons">
                <a href="<?php echo BASE_URL; ?>/users/register" class="btn btn-primary btn-lg">Get Started</a>
                <a href="<?php echo BASE_URL; ?>/users/login" class="btn btn-secondary btn-lg">Login</a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section">
        <div class="container">
            <h2><?php echo htmlspecialchars($data['content']['services_title'] ?? 'Our Awesome Services'); ?></h2>
            <p class="section-subtitle"><?php echo htmlspecialchars($data['content']['services_subtitle'] ?? 'We provide you with the best and most affordable services.'); ?></p>
            <div class="services-grid">
                <div class="service-item">
                    <i class="icon-airtime"></i>
                    <h3>Airtime Top-up</h3>
                </div>
                <div class="service-item">
                    <i class="icon-data"></i>
                    <h3>Data Bundles</h3>
                </div>
                <div class="service-item">
                    <i class="icon-cable"></i>
                    <h3>Cable TV</h3>
                </div>
                <div class="service-item">
                    <i class="icon-electricity"></i>
                    <h3>Electricity Bill</h3>
                </div>
                 <div class="service-item">
                    <i class="icon-transfer"></i>
                    <h3>Money Transfer</h3>
                </div>
                 <div class="service-item">
                    <i class="icon-exam"></i>
                    <h3>Exam PINs</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-us-section">
        <div class="container">
            <h2><?php echo htmlspecialchars($data['content']['why_us_title'] ?? 'Why Choose Us?'); ?></h2>
            <div class="features-grid">
                <div class="feature-item">
                    <h3><?php echo htmlspecialchars($data['content']['why_us_item1_title'] ?? 'We Are Fast'); ?></h3>
                    <p><?php echo htmlspecialchars($data['content']['why_us_item1_text'] ?? 'Our services are delivered instantly. No waiting time.'); ?></p>
                </div>
                <div class="feature-item">
                    <h3><?php echo htmlspecialchars($data['content']['why_us_item2_title'] ?? 'We Are Reliable'); ?></h3>
                    <p><?php echo htmlspecialchars($data['content']['why_us_item2_text'] ?? 'You can count on us for 24/7 service availability.'); ?></p>
                </div>
                <div class="feature-item">
                    <h3><?php echo htmlspecialchars($data['content']['why_us_item3_title'] ?? 'We Are Secure'); ?></h3>
                    <p><?php echo htmlspecialchars($data['content']['why_us_item3_text'] ?? 'Your transactions and data are always safe with us.'); ?></p>
                </div>
            </div>
        </div>
    </section>

</div>

<?php require_once APP_ROOT . '/app/views/includes/footer.php'; ?>