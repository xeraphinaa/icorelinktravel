<?php
require_once 'includes/functions.php';

$package_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($package_id <= 0) {
    set_flash_message('danger', 'Invalid package selected.');
    header('Location: ' . APP_URL . '/#packages');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM travel_packages WHERE id = ? AND status IN ('active', 'featured')");
    $stmt->execute([$package_id]);
    $package = $stmt->fetch();
    
    if (!$package) {
        set_flash_message('danger', 'Travel package not found or inactive.');
        header('Location: ' . APP_URL . '/#packages');
        exit;
    }

    $package_rating = get_package_rating($package_id, $pdo);
    $package_reviews = get_package_reviews($package_id, $pdo, 20);
} catch (PDOException $e) {
    die("Database query error: " . $e->getMessage());
}

$page_title = $package['name'];
$current_page = 'packages';
require_once 'includes/header.php';
?>

<!-- Package Details Hero -->
<section class="py-5 bg-dark-blue text-white" style="background: linear-gradient(135deg, rgba(11, 34, 64, 0.95) 0%, rgba(0, 162, 232, 0.8) 100%);">
    <div class="container py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/" class="text-white opacity-75 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/#packages" class="text-white opacity-75 text-decoration-none">Packages</a></li>
                <li class="breadcrumb-item text-white active" aria-current="page"><?php echo htmlspecialchars($package['name']); ?></li>
            </ol>
        </nav>
        <div class="row align-items-center mt-3 g-4">
            <div class="col-lg-8">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                    <span class="badge bg-primary px-3 py-2 text-uppercase font-serif"><?php echo htmlspecialchars($package['destination']); ?></span>
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-sm">
                        <i class="fa-solid fa-star me-1 text-dark"></i> <?php echo $package_rating['formatted_rating']; ?> / 5.0 (<?php echo $package_rating['total_reviews']; ?> <?php echo $package_rating['total_reviews'] === 1 ? 'Review' : 'Reviews'; ?>)
                    </span>
                </div>
                <h1 class="text-white fw-bold display-5"><?php echo htmlspecialchars($package['name']); ?></h1>
                <p class="lead opacity-75 mb-0"><i class="fa-solid fa-clock me-2"></i> <?php echo htmlspecialchars($package['duration']); ?> &bull; <i class="fa-solid fa-plane me-2"></i> Travel Date: <?php echo format_date($package['travel_date']); ?></p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="bg-white text-dark-blue p-4 rounded-4 shadow d-inline-block text-start w-100" style="max-width: 320px;">
                    <span class="text-muted small fw-bold uppercase">Package Price</span>
                    <h2 class="fw-extrabold text-primary mb-2"><?php echo format_price($package['price']); ?></h2>
                    <p class="small text-muted mb-3"><i class="fa-solid fa-circle-check text-success me-1"></i> Reservation: ₱2,000 per traveler</p>
                    
                    <?php if ($package['available_slots'] > 0): ?>
                        <?php if (strtotime($package['booking_deadline']) >= time()): ?>
                            <a href="<?php echo APP_URL; ?>/customer/book.php?package_id=<?php echo $package['id']; ?>" class="btn btn-primary-custom w-100 py-2">
                                <i class="fa-solid fa-cart-shopping me-2"></i> Book Package Now
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100" disabled>
                                <i class="fa-solid fa-clock-lock me-2"></i> Deadline Passed
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-danger w-100" disabled>
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> Fully Booked
                        </button>
                    <?php endif; ?>

                    <?php
                    $inquire_pkg_url = is_logged_in() 
                        ? APP_URL . '/customer/messages.php?inquire_type=package&id=' . $package['id']
                        : APP_URL . '/login.php?redirect=' . urlencode(APP_URL . '/customer/messages.php?inquire_type=package&id=' . $package['id']);
                    ?>
                    <a href="<?php echo htmlspecialchars($inquire_pkg_url); ?>" class="btn btn-outline-primary w-100 py-2 mt-2 rounded-3 fw-bold" id="btnInquireAccommodation">
                        <i class="fa-solid fa-comments me-2"></i> Inquire Accommodation
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Package Content Details -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Image & Overview -->
                <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
                    <img src="<?php echo !empty($package['image_path']) ? UPLOAD_URL . '/packages/' . $package['image_path'] : 'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=1400&q=80'; ?>" class="img-fluid w-100" style="max-height: 450px; object-fit: cover;" alt="<?php echo htmlspecialchars($package['name']); ?>">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3"><i class="fa-solid fa-compass text-primary me-2"></i> Package Overview</h4>
                        <p class="text-muted" style="line-height: 1.8; font-size: 1.05rem; white-space: pre-line;"><?php echo htmlspecialchars($package['description']); ?></p>
                    </div>
                </div>

                <!-- Itinerary -->
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4"><i class="fa-solid fa-map-location-dot text-primary me-2"></i> Travel Itinerary</h4>
                        <div class="itinerary-timeline">
                            <div class="text-muted" style="white-space: pre-line; line-height: 1.8; font-size: 1.05rem;">
                                <?php echo htmlspecialchars($package['itinerary']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar inclusions / exclusions -->
            <div class="col-lg-4">
                <!-- Deadline / Slots Stats -->
                <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-circle-info text-primary me-2"></i> Booking Info</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3 d-flex justify-content-between">
                                <span class="text-muted">Available Slots:</span>
                                <span class="fw-bold text-dark-blue"><?php echo htmlspecialchars($package['available_slots']); ?> traveler(s)</span>
                            </li>
                            <li class="mb-3 d-flex justify-content-between">
                                <span class="text-muted">Booking Deadline:</span>
                                <span class="fw-bold text-danger"><?php echo format_date($package['booking_deadline']); ?></span>
                            </li>
                            <li class="mb-0 d-flex justify-content-between">
                                <span class="text-muted">Travel Date:</span>
                                <span class="fw-bold text-success"><?php echo format_date($package['travel_date']); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Inclusions -->
                <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white" style="border-left: 4px solid var(--primary-color) !important;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-success mb-3"><i class="fa-solid fa-square-check me-2"></i> What's Included</h5>
                        <div class="small text-muted" style="white-space: pre-line; line-height: 1.6;">
                            <?php echo htmlspecialchars($package['inclusions']); ?>
                        </div>
                    </div>
                </div>

                <!-- Exclusions -->
                <div class="card border-0 rounded-4 shadow-sm bg-white" style="border-left: 4px solid #dc3545 !important;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-danger mb-3"><i class="fa-solid fa-circle-xmark me-2"></i> What's Excluded</h5>
                        <div class="small text-muted" style="white-space: pre-line; line-height: 1.6;">
                            <?php echo htmlspecialchars($package['exclusions']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Customer Reviews & Ratings Section -->
<section class="py-5 bg-white border-top">
    <div class="container">
        <div class="row align-items-center justify-content-between mb-4 g-3">
            <div class="col-md-7">
                <h3 class="fw-bold text-dark-blue mb-1">
                    <i class="fa-solid fa-star text-warning me-2"></i> Traveler Reviews & Ratings
                </h3>
                <p class="text-muted mb-0">Verified feedback and travel memories from guests who explored this destination.</p>
            </div>
            <div class="col-md-5 text-md-end">
                <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3): ?>
                    <a href="<?php echo APP_URL; ?>/customer/reviews.php?package_id=<?php echo $package['id']; ?>" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-bold">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Share Your Experience
                    </a>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/customer/reviews.php" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-bold auth-modal-trigger" data-auth-tab="login">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Write a Review
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rating Summary Card -->
        <div class="card border-0 rounded-4 shadow-sm bg-light p-4 mb-5">
            <div class="row g-4 align-items-center">
                <!-- Big Score -->
                <div class="col-md-4 text-center border-end-md">
                    <div class="display-3 fw-bold text-dark-blue mb-1"><?php echo $package_rating['formatted_rating']; ?></div>
                    <div class="text-warning fs-5 mb-2">
                        <?php 
                        $floor_stars = floor($package_rating['avg_rating']);
                        for ($i = 1; $i <= 5; $i++): 
                        ?>
                            <i class="fa-<?php echo $i <= $floor_stars ? 'solid' : 'regular'; ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="text-muted small">
                        Based on <strong><?php echo $package_rating['total_reviews']; ?></strong> verified <?php echo $package_rating['total_reviews'] === 1 ? 'traveler' : 'travelers'; ?>
                    </div>
                </div>

                <!-- Star Bars Breakdown -->
                <div class="col-md-8">
                    <?php 
                    $tot = max(1, $package_rating['total_reviews']);
                    for ($s = 5; $s >= 1; $s--): 
                        $cnt = $package_rating['stars_' . $s];
                        $pct = $package_rating['total_reviews'] > 0 ? round(($cnt / $tot) * 100) : 0;
                    ?>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="small fw-semibold text-muted text-nowrap" style="width: 55px;"><?php echo $s; ?> Stars</span>
                            <div class="progress flex-grow-1" style="height: 9px; background-color: #e5e7eb; border-radius: 10px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $pct; ?>%; border-radius: 10px;" aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="small text-muted text-nowrap" style="width: 45px;"><?php echo $cnt; ?> (<?php echo $pct; ?>%)</span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Reviews List -->
        <?php if (empty($package_reviews)): ?>
            <div class="text-center py-5 bg-light rounded-4">
                <i class="fa-regular fa-comments fa-3x text-muted opacity-50 mb-3"></i>
                <h5 class="fw-bold text-dark-blue">No traveler reviews yet for this package</h5>
                <p class="text-muted small mb-3">Have you experienced this journey? Be the first to share your feedback and travel photos!</p>
                <a href="<?php echo APP_URL; ?>/customer/reviews.php" class="btn btn-primary-custom px-4 py-2">
                    <i class="fa-solid fa-pen-to-square me-2"></i> Leave First Review
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($package_reviews as $rev): ?>
                    <div class="col-12">
                        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <!-- Avatar with initials -->
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm" style="width: 50px; height: 50px;">
                                        <?php 
                                        $initials = strtoupper(substr($rev['first_name'], 0, 1) . substr($rev['last_name'], 0, 1));
                                        echo htmlspecialchars($initials);
                                        ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark-blue fs-6 mb-0">
                                            <?php echo htmlspecialchars($rev['first_name'] . ' ' . $rev['last_name']); ?>
                                            <span class="badge bg-success bg-opacity-10 text-success small ms-2 fw-normal px-2 py-1 rounded-pill">
                                                <i class="fa-solid fa-circle-check me-1"></i> Verified Traveler
                                            </span>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fa-regular fa-calendar-check me-1"></i> <?php echo date('F d, Y', strtotime($rev['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-warning fs-6">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa-<?php echo $i <= $rev['rating'] ? 'solid' : 'regular'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                    <span class="small fw-bold text-dark ms-1"><?php echo $rev['rating']; ?>.0</span>
                                </div>
                            </div>

                            <!-- Comment -->
                            <p class="text-muted mb-3" style="line-height: 1.7; font-size: 1.02rem;">
                                <?php echo nl2br(htmlspecialchars($rev['comment'])); ?>
                            </p>

                            <!-- Review Photo if available -->
                            <?php if (!empty($rev['photo_path'])): ?>
                                <div class="mb-3">
                                    <div class="small text-muted fw-semibold mb-2">
                                        <i class="fa-solid fa-camera text-primary me-1"></i> Traveler Photos:
                                    </div>
                                    <div class="position-relative d-inline-block">
                                        <img src="<?php echo UPLOAD_URL . '/reviews/' . htmlspecialchars($rev['photo_path']); ?>" 
                                             alt="Traveler Photo" 
                                             class="rounded-3 shadow-sm border" 
                                             style="height: 140px; width: 200px; object-fit: cover; cursor: pointer; transition: transform 0.2s ease;"
                                             onmouseover="this.style.transform='scale(1.03)'"
                                             onmouseout="this.style.transform='scale(1)'"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#pkgPhotoModal" 
                                             data-photo-src="<?php echo UPLOAD_URL . '/reviews/' . htmlspecialchars($rev['photo_path']); ?>"
                                             data-customer-name="<?php echo htmlspecialchars($rev['first_name'] . ' ' . $rev['last_name']); ?>">
                                        <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-75 text-white small rounded-pill">
                                            <i class="fa-solid fa-expand me-1"></i> Enlarge
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Staff Official Reply -->
                            <?php if (!empty($rev['staff_reply'])): ?>
                                <div class="staff-reply-card mt-3 p-3 rounded-4 bg-light border-start border-4 border-primary">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-shield-halved me-1"></i> Official Response from Icorelink Team
                                            </span>
                                            <?php if (!empty($rev['staff_first_name'])): ?>
                                                <span class="small fw-bold text-dark-blue"><?php echo htmlspecialchars($rev['staff_first_name'] . ' ' . $rev['staff_last_name']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($rev['replied_at'])): ?>
                                            <small class="text-muted">
                                                <i class="fa-regular fa-clock me-1"></i> <?php echo date('F d, Y', strtotime($rev['replied_at'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-0 text-dark-blue small ps-1" style="line-height: 1.7;">
                                        <?php echo nl2br(htmlspecialchars($rev['staff_reply'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Lightbox Modal for Full-Size Review Photos -->
<div class="modal fade" id="pkgPhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 text-white pb-0">
                <h5 class="modal-title" id="pkgPhotoModalTitle">Traveler Photo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img src="" id="pkgPhotoModalImg" class="img-fluid rounded-4 shadow-lg" style="max-height: 80vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<!-- Modal: Request Quotation Form -->
<div class="modal fade icorelink-dialog" id="pkgQuoteModal" tabindex="-1" aria-labelledby="pkgQuoteModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header header-primary">
                <h5 class="modal-title" id="pkgQuoteModalLabel">
                    <i class="fa-solid fa-file-invoice-dollar text-warning me-2"></i> Request a Customized Quotation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pkgQuoteForm" method="POST" action="<?php echo APP_URL; ?>/customer/quotations.php">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="action" value="submit_quotation_request">
                <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">

                <div class="modal-body p-4">
                    <div class="dialog-summary-card mt-0 mb-3">
                        <div class="dialog-summary-row">
                            <span class="dialog-summary-label">Tour Package:</span>
                            <span class="dialog-summary-value text-dark-blue fw-bold"><?php echo htmlspecialchars($package['name']); ?></span>
                        </div>
                        <div class="dialog-summary-row">
                            <span class="dialog-summary-label">Destination:</span>
                            <span class="dialog-summary-value"><?php echo htmlspecialchars($package['destination']); ?></span>
                        </div>
                        <div class="dialog-summary-row">
                            <span class="dialog-summary-label">Base Rate:</span>
                            <span class="dialog-summary-value text-primary"><?php echo format_price($package['price']); ?> / pax</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">
                                <i class="fa-solid fa-calendar-day text-primary me-1"></i> Preferred Travel Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="travel_date" id="pdQuoteTravelDate" class="form-control" value="<?php echo htmlspecialchars($package['travel_date']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">
                                <i class="fa-solid fa-calendar-check text-primary me-1"></i> Optional Return Date
                            </label>
                            <input type="date" name="return_date" id="pdQuoteReturnDate" class="form-control" value="<?php echo date('Y-m-d', strtotime($package['travel_date'] . ' + 3 days')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">
                                <i class="fa-solid fa-users text-primary me-1"></i> Number of Travelers <span class="text-danger">*</span>
                            </label>
                            <select name="number_of_travelers" id="pdQuoteTravelers" class="form-select">
                                <?php for ($i = 1; $i <= 15; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i === 2 ? 'selected' : ''; ?>><?php echo $i; ?> Traveler(s)</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">
                                <i class="fa-solid fa-clock text-primary me-1"></i> Duration
                            </label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($package['duration']); ?>" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">
                                <i class="fa-solid fa-comment-dots text-primary me-1"></i> Special Requests & Preferences
                            </label>
                            <textarea name="special_requests" id="pdQuoteSpecialReq" class="form-control" rows="3" placeholder="Specify hotel tier preference, airline departure city, dietary requests, senior citizen/student IDs..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnOpenPdQuoteConfirm">
                        <i class="fa-solid fa-arrow-right me-1"></i> Review Quotation Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Quotation Confirmation Dialog -->
<div class="modal fade icorelink-dialog" id="pkgQuoteConfirmModal" tabindex="-1" aria-labelledby="pkgQuoteConfirmLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header header-primary">
                <h5 class="modal-title" id="pkgQuoteConfirmLabel">
                    <i class="fa-solid fa-clipboard-check text-warning me-2"></i> Review Quotation Request Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3">Please review your customized package quotation details before confirming and submitting.</p>

                <div class="dialog-summary-card">
                    <div class="dialog-summary-row">
                        <span class="dialog-summary-label"><i class="fa-solid fa-umbrella-beach text-primary me-1"></i> Selected Package:</span>
                        <span class="dialog-summary-value text-dark-blue fw-bold"><?php echo htmlspecialchars($package['name']); ?></span>
                    </div>
                    <div class="dialog-summary-row">
                        <span class="dialog-summary-label"><i class="fa-solid fa-location-dot text-primary me-1"></i> Destination:</span>
                        <span class="dialog-summary-value"><?php echo htmlspecialchars($package['destination']); ?></span>
                    </div>
                    <div class="dialog-summary-row">
                        <span class="dialog-summary-label"><i class="fa-solid fa-calendar-days text-primary me-1"></i> Travel Schedule:</span>
                        <span class="dialog-summary-value" id="pdModalDates">--</span>
                    </div>
                    <div class="dialog-summary-row">
                        <span class="dialog-summary-label"><i class="fa-solid fa-users text-primary me-1"></i> Travelers:</span>
                        <span class="dialog-summary-value" id="pdModalTravelers">2 Travelers</span>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold text-dark-blue small text-uppercase mb-1"><i class="fa-solid fa-message text-primary me-1"></i> Special Requests:</h6>
                    <p class="p-2.5 rounded bg-light border small text-muted mb-0" id="pdModalSpecialReq">None specified</p>
                </div>

                <div class="dialog-highlight-box mb-0">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    <strong>Official Estimate:</strong> Our travel team will calculate eligible promo discounts and package bundling options for you.
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary rounded-pill px-4" id="btnBackToPdQuoteForm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Edit Details
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnSubmitPdQuoteFinal">
                    <i class="fa-solid fa-paper-plane me-1"></i> Confirm & Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Guest Login Prompt -->
<div class="modal fade icorelink-dialog" id="loginPromptModal" tabindex="-1" aria-labelledby="loginPromptLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header header-primary text-white">
                <h5 class="modal-title w-100 justify-content-center" id="loginPromptLabel">
                    <i class="fa-solid fa-lock me-2"></i> Log In to Request a Quotation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="dialog-icon-circle icon-primary mx-auto">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <h5 class="fw-bold text-dark-blue mb-2">Customer Account Required</h5>
                <p class="text-muted small mb-3">Please sign in to submit a customized travel quotation request for <strong><?php echo htmlspecialchars($package['name']); ?></strong>. We will save your quotation directly to your portal dashboard.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <a href="<?php echo APP_URL; ?>/login.php?redirect=<?php echo urlencode('package-details.php?id=' . $package['id']); ?>" class="btn btn-primary rounded-pill px-4 fw-bold">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Log In Now
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pkgPhotoModal = document.getElementById('pkgPhotoModal');
    if (pkgPhotoModal) {
        pkgPhotoModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (trigger) {
                const src = trigger.getAttribute('data-photo-src');
                const customer = trigger.getAttribute('data-customer-name') || 'Traveler';
                const modalImg = document.getElementById('pkgPhotoModalImg');
                const modalTitle = document.getElementById('pkgPhotoModalTitle');
                if (modalImg) modalImg.src = src;
                if (modalTitle) modalTitle.textContent = customer + ' - Traveler Photo';
            }
        });
    }

    // Quotation Request & Confirmation Flow for Package Details
    const pkgQuoteForm = document.getElementById('pkgQuoteForm');
    const pkgQuoteModalEl = document.getElementById('pkgQuoteModal');
    const pkgQuoteConfirmModalEl = document.getElementById('pkgQuoteConfirmModal');
    const btnOpenPdQuoteConfirm = document.getElementById('btnOpenPdQuoteConfirm');
    const btnBackToPdQuoteForm = document.getElementById('btnBackToPdQuoteForm');
    const btnSubmitPdQuoteFinal = document.getElementById('btnSubmitPdQuoteFinal');

    if (pkgQuoteForm && pkgQuoteModalEl && pkgQuoteConfirmModalEl && btnOpenPdQuoteConfirm) {
        const bsQuoteModal = bootstrap.Modal.getOrCreateInstance(pkgQuoteModalEl);
        const bsConfirmModal = bootstrap.Modal.getOrCreateInstance(pkgQuoteConfirmModalEl);

        btnOpenPdQuoteConfirm.addEventListener('click', function(e) {
            e.preventDefault();

            const dateField = document.getElementById('pdQuoteTravelDate');
            const returnDateField = document.getElementById('pdQuoteReturnDate');
            const travelersSelect = document.getElementById('pdQuoteTravelers');
            const specialReqField = document.getElementById('pdQuoteSpecialReq');

            if (!dateField || !dateField.value) {
                if (window.ICorelinkDialog) {
                    window.ICorelinkDialog.toast('warning', 'Please select your preferred departure date.', 'Date Required');
                } else {
                    alert('Please select your preferred departure date.');
                }
                dateField?.focus();
                return;
            }

            let dateStr = dateField.value;
            if (returnDateField && returnDateField.value) {
                dateStr += ` &bull; Return: ${returnDateField.value}`;
            }

            const pdModalDates = document.getElementById('pdModalDates');
            const pdModalTravelers = document.getElementById('pdModalTravelers');
            const pdModalSpecialReq = document.getElementById('pdModalSpecialReq');

            if (pdModalDates) pdModalDates.innerHTML = dateStr;
            if (pdModalTravelers) pdModalTravelers.textContent = `${travelersSelect ? travelersSelect.value : 1} Traveler(s)`;
            if (pdModalSpecialReq) pdModalSpecialReq.textContent = (specialReqField?.value.trim()) || 'None specified';

            bsQuoteModal.hide();
            setTimeout(() => {
                bsConfirmModal.show();
            }, 350);
        });

        if (btnBackToPdQuoteForm) {
            btnBackToPdQuoteForm.addEventListener('click', function() {
                bsConfirmModal.hide();
                setTimeout(() => {
                    bsQuoteModal.show();
                }, 350);
            });
        }

        if (btnSubmitPdQuoteFinal) {
            btnSubmitPdQuoteFinal.addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Submitting Request...';
                pkgQuoteForm.submit();
            });
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

