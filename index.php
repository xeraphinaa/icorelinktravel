<?php
$current_page = 'home';
$page_title = 'Welcome to Icorelink Travel & Tours';
require_once 'includes/header.php';

$contact_success = '';
$contact_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact') {
    verify_csrf();
    
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $subject = clean_input($_POST['subject']);
    $message = clean_input($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $contact_error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contact_error = "Invalid email format.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_name, sender_email, sender_phone, subject, message, status) VALUES (?, ?, ?, ?, ?, 'unread')");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $new_msg_id = $pdo->lastInsertId();

            // notify staff and admins
            try {
                $staff_stmt = $pdo->query("SELECT id FROM users WHERE role_id IN (1, 2) AND status = 'active'");
                $staff_user_ids = $staff_stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($staff_user_ids as $staff_id) {
                    create_notification($pdo, $staff_id, "New website inquiry from " . htmlspecialchars($name) . " regarding: " . htmlspecialchars($subject));
                }
            } catch (Exception $notif_err) {
            }

            log_activity($pdo, null, 'Website Inquiry', "Guest inquiry received from $name ($email) - Subject: $subject");

            $contact_success = "Thank you! Your message has been sent directly to our staff and administrators. We will get back to you soon.";
        } catch (PDOException $e) {
            $contact_error = "Failed to send message: " . $e->getMessage();
        }
    }
}

$search_dest = isset($_GET['destination']) ? trim($_GET['destination']) : '';
$search_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$search_duration = isset($_GET['duration']) ? trim($_GET['duration']) : '';

$query_parts = [];
$params = [];

$query_str = "SELECT * FROM travel_packages WHERE status IN ('active', 'featured')";

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
    error_log("Failed to fetch packages: " . $e->getMessage());
    $packages = [];
}

