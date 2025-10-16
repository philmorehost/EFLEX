<?php
// includes/_landing.php
require_once __DIR__ . '/_navbar.php';

// --- Fetch content from database ---
$content_sql = "SELECT section_name, content FROM landing_page_content";
$content_result = $conn->query($content_sql);
$content = [];
while ($row = $content_result->fetch_assoc()) {
    $content[$row['section_name']] = $row['content'];
}

$stats = [
    ['icon' => 'fas fa-book-open', 'count' => '10k+', 'label' => 'Courses & Past Questions'],
    ['icon' => 'fas fa-user-graduate', 'count' => '500k+', 'label' => 'Active Students'],
    ['icon' => 'fas fa-chalkboard-teacher', 'count' => '1,000+', 'label' => 'Expert Tutors'],
    ['icon' => 'fas fa-award', 'count' => '98%', 'label' => 'Success Rate']
];

$testimonials = [
    [
        'name' => $content['testimonial_1_name'],
        'school' => $content['testimonial_1_school'],
        'image' => $content['testimonial_1_image'],
        'quote' => $content['testimonial_1_quote']
    ],
    [
        'name' => $content['testimonial_2_name'],
        'school' => $content['testimonial_2_school'],
        'image' => $content['testimonial_2_image'],
        'quote' => $content['testimonial_2_quote']
    ],
    [
        'name' => $content['testimonial_3_name'],
        'school' => $content['testimonial_3_school'],
        'image' => $content['testimonial_3_image'],
        'quote' => $content['testimonial_3_quote']
    ]
];
?>

<!-- Hero Section -->
<section class="hero-section text-center text-white">
    <div class="container">
        <h1><?php echo htmlspecialchars($content['hero_title']); ?></h1>
        <p class="lead"><?php echo htmlspecialchars($content['hero_subtitle']); ?></p>
        <a href="register.php" class="btn btn-lg btn-success">Get Started for Free</a>
        <a href="login.php" class="btn btn-lg btn-light">Login to Your Account</a>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section py-5">
    <div class="container">
        <div class="row text-center">
            <?php foreach ($stats as $stat): ?>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="<?php echo $stat['icon']; ?> fa-3x text-primary mb-3"></i>
                        <h3><?php echo $stat['count']; ?></h3>
                        <p class="text-muted"><?php echo $stat['label']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Why Choose Us?</h2>
            <p class="lead">We provide the best tools to help you succeed.</p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                    <h4>Extensive Question Bank</h4>
                    <p>Access thousands of past questions for JAMB, WAEC, and NECO, all in one place.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-laptop-code fa-3x text-primary mb-3"></i>
                    <h4>Real Exam Simulation</h4>
                    <p>Experience a real-time, computer-based test (CBT) environment to prepare you for the actual exam.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                    <h4>Performance Analytics</h4>
                    <p>Track your progress with detailed analytics and identify areas where you need to improve.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section id="testimonials" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2>What Our Students Say</h2>
            <p class="lead">Real success stories from students who trusted us.</p>
        </div>
        <div class="row">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="col-md-4">
                    <div class="card testimonial-card">
                        <div class="card-body">
                            <p class="card-text">"<?php echo htmlspecialchars($testimonial['quote']); ?>"</p>
                            <div class="d-flex align-items-center mt-3">
                                <img src="<?php echo htmlspecialchars($testimonial['image']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="rounded-circle me-3" style="width: 60px; height: 60px;">
                                <div>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($testimonial['name']); ?></h5>
                                    <small class="text-muted"><?php echo htmlspecialchars($testimonial['school']); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section bg-primary text-white text-center py-5">
    <div class="container">
        <h2>Ready to Start Your Journey to Success?</h2>
        <p class="lead">Join over 500,000 students who have achieved their academic dreams.</p>
        <a href="register.php" class="btn btn-lg btn-light">Sign Up Now</a>
    </div>
</section>
