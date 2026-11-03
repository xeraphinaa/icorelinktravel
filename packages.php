<?php
$current_page = 'packages';
$page_title = 'Tour Packages';
require_once 'includes/header.php';

$search_dest = isset($_GET['destination']) ? trim($_GET['destination']) : '';
$search_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$search_duration = isset($_GET['duration']) ? trim($_GET['duration']) : '';

$query_str = "SELECT * FROM travel_packages WHERE status IN ('active', 'featured')";
$params = [];

if (!empty($search_dest)) {
    $query_str .= " AND (destination LIKE ? OR name LIKE ?)";
    $params[] = "%$search_dest%";
    $params[] = "%$search_dest%";
}

if ($search_price > 0) {
    $query_str .= " AND price <= ?";
    $params[] = $search_price;
}

if (!empty($search_duration)) {
    $query_str .= " AND duration LIKE ?";
    $params[] = "%$search_duration%";
}

$query_str .= " ORDER BY CASE WHEN status = 'featured' THEN 1 ELSE 2 END, id DESC";

try {
    $stmt = $pdo->prepare($query_str);
    $stmt->execute($params);
    $packages = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to fetch packages: ' . $e->getMessage());
    $packages = [];
}
?>

<!-- Packages Hero Banner -->
<div class="packages-hero py-5 text-white" style="background: linear-gradient(135deg, rgba(105, 156, 225, 0.65) 0%, rgba(90, 142, 217, 0.60) 100%), url('<?php echo APP_URL; ?>/assets/images/philippines_hero_bg.png') center/cover no-repeat;">
    <div class="container py-4 text-center">
        <span class="badge bg-white text-primary fw-bold text-uppercase px-3 py-2 mb-3 shadow-sm" style="letter-spacing: 0.1em;">
            <i class="fa-solid fa-suitcase-rolling me-1"></i> Tailored Itineraries
        </span>
        <h1 class="display-4 fw-bold font-serif mb-3 text-white" style="text-shadow: 0 2px 12px rgba(7, 25, 47, 0.6);">All-Inclusive Tour Packages</h1>
        <p class="lead text-white opacity-95 mx-auto mb-0" style="max-width: 720px; text-shadow: 0 1px 8px rgba(7, 25, 47, 0.5);">
            Experience premium Philippine escapes with hotel accommodations, airport transfers, island hopping tours, and dedicated staff support.
        </p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <!-- Search & Filter Card -->
        <div class="search-card card mb-5 border-0 rounded-4 shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3 text-dark-blue fw-bold"><i class="fa-solid fa-magnifying-glass text-primary me-2"></i> Filter Travel Packages</h5>
                <form action="<?php echo APP_URL; ?>/packages.php" method="GET">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label text-muted small fw-bold">Destination or Package</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fa-solid fa-location-dot text-primary"></i></span>
                                <input type="text" name="destination" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($search_dest); ?>" placeholder="e.g. Palawan, Batanes, Cebu">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label text-muted small fw-bold">Maximum Budget (PHP)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fa-solid fa-tag text-primary"></i></span>
                                <input type="number" name="max_price" class="form-control bg-light border-0" value="<?php echo $search_price > 0 ? htmlspecialchars($search_price) : ''; ?>" placeholder="Maximum price">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label text-muted small fw-bold">Duration</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fa-solid fa-clock text-primary"></i></span>
                                <select name="duration" class="form-select bg-light border-0">
                                    <option value="">Any Duration</option>
                                    <option value="3 Days" <?php echo $search_duration === '3 Days' ? 'selected' : ''; ?>>3 Days 2 Nights</option>
                                    <option value="4 Days" <?php echo $search_duration === '4 Days' ? 'selected' : ''; ?>>4 Days 3 Nights</option>
                                    <option value="5 Days" <?php echo $search_duration === '5 Days' ? 'selected' : ''; ?>>5 Days or more</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary-custom w-100 py-2 fw-bold">
                                <i class="fa-solid fa-search me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($packages)): ?>
            <div class="alert alert-info text-center py-5 rounded-4 shadow-sm">
                <i class="fa-solid fa-circle-info fa-3x mb-3 text-primary opacity-50"></i>
                <h4 class="fw-bold">No Travel Packages Match Your Search</h4>
                <p class="text-muted mb-3">Try clearing search filters to see all available packages.</p>
                <a href="<?php echo APP_URL; ?>/packages.php" class="btn btn-primary-custom px-4 py-2">Reset Filters</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($packages as $pkg): ?>
                    <?php
                    $img = !empty($pkg['image_path'])
                        ? UPLOAD_URL . '/packages/' . $pkg['image_path']
                        : 'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=900&q=80';
                    $location_group = get_location_group($pkg['destination'] . ' ' . $pkg['name']);
                    ?>
                    <div class="col-12">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white p-3 p-lg-4">
                            <div class="row g-4 align-items-center">
                                <div class="col-lg-5">
                                    <div class="position-relative rounded-3 overflow-hidden shadow-sm">
                                        <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($pkg['name']); ?>" class="w-100" style="height: 280px; object-fit: cover;">
                                        <div class="position-absolute top-0 start-0 m-3">
                                            <span class="badge bg-dark-blue bg-opacity-75 text-white px-3 py-1 rounded-pill small">
                                                <i class="fa-solid fa-location-dot text-primary me-1"></i> <?php echo htmlspecialchars($pkg['destination']); ?>
                                            </span>
                                        </div>
                                        <?php if ($pkg['status'] === 'featured'): ?>
                                            <div class="position-absolute top-0 end-0 m-3">
                                                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill shadow-sm">
                                                    <i class="fa-solid fa-star me-1"></i> Featured
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($location_group)): ?>
                                            <div class="position-absolute bottom-0 start-0 m-3">
                                                <span class="badge bg-white text-dark-blue fw-bold px-3 py-1.5 rounded-pill shadow-sm border border-light" style="color: #0b2240 !important;">
                                                    <i class="fa-solid fa-map-pin me-1 text-primary"></i> <?php echo htmlspecialchars($location_group); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-lg-7 d-flex flex-column">
                                    <?php 
                                    $rating_data = get_package_rating($pkg['id'], $pdo);
                                    ?>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                        <h3 class="fw-bold text-dark-blue mb-0"><?php echo htmlspecialchars($pkg['name']); ?></h3>
                                        <?php echo render_star_rating_badge($rating_data['avg_rating'], $rating_data['total_reviews'], 'sm'); ?>
                                    </div>
                                    
                                    <div class="d-flex flex-wrap gap-3 my-2 text-muted small">
                                        <div><i class="fa-solid fa-clock text-primary me-1"></i> <strong>Duration:</strong> <?php echo htmlspecialchars($pkg['duration']); ?></div>
                                        <div><i class="fa-solid fa-calendar-day text-primary me-1"></i> <strong>Departure:</strong> <?php echo format_date($pkg['travel_date']); ?></div>
                                        <div>
                                            <i class="fa-solid fa-users text-primary me-1"></i> <strong>Slots Remaining:</strong> 
                                            <span class="badge bg-success bg-opacity-10 text-success fw-bold"><?php echo htmlspecialchars($pkg['available_slots']); ?> slots</span>
                                        </div>
                                    </div>

                                    <p class="text-muted small my-3" style="line-height: 1.6;">
                                        <?php echo htmlspecialchars(mb_strimwidth($pkg['description'], 0, 220, '...')); ?>
                                    </p>

                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top mt-auto">
                                        <div>
                                            <span class="text-muted d-block small" style="font-size: 0.78rem;">Rate per Guest</span>
                                            <span class="h3 fw-bold text-primary mb-0"><?php echo format_price($pkg['price']); ?></span>
                                            <span class="text-muted small ms-1">(Reservation: ₱2,000)</span>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="package-details.php?id=<?php echo $pkg['id']; ?>" class="btn btn-outline-custom px-3 py-2">
                                                View Itinerary
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/customer/custom_booking.php?package_id=<?php echo $pkg['id']; ?>" class="btn btn-outline-primary px-3 py-2 fw-semibold" title="Personalize with Flights & Hotels">
                                                <i class="fa-solid fa-wand-magic-sparkles me-1.5"></i> Customize
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/customer/book.php?package_id=<?php echo $pkg['id']; ?>" class="btn btn-primary-custom px-4 py-2">
                                                Book Now <i class="fa-solid fa-arrow-right ms-1.5"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
