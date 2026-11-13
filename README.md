# SportKick — Pro Sportswear & Performance Footwear E-Commerce Platform

> **"PUSH BEYOND LIMITS. Performance starts with what you wear."**  
> A full-stack sportswear and athletic footwear e-commerce web application built with **HTML5, Custom CSS3, Vanilla JavaScript (ES6+), PHP 8.5, and MySQL**.

---

## 1. Project Description
**SportKick** is a dedicated e-commerce platform engineered for athletes and sports enthusiasts to discover, filter, compare, and purchase high-performance sports shoes and technical sportswear across **8 competitive athletic disciplines**:
1. **Running**
2. **Basketball**
3. **Football**
4. **Cricket**
5. **Tennis**
6. **Badminton**
7. **Gym & Training**
8. **Hiking**

Inspired by global athletic retail leaders (**Nike, Adidas, Puma, and Foot Locker**), SportKick combines a bold, high-contrast Carbon Black (`#09090B`), Studio Alabaster (`#F5F5F7`), Pure White (`#FFFFFF`), and Electric Orange (`#FF5500`) interface with a relational MySQL database backend powered by PDO prepared statements, bcrypt password security, and asynchronous JSON APIs. All product pricing is natively formatted in **Indian Rupees (`₹` INR)**.

---

## 2. Objectives
1. **Centralized Multi-Sport Retail Catalog:** Organize athletic footwear and technical apparel by specific sport disciplines rather than generic clothing lists.
2. **Dynamic Database-Driven Architecture:** Load all categories, products, technical specifications, user profiles, shopping carts, wishlists, and orders dynamically from MySQL (`sportkick_db`).
3. **Multi-Parameter Search & Filtering:** Enable athletes to filter gear simultaneously by keyword/product name, sport category, brand, price range (`₹`), shoe/apparel size, colorway, equipment type, and sort criteria.
4. **Seamless Cart, Wishlist & Promo Engine:** Provide instant AJAX cart and wishlist operations with persistent guest-to-member session synchronization and promotional coupon validation (`KICK10`, `ATHLETE20`).
5. **Secure Authentication & Transactional Checkout:** Implement bcrypt password hashing (`password_hash` / `password_verify`), PDO prepared statements, and ACID-compliant MySQL transactions for order creation and inventory stock management.

---

## 3. Technology Stack
| Layer | Technology | Purpose |
| :--- | :--- | :--- |
| **Frontend Markup** | HTML5 | Semantic document structure, accessible forms, modal dialogs, and SEO-friendly product layouts |
| **Styling & UI/UX** | Custom CSS3 (`assets/css/style.css`) | 2,177-line responsive design system using CSS Custom Properties, CSS Grid, Flexbox, glassmorphism sticky header, and athletic typography (*Barlow Condensed* & *Plus Jakarta Sans*) |
| **Client-Side Scripting** | Vanilla JavaScript ES6+ (`assets/js/main.js`) | Asynchronous Fetch API calls for Cart, Wishlist, Quick View modal, gallery switching, quantity steppers, and toast notifications |
| **Backend Server** | PHP 8.5 | Server-side page rendering, RESTful JSON API controllers (`api/*.php`), session management, and business logic |
| **Database** | MySQL 8+ / 9+ (`sportkick_db`) | Persistent relational storage across 8 InnoDB tables with foreign key constraints and composite indexes |
| **Database Abstraction** | PHP Data Objects (PDO) | Parameterized prepared statements (`PDO::ATTR_EMULATE_PREPARES => false`) preventing SQL injection, plus auto-bootstrap schema initialization |
| **Version Control** | Git & GitHub | Source code tracking and remote repository hosting (`https://github.com/Mukund220304/sportkick`) |

---

