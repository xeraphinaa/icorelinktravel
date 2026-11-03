<?php
$current_page = 'about';
$page_title = 'About Us | Icorelink Travel & Tours';
require_once 'includes/header.php';
$settings = get_system_settings($pdo);
?>

<!-- Hero Header Section -->
<div class="about-hero py-5 text-white position-relative" style="background: linear-gradient(135deg, rgba(11, 34, 64, 0.95) 0%, rgba(0, 162, 232, 0.9) 100%), url('<?php echo APP_URL; ?>/assets/images/philippines_hero_bg.png') center/cover no-repeat;">
    <div class="container py-5 text-center position-relative" style="z-index: 2;">
        <span class="badge bg-white text-primary fw-bold text-uppercase px-3 py-2 mb-3 shadow-sm" style="letter-spacing: 0.12em;">
            <i class="fa-solid fa-award me-1"></i> DOT Accredited Agency • Since 2018
        </span>
        <h1 class="display-4 fw-bold font-serif mb-3 text-white">About Icorelink Travel & Tours</h1>
        <p class="lead text-white-50 mx-auto mb-4" style="max-width: 760px; line-height: 1.7;">
            We believe travel is not just about visiting places &mdash; it is about creating lifelong memories, savoring the moments, and discovering the soul of the Philippine islands with absolute comfort and safety.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?php echo APP_URL; ?>/packages.php" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-pill shadow-sm">
                <i class="fa-solid fa-compass me-2"></i> Explore Our Tours
            </a>
            <a href="<?php echo APP_URL; ?>/contact.php" class="btn btn-outline-light px-4 py-2 rounded-pill">
                <i class="fa-solid fa-envelope me-2"></i> Contact Our Team
            </a>
        </div>
    </div>
</div>

