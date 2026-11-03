<?php
$current_page = 'privacy';
$page_title = 'Privacy Policy | Icorelink Travel & Tours';
require_once 'includes/header.php';
$settings = get_system_settings($pdo);
?>

<!-- Privacy Banner Header -->
<div class="privacy-hero py-5 text-white" style="background: linear-gradient(135deg, rgba(11, 34, 64, 0.96) 0%, rgba(0, 162, 232, 0.9) 100%), url('<?php echo APP_URL; ?>/assets/images/philippines_hero_bg.png') center/cover no-repeat;">
    <div class="container py-4 text-center">
        <span class="badge bg-white text-primary fw-bold text-uppercase px-3 py-2 mb-3 shadow-sm" style="letter-spacing: 0.1em;">
            <i class="fa-solid fa-shield-halved me-1"></i> Data Privacy Act Compliance (RA 10173)
        </span>
        <h1 class="display-4 fw-bold font-serif mb-2 text-white">Privacy Policy</h1>
        <p class="lead text-white-50 mx-auto mb-0" style="max-width: 720px; font-size: 1.05rem;">
            Your privacy and personal information security are paramount to us. Learn how Icorelink Travel & Tours collects, protects, and manages your personal travel records.
        </p>
        <div class="small text-white-50 mt-3">
            Last Revised: <?php echo date('F d, Y'); ?> • Effective for All Registered Accounts
        </div>
    </div>
