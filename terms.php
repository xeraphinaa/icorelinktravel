<?php
$current_page = 'terms';
$page_title = 'Terms and Conditions | Icorelink Travel & Tours';
require_once 'includes/header.php';
$settings = get_system_settings($pdo);
?>

<!-- Terms Banner Header -->
<div class="terms-hero py-5 text-white" style="background: linear-gradient(135deg, rgba(11, 34, 64, 0.96) 0%, rgba(0, 162, 232, 0.9) 100%), url('<?php echo APP_URL; ?>/assets/images/philippines_hero_bg.png') center/cover no-repeat;">
    <div class="container py-4 text-center">
        <span class="badge bg-white text-primary fw-bold text-uppercase px-3 py-2 mb-3 shadow-sm" style="letter-spacing: 0.1em;">
            <i class="fa-solid fa-scale-balanced me-1"></i> Legal & Traveler Agreement
        </span>
        <h1 class="display-4 fw-bold font-serif mb-2 text-white">Terms and Conditions</h1>
        <p class="lead text-white-50 mx-auto mb-0" style="max-width: 720px; font-size: 1.05rem;">
            Please read these terms carefully before booking any tour package, flight schedule, or hotel accommodation with Icorelink Travel & Tours.
        </p>
        <div class="small text-white-50 mt-3">
            Last Updated: <?php echo date('F d, Y'); ?> • Effective Immediately
        </div>
    </div>
