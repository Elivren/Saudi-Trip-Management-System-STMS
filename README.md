# STMS - Saudi Trip Management System

A full-stack web application for managing tourist trips across Saudi Arabia, connecting tourists with certified local guides. Features dynamic city-based pricing, multi-city travel packages, self-planned itineraries, and optional add-ons (accommodation & meals).

---

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Frontend   | HTML5, CSS3, JavaScript (ES6+)    |
| Backend    | PHP 7.4+                          |
| Database   | MySQL / MariaDB                   |
| Server     | Apache (XAMPP)                    |
| Icons      | Font Awesome 6.5                  |
| Fonts      | Google Fonts (Poppins, Noto Kufi Arabic) |

---

## Project Structure

```
02stms/
├── index.html                 # Main Landing Page (Home)
├── login.html                 # Login & Role Selection Screen
├── register.html              # Registration Screen (Tourist / Guide)
├── book.html                  # Tourist Booking Page (Self-Planned / Package)
├── trips.html                 # Tourist Trip List + Packages Screen
├── trip-details.html          # Trip Details & Booking Screen
├── guide-dashboard.html       # Guide Dashboard – Manage Trips & Packages
├── admin-dashboard.html       # Admin Dashboard – Manage Users & Trips
├── profile.html               # Profile & Reviews Screen
├── install.php                # One-click database installer
├── README.md
├── css/
│   └── style.css              # All styles (variables, components, responsive)
├── js/
│   └── app.js                 # Shared frontend logic (auth, utilities, i18n)
├── php/
│   ├── config.php             # Database connection & helper functions
│   ├── auth.php               # Login, Register, Logout, Session check
│   ├── trips.php              # Trip CRUD, listing, filtering
│   ├── bookings.php           # Booking create, list, accept/reject, cancel
│   ├── packages.php           # Package CRUD, city pricing, price calculator
│   ├── reviews.php            # Review CRUD for tourists and guides
│   ├── users.php              # Profile update, password change, contact form
│   └── admin.php              # Dashboard stats, user/trip/review/FAQ management
└── mysql/
    ├── stms_database.sql      # Full database schema + seed data
    └── stms_update.sql        # Incremental migration for existing databases
```

---

## Key Features

### Dynamic City-Based Pricing
Prices vary by city, reflecting real-world cost differences across Saudi Arabia:

| City    | Base/Day (SAR) | Hotel/Night (SAR) | Transport/Day (SAR) |
|---------|---------------|-------------------|---------------------|
| Riyadh  | 350           | 450               | 150                 |
| Jeddah  | 300           | 400               | 130                 |
| Makkah  | 320           | 500               | 120                 |
| AlUla   | 180           | 240               | 80                  |

> Full pricing for 10 cities is stored in the `city_pricing` database table.

### Booking Types
After login, tourists choose between two booking paths:

- **Self-Planned Booking** – Pick cities, set days per city (max 10 days, with extension option), choose add-ons, and get a dynamic price breakdown.
- **Pre-Designed Package** – Browse expert-crafted multi-city packages by certified guides. Fixed duration, structured itinerary, transportation included by default.

### Optional Add-ons
Every booking includes **transportation by default**. Tourists can optionally add:
- **Accommodation** – Hotel per night, priced by city
- **Breakfast** – Daily, priced by city
- **Lunch** – Daily, priced by city
- **Dinner** – Daily, priced by city

Prices update in real-time as add-ons are toggled on/off.

### Guide Package Management
Guides can create and manage multi-city travel packages:
- Set a **fixed duration** (up to 10 days, cannot be extended by tourists)
- Add multiple cities with days allocated to each
- Build a **day-by-day itinerary** with titles and descriptions
- Upload **gallery images** (via URLs)
- Set **base price** and **max tourist capacity**
- View and **accept/reject** package bookings

