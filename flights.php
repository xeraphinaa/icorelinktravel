<?php
$current_page = 'flights';
$page_title = 'Flights & Airfare Coordination';
require_once 'includes/header.php';

$user_role = get_user_role();
$can_edit = is_logged_in() && in_array($user_role, ['Administrator', 'Staff']);
$manage_link = ($user_role === 'Staff') ? APP_URL . '/staff/flights.php' : APP_URL . '/admin/flights.php';

// Ensure flight_date_prices table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS flight_date_prices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        flight_id INT DEFAULT 0,
        flight_date DATE NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_flight_date (flight_id, flight_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {}

// Handle Admin/Staff Quick Flight Editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_edit_flight') {
    if ($can_edit) {
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        $name = clean_input($_POST['name'] ?? '');
        $route = clean_input($_POST['route'] ?? '');
        $origin = clean_input($_POST['origin'] ?? '');
        $destination = clean_input($_POST['destination'] ?? '');
        $airline = clean_input($_POST['airline'] ?? '');
        $cabin_class = clean_input($_POST['cabin_class'] ?? 'Economy');
        $departure_time = clean_input($_POST['departure_time'] ?? '');
        $arrival_time = clean_input($_POST['arrival_time'] ?? '');
        $duration = clean_input($_POST['duration'] ?? '');
        $stops = clean_input($_POST['stops'] ?? '');
        $baggage_info = clean_input($_POST['baggage_info'] ?? 'Baggage Included');
        $price = (float)($_POST['price'] ?? 0);
        $schedule = clean_input($_POST['schedule'] ?? '');
        $status = clean_input($_POST['status'] ?? 'active');
        $description = clean_input($_POST['description'] ?? '');
        $service = clean_input($_POST['service'] ?? '');
        $benefits = clean_input($_POST['benefits'] ?? '');

        if ($id > 0 && !empty($name) && !empty($route)) {
            $stmt = $pdo->prepare("UPDATE flights SET name = ?, route = ?, origin = ?, destination = ?, airline = ?, cabin_class = ?, departure_time = ?, arrival_time = ?, duration = ?, stops = ?, baggage_info = ?, price = ?, schedule = ?, status = ?, description = ?, service = ?, benefits = ? WHERE id = ?");
            $stmt->execute([$name, $route, $origin, $destination, $airline, $cabin_class, $departure_time, $arrival_time, $duration, $stops, $baggage_info, $price, $schedule, $status, $description, $service, $benefits, $id]);
            set_flash_message('success', 'Flight details & schedule updated successfully!');
        }
    }
    header('Location: flights.php');
    exit;
}

// Handle Admin/Staff Date Price Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_date_price') {
    if ($can_edit) {
        verify_csrf();
        $flight_date = clean_input($_POST['flight_date'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        if (!empty($flight_date) && $price > 0) {
            $stmt = $pdo->prepare("INSERT INTO flight_date_prices (flight_id, flight_date, price) VALUES (0, ?, ?) ON DUPLICATE KEY UPDATE price = VALUES(price)");
            $stmt->execute([$flight_date, $price]);
            set_flash_message('success', 'Date price successfully updated for ' . htmlspecialchars($flight_date));
        }
    }
    header('Location: flights.php');
    exit;
}

// Fetch active & featured flights
try {
    $stmt = $pdo->query("SELECT * FROM flights WHERE status IN ('active', 'featured') ORDER BY CASE WHEN status = 'featured' THEN 1 ELSE 2 END, id DESC");
    $flights = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to fetch flights: ' . $e->getMessage());
    $flights = [];
}

// Determine Trending / Most Purchased Flight based on inquiries & bookings
$trending_flight = null;
$trending_bookings_count = 148;
try {
    $stmt = $pdo->query("SELECT f.*, COUNT(m.id) as msg_count FROM flights f LEFT JOIN messages m ON (m.message LIKE CONCAT('%', f.name, '%') OR m.subject LIKE CONCAT('%', f.name, '%')) WHERE f.status IN ('active', 'featured') GROUP BY f.id ORDER BY msg_count DESC, f.status = 'featured' DESC, f.id DESC LIMIT 1");
    $trending_flight = $stmt->fetch();
    if ($trending_flight && !empty($trending_flight['msg_count'])) {
        $trending_bookings_count += (int)$trending_flight['msg_count'];
    }
} catch (Exception $e) {}

if (!$trending_flight && !empty($flights)) {
    $trending_flight = $flights[0];
}

// Fetch custom date prices map
$date_prices_map = [];
try {
    $stmt = $pdo->query("SELECT flight_date, price FROM flight_date_prices");
    while ($row = $stmt->fetch()) {
        $date_prices_map[$row['flight_date']] = (float)$row['price'];
    }
} catch (Exception $e) {}

// Fetch flight schedules map (grouped by flight_id)
$flight_schedules_map = [];
try {
    $stmt_s = $pdo->query("SELECT * FROM flight_schedules WHERE status = 'available' ORDER BY departure_time ASC, id ASC");
    while ($row = $stmt_s->fetch()) {
        $flight_schedules_map[$row['flight_id']][] = $row;
    }
} catch (Exception $e) {}

// Helper to format airport string cleanly into code and airport name
if (!function_exists('parse_airport_display')) {
    function parse_airport_display($str) {
        $str = trim($str ?? '');
        $code = '';
        $name = '';
        if (preg_match('/^([A-Z]{3})\s*[\x{2013}\x{2014}\-–—]\s*(.+)$/u', $str, $m)) {
            $code = $m[1];
            $name = trim($m[2]);
        } elseif (preg_match('/^(.+?)\s*\(([A-Z]{3})\)$/u', $str, $m)) {
            $code = $m[2];
            $name = trim($m[1]);
        } else {
            $code = $str;
            $name = '';
        }
        return ['code' => $code, 'name' => $name, 'raw' => $str];
    }
}

// Helper to retrieve airline branding icon and color accents
if (!function_exists('get_airline_meta')) {
    function get_airline_meta($airline_name) {
        $name_lower = strtolower($airline_name ?? '');
        if (strpos($name_lower, 'cebu') !== false) {
            return ['slug' => 'cebu', 'class' => 'airline-cebu', 'icon' => 'fa-plane-departure', 'accent' => '#eab308'];
        } elseif (strpos($name_lower, 'philippine') !== false || strpos($name_lower, 'pal') !== false) {
            return ['slug' => 'pal', 'class' => 'airline-pal', 'icon' => 'fa-plane-up', 'accent' => '#1d4ed8'];
        } elseif (strpos($name_lower, 'airasia') !== false) {
            return ['slug' => 'airasia', 'class' => 'airline-airasia', 'icon' => 'fa-plane', 'accent' => '#e11d48'];
        } elseif (strpos($name_lower, 'cebgo') !== false) {
            return ['slug' => 'cebgo', 'class' => 'airline-cebgo', 'icon' => 'fa-plane-departure', 'accent' => '#0284c7'];
        }
        return ['slug' => 'default', 'class' => 'airline-default', 'icon' => 'fa-plane', 'accent' => '#00a2e8'];
    }
}
?>

<!-- Custom CSS for Flight Search & Filter Panel (ICORELINK Theme) -->
<style>
.flight-search-wrapper {
    background-color: #f8fafc;
}
.route-header-card {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(11, 34, 64, 0.06);
    border: 1px solid #e2e8f0;
}
.trending-badge {
    background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
    color: #ffffff;
    font-weight: 700;
    font-size: 0.78rem;
    letter-spacing: 0.05em;
    padding: 6px 14px;
    border-radius: 50rem;
}
.date-ribbon-bar {
    background: #0b2238;
    padding: 14px 16px;
    border-radius: 14px;
    margin-bottom: 24px;
    position: relative;
    z-index: 100;
    overflow: visible !important;
}
.date-ribbon-item {
    background: #ffffff;
    border-radius: 10px;
    padding: 10px 18px;
    text-align: center;
    color: #0b2238;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 2px solid transparent;
    cursor: pointer;
    min-width: 125px;
    user-select: none;
}
.date-ribbon-item.active {
    background: #00a2e8;
    color: #ffffff;
    font-weight: bold;
    border-color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 162, 232, 0.4);
}
.date-ribbon-item.active .small-date-sub {
    color: #ffffff !important;
}
.date-ribbon-item:hover:not(.active) {
    background: #e6f4ff;
    transform: translateY(-1px);
}
.filter-card {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(11, 34, 64, 0.04);
}
.filter-group-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0b2238;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
/* Modern, Polished Flight Card Layout */
.flight-timeline-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 10px rgba(11, 34, 64, 0.04);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
}
.flight-timeline-card:hover {
    transform: translateY(-3px);
    border-color: #7dd3fc;
    box-shadow: 0 12px 28px rgba(0, 162, 232, 0.12);
}
.flight-card-inner {
    padding: 20px 22px;
}
@media (max-width: 575.98px) {
    .flight-card-inner {
        padding: 16px 14px;
    }
}

