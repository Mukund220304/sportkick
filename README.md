# SportKick — Pro Sportswear & Performance Footwear E-Commerce Platform

> **"GEAR UP. SHOW UP. NEVER STOP."**  
> A production-inspired, full-stack sportswear and athletic footwear e-commerce web application built with **HTML5, Custom CSS3, Vanilla JavaScript (ES6+), PHP 8+, and MySQL**.

---

## 1. Project Overview
**SportKick** is a dedicated e-commerce platform for high-performance sports shoes and technical sportswear across **8 competitive athletic disciplines**:
1. **Running**
2. **Basketball**
3. **Football**
4. **Cricket**
5. **Tennis**
6. **Badminton**
7. **Gym & Training**
8. **Hiking**

Designed with visual inspiration from **Nike, Adidas, Puma, and Foot Locker**, SportKick pairs a high-contrast Carbon Black (`#09090B`), Studio Alabaster (`#F5F5F7`), Pure White (`#FFFFFF`), and Electric Orange (`#FF5500`) design system with a relational MySQL backend powered by PDO prepared statements.

---

## 2. Technology Stack
- **Frontend:** HTML5, Custom CSS3 (`assets/css/style.css` — responsive grid, sticky glassmorphism header, athletic typography with *Barlow Condensed* & *Plus Jakarta Sans*), Vanilla JavaScript ES6+ (`assets/js/main.js`)
- **Backend:** PHP 8+ (Modular architecture, PDO database abstraction, RESTful JSON endpoints, session management)
- **Database:** MySQL (`sportkick_db` — 8 normalized relational tables with foreign keys and indexes)
- **Security:** `password_hash()` / `password_verify()` (bcrypt), PDO Prepared Statements (`PDO::ATTR_EMULATE_PREPARES => false`), output escaping (`htmlspecialchars`), server-side validation, and session synchronization
- **Version Control:** Git

---

## 3. Project Folder Structure
```text
sportkick/
├── index.php                  # Home Page (Hero banner, Brands, 8 Categories, Trending, Deals, New Arrivals)
├── shop.php                   # Product Listing, Category Browser & Multi-Parameter Search/Filter Page
├── product.php                # Product Details Page (Multi-angle gallery, Specs table, Size/Color/Qty, Related)
├── cart.php                   # Shopping Cart Page (Live Qty stepper, Remove, Promo Codes, Subtotal/Total)
├── wishlist.php               # Saved Wishlist Page (Instant toggle, Move to Cart)
├── checkout.php               # Checkout Form & Order Confirmation Receipt Page
├── login.php                  # Athlete Sign-In Page (with 1-click Demo Evaluator Auto-Fill)
├── register.php               # Athlete Account Registration Page
├── profile.php                # Profile Management, Address Book, Password Update & Order History
├── logout.php                 # Session Logout Controller
├── config/
│   └── database.php           # PDO MySQL connection manager + automatic schema/seed bootstrap
├── database/
│   └── sportkick.sql          # Full MySQL DDL Schema (8 tables) + 24 realistic products + demo users
├── includes/
│   ├── header.php             # Sticky SportKick Navbar, Live Search, 8-Sport Ribbon & Counters
│   ├── footer.php             # Global Footer, Quick View Modal Container & Toast Notification Stack
│   ├── functions.php          # Core query builders, cart/wishlist sync, formatting & security helpers
│   └── product_card.php       # Reusable Product Card component (Image, Badges, Sizes, Quick View, Cart/Wishlist)
├── api/
│   ├── products.php           # JSON API for product filtering and Quick View modal specifications
│   ├── cart.php               # JSON API for cart add/update/remove/clear and promo code validation
│   ├── wishlist.php           # JSON API for wishlist toggle, removal, and move-to-cart
│   ├── auth.php               # JSON API for registration, login, profile updates, and password changes
│   └── checkout.php           # Transactional JSON API for order creation & stock decrement
├── assets/
│   ├── css/
│   │   └── style.css          # Complete custom responsive athletic stylesheet
│   ├── js/
│   │   └── main.js            # Interactive AJAX Cart, Wishlist, Quick View Modal & Toast notifications
│   └── images/
│       └── placeholder-shoe.svg # Vector fallback illustration
├── tests/
│   └── verify_all.php         # Automated 13-point end-to-end backend & database verification script
└── README.md                  # Project documentation, setup guide, and testing report
```

---