### Trip Duration Rules
- **Maximum trip duration**: 10 days
- **Self-planned bookings**: Can request **extension days** beyond the 10-day limit
- **Guide packages**: Duration is **fixed** and cannot be extended

---

## Screens Overview

### Main Landing Page (`index.html`)
- Full-width animated background slideshow (desert, mountains, sea, city)
- Central card with **Book a Trip** and **Browse Trips** buttons
- Top navbar with logo, language switcher (AR/EN), About, Contact, FAQ links
- About section, Contact form, FAQ accordion, Footer

### Login & Role Selection (`login.html`)
- Split layout: Saudi landscape image + login form
- Role tabs: **Tourist**, **Tour Guide**, **Admin**
- Email & Password fields with validation
- Tourists are redirected to `book.html` after login
- Guides are redirected to `guide-dashboard.html` after login

### Registration (`register.html`)
- Split layout with heritage site image + registration form
- Fields: Full Name, Email, Phone, Password, Confirm Password
- Role selection: Tourist or Guide
- Optional: City / Region dropdowns

### Book a Trip (`book.html`)
- **Step 1**: Choose booking type (Self-Planned or Pre-Designed Package)
- **Self-Planned path**:
  - City selector with days per city (dynamic pricing)
  - Extension days option when at 10-day limit
  - Start date, number of people
  - Add-on toggles (accommodation, breakfast, lunch, dinner)
  - Real-time price breakdown summary
- **Package path**:
  - Browse package cards with city tags, duration, guide info
  - Detailed package view with itinerary, images, guide profile
  - Add-on toggles for accommodation and meals
  - Fixed duration (cannot be extended)

### Tourist Trip List (`trips.html`)
- Left sidebar with navigation (Book a Trip, My Bookings, My Reviews, Profile, Logout)
- Card-based trip grid showing: title, location, date, price, guide, rating
- **Tour Packages** section below trips showing available guide packages
- Filters: Location, Date, Price Range, Search box
- Enhanced **My Bookings** view with booking type badges (Trip / Package / Self-Planned), add-on icons, and total price

### Trip Details & Booking (`trip-details.html`)
- Large cover image hero with trip info overlay
- Detailed description, itinerary (day-by-day), guide info, reviews
- Sticky booking widget: date picker, people count, price calculator
- Success confirmation message after booking

### Guide Dashboard (`guide-dashboard.html`)
- **My Trips** – Table of guide's trips with status, bookings count, edit/delete
- **Bookings** – Accept/Reject tourist bookings per trip + **Package Bookings** table showing package reservations with add-on details and total price
- **Add New Trip** – Form with itinerary builder
- **My Packages** – Table of guide's packages with cities, duration, price, status, booking count
- **Create Package** – Form with city builder, day-by-day itinerary builder, gallery images, base price, max tourists
- **Client Reviews** – Reviews from tourists

### Admin Dashboard (`admin-dashboard.html`)
- Summary stat cards: Total Users, Total Trips, Pending Bookings, New Feedback
- **Manage Users**: Activate / Deactivate / Delete accounts, filter by role
- **Manage Trips**: View all trips, delete suspicious ones
- **Manage Bookings**: View all bookings across all types (trip, package, self-planned)
- **Reviews & Feedback**: View/delete reviews, read contact messages
- **System FAQs**: Add / Edit / Delete FAQ entries

### Profile & Reviews (`profile.html`)
- Profile header with avatar, name, role
- **Personal Info** tab: Edit name, phone, city, region
- **Change Password** tab
- **My Reviews** tab (tourist) / **Client Reviews** tab (guide)

---

## Database Setup

1. Make sure **XAMPP** is installed and **Apache** + **MySQL** are running.
2. **Option A (Recommended):** Open your browser and go to:
   ```
   http://localhost/02stmsarabic/02stms/install.php
   ```
   This will automatically create the database, all 13 tables, and seed data.