</div>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <!-- Sidebar Quick Links -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top p-3 bg-white rounded-4 shadow-sm border" style="top: 100px;">
                    <h6 class="fw-bold text-dark-blue mb-3 pb-2 border-bottom">
                        <i class="fa-solid fa-user-shield text-primary me-2"></i> Privacy Topics
                    </h6>
                    <nav class="nav flex-column gap-1 small">
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#priv-intro">1. Introduction & DPO</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#priv-collection">2. Data We Collect</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#priv-usage">3. Purpose of Processing</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#priv-sharing">4. Third-Party Sharing</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#priv-security">5. Data Security & Storage</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#priv-cookies">6. Cookies & Sessions</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#priv-rights">7. Your Privacy Rights</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#priv-retention">8. Retention & Disposal</a>
                        <a class="nav-link text-muted px-2 py-1 rounded hover-bg-light" href="#priv-contact">9. Inquiries & Contact</a>
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-lg-9">
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">

                    <!-- Section 1 -->
                    <div id="priv-intro" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 1</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Introduction & Regulatory Compliance</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            <strong>Icorelink Travel & Tours</strong> ("Agency", "we", "us", or "our") is strictly committed to protecting the privacy, confidentiality, and integrity of personal data collected from our clients and website users. This Privacy Policy is prepared in compliance with Republic Act No. 10173, otherwise known as the <em>Philippine Data Privacy Act of 2012 (DPA)</em>, its Implementing Rules and Regulations (IRR), and issuances by the National Privacy Commission (NPC).
                        </p>
                        <p class="text-muted mb-0" style="line-height: 1.8;">
                            By visiting our website or submitting personal information for booking and inquiries, you signify your acceptance of the practices described in this policy.
                        </p>
                    </div>

                    <!-- Section 2 -->
                    <div id="priv-collection" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 2</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Personal Information We Collect</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            To facilitate travel packages, flight manifests, hotel vouchers, and payments, we may collect the following categories of data:
                        </p>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <h6 class="fw-bold text-dark-blue mb-2"><i class="fa-solid fa-address-card text-primary me-2"></i> Identifying Information</h6>
                                    <ul class="text-muted small ps-3 mb-0" style="line-height: 1.6;">
                                        <li>Full Legal Name (matching valid photo ID)</li>
                                        <li>Date of Birth, Gender, Nationality</li>
                                        <li>Email address and mobile contact numbers</li>
                                        <li>Emergency contact person & details</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <h6 class="fw-bold text-dark-blue mb-2"><i class="fa-solid fa-plane-up text-info me-2"></i> Travel & Booking Specifics</h6>
                                    <ul class="text-muted small ps-3 mb-0" style="line-height: 1.6;">
                                        <li>Flight schedule preferences & seat numbers</li>
                                        <li>Government ID copy / Passport details for ticketing</li>
                                        <li>Special dietary or room accessibility needs</li>
                                        <li>Payment reference numbers & receipt screenshots</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3 -->
                    <div id="priv-usage" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 3</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">How We Use Your Personal Data</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            Your personal information is processed strictly for legitimate travel agency operations:
                        </p>
                        <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                            <li><strong>Ticket & Voucher Issuance:</strong> Transmitting accurate guest names to airline reservation systems (Cebu Pacific, PAL, AirAsia) and hotel partners.</li>
                            <li><strong>Booking Management & Confirmations:</strong> Generating official booking references, itinerary summaries, and balance invoices.</li>
                            <li><strong>Customer Support & Urgent Alerts:</strong> Contacting you regarding flight departure schedule changes, typhoon weather advisories, or gate updates.</li>
                            <li><strong>Accounting & Auditing:</strong> Recording transaction logs, financial receipts, and issuing BIR-compliant sales invoices.</li>
                        </ul>
                    </div>

                    <!-- Section 4 -->
                    <div id="priv-sharing" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 4</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Third-Party Disclosures</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            We do not sell, rent, or trade your personal data to advertisers or unauthorized entities. Your information is disclosed only to verified service providers strictly necessary to execute your travel itinerary:
                        </p>
                        <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                            <li><strong>Airlines & Coast Guard:</strong> For passenger manifests and flight ticketing as mandated by Philippine civil aviation authorities.</li>
                            <li><strong>Partner Hotels & Local Guides:</strong> For room check-in verification and island excursion manifests.</li>
                            <li><strong>Payment Providers:</strong> GCash, Maya, and banking networks to securely verify your transfer deposits.</li>
                            <li><strong>Government Agencies:</strong> Department of Tourism (DOT) or law enforcement only when strictly required by Philippine statutory law.</li>
                        </ul>
                    </div>

                    <!-- Section 5 -->
                    <div id="priv-security" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 5</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Data Protection & Information Security</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            We implement stringent technical, organizational, and physical safeguards to prevent accidental loss, unauthorized access, alteration, or disclosure of your data:
                        </p>
                        <div class="row g-3">
                            <div class="col-sm-4 text-center">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <i class="fa-solid fa-key text-primary fs-3 mb-2"></i>
                                    <h6 class="fw-bold text-dark-blue mb-1">Encrypted Passwords</h6>
                                    <p class="text-muted small mb-0">Bcrypt-hashed password security with salted tokens.</p>
                                </div>
                            </div>
                            <div class="col-sm-4 text-center">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <i class="fa-solid fa-lock text-success fs-3 mb-2"></i>
                                    <h6 class="fw-bold text-dark-blue mb-1">Role-Based Access</h6>
                                    <p class="text-muted small mb-0">Only verified administrative and ticketing personnel access customer documents.</p>
                                </div>
                            </div>
                            <div class="col-sm-4 text-center">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <i class="fa-solid fa-database text-warning fs-3 mb-2"></i>
                                    <h6 class="fw-bold text-dark-blue mb-1">Encrypted Storage</h6>
                                    <p class="text-muted small mb-0">Isolated storage volumes for uploaded government IDs and payment receipts.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 6 -->
                    <div id="priv-cookies" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 6</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Cookies & Session Management</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            Our platform utilizes session cookies exclusively for user authentication, security verification (CSRF tokens), and preserving your login state across website navigation.
                        </p>
                        <div class="p-3 bg-light rounded-3 border-start border-info border-4">
                            <p class="text-muted small mb-0">
                                <i class="fa-solid fa-circle-info text-info me-1"></i>
                                <strong>Session Life:</strong> Your session remains active while your browser or tab is open. Once you close your browser or explicitly log out, session identifiers are automatically purged from client memory to protect your account.
                            </p>
                        </div>
                    </div>

                    <!-- Section 7 -->
                    <div id="priv-rights" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 7</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Your Rights as a Data Subject</h3>
                        </div>
                        <p class="text-muted" style="line-height: 1.8;">
                            Under Chapter VIII of the Philippine Data Privacy Act, you have the following enforceable rights:
                        </p>
                        <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                            <li><strong>Right to be Informed:</strong> To know whether personal data pertaining to you is being processed.</li>
                            <li><strong>Right to Access:</strong> To request a copy of the personal details stored within your customer profile.</li>
                            <li><strong>Right to Rectification:</strong> To update inaccurate, outdated, or incomplete personal records via your Customer Profile dashboard.</li>
                            <li><strong>Right to Erasure or Blocking:</strong> To request the suspension, withdrawal, or removal of your personal data upon completion of your tour, subject to statutory record-retention laws.</li>
                            <li><strong>Right to Lodge a Complaint:</strong> Before the National Privacy Commission (NPC) if your privacy rights have been violated.</li>
                        </ul>
                    </div>

                    <!-- Section 8 & 9 -->
                    <div id="priv-retention" class="mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white px-2 py-1 fw-bold">Section 8 & 9</span>
                            <h3 class="fw-bold text-dark-blue mb-0 font-serif">Data Retention & Disposal</h3>
                        </div>
                        <p class="text-muted small mb-0" style="line-height: 1.8;">
                            Personal data linked to travel bookings and BIR tax receipts is retained for five (5) years in compliance with Philippine financial and tax regulation audits. Uploaded government ID files are automatically archived or deleted once the travel itinerary and airport security verification have concluded.
                        </p>
                    </div>

                    <!-- Section 10 -->
                    <div id="priv-contact" class="p-4 rounded-4 bg-light border text-center">
                        <h5 class="fw-bold text-dark-blue mb-2 font-serif"><i class="fa-solid fa-envelope-open-text text-primary me-2"></i> Data Protection Officer (DPO)</h5>
                        <p class="text-muted small mb-3">If you have inquiries regarding this Privacy Policy or wish to exercise your data subject rights, please reach out to our designated Data Protection Officer:</p>
                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 small text-muted">
                            <div><i class="fa-solid fa-envelope text-primary me-1"></i> <strong>Email:</strong> privacy@icorelinktravel.com</div>
                            <div><i class="fa-solid fa-phone text-primary me-1"></i> <strong>Hotline:</strong> +63 918 274 3144</div>
                            <div><i class="fa-solid fa-location-dot text-primary me-1"></i> Ermita, Manila, Philippines</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
