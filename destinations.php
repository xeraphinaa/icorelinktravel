<?php
$current_page = 'destinations';
$page_title = 'Explore Destinations';
require_once 'includes/header.php';

try {
    $stmt = $pdo->query("SELECT * FROM travel_packages WHERE status IN ('active', 'featured') ORDER BY CASE WHEN status = 'featured' THEN 1 ELSE 2 END, id DESC");
    $destination_packages = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to fetch destination packages: ' . $e->getMessage());
    $destination_packages = [];
}
?>

<!-- Destinations Banner Header -->
<div class="destinations-hero py-5 text-white" style="background: linear-gradient(135deg, rgba(105, 156, 225, 0.65) 0%, rgba(90, 142, 217, 0.60) 100%), url('<?php echo APP_URL; ?>/assets/images/philippines_hero_bg.png') center/cover no-repeat;">
    <div class="container py-4 text-center">
        <span class="badge bg-white text-primary fw-bold text-uppercase px-3 py-2 mb-3 shadow-sm" style="letter-spacing: 0.1em;">
            <i class="fa-solid fa-map-location-dot me-1"></i> Discover the Philippines
        </span>
        <h1 class="display-4 fw-bold font-serif mb-3 text-white" style="text-shadow: 0 2px 12px rgba(7, 25, 47, 0.6);">Philippine Island Destinations</h1>
        <p class="lead text-white opacity-95 mx-auto mb-0" style="max-width: 720px; text-shadow: 0 1px 8px rgba(7, 25, 47, 0.5);">
            From the limestone cliffs of El Nido to the rolling hills of Batanes and crystal waters of the Visayas, explore our curated travel destinations.
        </p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <!-- Filter Tabs / Quick Filter Pill Bar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-5 p-3 bg-white rounded-4 shadow-sm border">
            <div class="d-flex align-items-center gap-2 text-dark-blue fw-bold">
                <i class="fa-solid fa-compass text-primary fs-5"></i>
                <span>Showing <?php echo count($destination_packages); ?> Top Destinations</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo APP_URL; ?>/destinations.php" class="btn btn-sm btn-primary rounded-pill px-3">All Regions</a>
                <a href="<?php echo APP_URL; ?>/packages.php?destination=Palawan" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Palawan</a>
                <a href="<?php echo APP_URL; ?>/packages.php?destination=Batanes" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Batanes</a>
                <a href="<?php echo APP_URL; ?>/packages.php?destination=Cebu" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Cebu & Bohol</a>
            </div>
        </div>

        <?php if (empty($destination_packages)): ?>
            <div class="alert alert-info text-center py-5 rounded-4 shadow-sm">
                <i class="fa-solid fa-plane-slash fa-3x mb-3 text-primary opacity-50"></i>
                <h4 class="fw-bold">No Destinations Found</h4>
                <p class="text-muted mb-3">We are currently updating our itinerary schedules for new island destinations.</p>
                <a href="<?php echo APP_URL; ?>/packages.php" class="btn btn-primary-custom px-4 py-2">Browse All Packages</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($destination_packages as $package): ?>
                    <?php
                    $image_url = !empty($package['image_path'])
                        ? UPLOAD_URL . '/packages/' . $package['image_path']
                        : 'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=900&q=80';
                    $region = get_location_group($package['destination'] . ' ' . $package['name']);
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card destination-card h-100 border-0 rounded-4 overflow-hidden shadow-sm bg-white d-flex flex-column">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($package['destination']); ?>" class="w-100" style="height: 220px; object-fit: cover;">
                                <span class="position-absolute top-0 start-0 m-3 badge bg-dark-blue bg-opacity-75 text-white px-3 py-1 rounded-pill small">
                                    <i class="fa-solid fa-location-dot text-primary me-1"></i> <?php echo htmlspecialchars($region); ?>
                                </span>
                                <?php if ($package['status'] === 'featured'): ?>
                                    <span class="position-absolute top-0 end-0 m-3 badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill shadow-sm">
                                        <i class="fa-solid fa-star me-1"></i> Featured
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-primary fw-bold small text-uppercase" style="letter-spacing: 0.08em;">
                                        <?php echo htmlspecialchars($package['destination']); ?>
                                    </span>
                                    <span class="badge bg-light text-muted border">
                                        <i class="fa-solid fa-clock me-1 text-primary"></i> <?php echo htmlspecialchars($package['duration']); ?>
                                    </span>
                                </div>
                                <h4 class="fw-bold text-dark-blue mb-2"><?php echo htmlspecialchars($package['name']); ?></h4>
                                <p class="text-muted small mb-3 flex-grow-1" style="line-height: 1.6;">
                                    <?php echo htmlspecialchars(mb_strimwidth($package['description'], 0, 160, '...')); ?>
                                </p>
                                
                                <div class="border-top pt-3 mt-auto d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted d-block small" style="font-size: 0.75rem;">Starts at</span>
                                        <span class="fs-5 fw-bold text-primary"><?php echo format_price($package['price']); ?></span>
                                    </div>
                                    <a href="package-details.php?id=<?php echo (int) $package['id']; ?>" class="btn btn-primary-custom btn-sm px-3 py-2">
                                        Explore Itinerary <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
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