</div>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <!-- Left Sidebar Navigation / Quick Jump -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top p-3 bg-white rounded-4 shadow-sm border" style="top: 100px;">
                    <h6 class="fw-bold text-dark-blue mb-3 pb-2 border-bottom">
                        <i class="fa-solid fa-list-ul text-primary me-2"></i> Table of Contents
                    </h6>
                    <nav class="nav flex-column gap-1 small">
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#section-general">1. Acceptance of Terms</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#section-reservation">2. Reservations & Downpayment</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#section-payment">3. Payment Methods & Balance</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#section-flights">4. Flights & Baggage Policy</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#section-hotels">5. Hotels & Accommodations</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#section-cancellation">6. Cancellation & Rebooking</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#section-refunds">7. Refund Guidelines</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#section-force-majeure">8. Force Majeure & Weather</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#section-traveler-duty">9. Traveler Responsibilities</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#section-liability">10. Limitation of Liability</a>
                    </nav>
                </div>
            </div>

            <!-- Main Legal Content -->
            <div class="col-lg-9">
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">

                    <!-- Section 1 -->
                    <div id="section-general" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 1</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Acceptance of Agreement</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            By accessing our website, creating a customer account, reserving a tour package, or purchasing flight and hotel itineraries through <strong>Icorelink Travel & Tours</strong> ("Agency", "we", "us", or "our"), you ("Client", "Traveler", or "Guest") acknowledge that you have read, understood, and agreed to be legally bound by these Terms and Conditions.
                        </p>
                        <p class="text-muted mb-0" style="line-height: 1.8;">
                            If you are making a booking on behalf of family members, travel companions, or a group, you confirm that you have obtained their full consent and authority to bind them to all terms set forth herein.
                        </p>
                    </div>

                    <!-- Section 2 -->
                    <div id="section-reservation" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 2</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Booking Procedure & Reservation Fee</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            To provide maximum flexibility for our travelers, Icorelink Travel & Tours allows slot reservation prior to full payment:
                        </p>
                        <div class="p-3 bg-light rounded-3 border-start border-primary border-4 mb-3">
                            <h6 class="fw-bold text-dark-blue mb-1"><i class="fa-solid fa-circle-check text-primary me-2"></i> Standard Reservation Fee: PHP 2,000.00 Per Head</h6>
                            <p class="text-muted small mb-0">
                                A non-refundable reservation fee of <strong>PHP 2,000 per traveler</strong> is required to lock in tour package slots, hotel rooms, and airline ticket allocations. This deposit is fully deductible from your total package invoice.
                            </p>
                        </div>
                        <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                            <li>Bookings remain in <em>Pending</em> status until the reservation fee or full payment proof is uploaded and verified by our ticketing staff.</li>
                            <li>Unconfirmed bookings with unpaid reservation fees within 48 hours of creation are subject to automatic cancellation to release slots to other waitlisted travelers.</li>
                        </ul>
                    </div>

                    <!-- Section 3 -->
                    <div id="section-payment" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 3</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Payment Methods & Balance Settlement</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            We accept payments via our official accredited channels only: <strong>GCash, Maya (PayMaya), PayPal, and Philippine Direct Bank Transfers</strong>. Never send payments to personal or unverified accounts not displayed on our official platform.
                        </p>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border">
                                    <h6 class="fw-bold text-dark-blue mb-1"><i class="fa-solid fa-calendar-check text-success me-2"></i> Balance Payment Timeline</h6>
                                    <p class="text-muted small mb-0">The remaining invoice balance must be settled at least <strong>14 days before the departure date</strong> for domestic packages, and <strong>30 days before</strong> for peak-season holidays (Holy Week, Christmas, New Year).</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border">
                                    <h6 class="fw-bold text-dark-blue mb-1"><i class="fa-solid fa-receipt text-warning me-2"></i> Payment Verification</h6>
                                    <p class="text-muted small mb-0">Travelers must upload a clear screenshot of the transaction reference receipt in their Customer Dashboard under "Payments" for staff review.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4 -->
                    <div id="section-flights" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 4</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Flights & Airline Partner Policies</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            Domestic flight bookings arranged through Icorelink Travel & Tours are governed by the specific Conditions of Carriage of the respective operating airline (Cebu Pacific, Philippine Airlines, AirAsia Philippines):
                        </p>
                        <ul class="text-muted small ps-3 mb-3" style="line-height: 1.8;">
                            <li><strong>Airport Arrival:</strong> Guests are required to arrive at the designated airport terminal at least <strong>3 hours prior to scheduled departure</strong> for domestic flights.</li>
                            <li><strong>Baggage Allowances:</strong> Standard promotional fares include 7kg hand-carry baggage. Checked luggage (20kg or 32kg) must be pre-booked in the schedule selection panel. Excess baggage fees imposed at the airport counter are the sole responsibility of the passenger.</li>
                            <li><strong>Flight Schedule Adjustments:</strong> Operating airlines reserve the right to modify flight numbers, departure times, or aircraft types for operational or air traffic control reasons. The agency will immediately relay official airline schedule notifications.</li>
                        </ul>
                    </div>

                    <!-- Section 5 -->
                    <div id="section-hotels" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 5</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Hotels & Accommodations</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            All hotel and resort accommodations booked through our catalog adhere to official Philippine hospitality standards:
                        </p>
                        <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                            <li>Standard check-in time is <strong>2:00 PM</strong> and check-out time is <strong>12:00 PM</strong>, unless otherwise indicated by the property.</li>
                            <li>Incidental charges (mini-bar, room service, laundry, telephone calls, damages) are not included in the package rate and must be settled directly with hotel reception upon checkout.</li>
                            <li>Room bed configurations (king, twin, double) are subject to hotel availability upon arrival unless a specific room tier was guaranteed.</li>
                        </ul>
                    </div>

                    <!-- Section 6 & 7 -->
                    <div id="section-cancellation" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 6 & 7</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Cancellations, Rebooking & Refunds</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            <?php echo htmlspecialchars($settings['cancellation_policy'] ?? 'Cancellation requests must be submitted in writing through your account dashboard or via email.'); ?>
                        </p>
                        <div class="table-responsive my-3">
                            <table class="table table-bordered table-sm small align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Cancellation Notice Period</th>
                                        <th>Domestic Tour Package</th>
                                        <th>Flight Tickets</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>30+ Days Before Departure</td>
                                        <td>Full refund less reservation fee</td>
                                        <td>Subject to airline fare rules (rebooking fees apply)</td>
                                    </tr>
                                    <tr>
                                        <td>15 to 29 Days Before Departure</td>
                                        <td>50% refund of total package price</td>
                                        <td>Airline fare rules apply</td>
                                    </tr>
                                    <tr>
                                        <td>Less than 14 Days Before Departure</td>
                                        <td>Non-refundable</td>
                                        <td>Non-refundable / Airport taxes only</td>
                                    </tr>
                                    <tr>
                                        <td>No Show on Travel Day</td>
                                        <td>100% Forfeited</td>
                                        <td>100% Forfeited</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mb-0">
                            <strong>Refund Processing:</strong> Approved refunds are credited back to the original payment channel within 15 to 30 business days, subject to banking clearing schedules.
                        </p>
                    </div>

                    <!-- Section 8 -->
                    <div id="section-force-majeure" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 8</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Force Majeure & Weather Advisories</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            In the event of natural disasters, typhoons, severe weather warnings (PAGASA storm signals), sea travel bans issued by the Philippine Coast Guard (PCG), earthquakes, pandemics, civil unrest, or government quarantine mandates ("Force Majeure"), Icorelink Travel & Tours will prioritize guest safety above all else.
                        </p>
                        <p class="text-muted small mb-0">
                            Tours interrupted or canceled due to Force Majeure are eligible for <strong>free rebooking</strong> within 6 months, or travel vouchers corresponding to unutilized tour components minus costs already disbursed to local authorities or non-refundable suppliers.
                        </p>
                    </div>

                    <!-- Section 9 -->
                    <div id="section-traveler-duty" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 9</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Traveler Responsibilities & Identification</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            Each traveler is responsible for ensuring they possess:
                        </p>
                        <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                            <li><strong>Valid Government-Issued Photo ID:</strong> (e.g., Philippine National ID, Passport, Driver's License, SSS/GSIS ID, PRC ID) with names matching the exact spelling on flight tickets.</li>
                            <li><strong>Eco-Tourism & Protected Sanctuary Compliance:</strong> Adhering to municipal environmental ordinances (e.g., no single-use plastics in El Nido/Coron, no collection of corals or seashells in protected marine sanctuaries).</li>
                            <li><strong>Medical & Fitness Disclosure:</strong> Notifying our guides of any pre-existing medical conditions, pregnancy, or physical limitations prior to island hopping, trekking, or snorkeling activities.</li>
                        </ul>
                    </div>

                    <!-- Section 10 -->
                    <div id="section-liability" class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 10</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Limitation of Liability & Travel Insurance</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            While Icorelink Travel & Tours exercises the highest degree of diligence in selecting licensed tour operators, certified vessel captains, and DOT-accredited accommodation providers, the Agency acts solely as an intermediary coordinator. We strongly recommend that all travelers purchase comprehensive Travel Insurance covering medical emergencies, trip delays, and baggage loss.
                        </p>
                    </div>

                    <!-- Contact Footer within Terms -->
                    <div class="p-4 rounded-4 bg-light border text-center mt-5">
                        <h5 class="fw-bold text-dark-blue mb-2 font-serif">Have questions regarding our terms?</h5>
                        <p class="text-muted small mb-3">Our customer support officers are ready to clarify any booking rule or itinerary policy.</p>
                        <a href="<?php echo APP_URL; ?>/contact.php" class="btn btn-primary-custom px-4 py-2">
                            <i class="fa-solid fa-headset me-2"></i> Contact Customer Support
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