## 4. Features
- **Athletic Home Page (`index.php`):** High-impact hero banner (*"GEAR UP. SHOW UP. NEVER STOP."*), official partner brand strip, 8 interactive sport category cards with live item counts, Trending Athlete Picks, Exclusive Locker Room Deals banner, and New Season Arrivals.
- **Multi-Parameter Search & Filter Engine (`shop.php`):** Filter by keyword/SKU, sport category, brand (*Nike, Adidas, Puma, Asics, Under Armour, Yonex, Salomon*), price range in `₹`, size (`UK 6`–`UK 12`, `S`–`XXL`), colorway, equipment type (*Footwear* / *Apparel*), and 6 sorting modes.
- **Interactive Product Cards (`includes/product_card.php`):** Studio photography with hover zoom, discount percentage badges, `NEW`/`HOT` badges, star ratings, inline size selection pills, 1-click Add to Cart, Wishlist heart toggle, and Quick View modal trigger.
- **Quick View Modal (`api/products.php` + `assets/js/main.js`):** Inspect multi-angle product gallery images, technical specifications, size/colorway options, and add to bag without leaving the current page.
- **Detailed Product View (`product.php`):** Interactive thumbnail gallery switcher, live stock counter, savings calculation, size & colorway selectors, quantity stepper, parsed JSON technical specifications table, and related products from the same sport category.
- **Persistent Shopping Cart (`cart.php` & `api/cart.php`):** Add items, increment/decrement quantities, remove items, clear bag, apply promo codes (`KICK10` for 10% off any order; `ATHLETE20` for 20% off orders `₹10,000+`), automatic free express delivery over `₹4,999`, and guest-to-user cart merging upon login.
- **Saved Wishlist (`wishlist.php` & `api/wishlist.php`):** Save or remove favorite products across sessions and move items directly to the shopping cart.
- **Athlete Authentication & Profile Dashboard (`login.php`, `register.php`, `profile.php`, `api/auth.php`):** Registration, login (including a 1-click *Auto-Fill Demo* button for evaluators), profile and delivery address management, password update, and complete itemized order history.
- **Transactional Checkout & Order Receipt (`checkout.php` & `api/checkout.php`):** Validates customer and shipping details, supports 4 payment methods (*Credit/Debit Card, UPI/Instant Pay, Net Banking, Cash on Delivery*), records orders inside a MySQL transaction, decrements product stock, and generates a printable Order Confirmation Receipt (`#SK-YYYY-XXXXX`).

---

## 5. Project Structure
```text
sportkick/
├── index.php                        # Home Page (Hero, Brands, 8 Categories, Trending, Deals, New Arrivals)
├── shop.php                         # Product Listing, Category Browser & Multi-Parameter Filter Page
├── product.php                      # Product Details Page (Gallery, Specs Table, Size/Color/Qty, Related)
├── cart.php                         # Shopping Cart Page (Live Qty Stepper, Promo Codes, Subtotal/Total)
├── wishlist.php                     # Saved Wishlist Page
├── checkout.php                     # Checkout Form & Order Confirmation Receipt Page
├── login.php                        # Athlete Sign-In Page (with 1-Click Demo Auto-Fill)
├── register.php                     # Athlete Account Registration Page
├── profile.php                      # Athlete Profile, Address Book, Password Update & Order History
├── logout.php                       # Session Logout Controller
├── config/
│   └── database.php                 # PDO MySQL connection manager + automatic schema/seed bootstrap
├── database/
│   └── sportkick.sql                # Full MySQL DDL Schema (8 tables) + 24 INR products + demo users
├── includes/
│   ├── header.php                   # Sticky SportKick Navbar, Search Bar, 8-Sport Strip & Live Counters
│   ├── footer.php                   # Global Footer, Quick View Modal Container & Toast Notification Stack
│   ├── functions.php                # Core query builders, cart/wishlist sync, ₹ formatting & helpers
│   └── product_card.php             # Reusable Product Card component
├── api/
│   ├── products.php                 # JSON API for product filtering and Quick View modal specifications
│   ├── cart.php                     # JSON API for cart CRUD operations and promo code validation
│   ├── wishlist.php                 # JSON API for wishlist toggle, removal, and move-to-cart
│   ├── auth.php                     # JSON API for registration, login, profile updates & password changes
│   └── checkout.php                 # Transactional JSON API for order creation & stock decrement
├── assets/
│   ├── css/
│   │   └── style.css                # Custom responsive athletic stylesheet (2,177 lines)
│   ├── js/
│   │   └── main.js                  # Client-side AJAX Cart, Wishlist, Quick View & Toast engine
│   └── images/
│       └── placeholder-shoe.svg     # Vector fallback product illustration
├── documentation/
│   └── screenshots/                 # 14 verified screenshots of the live running application
├── tests/
│   └── verify_all.php               # Automated 13-point end-to-end backend & database verification suite
├── PROJECT_DOCUMENTATION_DATA.md    # Complete academic project report data collection document
└── README.md                        # Project overview, setup instructions, and documentation
```

