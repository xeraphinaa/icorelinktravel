<?php
$current_page = 'hotels';
$page_title = 'Partner Hotels & Resorts';
require_once 'includes/header.php';

// Fetch filters
$search_query = clean_input($_GET['q'] ?? '');
$rating_filter = clean_input($_GET['rating'] ?? '');

try {
    $sql = "SELECT h.*, 
            (SELECT MIN(price_per_night) FROM hotel_rooms WHERE hotel_id = h.id AND status = 'available') as min_room_price,
            (SELECT COUNT(*) FROM hotel_rooms WHERE hotel_id = h.id AND status = 'available') as room_count
            FROM hotels h 
            WHERE h.status IN ('active', 'featured')";
    $params = [];

    if (!empty($search_query)) {
        $sql .= " AND (h.name LIKE ? OR h.destination LIKE ? OR h.address LIKE ? OR h.description LIKE ?)";
        $term = "%$search_query%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    if (!empty($rating_filter)) {
        $sql .= " AND h.rating = ?";
        $params[] = $rating_filter;
    }

    $sql .= " ORDER BY CASE WHEN h.is_featured = 1 OR h.status = 'featured' THEN 1 ELSE 2 END, h.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $hotels = $stmt->fetchAll();

    // Fetch all active rooms grouped by hotel_id
    $rooms_stmt = $pdo->query("SELECT * FROM hotel_rooms WHERE status = 'available' ORDER BY price_per_night ASC");
    $all_rooms = $rooms_stmt->fetchAll();
    $hotel_rooms = [];
    foreach ($all_rooms as $r) {
        $hotel_rooms[$r['hotel_id']][] = $r;
    }

} catch (PDOException $e) {
    error_log('Failed to fetch hotels: ' . $e->getMessage());
    $hotels = [];
    $hotel_rooms = [];
}
?>

<style>
.hotel-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    border: 1px solid #e2e8f0;
}
.hotel-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0, 34, 68, 0.12) !important;
}
.amenity-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.76rem;
    font-weight: 500;
    background: #f1f5f9;
    color: #334155;
    padding: 0.25rem 0.6rem;
    border-radius: 9999px;
    margin-right: 0.25rem;
    margin-bottom: 0.3rem;
}
.room-type-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    transition: all 0.2s ease;
}
.room-type-card:hover {
    border-color: #0284c7;
    box-shadow: 0 8px 20px rgba(2, 132, 199, 0.08);
}
/* Remove the big dark backdrop shadow when clicking View Room Types */
.modal-backdrop,
.modal-backdrop.show,
.modal-backdrop.fade {
    display: none !important;
    opacity: 0 !important;
    background: transparent !important;
    pointer-events: none !important;
}

.bg-dark-blue {
    background-color: #0b2240 !important;
}

.modal {
    z-index: 1080 !important;
}

.modal-dialog {
    margin-top: 2.5rem !important;
    margin-bottom: 2.5rem !important;
}

.modal-content {
    box-shadow: 0 25px 50px -12px rgba(11, 34, 64, 0.35) !important;
    border: 1px solid #cbd5e1 !important;
}
</style>

