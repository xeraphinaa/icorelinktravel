<?php
$current_page = 'contact';
$page_title = 'Contact Us | Icorelink Travel & Tours';
require_once 'includes/header.php';
$settings = get_system_settings($pdo);

$contact_success = '';
$contact_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact') {
    verify_csrf();
    
    $name = clean_input($_POST['name'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $subject = clean_input($_POST['subject'] ?? '');
    $message = clean_input($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $contact_error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contact_error = "Please provide a valid email address.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_name, sender_email, sender_phone, subject, message, status) VALUES (?, ?, ?, ?, ?, 'unread')");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            
            // Notify staff and admins
            try {
                $staff_stmt = $pdo->query("SELECT id FROM users WHERE role_id IN (1, 2) AND status = 'active'");
                $staff_user_ids = $staff_stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($staff_user_ids as $staff_id) {
                    create_notification($pdo, $staff_id, "New website inquiry from " . htmlspecialchars($name) . " regarding: " . htmlspecialchars($subject));
                }
            } catch (Exception $notif_err) {}

            log_activity($pdo, null, 'Website Inquiry', "Inquiry from $name ($email) - Subject: $subject");
            $contact_success = "Thank you, $name! Your message has been sent directly to our travel specialists. We will get back to you within 24 hours.";
        } catch (PDOException $e) {
            $contact_error = "Failed to send message: " . $e->getMessage();
        }
    }
}
?>

<!-- Contact Hero Banner -->
<div class="contact-hero py-5 text-white" style="background: linear-gradient(135deg, rgba(11, 34, 64, 0.96) 0%, rgba(0, 162, 232, 0.9) 100%), url('<?php echo APP_URL; ?>/assets/images/philippines_hero_bg.png') center/cover no-repeat;">
    <div class="container py-4 text-center">
        <span class="badge bg-white text-primary fw-bold text-uppercase px-3 py-2 mb-3 shadow-sm" style="letter-spacing: 0.1em;">
            <i class="fa-solid fa-headset me-1"></i> We're Here For You 24/7
        </span>
        <h1 class="display-4 fw-bold font-serif mb-2 text-white">Contact Icorelink Travel & Tours</h1>
        <p class="lead text-white-50 mx-auto mb-0" style="max-width: 720px; font-size: 1.05rem;">
            Have questions about tour package bookings, domestic flight schedules, customized group itineraries, or corporate travel? Reach out to our dedicated travel team today.
        </p>
    </div>
</div>