/* Airline Branding & Metadata */
.airline-brand-badge {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(11, 34, 64, 0.05);
    transition: transform 0.2s ease;
}
.flight-timeline-card:hover .airline-brand-badge {
    transform: scale(1.05);
}
.airline-cebu {
    background: #fef08a;
    color: #854d0e;
    border: 1px solid #fde047;
}
.airline-pal {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
.airline-airasia {
    background: #ffe4e6;
    color: #e11d48;
    border: 1px solid #fecdd3;
}
.airline-cebgo {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}
.airline-default {
    background: #f0f9ff;
    color: #0284c7;
    border: 1px solid #bae6fd;
}

.airline-name-text {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0b2238;
    line-height: 1.25;
}
.flight-route-sub {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
    margin-top: 2px;
}
.flight-pill-badge {
    font-size: 0.73rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    letter-spacing: 0.01em;
}
.cabin-pill {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.baggage-pill {
    background: #f8fafc;
    color: #475569;
    border: 1px solid #e2e8f0;
}

/* Flight Timeline & Station Displays */
.flight-station {
    min-width: 90px;
    max-width: 155px;
    flex-shrink: 0;
}
.station-departure {
    text-align: left;
}
.station-arrival {
    text-align: right;
}
.flight-time-val {
    font-size: 1.32rem;
    font-weight: 800;
    color: #0b2238;
    line-height: 1.1;
    font-variant-numeric: tabular-nums;
}
.flight-station-code {
    display: inline-block;
    font-size: 0.92rem;
    font-weight: 800;
    color: #0284c7;
    letter-spacing: 0.03em;
    margin-top: 3px;
}
.flight-station-name {
    font-size: 0.76rem;
    color: #64748b;
    font-weight: 500;
    margin-top: 2px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
@media (max-width: 767.98px) {
    .flight-station {
        min-width: 75px;
        max-width: 110px;
    }
    .flight-time-val {
        font-size: 1.18rem;
    }
    .flight-station-name {
        display: none;
    }
}

/* Flight Path Middle Track */
.flight-path-middle {
    flex-grow: 1;
    padding: 0 14px;
    min-width: 90px;
}
.flight-duration-badge {
    font-size: 0.77rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 4px;
}
.flight-track-container {
    position: relative;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
}
.flight-track-node {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #0284c7;
    border: 2px solid #ffffff;
    box-shadow: 0 0 0 1px #0284c7;
    z-index: 2;
    flex-shrink: 0;
}
.flight-track-line {
    height: 2px;
    background: linear-gradient(90deg, #bae6fd 0%, #00a2e8 50%, #bae6fd 100%);
    width: 100%;
    position: relative;
    border-radius: 2px;
}
.flight-plane-icon-box {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #ffffff;
    padding: 0 6px;
    color: #00a2e8;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.flight-stops-indicator {
    margin-top: 4px;
}
.badge-direct {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 50rem;
    display: inline-block;
}
.badge-stops {
    background: #fffbeb;
    color: #d97706;
    border: 1px solid #fde68a;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 50rem;
    display: inline-block;
}

/* Right Column: Pricing & Action Hierarchy */
.flight-action-panel {
    display: flex;
    flex-direction: column;
    height: 100%;
    justify-content: center;
}
@media (min-width: 992px) {
    .flight-action-panel {
        border-left: 1px dashed #e2e8f0;
        padding-left: 18px;
    }
}
@media (max-width: 991.98px) {
    .flight-action-panel {
        border-top: 1px dashed #e2e8f0;
        padding-top: 16px;
        margin-top: 6px;
    }
}
.flight-price-caption {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #94a3b8;
    font-weight: 600;
    display: block;
}
.flight-price-val {
    font-size: 1.45rem;
    font-weight: 800;
    color: #0b2238;
    line-height: 1.15;
    margin: 2px 0;
}
.flight-price-sub {
    font-size: 0.74rem;
    color: #94a3b8;
    display: block;
}
.btn-book-primary {
    background: linear-gradient(135deg, #00a2e8 0%, #0081ba 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 10px;
    padding: 8px 14px;
    font-size: 0.88rem;
    font-weight: 700;
    transition: all 0.2s ease;
    box-shadow: 0 3px 10px rgba(0, 162, 232, 0.25);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-book-primary:hover {
    background: linear-gradient(135deg, #008ecb 0%, #006f9f 100%);
    transform: translateY(-1px);
    box-shadow: 0 5px 14px rgba(0, 162, 232, 0.35);
    color: #ffffff !important;
}
.btn-view-times {
    background: #f0f9ff;
    color: #0284c7 !important;
    border: 1px solid #bae6fd;
    border-radius: 10px;
    padding: 6px 12px;
    font-size: 0.81rem;
    font-weight: 600;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-view-times:hover {
    background: #e0f2fe;
    border-color: #7dd3fc;
    color: #0369a1 !important;
}
.btn-flight-secondary {
    background: #ffffff;
    color: #475569 !important;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 5px 8px;
    font-size: 0.76rem;
    font-weight: 600;
    transition: all 0.2s ease;
    text-decoration: none;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-flight-secondary:hover {
    background: #f8fafc;
    color: #0b2238 !important;
    border-color: #cbd5e1;
}

.sort-tab-btn {
    border: 1px solid #cbd5e1;
    background: #ffffff;
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 0.88rem;
    color: #0b2238;
    transition: all 0.2s ease;
}
.sort-tab-btn.active {
    background: #e6f4ff;
    border-color: #00a2e8;
    color: #00a2e8;
    font-weight: bold;
}
.text-dark-blue {
    color: #0b2238 !important;
}
/* Completely remove glitchy dark backdrop rectangle */
.modal-backdrop {
    display: none !important;
    opacity: 0 !important;
}
.modal.fade.show {
    background: rgba(11, 34, 64, 0.5) !important;
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
}
</style>

<!-- Flights Hero Banner -->
<div class="flights-hero py-4 text-white" style="background: linear-gradient(135deg, rgba(105, 156, 225, 0.65) 0%, rgba(90, 142, 217, 0.60) 100%), url('<?php echo APP_URL; ?>/assets/images/philippines_hero_bg.png') center/cover no-repeat;">
    <div class="container py-3">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div>
                <span class="badge bg-white text-primary fw-bold text-uppercase px-3 py-1 mb-2 shadow-sm" style="letter-spacing: 0.08em;">
                    <i class="fa-solid fa-plane-departure me-1"></i> Domestic Flight Schedules
                </span>
                <h1 class="h2 fw-bold font-serif mb-1 text-white" style="text-shadow: 0 2px 12px rgba(7, 25, 47, 0.6);">Flight Schedules & Route Coordination</h1>
                <p class="text-white opacity-95 small mb-0" style="text-shadow: 0 1px 8px rgba(7, 25, 47, 0.5);">Browse real-time domestic flight schedules, air corridors, baggage options, and full-year date pricing.</p>
            </div>
            <?php if ($can_edit): ?>
                <a href="<?php echo $manage_link; ?>" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-pill shadow-sm">
                    <i class="fa-solid fa-pen-to-square me-2"></i> Manage Flights & Fares (Staff/Admin)
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="flight-search-wrapper py-4">
    <div class="container">

        <?php display_flash_message(); ?>

        <!-- Upper Section: Trending / Most Purchased Flight Header (Ref: User Image) -->
        <div class="route-header-card p-3 p-md-4 mb-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-primary">
                        <i class="fa-solid fa-fire-flame-curved fs-3 text-danger"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="trending-badge shadow-sm"><i class="fa-solid fa-fire me-1"></i> #1 Trending & Most Purchased Flight</span>
                            <span class="badge bg-light text-dark-blue border"><i class="fa-solid fa-user-check text-success me-1"></i> <?php echo $trending_bookings_count; ?> Customer Bookings</span>
                        </div>
                        <h4 class="fw-bold text-dark-blue mb-1">
                            <?php echo htmlspecialchars($trending_flight['name'] ?? 'Boracay (MPH) → Cebu (CEB) & Domestic Corridors'); ?>
                        </h4>
                        <p class="text-muted small mb-0">
                            <i class="fa-solid fa-route text-primary me-1"></i> <strong>Route:</strong> <?php echo htmlspecialchars($trending_flight['route'] ?? 'Boracay (MPH) → Cebu (CEB)'); ?> 
                            &bull; <i class="fa-solid fa-calendar-days text-primary me-1"></i> Flexible Full-Year Dates &bull; 1 Passenger &bull; Economy Class
                        </p>
                    </div>
                </div>
                <div class="d-flex flex-column align-items-md-end gap-2">
                    <div class="text-md-end">
                        <small class="text-muted d-block">Top Fare Starting at</small>
                        <span class="h4 fw-bold text-danger mb-0">₱ <?php echo number_format((float)($trending_flight['price'] ?? 1821), 2); ?></span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?php echo APP_URL; ?>/customer/book.php?flight_id=<?php echo $trending_flight['id'] ?? 1; ?>" id="bookTrendingFlightBtn" class="btn btn-primary-custom btn-sm px-3 py-2 fw-bold rounded-pill">
                            <i class="fa-solid fa-plane-circle-check me-1"></i> Book Trending Flight
                        </a>
                        <button class="btn btn-outline-primary rounded-pill btn-sm px-3 py-2 fw-bold" onclick="window.scrollTo({top: document.getElementById('flightScheduleContainer').offsetTop - 20, behavior: 'smooth'});">
                            <i class="fa-solid fa-list me-1"></i> View Flight Schedules
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lower Section: Full-Year Date Ribbon Bar & Calendar Navigation (Ref: User Image) -->
        <div class="date-ribbon-bar shadow-sm">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2 px-1">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-white fw-bold small"><i class="fa-solid fa-calendar-week me-1 text-info"></i> Select Travel Date:</span>
                    <span id="activeDateDisplay" class="badge bg-primary text-white fw-bold">Mon 23 Nov 2026</span>
                </div>
                <div class="d-flex align-items-center gap-2 position-relative" id="datePriceEditWrapper">
                    <label class="text-white-50 small mb-0 d-none d-sm-inline" for="jumpDatePicker"><i class="fa-solid fa-calendar-day me-1"></i> Jump to Date:</label>
                    <input type="date" id="jumpDatePicker" class="form-control form-control-sm border-0 shadow-sm" style="max-width: 145px;">
                    <?php if ($can_edit): ?>
                        <button type="button" class="btn btn-warning btn-sm fw-bold px-3 shadow-sm rounded-pill" id="btnToggleDatePricePopover" title="Click to adjust fare for selected date">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Date Price (Staff/Admin)
                        </button>

                        <!-- Direct Floating Edit Popover Attached Right Below The Button -->
                        <div id="datePricePopover" class="card shadow-lg rounded-4 border-0 position-absolute" style="display: none; top: calc(100% + 10px); right: 0; width: 340px; z-index: 1060;">
                            <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center rounded-top-4">
                                <span class="fw-bold small"><i class="fa-solid fa-calendar-pen me-1 text-warning"></i> Edit Flight Fare for Date</span>
                                <button type="button" class="btn-close btn-close-white btn-sm" id="btnCloseDatePricePopover" aria-label="Close"></button>
                            </div>
                            <form method="POST" class="p-3 bg-white rounded-bottom-4">
                                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                <input type="hidden" name="action" value="save_date_price">
                                
                                <div class="mb-3 text-start">
                                    <label class="form-label fw-bold small text-dark mb-1">Target Date</label>
                                    <input type="date" name="flight_date" id="popoverFlightDate" class="form-control form-control-sm" required>
                                    <div class="text-muted" style="font-size: 0.75rem;">Selected from active date ribbon</div>
                                </div>

                                <div class="mb-3 text-start">
                                    <label class="form-label fw-bold small text-dark mb-1">Fare Rate (₱)</label>
                                    <input type="number" step="0.01" name="price" id="popoverFlightPrice" class="form-control form-control-sm fw-bold text-primary" placeholder="1829.00" required>
                                    <div class="text-muted" style="font-size: 0.75rem;">Custom fare for all flights on this date</div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" id="btnCancelDatePricePopover">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold"><i class="fa-solid fa-save me-1"></i> Save Fare Rate</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Date Ribbon Tabs with Prev / Next Navigation -->
            <div class="d-flex align-items-center justify-content-between gap-2">
                <button class="btn btn-sm btn-outline-light border-0 px-2" id="prevDateBtn" title="Previous Days"><i class="fa-solid fa-chevron-left fs-5"></i></button>
                <div class="d-flex gap-2 flex-grow-1 justify-content-start justify-content-md-center overflow-auto px-2 py-1" id="dateRibbonTrack">
                    <!-- Dynamic Date Tabs generated by JS across the year -->
                </div>
                <button class="btn btn-sm btn-outline-light border-0 px-2" id="nextDateBtn" title="Next Days"><i class="fa-solid fa-chevron-right fs-5"></i></button>
            </div>
        </div>

        <!-- Main Search Layout: Left Filter Panel + Flight Schedules List -->
        <div class="row g-4 mb-5">

            <!-- Left Filter Sidebar (Ref: Photos 1, 2, 3) -->
            <div class="col-lg-3 col-md-4">
                <div class="filter-card p-3">
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                        <h6 class="fw-bold text-dark-blue mb-0"><i class="fa-solid fa-filter me-2 text-primary"></i>Filter Flights</h6>
                        <button class="btn btn-link text-danger text-decoration-none small p-0 fw-bold" id="resetFiltersBtn">Reset All</button>
                    </div>

                    <!-- Stops Filter -->
                    <div class="mb-4">
                        <div class="filter-group-title">Stops</div>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="Direct" id="stopDirect" checked>
                            <label class="form-check-label small text-dark d-flex justify-content-between" for="stopDirect">
                                <span>Direct</span> <span class="text-muted">(5)</span>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="1 Stop" id="stop1" checked>
                            <label class="form-check-label small text-dark d-flex justify-content-between" for="stop1">
                                <span>1 Stop</span> <span class="text-muted">(56)</span>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="2+ Stops" id="stop2">
                            <label class="form-check-label small text-dark d-flex justify-content-between" for="stop2">
                                <span>2+ Stops</span> <span class="text-muted">(0)</span>
                            </label>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <!-- Airlines Filter -->
                    <div class="mb-4">
                        <div class="filter-group-title">Airlines</div>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="Cebu Pacific" id="airCebuPac" checked>
                            <label class="form-check-label small text-dark" for="airCebuPac">Cebu Pacific</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="Philippine Airlines" id="airPAL" checked>
                            <label class="form-check-label small text-dark" for="airPAL">Philippine Airlines</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="Cebgo" id="airCebgo" checked>
                            <label class="form-check-label small text-dark" for="airCebgo">Cebgo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="AirAsia" id="airAsia" checked>
                            <label class="form-check-label small text-dark" for="airAsia">AirAsia</label>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <!-- Departure Time Filter -->
                    <div class="mb-4">
                        <div class="filter-group-title">Departure Time</div>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="Early" id="timeEarly" checked>
                            <label class="form-check-label small text-dark" for="timeEarly">Early Flight <span class="text-muted small">(00:00 - 06:00)</span></label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="Morning" id="timeMorning" checked>
                            <label class="form-check-label small text-dark" for="timeMorning">Morning Flight <span class="text-muted small">(06:00 - 12:00)</span></label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="Afternoon" id="timeAfternoon" checked>
                            <label class="form-check-label small text-dark" for="timeAfternoon">Afternoon Flight <span class="text-muted small">(12:00 - 18:00)</span></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="Night" id="timeNight" checked>
                            <label class="form-check-label small text-dark" for="timeNight">Night Flight <span class="text-muted small">(18:00 - 00:00)</span></label>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <!-- Price Filter -->
                    <div class="mb-3">
                        <div class="filter-group-title">Price Range</div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input type="number" class="form-control form-control-sm" id="minPriceInput" value="1000" placeholder="Min ₱">
                            <span class="text-muted">-</span>
                            <input type="number" class="form-control form-control-sm" id="maxPriceInput" value="15000" placeholder="Max ₱">
                        </div>
                        <input type="range" class="form-range" id="priceRangeSlider" min="1000" max="15000" step="500" value="15000">
                    </div>

                    <hr class="my-3 opacity-25">

                    <!-- Transit Points -->
                    <div>
                        <div class="filter-group-title">Transit Points</div>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="Manila (MNL)" id="transitMNL" checked>
                            <label class="form-check-label small text-dark" for="transitMNL">Manila (MNL)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-checkbox" type="checkbox" value="Davao (DVO)" id="transitDVO" checked>
                            <label class="form-check-label small text-dark" for="transitDVO">Davao (DVO)</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Flight Search Results Schedule List (Ref: Photos 1, 2, 3) -->
            <div class="col-lg-9 col-md-8">

                <!-- Header Bar with Sort Cards -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <h5 class="fw-bold text-dark-blue mb-0">Departure flights</h5>
                        <p class="text-muted small mb-0">Showing departure schedules & price rates for <span id="currentSelectedDateLabel" class="fw-bold text-primary">Mon, 23 Nov 2026</span></p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="sort-tab-btn active" data-sort="recommended"><i class="fa-solid fa-thumbs-up me-1 text-primary"></i> Recommended <span class="d-block small text-muted" id="recPriceLabel">₱ 1,821</span></button>
                        <button class="sort-tab-btn" data-sort="cheapest"><i class="fa-solid fa-sack-dollar me-1 text-success"></i> Cheapest <span class="d-block small text-muted" id="cheapPriceLabel">₱ 1,821</span></button>
                        <button class="sort-tab-btn" data-sort="fastest"><i class="fa-solid fa-bolt me-1 text-warning"></i> Fastest <span class="d-block small text-muted" id="fastPriceLabel">₱ 2,157</span></button>
                    </div>
                </div>

                <!-- Interactive Schedule Cards Container -->
                <div id="flightScheduleContainer" class="d-flex flex-column gap-3">

                    <?php if (empty($flights)): ?>
                        <div class="alert alert-info text-center py-5 rounded-4 shadow-sm">
                            <i class="fa-solid fa-plane-circle-exclamation fa-3x mb-3 text-primary opacity-50"></i>
                            <h4 class="fw-bold">No Flights Listed Currently</h4>
                            <p class="text-muted mb-0">Please check back soon or inquire directly with our ticketing desk.</p>
                        </div>
                    <?php else: ?>

                        <?php foreach ($flights as $flight): ?>
                            <?php 
                            $airline = !empty($flight['airline']) ? $flight['airline'] : 'Cebu Pacific';
                            $origin = !empty($flight['origin']) ? $flight['origin'] : 'Manila (MNL)';
                            $destination = !empty($flight['destination']) ? $flight['destination'] : 'Cebu (CEB)';
                            $dep_time = !empty($flight['departure_time']) ? $flight['departure_time'] : '08:00 AM';
                            $arr_time = !empty($flight['arrival_time']) ? $flight['arrival_time'] : '09:15 AM';
                            $duration = !empty($flight['duration']) ? $flight['duration'] : '1h 15m';
                            $stops = !empty($flight['stops']) ? $flight['stops'] : 'Direct';
                            $cabin = !empty($flight['cabin_class']) ? $flight['cabin_class'] : 'Economy';
                            $baggage = !empty($flight['baggage_info']) ? $flight['baggage_info'] : 'Baggage Included';
                            $base_price = !empty($flight['price']) ? (float)$flight['price'] : 1821.00;

                            $orig_info = parse_airport_display($origin);
                            $dest_info = parse_airport_display($destination);
                            $airline_meta = get_airline_meta($airline);
                            $flight_schedules = $flight_schedules_map[$flight['id']] ?? [];
                            ?>

                            <div class="flight-timeline-card schedule-item" 
                                 data-airline="<?php echo htmlspecialchars($airline); ?>"
                                 data-stops="<?php echo htmlspecialchars($stops); ?>"
                                 data-base-price="<?php echo $base_price; ?>"
                                 data-price="<?php echo $base_price; ?>"
                                 data-dep-time="<?php echo htmlspecialchars($dep_time); ?>">
                                
                                <div class="flight-card-inner">
                                    <div class="row align-items-center g-3 g-xl-4">
                                        
                                        <!-- Left Details Column: Airline Identity & Amenities -->
                                        <div class="col-xl-3 col-lg-3 col-md-12">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="airline-brand-badge <?php echo $airline_meta['class']; ?>" title="<?php echo htmlspecialchars($airline); ?>">
                                                    <i class="fa-solid <?php echo $airline_meta['icon']; ?>"></i>
                                                </div>
                                                <div class="min-w-0 flex-grow-1">
                                                    <div class="airline-name-text text-truncate"><?php echo htmlspecialchars($airline); ?></div>
                                                    <div class="flight-route-sub text-truncate" title="<?php echo htmlspecialchars($flight['name']); ?>">
                                                        <?php echo htmlspecialchars($flight['name']); ?>
                                                    </div>
                                                    <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                                                        <span class="flight-pill-badge cabin-pill">
                                                            <i class="fa-solid fa-chair me-1"></i><?php echo htmlspecialchars($cabin); ?>
                                                        </span>
                                                        <span class="flight-pill-badge baggage-pill" title="<?php echo htmlspecialchars($baggage); ?>">
                                                            <i class="fa-solid fa-suitcase-rolling me-1 text-primary"></i><?php echo htmlspecialchars($baggage); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Middle Timeline Column: Departure, Flight Path & Arrival -->
                                        <div class="col-xl-6 col-lg-6 col-md-8">
                                            <div class="d-flex align-items-center justify-content-between">
                                                
                                                <!-- Departure Station -->
                                                <div class="flight-station station-departure">
                                                    <div class="flight-time-val"><?php echo htmlspecialchars($dep_time); ?></div>
                                                    <div class="flight-station-code"><?php echo htmlspecialchars($orig_info['code']); ?></div>
                                                    <div class="flight-station-name" title="<?php echo htmlspecialchars($orig_info['name'] ?: $origin); ?>">
                                                        <?php echo htmlspecialchars($orig_info['name'] ?: $origin); ?>
                                                    </div>
                                                </div>

                                                <!-- Flight Path Middle Track -->
                                                <div class="flight-path-middle text-center">
                                                    <div class="flight-duration-badge"><?php echo htmlspecialchars($duration); ?></div>
                                                    <div class="flight-track-container">
                                                        <span class="flight-track-node"></span>
                                                        <div class="flight-track-line">
                                                            <div class="flight-plane-icon-box">
                                                                <i class="fa-solid fa-plane"></i>
                                                            </div>
                                                        </div>
                                                        <span class="flight-track-node"></span>
                                                    </div>
                                                    <div class="flight-stops-indicator">
                                                        <?php if (stripos($stops, 'Direct') !== false): ?>
                                                            <span class="badge-direct"><i class="fa-solid fa-bolt me-1"></i>Direct</span>
                                                        <?php else: ?>
                                                            <span class="badge-stops"><i class="fa-solid fa-circle-nodes me-1"></i><?php echo htmlspecialchars($stops); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- Arrival Station -->
                                                <div class="flight-station station-arrival">
                                                    <div class="flight-time-val"><?php echo htmlspecialchars($arr_time); ?></div>
                                                    <div class="flight-station-code"><?php echo htmlspecialchars($dest_info['code']); ?></div>
                                                    <div class="flight-station-name" title="<?php echo htmlspecialchars($dest_info['name'] ?: $destination); ?>">
                                                        <?php echo htmlspecialchars($dest_info['name'] ?: $destination); ?>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <!-- Right Price & Action Column -->
                                        <div class="col-xl-3 col-lg-3 col-md-4">
                                            <div class="flight-action-panel text-md-end">
                                                <div class="mb-2">
                                                    <span class="flight-price-caption">Starting from</span>
                                                    <div class="flight-price-val display-price-label">₱ <?php echo number_format($base_price, 2); ?></div>
                                                    <span class="flight-price-sub">Price / Pax</span>
                                                </div>

                                                <div class="d-flex flex-column gap-1 w-100">
                                                    <!-- Primary CTA: Book Flight -->
                                                    <a href="<?php echo APP_URL; ?>/customer/book.php?flight_id=<?php echo $flight['id']; ?>" 
                                                       class="btn btn-book-primary w-100 btn-book-flight-direct" 
                                                       data-flight-id="<?php echo $flight['id']; ?>">
                                                        <i class="fa-solid fa-plane-circle-check me-1.5"></i> Book Now
                                                    </a>

                                                    <!-- View Flight Times (Modal Trigger) -->
                                                    <button type="button" class="btn btn-view-times w-100" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#flightTimesModal<?php echo $flight['id']; ?>">
                                                        <i class="fa-regular fa-clock me-1 text-primary"></i> View Flight Times (<?php echo count($flight_schedules); ?>)
                                                    </button>

                                                    <!-- Compact Secondary Actions (Trip Builder + Inquiry) -->
                                                    <div class="d-flex gap-1 w-100 mt-1">
                                                        <a href="<?php echo APP_URL; ?>/customer/custom_booking.php?flight_id=<?php echo $flight['id']; ?>" 
                                                           class="btn btn-flight-secondary flex-fill" 
                                                           title="Customize in Trip Builder">
                                                            <i class="fa-solid fa-wand-magic-sparkles me-1 text-primary"></i> Trip Builder
                                                        </a>
                                                        <a href="<?php echo APP_URL; ?>/customer/messages.php?inquire_type=flight&id=<?php echo $flight['id']; ?>" 
                                                           class="btn btn-flight-secondary flex-fill" 
                                                           title="Flight Inquiry">
                                                            <i class="fa-solid fa-plane-departure me-1 text-secondary"></i> Inquiry
                                                        </a>
                                                    </div>

                                                    <?php if ($can_edit): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning w-100 mt-1 fw-bold btn-trigger-edit-flight py-1"
                                                                data-id="<?php echo $flight['id']; ?>"
                                                                data-name="<?php echo htmlspecialchars($flight['name']); ?>"
                                                                data-route="<?php echo htmlspecialchars($flight['route']); ?>"
                                                                data-origin="<?php echo htmlspecialchars($flight['origin'] ?? ''); ?>"
                                                                data-destination="<?php echo htmlspecialchars($flight['destination'] ?? ''); ?>"
                                                                data-airline="<?php echo htmlspecialchars($flight['airline'] ?? ''); ?>"
                                                                data-cabin="<?php echo htmlspecialchars($cabin); ?>"
                                                                data-dep-time="<?php echo htmlspecialchars($flight['departure_time'] ?? ''); ?>"
                                                                data-arr-time="<?php echo htmlspecialchars($flight['arrival_time'] ?? ''); ?>"
                                                                data-duration="<?php echo htmlspecialchars($flight['duration'] ?? ''); ?>"
                                                                data-stops="<?php echo htmlspecialchars($flight['stops'] ?? ''); ?>"
                                                                data-baggage="<?php echo htmlspecialchars($baggage); ?>"
                                                                data-price="<?php echo htmlspecialchars($flight['price'] ?? ''); ?>"
                                                                data-schedule="<?php echo htmlspecialchars($flight['schedule'] ?? ''); ?>"
                                                                data-status="<?php echo htmlspecialchars($flight['status']); ?>"
                                                                data-description="<?php echo htmlspecialchars($flight['description'] ?? ''); ?>"
                                                                data-service="<?php echo htmlspecialchars($flight['service'] ?? ''); ?>"
                                                                data-benefits="<?php echo htmlspecialchars($flight['benefits'] ?? ''); ?>">
                                                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Flight (Staff)
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- ================= FLIGHT SCHEDULES / TIMES MODALS ================= -->
<?php if (!empty($flights)): ?>
    <?php foreach ($flights as $flight): ?>
        <?php $schedules = $flight_schedules_map[$flight['id']] ?? []; ?>
        <div class="modal fade" id="flightTimesModal<?php echo $flight['id']; ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                    <div class="modal-header text-white rounded-top-4 py-3 px-4" style="background: #0b2240 !important;">
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white">
                                <i class="fa-solid fa-plane-departure text-warning me-2"></i> <?php echo htmlspecialchars($flight['name']); ?> — Flight Times & Rates
                            </h5>
                            <div class="small text-white-50 mt-1">
                                <i class="fa-solid fa-plane text-info me-1"></i> <?php echo htmlspecialchars($flight['airline'] ?: 'Cebu Pacific'); ?> • <?php echo htmlspecialchars($flight['origin']); ?> <i class="fa-solid fa-arrow-right-long text-warning mx-1"></i> <?php echo htmlspecialchars($flight['destination']); ?>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 bg-light">
                        <?php if (empty($schedules)): ?>
                            <div class="text-center py-5 bg-white rounded-4 border">
                                <i class="fa-solid fa-plane-slash fa-3x text-muted opacity-50 mb-3"></i>
                                <h5 class="fw-bold text-dark-blue">No Alternative Times Configured</h5>
                                <p class="text-muted small mb-0">The standard scheduled flight is at <?php echo htmlspecialchars($flight['departure_time']); ?>.</p>
                                <a href="<?php echo APP_URL; ?>/customer/book.php?flight_id=<?php echo $flight['id']; ?>" class="btn btn-primary-custom btn-sm rounded-pill px-4 mt-3">Book Standard Flight</a>
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($schedules as $sched): ?>
                                    <div class="bg-white p-3 p-md-4 rounded-4 shadow-sm border d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 rounded-pill small">
                                                    <?php echo htmlspecialchars($sched['cabin_class'] ?: 'Economy'); ?>
                                                </span>
                                                <span class="badge bg-light text-dark border small">
                                                    Flight <?php echo htmlspecialchars($sched['flight_number']); ?>
                                                </span>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 small">
                                                    <?php echo intval($sched['available_seats']); ?> Seats Left
                                                </span>
                                            </div>

                                            <div class="d-flex align-items-center gap-3 my-2">
                                                <div>
                                                    <h4 class="fw-bold text-dark-blue mb-0"><?php echo htmlspecialchars($sched['departure_time']); ?></h4>
                                                    <small class="text-muted"><?php echo htmlspecialchars($flight['origin']); ?></small>
                                                </div>
                                                <div class="text-center px-2">
                                                    <small class="text-muted fw-bold d-block" style="font-size:0.75rem;"><?php echo htmlspecialchars($sched['duration']); ?></small>
                                                    <div class="d-flex align-items-center justify-content-center text-primary">
                                                        <i class="fa-solid fa-arrow-right-long"></i>
                                                    </div>
                                                    <small class="text-primary fw-bold" style="font-size:0.75rem;"><?php echo htmlspecialchars($sched['stops']); ?></small>
                                                </div>
                                                <div>
                                                    <h4 class="fw-bold text-dark-blue mb-0"><?php echo htmlspecialchars($sched['arrival_time']); ?></h4>
                                                    <small class="text-muted"><?php echo htmlspecialchars($flight['destination']); ?></small>
                                                </div>
                                            </div>

                                            <div class="small text-muted mt-2">
                                                <i class="fa-solid fa-suitcase-rolling me-1 text-secondary"></i> <?php echo htmlspecialchars($sched['baggage_info']); ?>
                                                <span class="mx-2">•</span>
                                                <i class="fa-solid fa-calendar me-1 text-secondary"></i> <?php echo htmlspecialchars($sched['days_active'] ?: 'Daily'); ?>
                                            </div>
                                        </div>

                                        <div class="text-md-end border-start-md ps-md-4 pt-2 pt-md-0 d-flex flex-row flex-md-column justify-content-between align-items-center align-items-md-end">
                                            <div>
                                                <div class="small text-muted">Fare per pax</div>
                                                <div class="h4 fw-bold text-primary mb-2">₱ <?php echo number_format((float)$sched['price'], 2); ?></div>
                                            </div>
                                            <a href="<?php echo APP_URL; ?>/customer/book.php?flight_id=<?php echo $flight['id']; ?>&schedule_id=<?php echo $sched['id']; ?>" class="btn btn-primary-custom px-4 py-2 fw-bold text-nowrap rounded-pill">
                                                Book Now <i class="fa-solid fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="modal-footer bg-white border-top justify-content-between py-3 px-4">
                        <div class="small text-muted">
                            <i class="fa-solid fa-phone text-primary me-1"></i> Need assistance? Contact our ticketing desk 24/7.
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm px-4 rounded-pill" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Admin & Staff Instant Edit Flight Modal -->
<?php if ($can_edit): ?>
<div class="modal fade" id="quickEditFlightModal" tabindex="-1" aria-labelledby="quickEditFlightModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="quickEditFlightModalLabel"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Flight Route & Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="action" value="quick_edit_flight">
                    <input type="hidden" name="id" id="editModalFlightId">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Flight Name *</label>
                            <input name="name" id="editModalFlightName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Route Badge *</label>
                            <input name="route" id="editModalFlightRoute" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Origin (Airport Code)</label>
                            <input name="origin" id="editModalFlightOrigin" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Destination (Airport Code)</label>
                            <input name="destination" id="editModalFlightDestination" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Airline Carrier</label>
                            <input name="airline" id="editModalFlightAirline" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Cabin Class</label>
                            <select name="cabin_class" id="editModalFlightCabin" class="form-select">
                                <option value="Economy">Economy</option>
                                <option value="Premium Economy">Premium Economy</option>
                                <option value="Business">Business</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Departure Time</label>
                            <input name="departure_time" id="editModalFlightDepTime" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Arrival Time</label>
                            <input name="arrival_time" id="editModalFlightArrTime" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Duration</label>
                            <input name="duration" id="editModalFlightDuration" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Stops / Connections</label>
                            <input name="stops" id="editModalFlightStops" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Base Fare Price per Pax (₱)</label>
                            <input type="number" step="0.01" name="price" id="editModalFlightPrice" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Baggage Inclusions</label>
                            <input name="baggage_info" id="editModalFlightBaggage" class="form-control" placeholder="e.g. Baggage Included">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Flight Schedule / Frequency</label>
                            <input name="schedule" id="editModalFlightSchedule" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Description *</label>
                        <textarea name="description" id="editModalFlightDescription" class="form-control" rows="2" required></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Service Type *</label>
                            <input name="service" id="editModalFlightService" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Inclusions & Benefits *</label>
                            <input name="benefits" id="editModalFlightBenefits" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold"><i class="fa-solid fa-save me-1"></i> Save Flight Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- Full-Year Date Ribbon & Filtering JS Logic -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const storedDatePrices = <?php echo json_encode($date_prices_map); ?>;
    let currentDate = new Date(2026, 10, 23);

    const ribbonTrack = document.getElementById('dateRibbonTrack');
    const activeDateDisplay = document.getElementById('activeDateDisplay');
    const selectedDateLabel = document.getElementById('currentSelectedDateLabel');
    const jumpPicker = document.getElementById('jumpDatePicker');
    const toggleDateBtn = document.getElementById('btnToggleDatePricePopover');
    const popoverBox = document.getElementById('datePricePopover');
    const closePopoverBtn = document.getElementById('btnCloseDatePricePopover');
    const cancelPopoverBtn = document.getElementById('btnCancelDatePricePopover');
    const popoverDateInput = document.getElementById('popoverFlightDate');
    const popoverPriceInput = document.getElementById('popoverFlightPrice');

    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    function formatDateKey(dt) {
        const yyyy = dt.getFullYear();
        const mm = String(dt.getMonth() + 1).padStart(2, '0');
        const dd = String(dt.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function calculateDatePrice(dt) {
        const key = formatDateKey(dt);
        if (storedDatePrices[key]) {
            return storedDatePrices[key];
        }
        const base = 1821;
        const day = dt.getDay();
        if (day === 1) return 1829;
        if (day === 5) return 2157;
        if (day === 6) return 2444;
        if (day === 0) return 2587;
        return base;
    }

    function renderRibbon(startDate) {
        ribbonTrack.innerHTML = '';
        const baseDt = new Date(startDate);
        baseDt.setDate(baseDt.getDate() - 2);

        for (let i = 0; i < 7; i++) {
            const dt = new Date(baseDt);
            dt.setDate(dt.getDate() + i);

            const isSelected = formatDateKey(dt) === formatDateKey(startDate);
            const price = calculateDatePrice(dt);
            const dateStr = `${dayNames[dt.getDay()]} ${dt.getDate()} ${monthNames[dt.getMonth()]}`;

            const item = document.createElement('div');
            item.className = `date-ribbon-item text-center ${isSelected ? 'active' : ''}`;
            item.setAttribute('data-date', formatDateKey(dt));
            item.setAttribute('data-price', price);

            item.innerHTML = `
                <div class="small ${isSelected ? 'text-white' : 'text-muted'} small-date-sub">${dateStr}</div>
                <div class="small ${isSelected ? 'text-white fw-bold' : 'text-primary fw-bold'}">₱ ${Number(price).toLocaleString('en-US', {minimumFractionDigits: 0})}</div>
            `;

            item.addEventListener('click', function() {
                currentDate = new Date(dt);
                updateActiveDateUI();
            });

            ribbonTrack.appendChild(item);
        }
    }

    function updateActiveDateUI() {
        const key = formatDateKey(currentDate);
        const price = calculateDatePrice(currentDate);
        const fullDateStr = `${dayNames[currentDate.getDay()]} ${currentDate.getDate()} ${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;

        if (activeDateDisplay) activeDateDisplay.textContent = fullDateStr;
        if (selectedDateLabel) selectedDateLabel.textContent = fullDateStr;
        if (jumpPicker) jumpPicker.value = key;
        if (popoverDateInput) popoverDateInput.value = key;
        if (popoverPriceInput) popoverPriceInput.value = price;

        // Update Book Now buttons with selected date
        const trendingBtn = document.getElementById('bookTrendingFlightBtn');
        if (trendingBtn) {
            const trBase = trendingBtn.getAttribute('href').split('&date=')[0];
            trendingBtn.setAttribute('href', trBase + '&date=' + key);
        }
        document.querySelectorAll('.btn-book-flight-direct').forEach(btn => {
            const fId = btn.getAttribute('data-flight-id');
            btn.setAttribute('href', '<?php echo APP_URL; ?>/customer/book.php?flight_id=' + fId + '&date=' + key);
        });

        renderRibbon(currentDate);
        updateScheduleItemPrices(price);
    }

    function updateScheduleItemPrices(selectedFare) {
        const scheduleItems = document.querySelectorAll('.schedule-item');
        let lowestPrice = selectedFare;

        scheduleItems.forEach(item => {
            const basePrice = parseFloat(item.dataset.basePrice) || 1821;
            const ratio = selectedFare / 1829;
            const updatedFare = Math.round(basePrice * ratio);

            item.dataset.price = updatedFare;
            const priceDisplay = item.querySelector('.display-price-label');
            if (priceDisplay) {
                priceDisplay.textContent = `₱ ${updatedFare.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
            }
            if (updatedFare < lowestPrice) lowestPrice = updatedFare;
        });

        const recLabel = document.getElementById('recPriceLabel');
        const cheapLabel = document.getElementById('cheapPriceLabel');
        const fastLabel = document.getElementById('fastPriceLabel');
        if (recLabel) recLabel.textContent = `₱ ${lowestPrice.toLocaleString('en-US')}`;
        if (cheapLabel) cheapLabel.textContent = `₱ ${lowestPrice.toLocaleString('en-US')}`;
        if (fastLabel) fastLabel.textContent = `₱ ${Math.round(lowestPrice * 1.18).toLocaleString('en-US')}`;
    }

    document.getElementById('prevDateBtn').addEventListener('click', function() {
        currentDate.setDate(currentDate.getDate() - 1);
        updateActiveDateUI();
    });

    document.getElementById('nextDateBtn').addEventListener('click', function() {
        currentDate.setDate(currentDate.getDate() + 1);
        updateActiveDateUI();
    });

    if (jumpPicker) {
        jumpPicker.addEventListener('change', function() {
            if (this.value) {
                const parts = this.value.split('-');
                currentDate = new Date(parts[0], parts[1] - 1, parts[2]);
                updateActiveDateUI();
            }
        });
    }

    updateActiveDateUI();

    // Toggle and manage attached Edit Date Price Popover
    if (toggleDateBtn && popoverBox) {
        toggleDateBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = (popoverBox.style.display === 'block');
            popoverBox.style.display = isVisible ? 'none' : 'block';
            if (!isVisible) {
                const key = formatDateKey(currentDate);
                const price = calculateDatePrice(currentDate);
                if (popoverDateInput) popoverDateInput.value = key;
                if (popoverPriceInput) {
                    popoverPriceInput.value = price;
                    popoverPriceInput.focus();
                }
            }
        });

        if (closePopoverBtn) {
            closePopoverBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                popoverBox.style.display = 'none';
            });
        }

        if (cancelPopoverBtn) {
            cancelPopoverBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                popoverBox.style.display = 'none';
            });
        }

        // Close popover when clicking anywhere outside
        document.addEventListener('click', function(e) {
            if (popoverBox.style.display === 'block' && !popoverBox.contains(e.target) && e.target !== toggleDateBtn) {
                popoverBox.style.display = 'none';
            }
        });
    }

    // Trigger Edit Flight Modal for Admin/Staff
    document.querySelectorAll('.btn-trigger-edit-flight').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const d = this.dataset;
            document.getElementById('editModalFlightId').value = d.id || '';
            document.getElementById('editModalFlightName').value = d.name || '';
            document.getElementById('editModalFlightRoute').value = d.route || '';
            document.getElementById('editModalFlightOrigin').value = d.origin || '';
            document.getElementById('editModalFlightDestination').value = d.destination || '';
            document.getElementById('editModalFlightAirline').value = d.airline || '';
            if (document.getElementById('editModalFlightCabin')) {
                document.getElementById('editModalFlightCabin').value = d.cabin || 'Economy';
            }
            document.getElementById('editModalFlightDepTime').value = d.depTime || '';
            document.getElementById('editModalFlightArrTime').value = d.arrTime || '';
            document.getElementById('editModalFlightDuration').value = d.duration || '';
            document.getElementById('editModalFlightStops').value = d.stops || '';
            if (document.getElementById('editModalFlightBaggage')) {
                document.getElementById('editModalFlightBaggage').value = d.baggage || 'Baggage Included';
            }
            document.getElementById('editModalFlightPrice').value = d.price || '';
            document.getElementById('editModalFlightSchedule').value = d.schedule || '';
            document.getElementById('editModalFlightDescription').value = d.description || '';
            document.getElementById('editModalFlightService').value = d.service || '';
            document.getElementById('editModalFlightBenefits').value = d.benefits || '';

            const editModalEl = document.getElementById('quickEditFlightModal');
            if (editModalEl && window.bootstrap) {
                const modal = new bootstrap.Modal(editModalEl);
                modal.show();
            }
        });
    });

    // Filters and Sorting
    const checkboxes = document.querySelectorAll('.filter-checkbox');
    const priceSlider = document.getElementById('priceRangeSlider');
    const maxPriceInput = document.getElementById('maxPriceInput');
    const minPriceInput = document.getElementById('minPriceInput');
    const resetBtn = document.getElementById('resetFiltersBtn');
    const sortBtns = document.querySelectorAll('.sort-tab-btn');

    function filterSchedules() {
        const selectedAirlines = Array.from(document.querySelectorAll('#airCebuPac, #airPAL, #airCebgo, #airAsia'))
                                      .filter(cb => cb.checked).map(cb => cb.value.toLowerCase());
        
        const selectedStops = Array.from(document.querySelectorAll('#stopDirect, #stop1, #stop2'))
                                   .filter(cb => cb.checked).map(cb => cb.value.toLowerCase());

        const maxPrice = parseFloat(priceSlider.value) || 999999;
        const minPrice = parseFloat(minPriceInput.value) || 0;

        document.querySelectorAll('.schedule-item').forEach(item => {
            const airline = (item.dataset.airline || '').toLowerCase();
            const stops = (item.dataset.stops || '').toLowerCase();
            const price = parseFloat(item.dataset.price) || 0;

            const matchAirline = selectedAirlines.length === 0 || selectedAirlines.some(a => airline.includes(a));
            const matchStops = selectedStops.length === 0 || selectedStops.some(s => stops.includes(s));
            const matchPrice = price >= minPrice && price <= maxPrice;

            if (matchAirline && matchStops && matchPrice) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', filterSchedules));
    
    if (priceSlider && maxPriceInput) {
        priceSlider.addEventListener('input', function() {
            maxPriceInput.value = this.value;
            filterSchedules();
        });
        maxPriceInput.addEventListener('input', function() {
            priceSlider.value = this.value;
            filterSchedules();
        });
        minPriceInput.addEventListener('input', filterSchedules);
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = true);
            if (priceSlider) priceSlider.value = 15000;
            if (maxPriceInput) maxPriceInput.value = 15000;
            if (minPriceInput) minPriceInput.value = 1000;
            filterSchedules();
        });
    }

    sortBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            sortBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const sortType = this.dataset.sort;
            const container = document.getElementById('flightScheduleContainer');
            const items = Array.from(document.querySelectorAll('.schedule-item'));

            if (sortType === 'cheapest') {
                items.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
            } else if (sortType === 'fastest') {
                items.sort((a, b) => (a.dataset.stops.includes('Direct') ? -1 : 1));
            } else {
                items.sort((a, b) => 0.5 - Math.random());
            }

            items.forEach(item => container.appendChild(item));
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
