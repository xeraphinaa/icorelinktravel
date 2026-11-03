# 🌴 Icorelink Travel & Tours

A modern, full-featured web application and management system for **Icorelink Travel & Tours** — a DOT-accredited Philippine travel agency.

---

## 🌟 Features

- **Dynamic Homepage & Showcase:**
  - Real-time announcement ticker with travel guidelines, promo flash sales, and marine sanctuary rules.
  - Interactive Philippine tourism video player (inline and floating mode).
  - Verified traveler testimonials with high-resolution photo popups.
  - Interactive contact and inquiry form with integrated location map.
- **Flight & Hotel Coordination:**
  - Comprehensive airline search, seat class selector, and booking inquiries.
  - Hotel and resort accommodations across top Philippine destinations.
- **Tour Packages:**
  - Handpicked tours (El Nido, Coron, Boracay, Batanes, Cebu, Siargao, and more).
  - Package details, inclusions, exclusions, and booking reservation flow.
- **Customer & Staff Portals:**
  - Fast overlay modals for authentication (Sign In & Sign Up).
  - Role-based access control (Admin, Staff, Customer).
  - Reservation receipt uploads and booking status verification.

---

## 🚀 Live Preview on GitHub Pages

The repository includes a standalone, production-ready [`index.html`](index.html) configured with relative asset links. 

To enable GitHub Pages:
1. Go to your repository **Settings** on GitHub.
2. Select **Pages** from the left sidebar.
3. Under **Branch**, select `main` (or `master`) and folder `/ (root)`.
4. Click **Save**. Your site will be live at `https://<your-username>.github.io/<repository-name>/`.

---

## 💻 Local Setup (XAMPP / PHP & MySQL)

For full dynamic functionality including user login, database storage, booking reservations, and admin/staff management:

1. **Clone the repository:**
   ```bash
   git clone https://github.com/<your-username>/<repo-name>.git
   ```
2. **Move into your XAMPP web root:**
   - Place the project folder into `C:\xampp\htdocs\icorelink`
3. **Start Apache & MySQL:**
   - Open **XAMPP Control Panel** and click **Start** for both Apache and MySQL.
4. **Import the Database:**
   - Open [phpMyAdmin](http://localhost/phpmyadmin/).
   - Create a database (e.g., `icorelink_db`).
   - Import the schema from the [`database schema/`](./database%20schema/) directory.
   - Update your credentials in [`config/database.php`](./config/database.php).
5. **Open in Browser:**
   - Visit: [http://localhost/icorelink/](http://localhost/icorelink/)

---

## 🛠️ Tech Stack

- **Frontend:** HTML5, CSS3 (Custom Responsive Design), JavaScript (Vanilla ES6+), Bootstrap 5, FontAwesome 6, Animate.css
- **Backend:** PHP 8.0+
- **Database:** MySQL / MariaDB (PDO)
- **Web Server:** Apache (XAMPP)

---

## 📄 License & Copyright

&copy; 2026 **Icorelink Travel & Tours**. All rights reserved.