$reviews = [];
try {
    $stmt = $pdo->query("
        SELECT r.*, c.first_name, c.last_name, tp.name as package_name 
        FROM reviews r 
        JOIN customers c ON r.customer_id = c.id 
        JOIN travel_packages tp ON r.package_id = tp.id 
        WHERE r.is_approved = 1 
        ORDER BY r.id DESC LIMIT 6
    ");
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Failed to fetch testimonials: " . $e->getMessage());
}

// sample reviews if database is empty
if (empty($reviews)) {
    $reviews = [
        [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'package_name' => 'El Nido Palawan Escape',
            'rating' => 5,
            'comment' => 'Our El Nido tour was breathtaking! The booking process was extremely smooth, and the customer portal was very easy to use to upload our payment receipts.'
        ],
        [
            'first_name' => 'Sarah',
            'last_name' => 'Conor',
            'package_name' => 'Breathtaking Batanes Tour',
            'rating' => 5,
            'comment' => 'Batanes was an absolute dream. Icorelink Staff handled everything from transport to accommodations perfectly. The custom quotation they generated helped us stay within budget!'
        ]
    ];
}

$home_settings = get_system_settings($pdo);
$hero_title = $home_settings['home_hero_title'] ?? 'Discover the Philippines, Your Next Great Escape';
$hero_desc = $home_settings['home_hero_desc'] ?? 'From hidden beaches and emerald mountains to vibrant cities and centuries-old traditions, the Philippines is a destination made for discovery. Journey across its 7,641 islands, meet the warmth of the Filipino people, taste unforgettable local flavors, and create memories in places where every sunrise tells a different story. Your adventure begins here. Experience unforgettable journeys with Icorelink Travel & Tours.';

$badge_color_classes = [
    'warning' => 'bg-warning text-dark',
    'info' => 'bg-info text-dark',
    'primary' => 'bg-primary text-white',
    'success' => 'bg-success text-white',
    'danger' => 'bg-danger text-white',
    'dark' => 'bg-dark text-white',
    'secondary' => 'bg-secondary text-white'
];
?>

<!-- Unified Hero Section -->
<section class="hero-section text-center text-white py-5 position-relative">
    <div class="container position-relative z-index-2">
        <!-- Main Title & Description -->
        <h1 class="hero-title text-white fw-bold mb-0" style="font-size: clamp(2.2rem, 4vw, 3.2rem); letter-spacing: -0.5px; text-shadow: 0 2px 10px rgba(7, 25, 47, 0.35);">
            <?php echo htmlspecialchars($hero_title); ?>
        </h1>
        <div class="ph-fun-divider"></div>
        <p class="ph-fun-subtitle mx-auto mb-5 text-white opacity-95" style="max-width: 900px; font-size: 1.15rem; line-height: 1.8; text-shadow: 0 1px 6px rgba(7, 25, 47, 0.25);">
            <?php echo nl2br(htmlspecialchars($hero_desc)); ?>
        </p>

        <!-- 4 Feature Cards Grid -->
        <div class="row g-4 text-start">
            <?php for ($i = 1; $i <= 4; $i++): 
                $c_badge = $home_settings["home_card_{$i}_badge"] ?? 'CATEGORY';
                $c_badge_col = $home_settings["home_card_{$i}_badge_color"] ?? 'warning';
                $c_badge_class = $badge_color_classes[$c_badge_col] ?? 'bg-warning text-dark';
                $c_title = $home_settings["home_card_{$i}_title"] ?? "Service Card {$i}";
                $c_desc = $home_settings["home_card_{$i}_desc"] ?? '';
                $c_btn = $home_settings["home_card_{$i}_btn_text"] ?? 'Explore More';
                $c_link = $home_settings["home_card_{$i}_link"] ?? '#';
                $c_media_type = $home_settings["home_card_{$i}_media_type"] ?? 'image';
                $c_media_url = $home_settings["home_card_{$i}_media_url"] ?? '';

                $full_link = (preg_match('#^(https?:)?//#i', $c_link)) ? $c_link : APP_URL . '/' . ltrim($c_link, '/');
                $full_media = (preg_match('#^(https?:)?//#i', $c_media_url)) ? $c_media_url : APP_URL . '/' . ltrim($c_media_url, '/');

                $is_video = ($c_media_type === 'video') || preg_match('/\.(mp4|webm|ogv|mov)$/i', $c_media_url);
                $is_gif = ($c_media_type === 'gif') || preg_match('/\.gif$/i', $c_media_url);
            ?>
                <div class="col-lg-3 col-md-6">
                    <a href="<?php echo htmlspecialchars($full_link); ?>" class="ph-card-link text-decoration-none d-block">
                        <div class="ph-card position-relative overflow-hidden rounded-4 shadow-lg" <?php if (!$is_video && !$is_gif && !empty($c_media_url)): ?>style="background-image: url('<?php echo htmlspecialchars($full_media); ?>');"<?php endif; ?>>
                            
                            <?php if ($is_video && !empty($c_media_url)): ?>
                                <video class="ph-card-bg-video" autoplay muted loop playsinline poster="<?php echo APP_URL; ?>/assets/images/philippines_hero_bg.png">
                                    <source src="<?php echo htmlspecialchars($full_media); ?>" type="video/mp4">
                                </video>
                            <?php elseif ($is_gif && !empty($c_media_url)): ?>
                                <img class="ph-card-bg-media" src="<?php echo htmlspecialchars($full_media); ?>" alt="<?php echo htmlspecialchars($c_title); ?>" loading="lazy">
                            <?php endif; ?>

                            <div class="ph-card-overlay"></div>
                            <div class="ph-card-content p-4 d-flex flex-column justify-content-between text-white">
                                <!-- UPPER Section: Large Category Badge (DESTINATIONS, PACKAGES, FLIGHTS, HOTELS) -->
                                <div class="ph-card-top-content">
                                    <span class="badge <?php echo $c_badge_class; ?> ph-card-badge-large"><?php echo htmlspecialchars($c_badge); ?></span>
                                </div>

                                <!-- BELOW Section: Title, Description & Action Button -->
                                <div class="ph-card-bottom-content mt-auto">
                                    <h3 class="ph-card-title fw-bold mb-2"><?php echo htmlspecialchars($c_title); ?></h3>
                                    <p class="ph-card-desc small mb-3"><?php echo htmlspecialchars($c_desc); ?></p>
                                    <div class="ph-card-arrow">
                                        <span><?php echo htmlspecialchars($c_btn); ?></span> <i class="fa-solid fa-arrow-right-long ms-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us & Featured Philippines Video -->
<section class="py-5 bg-white home-testimonials-section" id="about">
    <div id="why-icorelink-section"></div>
    <div class="container">
        <div class="row g-4 g-lg-5 align-items-center">
            <!-- Left Column: Title + Subtitle + 2x2 Feature Grid -->
            <div class="col-lg-6">
                <div class="mb-4">
                    <h2 class="font-serif display-6 fw-bold text-dark-blue mb-2">Why Travel with Icorelink?</h2>
                    <p class="text-muted fs-6">We provide full security, premium travel support, and unforgettable memories.</p>
                </div>
                
                <div class="row g-4 mt-1">
                    <!-- Feature 1: Safe & Secured -->
                    <div class="col-sm-6">
                        <div class="d-flex flex-column align-items-start text-start">
                            <div class="icon-box mb-3">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <h5 class="fw-bold text-dark-blue mb-1">Safe & Secured</h5>
                            <p class="text-muted small mb-0" style="line-height: 1.5;">All packages are handled by professional licensed local travel guides.</p>
                        </div>
                    </div>
                    
                    <!-- Feature 2: Affordable Rates -->
                    <div class="col-sm-6">
                        <div class="d-flex flex-column align-items-start text-start">
                            <div class="icon-box mb-3">
                                <i class="fa-solid fa-wallet"></i>
                            </div>
                            <h5 class="fw-bold text-dark-blue mb-1">Affordable Rates</h5>
                            <p class="text-muted small mb-0" style="line-height: 1.5;">We offer flexible payment terms with GCash, Maya, PayPal, and Bank Transfers.</p>
                        </div>
                    </div>

                    <!-- Feature 3: Easy Reservation -->
                    <div class="col-sm-6">
                        <div class="d-flex flex-column align-items-start text-start">
                            <div class="icon-box mb-3">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <h5 class="fw-bold text-dark-blue mb-1">Easy Reservation</h5>
                            <p class="text-muted small mb-0" style="line-height: 1.5;">Pay the reservation fee of PHP 2,000 per head to lock in your slots instantly.</p>
                        </div>
                    </div>

                    <!-- Feature 4: Unmatched Support -->
                    <div class="col-sm-6">
                        <div class="d-flex flex-column align-items-start text-start">
                            <div class="icon-box mb-3">
                                <i class="fa-solid fa-heart-circle-check"></i>
                            </div>
                            <h5 class="fw-bold text-dark-blue mb-1">Unmatched Support</h5>
                            <p class="text-muted small mb-0" style="line-height: 1.5;">Our dedicated customer support agents are ready to assist you anytime.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Video Container -->
            <div class="col-lg-6">
                <?php if (!empty($settings['ph_video_enabled']) && $settings['ph_video_enabled'] == '1'): ?>
                    <?php 
                    $video_url = $settings['ph_video_url'] ?? 'https://www.youtube.com/embed/35npVaFGHMY';
                    $video_type = $settings['ph_video_type'] ?? 'youtube';
                    $video_title = $settings['ph_video_title'] ?? 'Beauty of the Philippines';
                    $video_desc = $settings['ph_video_description'] ?? 'Explore the islands, culture, and natural wonders of the Philippines.';
                    $poster_raw = $settings['ph_video_poster'] ?? '';
                    $poster_url = !empty($poster_raw) ? (strpos($poster_raw, 'http') === 0 ? $poster_raw : APP_URL . '/' . $poster_raw) : APP_URL . '/assets/images/philippines_hero_bg.png';
                    ?>
                    <div class="ph-video-card rounded-4 shadow-lg overflow-hidden position-relative border border-secondary border-opacity-10 bg-dark text-white" id="mainHomeVideoCard">
                        <!-- Top Bar -->
                        <div class="ph-video-card-header d-flex align-items-center justify-content-between px-3 py-2 bg-dark bg-gradient text-white border-bottom border-secondary border-opacity-25">
                            <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                                <span class="badge bg-danger rounded-pill px-2 py-1 small fw-bold">
                                    <i class="fa-solid fa-circle-play me-1"></i> HD
                                </span>
                                <span class="fw-semibold small text-truncate text-white-50" style="max-width: 280px;" id="phMainTitleText">
                                    <?php echo htmlspecialchars($video_title); ?>
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                <button type="button" class="btn btn-sm btn-dark text-white-50 p-1 hover-white" id="phMainMinimizeBtn" title="Minimize to Floating Player">
                                    <i class="fa-solid fa-window-minimize"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-dark text-white-50 p-1 hover-white" id="phMainMaximizeBtn" title="Maximize Video">
                                    <i class="fa-solid fa-expand"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Main Video Viewport -->
                        <div class="ph-video-viewport position-relative ratio ratio-16x9 bg-black" id="phMainVideoViewport">
                            <?php if ($video_type === 'mp4' || preg_match('/\.(mp4|webm|ogv)$/i', $video_url)): ?>
                                <?php $full_mp4 = (strpos($video_url, 'http') === 0) ? $video_url : APP_URL . '/' . $video_url; ?>
                                <video id="phMainNativeVideo" class="w-100 h-100 object-fit-cover" poster="<?php echo htmlspecialchars($poster_url); ?>" playsinline preload="auto" loop style="cursor: pointer;">
                                    <source src="<?php echo htmlspecialchars($full_mp4); ?>" type="video/mp4">
                                </video>
                            <?php else: ?>
                                <?php 
                                $embed_src = $video_url;
                                if (strpos($embed_src, 'youtube.com/watch?v=') !== false) {
                                    $v_id = explode('v=', $embed_src)[1] ?? '';
                                    $v_id = explode('&', $v_id)[0] ?? '';
                                    $embed_src = 'https://www.youtube.com/embed/' . $v_id;
                                } elseif (strpos($embed_src, 'youtu.be/') !== false) {
                                    $v_id = explode('youtu.be/', $embed_src)[1] ?? '';
                                    $v_id = explode('?', $v_id)[0] ?? '';
                                    $embed_src = 'https://www.youtube.com/embed/' . $v_id;
                                }
                                $yt_params = [
                                    'enablejsapi' => 1,
                                    'rel' => 0,
                                    'modestbranding' => 1,
                                    'controls' => 1
                                ];
                                $embed_src .= (strpos($embed_src, '?') !== false ? '&' : '?') . http_build_query($yt_params);
                                ?>
                                <iframe id="phMainYoutubeIframe" src="<?php echo htmlspecialchars($embed_src); ?>" title="Beauty of the Philippines" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="w-100 h-100 border-0"></iframe>
                            <?php endif; ?>
                        </div>

                        <!-- Video Progress Bar (Clickable / Scrubbable) -->
                        <div class="ph-progress-container px-3 pt-2 bg-dark">
                            <div class="ph-progress-track position-relative" id="phMainProgressTrack" title="Seek video" style="height: 6px; background: rgba(255,255,255,0.2); border-radius: 4px; cursor: pointer; transition: height 0.15s ease;">
                                <div class="ph-progress-fill position-absolute top-0 start-0 h-100 rounded-pill bg-warning" id="phMainProgressFill" style="width: 0%; pointer-events: none;"></div>
                            </div>
                        </div>

                        <!-- Video Control Toolbar -->
                        <div class="ph-video-toolbar d-flex align-items-center justify-content-between px-3 py-2 bg-dark text-white border-top border-secondary border-opacity-25 gap-2 flex-wrap">
                            <div class="d-flex align-items-center gap-1 gap-sm-2">
                                <button type="button" class="btn btn-primary btn-sm px-3 rounded-pill fw-bold" id="phMainPlayPauseBtn">
                                    <i class="fa-solid fa-play me-1" id="phMainPlayIcon"></i> <span id="phMainPlayLabel">Play</span>
                                </button>
                                <button type="button" class="btn btn-outline-light btn-sm rounded-circle p-1 d-flex align-items-center justify-content-center" id="phMainRewindBtn" title="Rewind 10 Seconds (Playback)" style="width:32px; height:32px;">
                                    <i class="fa-solid fa-rotate-left small"></i>
                                </button>
                                <button type="button" class="btn btn-outline-light btn-sm rounded-circle p-1 d-flex align-items-center justify-content-center" id="phMainForwardBtn" title="Forward 10 Seconds" style="width:32px; height:32px;">
                                    <i class="fa-solid fa-rotate-right small"></i>
                                </button>
                                <button type="button" class="btn btn-outline-light btn-sm rounded-circle p-1 d-flex align-items-center justify-content-center" id="phMainStopBtn" title="Stop Video" style="width:32px; height:32px;">
                                    <i class="fa-solid fa-stop small"></i>
                                </button>
                            </div>

                            <!-- Duration of Time Display -->
                            <div class="d-flex align-items-center">
                                <span class="badge bg-black bg-opacity-75 text-light font-monospace small px-2 py-1 border border-secondary border-opacity-50" id="phMainTimeDisplay" style="font-size: 0.8rem; letter-spacing: 0.5px;" title="Current Time / Total Duration">
                                    0:00 / 0:00
                                </span>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-link text-white p-0 text-decoration-none" id="phMainMuteBtn" title="Mute/Unmute Sound">
                                    <i class="fa-solid fa-volume-xmark fs-5 text-white-50" id="phMainVolIcon"></i>
                                </button>
                                <input type="range" class="form-range mx-1" id="phMainVolSlider" min="0" max="1" step="0.05" value="0.8" style="max-width: 80px;">
                                <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 py-1 fw-semibold small" id="phMainFloatTriggerBtn">
                                    <i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i> Float Player
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Verified Traveler Reviews & Testimonials -->
<section id="reviews" class="py-5" style="background-color: #f3f8fd;">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-5">
            <div>
                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill small fw-bold mb-2 shadow-sm">
                    <i class="fa-solid fa-star me-1 text-dark"></i> TRAVELER TESTIMONIALS
                </span>
                <h2 class="font-serif text-dark-blue mb-1">Loved by Explorers & Adventurers</h2>
                <p class="text-muted mb-0">Real memories, genuine feedback, and verified reviews from travelers across the Philippines.</p>
            </div>
            <div>
                <a href="<?php echo APP_URL; ?>/packages.php" class="btn btn-primary-custom px-4 py-2">
                    Browse All Packages <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($reviews as $rev): ?>
                <div class="col-lg-6">
                    <div class="card border-0 rounded-4 shadow-sm p-4 h-100 bg-white d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm" style="width: 48px; height: 48px;">
                                        <?php 
                                        $init = strtoupper(substr($rev['first_name'] ?? 'G', 0, 1) . substr($rev['last_name'] ?? 'U', 0, 1));
                                        echo htmlspecialchars($init);
                                        ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark-blue mb-0"><?php echo htmlspecialchars(($rev['first_name'] ?? '') . ' ' . ($rev['last_name'] ?? '')); ?></h6>
                                        <span class="badge bg-success bg-opacity-10 text-success small fw-normal px-2 py-0">
                                            <i class="fa-solid fa-circle-check me-1"></i> Verified Traveler
                                        </span>
                                    </div>
                                </div>
                                <div class="text-warning small text-nowrap">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa-<?php echo $i <= ($rev['rating'] ?? 5) ? 'solid' : 'regular'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                    <span class="small fw-bold text-dark ms-1"><?php echo $rev['rating'] ?? 5; ?>.0</span>
                                </div>
                            </div>

                            <span class="badge bg-light text-dark-blue border mb-2 small fw-semibold">
                                <i class="fa-solid fa-map-pin text-primary me-1"></i> <?php echo htmlspecialchars($rev['package_name'] ?? 'Philippine Tour'); ?>
                            </span>

                            <p class="text-muted mb-3" style="line-height: 1.6; font-size: 0.98rem;">
                                "<?php echo nl2br(htmlspecialchars($rev['comment'] ?? '')); ?>"
                            </p>

                            <?php if (!empty($rev['photo_path'])): ?>
                                <div class="mb-3">
                                    <div class="small text-muted fw-semibold mb-1"><i class="fa-solid fa-camera text-primary me-1"></i> Traveler Trip Photo:</div>
                                    <img src="<?php echo UPLOAD_URL . '/reviews/' . htmlspecialchars($rev['photo_path']); ?>" 
                                         alt="Traveler Photo" 
                                         class="rounded-3 shadow-sm border" 
                                         style="height: 120px; width: 170px; object-fit: cover; cursor: pointer; transition: transform 0.2s;"
                                         onmouseover="this.style.transform='scale(1.03)'"
                                         onmouseout="this.style.transform='scale(1)'"
                                         data-bs-toggle="modal"
                                         data-bs-target="#homePhotoModal"
                                         data-photo-src="<?php echo UPLOAD_URL . '/reviews/' . htmlspecialchars($rev['photo_path']); ?>"
                                         data-customer-name="<?php echo htmlspecialchars(($rev['first_name'] ?? '') . ' ' . ($rev['last_name'] ?? '')); ?>">
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($rev['staff_reply'])): ?>
                            <div class="staff-reply-card mt-3 p-3 rounded-3 bg-light border-start border-3 border-primary">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-primary px-2 py-0 small"><i class="fa-solid fa-shield-halved me-1"></i> Icorelink Team</span>
                                    <small class="text-muted">Official Response</small>
                                </div>
                                <p class="mb-0 text-dark-blue small" style="line-height: 1.5; font-size: 0.88rem;">
                                    <?php echo htmlspecialchars($rev['staff_reply']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Lightbox Modal for Full-Size Review Photos -->
<div class="modal fade" id="homePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 text-white pb-0">
                <h5 class="modal-title" id="homePhotoModalTitle">Traveler Photo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img src="" id="homePhotoModalImg" class="img-fluid rounded-4 shadow-lg" style="max-height: 80vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const homePhotoModal = document.getElementById('homePhotoModal');
    if (homePhotoModal) {
        homePhotoModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (trigger) {
                const src = trigger.getAttribute('data-photo-src');
                const customer = trigger.getAttribute('data-customer-name') || 'Traveler';
                const modalImg = document.getElementById('homePhotoModalImg');
                const modalTitle = document.getElementById('homePhotoModalTitle');
                if (modalImg) modalImg.src = src;
                if (modalTitle) modalTitle.textContent = customer + ' - Traveler Photo';
            }
        });
    }
});
</script>