<!-- Main About Content Section -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <!-- Story / Brand Overview -->
        <div class="row g-5 align-items-center mb-5">
            <div class="col-lg-6">
                <div class="pe-lg-3">
                    <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill text-uppercase mb-2">Our Story</span>
                    <h2 class="display-6 fw-bold text-dark-blue mb-4 font-serif">Connecting Travelers to the Beauty of the Philippines</h2>
                    <p class="text-muted fs-6 mb-3" style="line-height: 1.8;">
                        Established with a passionate dedication to Philippine tourism, <strong>Icorelink Travel & Tours</strong> has grown into a premier full-service travel partner. Headquartered in Ermita, Manila, we connect domestic and international explorers to the nation’s most stunning archipelagos &mdash; from the white sands of Boracay to the majestic limestone lagoons of El Nido, Coron, and the rolling hills of Batanes.
                    </p>
                    <p class="text-muted fs-6 mb-4" style="line-height: 1.8;">
                        Our motto, <em class="text-primary fw-bold">"Savor the Moments"</em>, drives everything we do. We remove the stress of flight schedules, transfers, room bookings, and permits so you can immerse yourself completely in the thrill of discovery and the warmth of Filipino hospitality.
                    </p>
                    <div class="row g-3 pt-2">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-light border">
                                <div class="icon-box text-primary fs-3">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark-blue">100% Licensed</h6>
                                    <small class="text-muted">DOT & LGU Registered</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-light border">
                                <div class="icon-box text-warning fs-3">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark-blue">15,000+ Happy Guests</h6>
                                    <small class="text-muted">Over 98% 5-Star Reviews</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="position-relative">
                    <div class="row g-3">
                        <div class="col-6">
                            <img src="https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=700&q=80" alt="Palawan Lagoon" class="img-fluid rounded-4 shadow-sm w-100 object-fit-cover" style="height: 280px;">
                        </div>
                        <div class="col-6">
                            <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=700&q=80" alt="Tropical Beach" class="img-fluid rounded-4 shadow-sm w-100 object-fit-cover mt-4" style="height: 280px;">
                        </div>
                        <div class="col-12">
                            <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=900&q=80" alt="Airplane Above Clouds" class="img-fluid rounded-4 shadow-sm w-100 object-fit-cover" style="height: 180px;">
                        </div>
                    </div>
                    <div class="position-absolute top-50 start-50 translate-middle bg-white p-3 rounded-4 shadow-lg text-center border" style="min-width: 180px;">
                        <i class="fa-solid fa-plane-departure text-primary fs-3 mb-1"></i>
                        <h5 class="fw-bold text-dark-blue mb-0">Seamless Flights</h5>
                        <small class="text-muted">Direct Airline Ticketing</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mission, Vision & Core Values -->
        <div class="row g-4 my-5 py-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center hover-lift" style="background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); border-top: 4px solid #00a2e8 !important;">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 68px; height: 68px;">
                        <i class="fa-solid fa-bullseye fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark-blue mb-3 font-serif">Our Mission</h4>
                    <p class="text-muted small mb-0" style="line-height: 1.7;">
                        To deliver unparalleled, worry-free travel experiences by providing transparent pricing, licensed safety standards, handpicked hotel accommodations, and reliable flight connections tailored for every Filipino family and global traveler.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center hover-lift" style="background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); border-top: 4px solid #ff9800 !important;">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 text-warning" style="width: 68px; height: 68px;">
                        <i class="fa-solid fa-eye fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark-blue mb-3 font-serif">Our Vision</h4>
                    <p class="text-muted small mb-0" style="line-height: 1.7;">
                        To be the most trusted and customer-acclaimed travel and tours agency in the Philippines, celebrated for ethical tourism, eco-friendly island conservation, and digital excellence in travel coordination.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center hover-lift" style="background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); border-top: 4px solid #10b981 !important;">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success" style="width: 68px; height: 68px;">
                        <i class="fa-solid fa-hand-holding-heart fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark-blue mb-3 font-serif">Core Values</h4>
                    <p class="text-muted small mb-0" style="line-height: 1.7;">
                        <strong>Integrity & Transparency:</strong> No hidden booking fees.<br>
                        <strong>Malasakit:</strong> Genuine care for travelers' comfort and welfare.<br>
                        <strong>Excellence:</strong> Reliable, round-the-clock concierge support before and during trips.
                    </p>
                </div>
            </div>
        </div>

        <!-- What Sets Us Apart (Why Choose Icorelink) -->
        <div class="p-5 rounded-4 my-5" style="background: linear-gradient(135deg, #0b2240 0%, #173d6d 100%); color: #ffffff;">
            <div class="text-center mb-5">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill text-uppercase mb-2">Why Travel With Us</span>
                <h2 class="display-6 fw-bold text-white font-serif">The Icorelink Travel Guarantee</h2>
                <p class="text-white-50 mx-auto" style="max-width: 620px;">Every package and itinerary is crafted with safety, convenience, and value in mind.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-3 col-sm-6 text-center">
                    <div class="mb-3 text-warning fs-1"><i class="fa-solid fa-lock"></i></div>
                    <h5 class="fw-bold text-white mb-2">PHP 2,000 Reservation</h5>
                    <p class="text-white-50 small mb-0">Lock your slots easily with our flexible downpayment policy without paying the entire balance upfront.</p>
                </div>
                <div class="col-md-3 col-sm-6 text-center">
                    <div class="mb-3 text-warning fs-1"><i class="fa-solid fa-plane"></i></div>
                    <h5 class="fw-bold text-white mb-2">Flight Time Flexibility</h5>
                    <p class="text-white-50 small mb-0">Choose your preferred airline, departure time slot, baggage tier, and seat category directly on our platform.</p>
                </div>
                <div class="col-md-3 col-sm-6 text-center">
                    <div class="mb-3 text-warning fs-1"><i class="fa-solid fa-hotel"></i></div>
                    <h5 class="fw-bold text-white mb-2">Curated Hotels</h5>
                    <p class="text-white-50 small mb-0">We inspect and partner only with verified hotels, private beach villas, and resorts with verified reviews.</p>
                </div>
                <div class="col-md-3 col-sm-6 text-center">
                    <div class="mb-3 text-warning fs-1"><i class="fa-solid fa-headset"></i></div>
                    <h5 class="fw-bold text-white mb-2">24/7 Flight & Tour Help</h5>
                    <p class="text-white-50 small mb-0">Our dedicated tour officers and airport coordinators are available whenever your flight or tour requires updates.</p>
                </div>
            </div>
        </div>

        <!-- Office & Accreditation Info -->
        <div class="row g-4 align-items-center pt-4">
            <div class="col-lg-6">
                <div class="bg-light p-4 rounded-4 border">
                    <h4 class="fw-bold text-dark-blue mb-3 font-serif"><i class="fa-solid fa-building-circle-check text-primary me-2"></i> Official Accreditation & Headquarters</h4>
                    <p class="text-muted small mb-3">Icorelink Travel & Tours is officially accredited by the Department of Tourism (DOT) and holds active city permits in the City of Manila.</p>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li class="mb-2"><i class="fa-solid fa-location-dot text-primary me-2"></i> <strong>Office:</strong> 1500 Taft Ave cor JL Escoda St. Ermita, Manila, Philippines</li>
                        <li class="mb-2"><i class="fa-solid fa-phone text-primary me-2"></i> <strong>Direct Line:</strong> +63 918 274 3144 / <?php echo htmlspecialchars($settings['contact_number'] ?? '0917 123 4567'); ?></li>
                        <li class="mb-2"><i class="fa-solid fa-envelope text-primary me-2"></i> <strong>Email:</strong> icorelink.travel@gmail.com</li>
                        <li class="mb-0"><i class="fa-solid fa-clock text-primary me-2"></i> <strong>Hours:</strong> <?php echo htmlspecialchars($settings['office_hours'] ?? 'Mon - Sat: 8:00 AM - 6:00 PM'); ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-end">
                <h4 class="fw-bold text-dark-blue mb-3 font-serif">Have questions or custom tour requests?</h4>
                <p class="text-muted small mb-4">Our travel consultants are ready to tailor an unforgettable itinerary for your family, group, or company team building.</p>
                <div class="d-flex justify-content-center justify-content-lg-end gap-3">
                    <a href="<?php echo APP_URL; ?>/contact.php" class="btn btn-primary-custom px-4 py-2">
                        <i class="fa-solid fa-paper-plane me-1"></i> Send an Inquiry
                    </a>
                    <a href="<?php echo APP_URL; ?>/packages.php" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                        Browse Tour Packages
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