3. **Option B (Manual):** Open **phpMyAdmin** at `http://localhost/phpmyadmin`.
   - Click **Import** tab
   - Choose file: `mysql/stms_database.sql`
   - Click **Go**

4. **Option C (Upgrade existing DB):** If you already have the old database and want to add the new features:
   - Import `mysql/stms_update.sql` via phpMyAdmin

All options will create/update the `stms_db` database with all tables and seed data.

---

## Database Tables

| Table               | Description                                        |
|---------------------|----------------------------------------------------|
| `users`             | All users (tourists, guides, admin)                |
| `trips`             | Trip listings created by guides                    |
| `bookings`          | All booking requests (trip, package, self-planned) |
| `booking_cities`    | Cities selected in self-planned bookings           |
| `city_pricing`      | Dynamic pricing per city (base, hotel, meals, transport) |
| `packages`          | Multi-city travel packages created by guides       |
| `package_cities`    | Cities included in each package                    |
| `package_itinerary` | Day-by-day schedule for packages                   |
| `package_images`    | Gallery images for packages                        |
| `reviews`           | Tourist reviews and ratings                        |
| `feedback`          | Contact form messages                              |
| `faqs`              | Frequently asked questions                         |

---

## Seed Data (Default Accounts)

| Role    | Email              | Password     |
|---------|--------------------|--------------|
| Admin   | admin@stms.sa      | admin123     |
| Guide   | ahmed@stms.sa      | guide123     |
| Guide   | fatimah@stms.sa    | guide123     |
| Guide   | omar@stms.sa       | guide123     |
| Guide   | mohammed@stms.sa   | guide123     |
| Tourist | sarah@email.com    | tourist123   |
| Tourist | mohammed@email.com | tourist123   |
| Tourist | emily@email.com    | tourist123   |

### Seed Packages
| Package                                  | Guide     | Duration | Cities                    | Price (SAR) |
|------------------------------------------|-----------|----------|---------------------------|-------------|
| Saudi Grand Tour: Jeddah, Riyadh & AlUla | Mohammed  | 10 Days  | Jeddah, Riyadh, AlUla     | 4,500       |
| Jeddah & Abha Explorer                   | Ahmed     | 7 Days   | Jeddah, Abha              | 3,200       |

> **Note:** All seed passwords use bcrypt hashing. In production, each user should have a unique password.

---

## How to Run