<!-- Booking Information & Policies -->
<section id="guidelines" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="font-serif">Booking Guidelines & Agency Policies</h2>
            <p class="text-muted">Important details to know before confirming your travel packages</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="policy-card">
                    <div class="icon-box"><i class="fa-solid fa-circle-info"></i></div>
                    <h5>Booking Guidelines</h5>
                    <div class="small text-muted" style="white-space: pre-line;">
                        <?php echo htmlspecialchars($settings['booking_guidelines'] ?? 'Refer to terms'); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="policy-card">
                    <div class="icon-box"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <h5>Cancellation Policy</h5>
                    <div class="small text-muted" style="white-space: pre-line;">
                        <?php echo htmlspecialchars($settings['cancellation_policy'] ?? 'Refer to terms'); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="policy-card">
                    <div class="icon-box"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                    <h5>Refund Policy</h5>
                    <div class="small text-muted" style="white-space: pre-line;">
                        <?php echo htmlspecialchars($settings['refund_policy'] ?? 'Refer to terms'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- Travel Promotions, Guidelines & Rules Hub -->
<section id="guidelines" class="py-5" style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
    <div id="announcements"></div>
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill text-uppercase mb-2">Traveler Advisory Hub</span>
            <h2 class="display-6 fw-bold text-dark-blue">Travel Promotions, Guidelines & Rules</h2>
            <p class="text-muted mx-auto" style="max-width: 650px;">
                Stay updated with the latest Philippine travel advisories, seasonal promos, valid identification guidelines, and marine sanctuary protection rules.
            </p>
        </div>

        <div class="row g-4">
            <!-- Promotions Card -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header border-0 py-3 px-4 text-white" style="background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold"><i class="fa-solid fa-fire me-2"></i> Seasonal Promos</span>
                            <span class="badge bg-white text-dark small">Discounts</span>
                        </div>
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold text-dark-blue mb-2">Summer Island Flash Sale</h5>
                            <p class="text-muted small mb-3">Book any Palawan or Cebu 3D2N package this month and get a complimentary sunset cruise voucher plus free roundtrip airport transfers!</p>
                            <ul class="list-unstyled small text-muted mb-4">
                                <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> Early Bird Batanes free cultural dinner</li>
                                <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> Up to 20% discount on group bookings</li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-outline-warning w-100 btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#announcementModal">
                            <i class="fa-solid fa-eye me-1"></i> View Promo Details
                        </button>
                    </div>
                </div>
            </div>

            <!-- Guidelines Card -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header border-0 py-3 px-4 text-white" style="background: linear-gradient(135deg, #00a2e8 0%, #0077b6 100%);">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold"><i class="fa-solid fa-clipboard-check me-2"></i> Travel Guidelines</span>
                            <span class="badge bg-white text-dark small">Check-in</span>
                        </div>
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold text-dark-blue mb-2">Valid Domestic ID Requirements</h5>
                            <p class="text-muted small mb-3">All airline and seaport passengers must present at least one valid government-issued photo ID matching the exact ticket name.</p>
                            <ul class="list-unstyled small text-muted mb-4">
                                <li class="mb-2"><i class="fa-solid fa-id-card text-primary me-2"></i> Passport, PhilSys, Driver's License, PRC</li>
                                <li class="mb-2"><i class="fa-solid fa-clock text-primary me-2"></i> NAIA Airport arrival: 2.5 hours prior</li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-outline-primary w-100 btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#announcementModal">
                            <i class="fa-solid fa-eye me-1"></i> View ID Guidelines
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rules Card -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header border-0 py-3 px-4 text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold"><i class="fa-solid fa-shield-halved me-2"></i> Island & Maritime Rules</span>
                            <span class="badge bg-white text-dark small">Protected</span>
                        </div>
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold text-dark-blue mb-2">Zero-Plastic & Sanctuary Rules</h5>
                            <p class="text-muted small mb-3">Protected marine sanctuaries in El Nido, Coron, and Apo Reef strictly prohibit single-use plastics and touching live reefs or wildlife.</p>
                            <ul class="list-unstyled small text-muted mb-4">
                                <li class="mb-2"><i class="fa-solid fa-leaf text-success me-2"></i> Leave No Trace eco-compliance</li>
                                <li class="mb-2"><i class="fa-solid fa-life-ring text-success me-2"></i> Philippine Coast Guard weather clearance</li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-outline-success w-100 btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#announcementModal">
                            <i class="fa-solid fa-eye me-1"></i> View Island Rules
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form & Google Map -->
<section id="contact" class="py-5 bg-light">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <!-- Contact details -->
            <div class="col-lg-5">
                <div class="bg-white text-dark p-5 rounded-4 h-100 d-flex flex-column justify-content-between shadow-sm">
                    <div>
                        <h3 class="text-dark mb-3">Get in Touch</h3>
                        <p class="text-dark mb-4">Have questions about a package, itineraries, or reservation policies? Send us a message and our staff will reply within 24 hours.</p>
                        
                        <div class="mb-4 d-flex align-items-start gap-3">
                            <i class="fa-solid fa-phone text-primary fs-4 mt-1"></i>
                            <div>
                                <h6 class="text-dark mb-1">Call Us</h6>
                                <p class="text-dark small mb-0"><?php echo htmlspecialchars($settings['contact_number'] ?? 'Contact Info'); ?></p>
                            </div>
                        </div>

                        <div class="mb-4 d-flex align-items-start gap-3">
                            <i class="fa-solid fa-envelope text-primary fs-4 mt-1"></i>
                            <div>
                                <h6 class="text-dark mb-1">Email Us</h6>
                                <p class="text-dark small mb-0"><?php echo htmlspecialchars($settings['email'] ?? 'info@icorelinktravel.com'); ?></p>
                            </div>
                        </div>

                        <div class="mb-4 d-flex align-items-start gap-3">
                            <i class="fa-solid fa-globe text-primary fs-4 mt-1"></i>
                            <div>
                                <h6 class="text-dark mb-1">Website</h6>
                                <p class="text-dark small mb-0">
                                    <a href="https://www.facebook.com/share/1CJkRRYA86/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-primary">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-primary small me-1"></i> icorelink.com
                                    </a>
                                </p>
                            </div>
                        </div>

                        <div class="mb-4 d-flex align-items-start gap-3">
                            <i class="fa-solid fa-clock text-primary fs-4 mt-1"></i>
                            <div>
                                <h6 class="text-dark mb-1">Office Hours</h6>
                                <p class="text-dark small mb-0"><?php echo htmlspecialchars($settings['office_hours'] ?? 'Office Hours'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Embed a dynamic Google Map iframe centered around PH tourism -->
                    <div class="rounded overflow-hidden" style="height: 180px;">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.3414902092147!2d120.9818817!3d14.5796229!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397ca264d0a92d7%3A0x63390c58e0a30b8e!2sRizal%20Park!5e0!3m2!1sen!2sph!4v1680000000000!5m2!1sen!2sph" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Icorelink Google Map">
                        </iframe>
                    </div>
                </div>
            </div>

            <!-- Message Form -->
            <div class="col-lg-7">
                <div class="bg-white p-5 rounded-4 h-100 shadow-sm">
                    <h3 class="mb-4">Send a Message</h3>
                    
                    <?php if (!empty($contact_success)): ?>
                        <div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i> <?php echo $contact_success; ?></div>
                    <?php endif; ?>

                    <?php if (!empty($contact_error)): ?>
                        <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo $contact_error; ?></div>
                    <?php endif; ?>

                    <form action="<?php echo APP_URL; ?>/#contact" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="action" value="contact">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="Enter full name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required placeholder="Enter email address">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="Enter contact number">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control" required placeholder="What is this inquiry about?">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Your Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="4" required placeholder="Type your inquiry details..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary-custom px-4 py-2">
                            <i class="fa-solid fa-paper-plane me-2"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