---

## 6. Installation Requirements

### Software Requirements
- **Operating System:** macOS, Windows 10/11, or Linux (Ubuntu/Debian/Fedora)
- **Web Server / Runtime:** PHP 8.0 or higher (tested on PHP 8.5.12 CLI / Built-in Web Server; compatible with Apache/XAMPP/MAMP/WAMP)
- **PHP Extensions:** `pdo`, `pdo_mysql`, `session`, `json`, `mbstring`
- **Database Server:** MySQL 8.0+ or MariaDB 10.5+
- **Web Browser:** Google Chrome, Mozilla Firefox, Apple Safari, or Microsoft Edge

### Hardware Requirements (Minimum)
- **Processor:** Dual-Core 2.0 GHz or higher (Intel Core i3 / AMD Ryzen 3 / Apple Silicon M-series)
- **RAM:** 4 GB minimum (8 GB+ recommended)
- **Disk Space:** 150 MB free storage for project files, database tables, and assets

---

## 7. Setup Instructions & Database Configuration

### Step 1: Clone the Repository
```bash
git clone https://github.com/Mukund220304/sportkick.git
cd sportkick
```

### Step 2: Database Configuration
The database connection is configured in [`config/database.php`](file:///Users/mukund/Desktop/nithilan/config/database.php) using environment variables with sensible local defaults:
- **Host (`DB_HOST`):** `127.0.0.1` (with automatic fallback to `localhost` Unix socket)
- **Port (`DB_PORT`):** `3306`
- **Database Name (`DB_NAME`):** `sportkick_db`
- **Username (`DB_USER`):** `root`
- **Password (`DB_PASS`):** `""` (empty by default for local Homebrew/XAMPP setups)

> **Auto-Bootstrap Feature:** If `sportkick_db` does not exist when the application is first opened, `config/database.php` automatically executes `database/sportkick.sql` to create all 8 tables and seed the 8 sports categories, 24 products, and demo accounts.

To import the SQL schema manually via terminal:
```bash
mysql -u root < database/sportkick.sql
```

---

## 8. How to Run Locally

### Option A: PHP Built-in Server + Homebrew MySQL (macOS / Linux)
```bash
# 1. Start MySQL service
brew services start mysql

# 2. Import schema and sample data
mysql -u root < database/sportkick.sql

# 3. Start the PHP development server on port 8080
php -S 127.0.0.1:8080

# 4. Run the automated verification test suite (optional)
php tests/verify_all.php
```
Open **http://127.0.0.1:8080** in your browser.

### Option B: XAMPP / WAMP / MAMP
1. Copy the `sportkick` directory into `htdocs/sportkick`.
2. Start **Apache** and **MySQL** from the XAMPP/MAMP Control Panel.
3. Open `http://localhost/phpmyadmin` and import `database/sportkick.sql` (or simply visit the site and let the auto-bootstrapper initialize `sportkick_db`).
4. Visit `http://localhost/sportkick/index.php`.

### Demo Credentials & Promo Codes
- **Demo Athlete Login:** `athlete@sportkick.com` / `SportKick123` (or `nithilan@sportkick.com` / `SportKick123`)
- **Promo Codes:** `KICK10` (10% off any order) • `ATHLETE20` (20% off orders `₹10,000+`)

---

## 9. Application Screenshots
All screenshots were captured directly from the live running application and are stored in [`documentation/screenshots/`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots):

| Screenshot | File Path | Description |
| :--- | :--- | :--- |
| **01. Home Hero & Navbar** | [`01_home_page_hero.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/01_home_page_hero.png) | Sticky navbar, 8-sport ribbon, hero banner (*"PUSH BEYOND LIMITS."* / *"Performance starts with what you wear."*), and featured brand tiles |
| **02. Categories & Trending** | [`02_home_categories_and_trending.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/02_home_categories_and_trending.png) | 8 Shop by Sport category cards and Trending Products in `₹` INR |
| **03. Campaign & New Arrivals** | [`03_home_exclusive_deals_and_new_arrivals.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/03_home_exclusive_deals_and_new_arrivals.png) | Promotional Campaign split banner (`KICK10`, `ATHLETE20`) and New Arrivals |
| **15. Best Sellers, About & Newsletter** | [`15_home_bestsellers_about_newsletter.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/15_home_bestsellers_about_newsletter.png) | Best Sellers grid, About SportKick section, and Locker Club Newsletter banner |
| **04. Shop Catalog** | [`04_shop_all_products.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/04_shop_all_products.png) | Full product catalog (`shop.php`) with sticky multi-parameter filter sidebar |
| **05. Search & Filters** | [`05_search_and_filter_results.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/05_search_and_filter_results.png) | Filtered view by Sport Category (*Running*), Price Range (`₹4,000–₹20,000`), and Sort Order |
| **06. Product Details** | [`06_product_details_page.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/06_product_details_page.png) | Multi-angle image gallery, `₹15,999` pricing, size/colorway pills, quantity stepper, and technical specs table |
| **07. Quick View Modal** | [`07_quick_view_modal.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/07_quick_view_modal.png) | Asynchronous Quick View modal displaying gallery, specs, and instant Add to Bag |
| **08. Login Page** | [`08_login_page.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/08_login_page.png) | Athlete sign-in form with 1-click Demo Evaluator Auto-Fill |
| **09. Registration Page** | [`09_register_page.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/09_register_page.png) | New athlete account creation form with validation |
| **10. Athlete Dashboard** | [`10_athlete_profile_dashboard.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/10_athlete_profile_dashboard.png) | Logged-in Athlete Profile, Delivery Address form, Password Update, and MySQL Order History |
| **11. Saved Wishlist** | [`11_wishlist_page.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/11_wishlist_page.png) | Saved favorite products in `wishlist.php` |
| **12. Shopping Cart** | [`12_shopping_cart_page.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/12_shopping_cart_page.png) | Active shopping bag with quantity steppers, `KICK10` promo discount, savings, and `₹` totals |
| **13. Checkout Form** | [`13_checkout_page.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/13_checkout_page.png) | Customer details, delivery address, payment method selection, and order summary |
| **14. Order Confirmation** | [`14_order_confirmation_receipt.png`](file:///Users/mukund/Desktop/nithilan/documentation/screenshots/14_order_confirmation_receipt.png) | Verified Order Confirmation Receipt (`#SK-2026-84920`) with itemized breakdown |

---

## 10. Future Scope
- **Dedicated Admin Dashboard:** Web-based back-office panel for adding/editing products, uploading images, managing inventory levels, and updating order statuses (`Confirmed` -> `Shipped` -> `Delivered`).
- **Live Payment Gateway Integration:** Razorpay / Stripe webhook integration for real-time UPI QR, Net Banking, and card authorization.
- **Verified Customer Reviews & Ratings:** Allow authenticated buyers to submit star ratings, fit feedback, and photo reviews on purchased items.
- **Automated Email & SMS Notifications:** Transactional order confirmation and shipment tracking alerts via SMTP / Twilio.
- **Size Recommendation Engine:** Interactive foot-length (cm) and sport-discipline fit calculator.
