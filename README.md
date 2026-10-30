# Hospital Management System

A full-stack DBMS mini-project built with **HTML5, CSS3, JavaScript** (frontend)
and **PHP + MySQL** (backend), based on the attached project synopsis.

## Features

- Centered landing page with the project name and two entry points: **Admin Login** and **Patient (User) Login**
- **Admin portal**
  - Dashboard with live counts (patients, doctors, staff, appointments, beds, unpaid bills)
  - Patient records (add / edit / delete) — the DBMS "Patient" entity
  - Doctor management (add / edit / delete) — the "Doctor" entity
  - Staff management (add / edit / delete) — the "Staff" entity
  - Appointment scheduling — book, confirm, complete, cancel, delete
  - Bed / ward allocation — add beds, allocate to a patient, free a bed
  - Billing — generate bills, mark paid, delete
- **Patient (user) portal**
  - Self-registration and login
  - Personal dashboard: profile, appointment history, bills, current bed/ward
  - Book a new appointment with any available doctor
- Role-based, session-based authentication (separate sessions for admin and patient)
- Passwords are hashed with PHP's `password_hash()` / verified with `password_verify()`
- Responsive layout (sidebar collapses to a hamburger menu on mobile)

## Tech Stack

| Layer     | Technology                        |
|-----------|------------------------------------|
| Frontend  | HTML5, CSS3 (custom, no framework), vanilla JavaScript |
| Backend   | PHP 8 (PDO + prepared statements)  |
| Database  | MySQL / MariaDB                    |
| Server    | Apache (XAMPP / WAMP / LAMP)        |

## Folder Structure

```
hospital_management_system/
├── index.html                  ← Home page (project name + Admin/User login buttons)
├── css/style.css                ← All styling
├── js/script.js                 ← Sidebar toggle, client-side validation, confirmations
├── config/db.php                ← MySQL connection settings (EDIT THIS if needed)
├── database/hms_db.sql          ← Import this to create the database & tables
├── includes/                    ← Shared PHP partials (session guards, sidebars)
├── admin/                       ← Admin portal (login, dashboard, patients, doctors, staff, appointments, beds, billing)
└── user/                        ← Patient portal (login, register, dashboard, book_appointment)
```

## Setup Instructions (XAMPP / WAMP)

1. **Install XAMPP** (or WAMP/MAMP) if you don't already have it, and start **Apache** and **MySQL** from the control panel.
2. **Copy the project folder** `hospital_management_system` into your server's web root:
   - XAMPP (Windows): `C:\xampp\htdocs\hospital_management_system`
   - XAMPP (Linux): `/opt/lampp/htdocs/hospital_management_system`
   - MAMP (Mac): `/Applications/MAMP/htdocs/hospital_management_system`
3. **Create the database**:
   - Open `http://localhost/phpmyadmin`
   - Click **Import**, choose the file `database/hms_db.sql`, and click **Go**.
   - This creates the `hms_db` database, all tables, a few sample beds, and one default admin account.
4. **Check the DB credentials** in `config/db.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'hms_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');   // default XAMPP has no password
   ```
   Edit these if your MySQL username/password are different.
5. **Open the app** in your browser:
   ```
   http://localhost/hospital_management_system/
   ```

## Default Login

**Admin**
- Username: `admin`
- Password: `admin123`

**Patient (User)**
- No default account — click **User Login → Create an account** on the login page to register a new patient.

## Notes for your report / viva

- The database design follows the synopsis exactly: `Patient`, `Doctor`, `Staff`, `Appointment`, `Bed/Ward`, `Billing`, and `Login/User` (implemented as `admins` + `patients.username/password`) tables.
- All SQL queries use **PDO prepared statements** to prevent SQL injection.
- Passwords are never stored in plain text — they're hashed with bcrypt via `password_hash()`.
- Sessions (`$_SESSION`) are used for role-based login state, with separate guards (`admin_auth.php` / `user_auth.php`) protecting each portal's pages.
- The `billing.total_amount` column is a MySQL **generated column** (`treatment_cost + room_charges`), calculated automatically by the database.

## Possible Future Enhancements (mentioned in the synopsis's conclusion)

- Online appointment booking with doctor calendars / time-slot conflict checks
- Telemedicine / video consultation integration
- Email/SMS notifications for appointment confirmations
- Downloadable PDF bills and reports