1. Place the `02stmsarabic/02stms` folder inside `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Import the database (see Database Setup above).
4. Open your browser and navigate to:

```
http://localhost/02stmsarabic/02stms/
```

---

## API Endpoints

### Authentication (`php/auth.php`)
| Action     | Method | Description              |
|------------|--------|--------------------------|
| `login`    | POST   | Authenticate user        |
| `register` | POST   | Create new account       |
| `logout`   | GET    | Destroy session          |
| `check`    | GET    | Check current session    |

### Trips (`php/trips.php`)
| Action        | Method | Description                 |
|---------------|--------|-----------------------------|
| `list`        | GET    | List all trips (with filters) |
| `get`         | GET    | Get single trip details     |
| `create`      | POST   | Create trip (guide only)    |
| `update`      | POST   | Update trip (guide only)    |
| `delete`      | POST   | Delete trip (guide/admin)   |
| `guide_trips` | GET    | List guide's own trips      |

### Packages (`php/packages.php`)
| Action           | Method | Description                            |
|------------------|--------|----------------------------------------|
| `list`           | GET    | List all active packages               |
| `get`            | GET    | Get package details with itinerary     |
| `create`         | POST   | Create package (guide only)            |
| `update`         | POST   | Update package (guide only)            |
| `delete`         | POST   | Delete package (guide/admin)           |
| `guide_packages` | GET    | List guide's own packages              |
| `city_pricing`   | GET    | Get dynamic pricing for all cities     |
| `calculate_price`| POST   | Calculate total price with add-ons     |

### Bookings (`php/bookings.php`)
| Action                  | Method | Description                              |
|-------------------------|--------|------------------------------------------|
| `create`                | POST   | Book a trip (tourist only)               |
| `create_package_booking`| POST   | Book a package with add-ons (tourist)    |
| `create_self_planned`   | POST   | Create self-planned booking (tourist)    |
| `my_bookings`           | GET    | Tourist's bookings (all types)           |
| `trip_bookings`         | GET    | Bookings for a trip (guide)              |
| `package_bookings`      | GET    | Bookings for guide's packages            |
| `update_status`         | POST   | Accept/Reject booking (guide)            |
| `cancel`                | POST   | Cancel booking (tourist)                 |

### Reviews (`php/reviews.php`)
| Action          | Method | Description                   |
|-----------------|--------|-------------------------------|
| `create`        | POST   | Submit review (tourist only)  |
| `my_reviews`    | GET    | Tourist's reviews             |
| `guide_reviews` | GET    | Reviews for guide's trips     |
| `trip_reviews`  | GET    | Reviews for a specific trip   |
| `delete`        | POST   | Delete review (admin only)    |

### Users (`php/users.php`)
| Action            | Method | Description                |
|-------------------|--------|----------------------------|
| `profile`         | GET    | Get current user profile   |
| `update_profile`  | POST   | Update profile info        |
| `change_password` | POST   | Change password            |
| `contact`         | POST   | Submit contact form        |

### Admin (`php/admin.php`)
| Action               | Method | Description                  |
|----------------------|--------|------------------------------|
| `dashboard`          | GET    | Get system statistics        |
| `users`              | GET    | List all users               |
| `update_user_status` | POST   | Activate/Deactivate user     |
| `delete_user`        | POST   | Delete user                  |
| `all_trips`          | GET    | List all trips               |
| `all_bookings`       | GET    | List all bookings (all types)|
| `all_reviews`        | GET    | List all reviews             |
| `faqs`               | GET    | List FAQs                    |
| `save_faq`           | POST   | Create/Update FAQ            |
| `delete_faq`         | POST   | Delete FAQ                   |
| `feedback`           | GET    | List contact feedback        |

---

## Features

- **Dynamic City Pricing** – Prices vary by city (Riyadh highest, AlUla lowest) across 10 Saudi cities
- **Multi-City Packages** – Guides create fixed-duration packages spanning multiple cities with day-by-day itineraries
- **Self-Planned Bookings** – Tourists build custom itineraries with flexible city/day selection
- **Optional Add-ons** – Accommodation, breakfast, lunch, dinner adjust the total price per city
- **Transportation Included** – All bookings include transportation by default
- **Max 10-Day Duration** – Trip limit of 10 days with extension option for self-planned bookings
- **Real-Time Price Calculator** – Live price breakdown as tourists configure their trip
- **Responsive Design** – Works on desktop, tablet, and mobile
- **Bilingual (AR/EN)** – Full Arabic and English language support with RTL layout
- **Role-Based Access** – Tourist, Guide, and Admin with different permissions
- **Session Management** – PHP sessions with localStorage fallback
- **Animated UI** – Smooth transitions, slide animations, hover effects
- **Secure Passwords** – bcrypt hashing via `password_hash()`
- **Input Sanitization** – Server-side validation and XSS prevention
- **Offline Fallback** – Static sample data when database is unavailable

---

## Color Scheme

| Color         | Hex       | Usage                    |
|---------------|-----------|--------------------------|
| Primary Green | `#006C35` | Buttons, accents, links  |
| Gold          | `#C8A951` | Highlights, badges       |
| Sand          | `#F5E6C8` | Backgrounds              |
| Dark          | `#1a1a2e` | Sidebar, footer          |
| Danger Red    | `#e74c3c` | Errors, delete actions   |
| Info Blue     | `#3498db` | Information badges       |

---

## License

This project is developed for educational purposes as part of the Saudi Trip Management System coursework.