## 4. Database Schema (`sportkick_db`)
Defined in [`database/sportkick.sql`](file:///Users/mukund/Desktop/nithilan/database/sportkick.sql):
| Table | Purpose |
| :--- | :--- |
| `users` | Registered athletes (`id`, `full_name`, `email`, `password_hash`, `phone`, `address`, `city`, `state`, `zip_code`, `created_at`) |
| `categories` | 8 dedicated sports categories (`Running`, `Basketball`, `Football`, `Cricket`, `Tennis`, `Badminton`, `Gym & Training`, `Hiking`) |
| `products` | 24 realistic sports shoes & apparel items (`name`, `brand`, `sku`, `price`, `original_price`, `discount_percent`, `rating`, `sizes`, `colors`, `specifications` JSON, `image_main`, `image_gallery` JSON, `stock`, flags) |
| `cart` | Persistent user and guest shopping carts (`id`, `user_id`, `session_id`) |
| `cart_items` | Line items in a cart (`cart_id`, `product_id`, `size`, `color`, `quantity`) |
| `wishlist` | Saved favorite products (`id`, `user_id`, `session_id`, `product_id`) |
| `orders` | Confirmed customer orders (`order_number`, `user_id`, shipping details, `payment_method`, `subtotal`, `discount_amount`, `shipping_fee`, `total_amount`, `order_status`) |
| `order_items` | Individual line items tied to an order (`order_id`, `product_id`, `product_name`, `brand`, `size`, `color`, `unit_price`, `quantity`, `line_total`) |

---

## 5. How to Run the Project Locally

### Option A: Quick Start with PHP Built-in Server & MySQL (Recommended)
1. **Start MySQL Server:**
   ```bash
   brew services start mysql
   ```
2. **Import the Database Schema & Sample Data:**
   ```bash
   mysql -u root < database/sportkick.sql
   ```
   *(Note: `config/database.php` also includes an auto-bootstrapper that automatically creates and seeds `sportkick_db` from `database/sportkick.sql` on first request if the database does not exist yet!)*
3. **Start the PHP Server:**
   ```bash
   php -S 127.0.0.1:8080
   ```
4. **Open in Browser:**
   Visit **http://127.0.0.1:8080**

### Option B: Using XAMPP / MAMP / WAMP
1. Copy the project folder into `htdocs/sportkick` (XAMPP) or `MAMP/htdocs/sportkick`.
2. Open **phpMyAdmin** (`http://localhost/phpmyadmin`) and import `database/sportkick.sql`.
3. Visit `http://localhost/sportkick/index.php`.

---

## 6. Demo Credentials & Promo Codes
- **Demo Athlete Account:**
  - **Email:** `athlete@sportkick.com`
  - **Password:** `SportKick123`
  *(A 1-click **"Auto-Fill Demo"** button is available directly on `login.php` for quick evaluation)*
- **Active Promo Codes (Test in Cart / Checkout):**
  - `KICK10` — **10% OFF** any order
  - `ATHLETE20` — **20% OFF** orders of ₹10,000 or more

---

## 7. Testing Report
Run the automated verification suite at any time via:
```bash
php tests/verify_all.php
```

| # | Test Case | Result | Details |
| :- | :--- | :---: | :--- |
| 1 | **MySQL Database Schema (8 Tables)** | **PASS** | Verified `users`, `categories`, `products`, `cart`, `cart_items`, `wishlist`, `orders`, `order_items` |
| 2 | **8 Mandatory Sports Categories** | **PASS** | Verified Running, Basketball, Football, Cricket, Tennis, Badminton, Gym & Training, Hiking |
| 3 | **Products Populated in MySQL** | **PASS** | 24 realistic footwear & apparel products loaded from MySQL (in ₹ INR) |
| 4 | **Search by Product Name** | **PASS** | Query `"Alphafly"` returns *Nike Air Zoom Alphafly Next% 3 Proto* |
| 5 | **Filter by Brand** | **PASS** | Brand `"Adidas"` returns 5 matching Adidas products |
| 6 | **Filter by Sport Category** | **PASS** | Category `"badminton"` returns 3 Yonex & Asics court products |
| 7 | **Filter by Price Range** | **PASS** | Range `₹8,000 - ₹14,000` returns 10 products strictly within bounds |
| 8 | **Filter by Size** | **PASS** | Size `"UK 9"` matches 16 footwear products via `FIND_IN_SET` |
| 9 | **Filter by Color** | **PASS** | Color `"Orange"` matches 13 products |
| 10 | **User Registration & Session Auth** | **PASS** | Bcrypt password hash created, verified, and session established |
| 11 | **Shopping Cart & Promo Engine** | **PASS** | Added qty 2, applied `KICK10` (10% off), verified subtotal & total calculation |
| 12 | **Wishlist Operations** | **PASS** | Toggle save/remove and fetch verified against `wishlist` table |
| 13 | **Checkout & Order Persistence** | **PASS** | Transactional `orders` + `order_items` creation and stock update verified |