<section class="py-5 bg-light" id="contact-details">
    <div class="container">
        <!-- 3 Quick Info Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white hover-lift">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-phone fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark-blue mb-2">Give Us a Call</h5>
                    <p class="text-muted small mb-2">Talk directly to our ticketing & tour desk:</p>
                    <a href="tel:+639182743144" class="fw-bold text-primary text-decoration-none fs-6">+63 918 274 3144</a>
                    <div class="text-muted small mt-1"><?php echo htmlspecialchars($settings['contact_number'] ?? '0917 123 4567'); ?></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white hover-lift">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-info bg-opacity-10 text-info" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-envelope fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark-blue mb-2">Email Inquiries</h5>
                    <p class="text-muted small mb-2">Send us itinerary questions or custom proposals:</p>
                    <a href="mailto:icorelink.travel@gmail.com" class="fw-bold text-primary text-decoration-none fs-6">icorelink.travel@gmail.com</a>
                    <div class="text-muted small mt-1"><?php echo htmlspecialchars($settings['email'] ?? 'info@icorelinktravel.com'); ?></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white hover-lift">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-location-dot fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark-blue mb-2">Visit Our Headquarters</h5>
                    <p class="text-muted small mb-2">DOT-Accredited Travel Office:</p>
                    <span class="small text-dark fw-semibold">1500 Taft Ave cor JL Escoda St. Ermita, Manila, Philippines</span>
                    <div class="text-muted small mt-1"><?php echo htmlspecialchars($settings['office_hours'] ?? 'Mon - Sat: 8:00 AM - 6:00 PM'); ?></div>
                </div>
            </div>
        </div>

        <!-- Contact Form + Map & Payment Box -->
        <div class="row g-4 align-items-stretch">
            <!-- Left Column: Form -->
            <div class="col-lg-7">
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border h-100">
                    <div class="mb-4">
                        <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill text-uppercase small mb-2">Send a Message</span>
                        <h3 class="fw-bold text-dark-blue font-serif mb-2">How Can We Help You?</h3>
                        <p class="text-muted small mb-0">Fill out the form below and one of our travel consultants will respond within 24 hours.</p>
                    </div>

                    <?php if (!empty($contact_success)): ?>
                        <div class="alert alert-success rounded-3 shadow-sm alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i> <?php echo htmlspecialchars($contact_success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($contact_error)): ?>
                        <div class="alert alert-danger rounded-3 shadow-sm alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($contact_error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo APP_URL; ?>/contact.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="action" value="contact">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g. Maria Santos" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required placeholder="e.g. maria@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Contact Number</label>
                                <input type="tel" name="phone" class="form-control" placeholder="e.g. 0917 123 4567" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Subject / Inquiry Type <span class="text-danger">*</span></label>
                                <select name="subject" class="form-select" required>
                                    <option value="" disabled <?php echo empty($_POST['subject']) ? 'selected' : ''; ?>>Select a topic...</option>
                                    <option value="Tour Package Booking" <?php echo (($_POST['subject'] ?? '') === 'Tour Package Booking') ? 'selected' : ''; ?>>Tour Package Booking</option>
                                    <option value="Flight Schedules & Times" <?php echo (($_POST['subject'] ?? '') === 'Flight Schedules & Times') ? 'selected' : ''; ?>>Flight Schedules & Times</option>
                                    <option value="Hotel Accommodations" <?php echo (($_POST['subject'] ?? '') === 'Hotel Accommodations') ? 'selected' : ''; ?>>Hotel Accommodations</option>
                                    <option value="Custom Group / Company Tour" <?php echo (($_POST['subject'] ?? '') === 'Custom Group / Company Tour') ? 'selected' : ''; ?>>Custom Group / Company Tour</option>
                                    <option value="Payment & Downpayment Verification" <?php echo (($_POST['subject'] ?? '') === 'Payment & Downpayment Verification') ? 'selected' : ''; ?>>Payment & Downpayment Verification</option>
                                    <option value="General Questions" <?php echo (($_POST['subject'] ?? '') === 'General Questions') ? 'selected' : ''; ?>>General Questions</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-dark">Your Message <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control" rows="5" required placeholder="Tell us about your travel dates, number of guests, destination preferences, or specific questions..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-primary-custom px-4 py-2 w-100 fw-bold">
                                    <i class="fa-solid fa-paper-plane me-2"></i> Send Inquiry to Travel Desk
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Map, Social, Authorized Payments -->
            <div class="col-lg-5">
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h4 class="fw-bold text-dark-blue font-serif mb-3">Our Office Location</h4>
                        <div class="rounded-4 overflow-hidden mb-4 shadow-sm border" style="height: 200px;">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.3414902092147!2d120.9818817!3d14.5796229!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397ca264d0a92d7%3A0x63390c58e0a30b8e!2sRizal%20Park!5e0!3m2!1sen!2sph!4v1680000000000!5m2!1sen!2sph" 
                                width="100%" 
                                height="100%" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Icorelink Office Map">
                            </iframe>
                        </div>

                        <h6 class="fw-bold text-dark-blue mb-2"><i class="fa-solid fa-share-nodes text-primary me-2"></i> Connect on Social Media</h6>
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <a href="<?php echo htmlspecialchars($settings['facebook_page'] ?? 'https://www.facebook.com'); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="fa-brands fa-facebook-f me-1"></i> Facebook Official
                            </a>
                            <a href="mailto:icorelink.travel@gmail.com" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                <i class="fa-solid fa-envelope me-1"></i> Email
                            </a>
                        </div>

                        <h6 class="fw-bold text-dark-blue mb-2"><i class="fa-solid fa-wallet text-success me-2"></i> Authorized Reservation Channels</h6>
                        <p class="text-muted small mb-3">For PHP 2,000 reservation deposits and balance settlements:</p>
                        
                        <div class="d-flex flex-column gap-2 small">
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="payment-logo-pill pill-gcash"><img src="<?php echo APP_URL; ?>/assets/images/gcashlogo.png" alt="GCash"></span>
                                    <span class="fw-bold text-dark">GCash</span>
                                </div>
                                <span class="font-monospace text-primary fw-bold"><?php echo htmlspecialchars(!empty($settings['gcash_number']) ? $settings['gcash_number'] : '0917-123-4567'); ?></span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="payment-logo-pill pill-maya"><img src="<?php echo APP_URL; ?>/assets/images/mayalogo.png" alt="Maya"></span>
                                    <span class="fw-bold text-dark">Maya</span>
                                </div>
                                <span class="font-monospace text-primary fw-bold"><?php echo htmlspecialchars(!empty($settings['maya_number']) ? $settings['maya_number'] : '0917-123-4567'); ?></span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="payment-logo-pill pill-paypal"><img src="<?php echo APP_URL; ?>/assets/images/paypallogo.jpg" alt="PayPal"></span>
                                    <span class="fw-bold text-dark">PayPal</span>
                                </div>
                                <span class="font-monospace text-primary fw-bold"><?php echo htmlspecialchars(!empty($settings['paypal_email']) ? $settings['paypal_email'] : 'billing@icorelinktravel.com'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