<!-- Hotels Hero Banner -->
<div class="hotels-hero py-5 text-white" style="background: linear-gradient(135deg, rgba(105, 156, 225, 0.65) 0%, rgba(90, 142, 217, 0.60) 100%), url('<?php echo APP_URL; ?>/assets/images/philippines_hero_bg.png') center/cover no-repeat;">
    <div class="container py-4 text-center">
        <span class="badge bg-white text-primary fw-bold text-uppercase px-3 py-2 mb-3 shadow-sm" style="letter-spacing: 0.1em;">
            <i class="fa-solid fa-hotel me-1"></i> Handpicked Accommodations & Resorts
        </span>
        <h1 class="display-4 fw-bold font-serif mb-3 text-white" style="text-shadow: 0 2px 12px rgba(7, 25, 47, 0.6);">Partner Hotels & Stays</h1>
        <p class="lead text-white opacity-95 mx-auto mb-4" style="max-width: 760px; text-shadow: 0 1px 8px rgba(7, 25, 47, 0.5);">
            Experience genuine Filipino hospitality with certified luxury resorts, beachfront stays, and boutique city hotels vetted for ultimate comfort, security, and world-class amenities.
        </p>

        <!-- Search & Filter Bar -->
        <div class="card border-0 shadow-lg rounded-4 p-3 mx-auto text-start" style="max-width: 820px; background: rgba(255, 255, 255, 0.98);">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="q" class="form-control border-0 bg-transparent" placeholder="Search hotel name, destination, address..." value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="rating" class="form-select border-0 bg-light rounded-3">
                        <option value="">All Star Ratings</option>
                        <option value="5-Star" <?php echo $rating_filter === '5-Star' ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ 5-Star</option>
                        <option value="4-Star" <?php echo $rating_filter === '4-Star' ? 'selected' : ''; ?>>⭐⭐⭐⭐ 4-Star</option>
                        <option value="3-Star" <?php echo $rating_filter === '3-Star' ? 'selected' : ''; ?>>⭐⭐⭐ 3-Star</option>
                        <option value="2-Star" <?php echo $rating_filter === '2-Star' ? 'selected' : ''; ?>>⭐⭐ 2-Star</option>
                        <option value="Boutique / Resort" <?php echo $rating_filter === 'Boutique / Resort' ? 'selected' : ''; ?>>Boutique / Resort</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary-custom w-100 py-2 rounded-3 fw-bold">
                        <i class="fa-solid fa-sliders me-1"></i> Filter Stays
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="font-serif fw-bold text-dark-blue mb-1">Available Accommodations</h2>
                <p class="text-muted small mb-0">Showing <?php echo count($hotels); ?> properties matching your criteria.</p>
            </div>
            <?php if (!empty($search_query) || !empty($rating_filter)): ?>
                <a href="<?php echo APP_URL; ?>/hotels.php" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="fa-solid fa-xmark me-1"></i> Clear Filters
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($hotels)): ?>
            <div class="alert alert-info text-center py-5 rounded-4 shadow-sm bg-white border-0">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="fa-solid fa-hotel fa-2x"></i>
                </div>
                <h4 class="fw-bold text-dark-blue">No Hotels Listed Currently</h4>
                <p class="text-muted mb-3">No hotels matched your search criteria. Try clearing filters or exploring our custom package builder.</p>
                <a href="<?php echo APP_URL; ?>/hotels.php" class="btn btn-primary-custom rounded-pill px-4">View All Hotels</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($hotels as $hotel): ?>
                    <?php 
                    $image_url = !empty($hotel['image_path']) 
                        ? UPLOAD_URL . '/hotels/' . $hotel['image_path'] 
                        : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=900&q=80'; 
                    
                    $rooms = $hotel_rooms[$hotel['id']] ?? [];
                    $room_count = count($rooms);
                    
                    // Determine starting rate: minimum of rooms, or hotel price_per_night
                    $starting_rate = !empty($hotel['min_room_price']) 
                        ? floatval($hotel['min_room_price']) 
                        : floatval($hotel['price_per_night'] ?? 2500.00);

                    // Amenities parse
                    $amenities_str = $hotel['amenities'] ?: ($hotel['includes'] ?: '');
                    $amenities_arr = array_filter(array_map('trim', explode(',', $amenities_str)));
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card hotel-card h-100 rounded-4 overflow-hidden shadow-sm bg-white d-flex flex-column">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="w-100" style="height: 230px; object-fit: cover;">
                                
                                <!-- Destination Badge -->
                                <div class="position-absolute top-0 start-0 m-3">
                                    <span class="badge bg-dark-blue bg-opacity-90 text-white px-3 py-1 rounded-pill small shadow-sm">
                                        <i class="fa-solid fa-location-dot text-info me-1"></i> <?php echo htmlspecialchars($hotel['destination'] ?: $hotel['location']); ?>
                                    </span>
                                </div>

                                <!-- Rating & Featured Badges -->
                                <div class="position-absolute top-0 end-0 m-3 d-flex flex-column align-items-end gap-1">
                                    <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill shadow-sm">
                                        <i class="fa-solid fa-star me-1"></i> <?php echo htmlspecialchars($hotel['rating'] ?: '5-Star'); ?>
                                    </span>
                                    <?php if (!empty($hotel['is_featured']) || $hotel['status'] === 'featured'): ?>
                                        <span class="badge bg-primary text-white fw-bold px-2 py-1 rounded-pill small shadow-sm">
                                            <i class="fa-solid fa-award me-1"></i> Featured
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Room Count Pill -->
                                <div class="position-absolute bottom-0 end-0 m-3">
                                    <span class="badge bg-dark bg-opacity-75 text-white px-3 py-1 rounded-pill small">
                                        <i class="fa-solid fa-door-open me-1"></i> <?php echo $room_count; ?> Room Tier<?php echo $room_count !== 1 ? 's' : ''; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h4 class="fw-bold text-dark-blue mb-1"><?php echo htmlspecialchars($hotel['name']); ?></h4>
                                        <div class="small text-muted">
                                            <i class="fa-solid fa-map-pin text-danger me-1"></i> <?php echo htmlspecialchars($hotel['address'] ?: ($hotel['destination'] ?: $hotel['location'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-muted small mb-3 flex-grow-1" style="line-height: 1.55;">
                                    <?php echo htmlspecialchars($hotel['description'] ?: 'Experience unmatched comfort and tailored Philippine hospitality.'); ?>
                                </p>

                                <!-- Check-in & Check-out -->
                                <div class="d-flex align-items-center gap-3 p-2 px-3 bg-light rounded-3 mb-3 small text-muted">
                                    <div>
                                        <i class="fa-regular fa-clock text-primary me-1"></i> Check-in: <strong><?php echo htmlspecialchars($hotel['check_in_time'] ?: '2:00 PM'); ?></strong>
                                    </div>
                                    <span class="text-secondary">•</span>
                                    <div>
                                        <i class="fa-regular fa-clock text-danger me-1"></i> Check-out: <strong><?php echo htmlspecialchars($hotel['check_out_time'] ?: '12:00 PM'); ?></strong>
                                    </div>
                                </div>

                                <!-- Amenities list -->
                                <?php if (!empty($amenities_arr)): ?>
                                    <div class="mb-3">
                                        <div class="small text-muted fw-bold mb-1">Hotel Amenities:</div>
                                        <div class="d-flex flex-wrap">
                                            <?php foreach (array_slice($amenities_arr, 0, 5) as $am): ?>
                                                <span class="amenity-pill">
                                                    <i class="fa-solid fa-circle-check text-success"></i> <?php echo htmlspecialchars($am); ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if (count($amenities_arr) > 5): ?>
                                                <span class="amenity-pill text-muted">+<?php echo count($amenities_arr) - 5; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Starting Rate -->
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto mb-3">
                                    <div>
                                        <div class="small text-muted">Starting from</div>
                                        <div class="fw-bold text-primary fs-5">
                                            ₱<?php echo number_format($starting_rate, 2); ?>
                                            <small class="text-muted fs-6 fw-normal">/ night</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#hotelRoomsModal<?php echo $hotel['id']; ?>">
                                        <i class="fa-solid fa-bed me-1"></i> View Room Types
                                    </button>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex flex-column gap-2">
                                    <a href="<?php echo APP_URL; ?>/customer/book.php?hotel_id=<?php echo $hotel['id']; ?>" class="btn btn-primary-custom w-100 py-2 fw-bold">
                                        <i class="fa-solid fa-calendar-check me-1.5"></i> Book Now
                                    </a>
                                    <a href="<?php echo APP_URL; ?>/customer/custom_booking.php?hotel_id=<?php echo $hotel['id']; ?>" class="btn btn-outline-primary w-100 py-1 btn-sm fw-semibold">
                                        <i class="fa-solid fa-wand-magic-sparkles me-1.5"></i> Customize in Trip Builder
                                    </a>
                                    <a href="<?php echo APP_URL; ?>/customer/messages.php?inquire_type=hotel&id=<?php echo $hotel['id']; ?>" class="btn btn-outline-secondary w-100 py-1 btn-sm">
                                        <i class="fa-solid fa-comments me-1.5"></i> Inquire Accommodation
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

<!-- ================= HOTEL ROOM MODALS (Rendered at root level outside grid) ================= -->
<?php if (!empty($hotels)): ?>
    <?php foreach ($hotels as $hotel): ?>
        <?php 
        $rooms = $hotel_rooms[$hotel['id']] ?? [];
        ?>
        <div class="modal fade" id="hotelRoomsModal<?php echo $hotel['id']; ?>" tabindex="-1" aria-labelledby="hotelRoomsModalLabel<?php echo $hotel['id']; ?>" aria-hidden="true" data-bs-backdrop="false">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
                <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                    <div class="modal-header text-white rounded-top-4 py-3 px-4" style="background: #0b2240 !important;">
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white" id="hotelRoomsModalLabel<?php echo $hotel['id']; ?>">
                                <i class="fa-solid fa-hotel text-warning me-2"></i> <?php echo htmlspecialchars($hotel['name']); ?> — Room Categories & Rates
                            </h5>
                            <div class="small text-white-50 mt-1">
                                <i class="fa-solid fa-location-dot text-info me-1"></i> <?php echo htmlspecialchars($hotel['destination'] ?: $hotel['location']); ?> • <?php echo htmlspecialchars($hotel['rating'] ?: '5-Star'); ?> • Check-in <?php echo htmlspecialchars($hotel['check_in_time'] ?: '2:00 PM'); ?> / Check-out <?php echo htmlspecialchars($hotel['check_out_time'] ?: '12:00 PM'); ?>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 bg-light">
                        <?php if (empty($rooms)): ?>
                            <div class="text-center py-5 bg-white rounded-4 border">
                                <i class="fa-solid fa-bed fa-3x text-muted opacity-50 mb-3"></i>
                                <h5 class="fw-bold text-dark-blue">Standard Accommodations Available</h5>
                                <p class="text-muted small mb-3">Custom room configurations can be arranged upon trip customization.</p>
                                <a href="<?php echo APP_URL; ?>/customer/book.php?hotel_id=<?php echo $hotel['id']; ?>" class="btn btn-primary-custom rounded-pill px-4 fw-bold">
                                    <i class="fa-solid fa-calendar-check me-1"></i> Book Now (₱<?php echo number_format($hotel['price_per_night'] ?? 2500, 2); ?>/night)
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($rooms as $rm): ?>
                                    <?php 
                                    $rm_img = !empty($rm['image_path']) 
                                        ? UPLOAD_URL . '/hotels/rooms/' . $rm['image_path']
                                        : 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=600&q=80';
                                    
                                    $rm_amenities = array_filter(array_map('trim', explode(',', $rm['amenities'] ?: '')));
                                    ?>
                                    <div class="col-lg-6">
                                        <div class="room-type-card p-3 h-100 d-flex flex-column">
                                            <div class="row g-3">
                                                <div class="col-sm-5">
                                                    <img src="<?php echo htmlspecialchars($rm_img); ?>" alt="<?php echo htmlspecialchars($rm['room_type']); ?>" class="w-100 rounded-3" style="height: 160px; object-fit: cover;">
                                                </div>
                                                <div class="col-sm-7 d-flex flex-column">
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <h5 class="fw-bold text-dark-blue mb-0"><?php echo htmlspecialchars($rm['room_type']); ?></h5>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                                                            <?php echo $rm['available_rooms']; ?> Left
                                                        </span>
                                                    </div>

                                                    <div class="small text-muted mb-2">
                                                        <span class="me-2"><i class="fa-solid fa-bed text-primary me-1"></i> <?php echo htmlspecialchars($rm['bed_type'] ?: 'King Bed'); ?></span>
                                                        <span><i class="fa-solid fa-user-group text-info me-1"></i> Max <?php echo htmlspecialchars($rm['max_guests'] ?: '2 Adults'); ?></span>
                                                    </div>

                                                    <div class="small text-muted mb-2">
                                                        <?php if (!empty($rm['room_size'])): ?>
                                                            <span class="me-2"><i class="fa-solid fa-ruler-combined text-secondary me-1"></i> <?php echo htmlspecialchars($rm['room_size']); ?></span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($rm['view_type'])): ?>
                                                            <span><i class="fa-solid fa-eye text-warning me-1"></i> <?php echo htmlspecialchars($rm['view_type']); ?></span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if (!empty($rm_amenities)): ?>
                                                        <div class="mb-2">
                                                            <?php foreach (array_slice($rm_amenities, 0, 4) as $ra): ?>
                                                                <span class="badge bg-light text-dark border me-1 small mb-1" style="font-size: 0.72rem;">
                                                                    <?php echo htmlspecialchars($ra); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="mt-auto pt-2 d-flex justify-content-between align-items-center border-top">
                                                        <div>
                                                            <span class="fs-5 fw-bold text-primary">₱<?php echo number_format($rm['price_per_night'], 2); ?></span>
                                                            <small class="text-muted">/ night</small>
                                                        </div>
                                                        <a href="<?php echo APP_URL; ?>/customer/book.php?hotel_id=<?php echo $hotel['id']; ?>&room_id=<?php echo $rm['id']; ?>" class="btn btn-primary-custom btn-sm rounded-pill px-3 fw-bold">
                                                            Book Now <i class="fa-solid fa-arrow-right ms-1"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer bg-white rounded-bottom-4 d-flex justify-content-between">
                        <div class="small text-muted">
                            <i class="fa-solid fa-phone me-1 text-primary"></i> <?php echo htmlspecialchars($hotel['contact_number'] ?: 'Contact our reservations team'); ?> 
                            <?php if (!empty($hotel['email'])): ?>
                                • <i class="fa-solid fa-envelope me-1 text-primary"></i> <?php echo htmlspecialchars($hotel['email']); ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-secondary rounded-pill btn-sm px-4" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Close modal cleanly when clicking outside modal content
    document.querySelectorAll('.modal').forEach(function(modalElem) {
        modalElem.addEventListener('click', function(e) {
            if (e.target === modalElem) {
                const modalInstance = bootstrap.Modal.getInstance(modalElem);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

