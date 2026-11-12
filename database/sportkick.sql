-- ============================================================================
-- SportKick E-Commerce Platform - Complete MySQL Database Schema & Seed Data
-- Database: sportkick_db
-- Tables: users, categories, products, cart, cart_items, wishlist, orders, order_items
-- ============================================================================

CREATE DATABASE IF NOT EXISTS sportkick_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sportkick_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- 1. Table: users
-- ----------------------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30) DEFAULT '',
    address VARCHAR(255) DEFAULT '',
    city VARCHAR(100) DEFAULT '',
    state VARCHAR(100) DEFAULT '',
    zip_code VARCHAR(20) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 2. Table: categories
-- ----------------------------------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    tagline VARCHAR(180) NOT NULL,
    description TEXT,
    image_url VARCHAR(500) NOT NULL,
    featured TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 3. Table: products
-- ----------------------------------------------------------------------------
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    brand VARCHAR(80) NOT NULL,
    sku VARCHAR(60) NOT NULL UNIQUE,
    product_type VARCHAR(40) NOT NULL DEFAULT 'Footwear',
    price DECIMAL(10, 2) NOT NULL,
    original_price DECIMAL(10, 2) NOT NULL,
    discount_percent INT NOT NULL DEFAULT 0,
    rating DECIMAL(3, 2) NOT NULL DEFAULT 4.50,
    reviews_count INT NOT NULL DEFAULT 0,
    sizes VARCHAR(255) NOT NULL,
    colors VARCHAR(255) NOT NULL,
    color_primary VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    specifications TEXT NOT NULL,
    image_main VARCHAR(500) NOT NULL,
    image_gallery TEXT NOT NULL,
    is_trending TINYINT(1) DEFAULT 0,
    is_new_arrival TINYINT(1) DEFAULT 0,
    is_deal TINYINT(1) DEFAULT 0,
    stock INT NOT NULL DEFAULT 25,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_products_category (category_id),
    INDEX idx_products_brand (brand),
    INDEX idx_products_price (price),
    INDEX idx_products_color (color_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 4. Table: cart
-- ----------------------------------------------------------------------------
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(128) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_cart_user (user_id),
    INDEX idx_cart_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 5. Table: cart_items
-- ----------------------------------------------------------------------------
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    size VARCHAR(30) NOT NULL DEFAULT 'UK 9',
    color VARCHAR(50) NOT NULL DEFAULT 'Black',
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uq_cart_product_variant (cart_id, product_id, size, color)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 6. Table: wishlist
-- ----------------------------------------------------------------------------
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(128) DEFAULT '',
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_wishlist_user (user_id),
    INDEX idx_wishlist_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 7. Table: orders
-- ----------------------------------------------------------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(40) NOT NULL UNIQUE,
    user_id INT NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(30) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_state VARCHAR(100) NOT NULL,
    shipping_zip VARCHAR(20) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(40) NOT NULL DEFAULT 'Paid',
    order_status VARCHAR(40) NOT NULL DEFAULT 'Confirmed',
    subtotal DECIMAL(10, 2) NOT NULL,
    discount_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    shipping_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_orders_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 8. Table: order_items
-- ----------------------------------------------------------------------------
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,
    product_name VARCHAR(180) NOT NULL,
    brand VARCHAR(80) NOT NULL,
    size VARCHAR(30) NOT NULL,
    color VARCHAR(50) NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    line_total DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- SEED DATA: Demo Users
-- Default password for both accounts: SportKick123
-- ============================================================================
INSERT INTO users (full_name, email, password_hash, phone, address, city, state, zip_code) VALUES
('Aarav Sharma', 'athlete@sportkick.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+91 98765 43210', '42 Olympic Residency, Indiranagar', 'Bengaluru', 'Karnataka', '560038'),
('Nithilan K', 'nithilan@sportkick.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+91 98450 11223', '18 Champions Avenue, Anna Nagar', 'Chennai', 'Tamil Nadu', '600040');

-- ============================================================================
-- SEED DATA: 8 Sports Categories
-- ============================================================================
INSERT INTO categories (id, name, slug, tagline, description, image_url, featured, display_order) VALUES
(1, 'Running', 'running', 'Engineered Carbon & Nitro Propulsion', 'Marathon-grade road racers, daily cushioned trainers, and breathable performance running apparel built for relentless pace.', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=85', 1, 1),
(2, 'Basketball', 'basketball', 'Explosive Court Lockdown & Air Zoom', 'High-top and low-profile signature court shoes, moisture-wicking jerseys, and impact-absorbing cushioning for vertical dominance.', 'https://images.unsplash.com/photo-1579338559194-a162d19bf842?auto=format&fit=crop&w=900&q=85', 1, 2),
(3, 'Football', 'football', 'Precision Touch & Firm-Ground Traction', 'Elite FG/AG football boots, aerodynamic match kits, and grip-engineered cleats designed for lethal acceleration on the pitch.', 'https://images.unsplash.com/photo-1511886929837-354d827aae26?auto=format&fit=crop&w=900&q=85', 1, 3),
(4, 'Cricket', 'cricket', 'All-Day Pitch Stability & Spike Grip', 'Professional rubber-stud and metal-spike cricket footwear, ventilated pro jerseys, and high-impact compression layers.', 'https://images.unsplash.com/photo-1531415074968-036ba1b575da?auto=format&fit=crop&w=900&q=85', 1, 4),
(5, 'Tennis', 'tennis', 'Lateral Hard-Court & Clay Durability', 'Reinforced toe-drag tennis shoes and ultra-lightweight court apparel engineered for rapid baseline slides and split-steps.', 'https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=900&q=85', 1, 5),
(6, 'Badminton', 'badminton', 'Non-Marking Gum Sole & Featherlight Agility', 'Hexagrip indoor court shoes and sweat-dissipating tournament tops built for explosive lunges and rapid directional changes.', 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=900&q=85', 1, 6),
(7, 'Gym & Training', 'gym-training', 'Flat-Heel Barbell Stability & HIIT Flex', 'Dual-density cross-trainers, heavy-lift flat soles, and four-way stretch compression gear for uncompromising strength sessions.', 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=900&q=85', 1, 7),
(8, 'Hiking', 'hiking', 'GORE-TEX Weatherproofing & Vibram Lug Grip', 'All-terrain trail runners, mid-cut alpine trekkers, and ripstop outdoor shells built to conquer wet rock and steep ascents.', 'https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=900&q=85', 1, 8);

-- ============================================================================
-- SEED DATA: 24 Realistic Sportswear & Footwear Products (3 per Category)
-- ============================================================================
INSERT INTO products (
    id, category_id, name, slug, brand, sku, product_type,
    price, original_price, discount_percent, rating, reviews_count,
    sizes, colors, color_primary, description, specifications,
    image_main, image_gallery, is_trending, is_new_arrival, is_deal, stock
) VALUES
-- 1. RUNNING
(1, 1, 'Nike Air Zoom Alphafly Next% 3 Proto', 'nike-air-zoom-alphafly-next-3', 'Nike', 'SK-RUN-001', 'Footwear',
 189.99, 229.99, 17, 4.90, 342,
 'UK 7,UK 8,UK 9,UK 10,UK 11', 'Orange,White,Black', 'Orange',
 'Fine-tuned for marathon speed, the Air Zoom Alphafly Next% 3 pushes you beyond what you thought possible. Dual Air Zoom units in the forefoot combine with full-length ZoomX foam and a full-length carbon fiber Flyplate to deliver unprecedented energy return on road surfaces.',
 '{"Upper":"Atomknit 3.0 Breathable Mesh","Midsole":"Full-Length ZoomX Foam + Carbon Flyplate","Outsole":"Fast-Shot High-Abrasion Rubber","Heel-to-Toe Drop":"8 mm","Weight":"218g (Men UK 9)","Surface":"Road / Marathon Racing"}',
 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=1000&q=85"]',
 1, 1, 1, 32),

(2, 1, 'Adidas Adizero Boston 12 Carbon', 'adidas-adizero-boston-12-carbon', 'Adidas', 'SK-RUN-002', 'Footwear',
 139.99, 169.99, 18, 4.75, 218,
 'UK 6,UK 7,UK 8,UK 9,UK 10,UK 11', 'Black,White,Blue', 'Black',
 'Built for tempo runs and race-day preparation, the Adizero Boston 12 merges ultra-light Lightstrike Pro cushioning with glass-fiber ENERGYRODS 2.0 that limit energy loss underfoot. Finished with a Continental rubber outsole for relentless wet and dry grip.',
 '{"Upper":"Engineered Recycled Mesh","Midsole":"Lightstrike Pro + ENERGYRODS 2.0","Outsole":"Continental Grip Rubber","Heel-to-Toe Drop":"7 mm","Weight":"267g (UK 9)","Surface":"Road / Tempo Training"}',
 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=1000&q=85"]',
 1, 0, 0, 45),

(3, 1, 'Puma Deviate NITRO Elite Singlet & Aero Split Set', 'puma-deviate-nitro-elite-singlet', 'Puma', 'SK-RUN-003', 'Apparel',
 64.99, 84.99, 24, 4.65, 114,
 'S,M,L,XL', 'Black,Orange', 'Black',
 'Ultra-featherweight race day running singlet paired with laser-perforated 3-inch split shorts. Constructed with dryCELL moisture-wicking micro-knit and bonded chafe-free seams for high-humidity distance running.',
 '{"Fabric":"88% Recycled Polyester, 12% Elastane","Technology":"Puma dryCELL + Laser Vent Zones","Fit":"Athletic Aero Race Fit","Reflectivity":"360-Degree Night Run Accents","Care":"Machine Wash Cold"}',
 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1556906781-9a412961c28c?auto=format&fit=crop&w=1000&q=85"]',
 0, 1, 1, 50),

-- 2. BASKETBALL
(4, 2, 'Nike LeBron XXI Electric Court', 'nike-lebron-xxi-electric-court', 'Nike', 'SK-BB-001', 'Footwear',
 169.99, 209.99, 19, 4.85, 289,
 'UK 8,UK 9,UK 10,UK 11,UK 12', 'Red,Black,White', 'Red',
 'Designed for explosive open-floor attacks and rim-rattling ascents, the LeBron XXI features a 360-degree zonal cabling system wrapped in a vented dimensional shell, paired with a bottom-loaded Zoom Turbo unit and 13mm heel Zoom Air bag.',
 '{"Upper":"360-Degree Zonal Cable Containment Mesh","Midsole":"Cushlon 2.0 + Forefoot Zoom Turbo","Outsole":"Multi-Directional Herringbone Rubber","Collar":"Low-Cut Padded Achilles Lock","Court Type":"Indoor Hardwood & Outdoor Tartan"}',
 'https://images.unsplash.com/photo-1579338559194-a162d19bf842?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1579338559194-a162d19bf842?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=1000&q=85"]',
 1, 1, 0, 28),

(5, 2, 'Puma All-Pro NITRO Court Venom', 'puma-all-pro-nitro-court-venom', 'Puma', 'SK-BB-002', 'Footwear',
 119.99, 149.99, 20, 4.70, 164,
 'UK 7,UK 8,UK 9,UK 10,UK 11', 'White,Orange,Black', 'White',
 'Combining nitrogen-injected NITRO SQD foam with a targeted cord lock-down lacing structure, the All-Pro NITRO delivers soft inner-core impact protection and a firm outer rim for lethal step-back crossovers.',
 '{"Upper":"Engineered Multi-Zone Knit","Midsole":"Dual-Density NITRO SQD Foam","Outsole":"High-Abrasion Sticky Rubber","Support":"TPU Heel Counter","Court Type":"Indoor / Pro Court"}',
 'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1579338559194-a162d19bf842?auto=format&fit=crop&w=1000&q=85"]',
 0, 0, 1, 36),

(6, 2, 'Under Armour Curry Splash Pro Mesh Jersey', 'under-armour-curry-splash-pro-jersey', 'Under Armour', 'SK-BB-003', 'Apparel',
 54.99, 69.99, 21, 4.60, 97,
 'S,M,L,XL,XXL', 'Black,Red,White', 'Black',
 'Heavyweight double-knit mesh basketball jersey engineered for zero cling during four-quarter battles. Features ribbed armholes, anti-odor finish, and side-hem split vents for unrestricted shooting motion.',
 '{"Material":"100% Textured Knit Polyester","Breathability":"Open-Hole Micro Mesh","Fit":"Loose Court Fit","Wicking":"UA Tech Fast-Dry Fibers","Care":"Machine Wash Cold, Line Dry"}',
 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1519861531473-9200262188bf?auto=format&fit=crop&w=1000&q=85"]',
 0, 1, 0, 60),

-- 3. FOOTBALL
(7, 3, 'Nike Mercurial Superfly 9 Elite FG', 'nike-mercurial-superfly-9-elite-fg', 'Nike', 'SK-FB-001', 'Footwear',
 219.99, 269.99, 19, 4.95, 412,
 'UK 6,UK 7,UK 8,UK 9,UK 10,UK 11', 'Orange,Black,White', 'Orange',
 'Instantly tilt the pitch with the Mercurial Superfly 9 Elite. Equipped with a football-specific 3/4-length Zoom Air unit seated directly in the soleplate and a Vaporposite+ grippy grid mesh upper for high-velocity ball control.',
 '{"Upper":"Vaporposite+ Grippy Chevron Mesh + Flyknit Collar","Soleplate":"3/4 Zoom Air Unit + Tri-Star FG Studs","Surface":"Firm Natural Grass (FG)","Weight":"192g (UK 9)","Insole":"Cushioned Anti-Slip Grip Liner"}',
 'https://images.unsplash.com/photo-1511886929837-354d827aae26?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1511886929837-354d827aae26?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1560272564-c83b66b1ad12?auto=format&fit=crop&w=1000&q=85"]',
 1, 1, 1, 22),

(8, 3, 'Adidas Predator Accuracy.1 Low FG', 'adidas-predator-accuracy-1-low-fg', 'Adidas', 'SK-FB-002', 'Footwear',
 174.99, 219.99, 20, 4.80, 275,
 'UK 7,UK 8,UK 9,UK 10,UK 11', 'Black,Red,White', 'Black',
 'Top-corner curl meets effortless first touch. The Predator Accuracy.1 packs High Definition Grip rubber elements across the strike zone of its soft HybridTouch upper, underpinned by a weighted Power Facet in the split outsole for ferocious ball striking.',
 '{"Upper":"HybridTouch Microfiber + HD Rubber Strike Fins","Outsole":"FacetFrame Split Tooling with Power Facet","Collar":"Two-Piece Primeknit Low Collar","Stud Config":"Firm Ground Bladed Studs","Weight":"215g"}',
 'https://images.unsplash.com/photo-1560272564-c83b66b1ad12?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1560272564-c83b66b1ad12?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1511886929837-354d827aae26?auto=format&fit=crop&w=1000&q=85"]',
 1, 0, 0, 30),

(9, 3, 'Puma ULTRA Match Pro Training Kit', 'puma-ultra-match-pro-training-kit', 'Puma', 'SK-FB-003', 'Apparel',
 59.99, 79.99, 25, 4.65, 138,
 'S,M,L,XL', 'Blue,Black,White', 'Blue',
 'Engineered for high-tempo pitch sessions, the ULTRA Match Pro Training Jersey & Short set uses ultra-lightweight jacquard fabric and underarm mesh gussets to keep core temperature regulated during 90-minute drills.',
 '{"Fabric":"100% Recycled Jacquard Knit","Fit":"Slim Pro Pitch Cut","Ventilation":"Laser-Cut Back & Underarm Panels","Moisture Control":"dryCELL Hydrophobic Finish","Includes":"Match Top + Zip-Pocket Training Shorts"}',
 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1511886929837-354d827aae26?auto=format&fit=crop&w=1000&q=85"]',
 0, 1, 1, 48),

-- 4. CRICKET
(10, 4, 'Asics GEL-Peake 2 Pro Cricket Studs', 'asics-gel-peake-2-pro-cricket', 'Asics', 'SK-CR-001', 'Footwear',
 109.99, 134.99, 19, 4.80, 192,
 'UK 7,UK 8,UK 9,UK 10,UK 11', 'White,Blue', 'White',
 'Developed with international cricketers for hard turf and synthetic wickets, the GEL-Peake 2 features synthetic leather overlays for midfoot lockdown, rearfoot GEL impact technology, and a dimpled rubber-cleat outsole for explosive crease sprints.',
 '{"Upper":"Seamless Synthetic Leather + Toe Bumper","Midsole":"Rearfoot GEL + Solyte Cushioning","Outsole":"Multi-Studded Hard Wicket Rubber","Support":"TRUSSTIC Midfoot Torsion Bridge","Discipline":"Batting / Fielding / All-Round"}',
 'https://images.unsplash.com/photo-1531415074968-036ba1b575da?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1531415074968-036ba1b575da?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=1000&q=85"]',
 1, 0, 0, 40),

(11, 4, 'Adidas 22YDS Boost Bowling Spikes', 'adidas-22yds-boost-bowling-spikes', 'Adidas', 'SK-CR-002', 'Footwear',
 149.99, 184.99, 19, 4.85, 156,
 'UK 8,UK 9,UK 10,UK 11,UK 12', 'White,Orange', 'White',
 'Engineered specifically for fast bowlers hitting the delivery stride under extreme load. Combines full-length BOOST midsole cushioning with a bevelled heel, midfoot lockdown strap, and an 11-spike TPU plate.',
 '{"Upper":"Ripstop Mesh with TPU Ankle Lockdown Strap","Midsole":"Full-Length Responsive BOOST","Outsole":"11-Pin Removable Metal/TPU Spike Plate","Toe Guard":"Moulded Abrasion Shield","Discipline":"Fast & Medium Pace Bowling"}',
 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1531415074968-036ba1b575da?auto=format&fit=crop&w=1000&q=85"]',
 0, 1, 1, 24),

(12, 4, 'Puma evoSPEED Pro Test & ODI Match Shirt', 'puma-evospeed-pro-cricket-shirt', 'Puma', 'SK-CR-003', 'Apparel',
 49.99, 64.99, 23, 4.70, 108,
 'S,M,L,XL,XXL', 'Blue,White', 'Blue',
 'Worn under blazing stadium lights, the evoSPEED Pro Cricket Shirt combines UV-50+ sun protection with articulated raglan sleeves and honeycomb mesh back panelling for unrestricted bowling and batting arcs.',
 '{"Fabric":"92% Polyester, 8% Elastane Honeycomb Knit","Protection":"UPF 50+ Solar Shield","Collar":"Concealed Snap-Button Polo Collar","Ventilation":"Full Mesh Spine Zone","Fit":"Ergonomic Cricketer Cut"}',
 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1531415074968-036ba1b575da?auto=format&fit=crop&w=1000&q=85"]',
 0, 0, 1, 55),

-- 5. TENNIS
(13, 5, 'NikeCourt Air Zoom Vapor Pro 2 Hard Court', 'nikecourt-air-zoom-vapor-pro-2-hc', 'Nike', 'SK-TN-001', 'Footwear',
 129.99, 159.99, 19, 4.80, 231,
 'UK 6,UK 7,UK 8,UK 9,UK 10,UK 11', 'White,Black,Orange', 'White',
 'Built for interminable baseline rallies and lightning net approaches, the Vapor Pro 2 brings you lower to the court with a forefoot Air Zoom unit, full-length footframe for hard lateral brakes, and a data-informed herringbone outsole that grips without preventing slides.',
 '{"Upper":"Dual-Layer Reinforced Mesh + Inner Sleeve","Midsole":"Forefoot Zoom Air + Low-Profile Foam","Outsole":"Data-Informed Herringbone Hard-Court Rubber","Lateral Support":"Full-Length TPU Footframe","Surface":"Hard Court / All Court"}',
 'https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1560072810-1cffb09faf0f?auto=format&fit=crop&w=1000&q=85"]',
 1, 1, 0, 34),

(14, 5, 'Asics Court FF 3 Novak Pro Edition', 'asics-court-ff-3-novak-pro', 'Asics', 'SK-TN-002', 'Footwear',
 164.99, 194.99, 15, 4.95, 310,
 'UK 7,UK 8,UK 9,UK 10,UK 11', 'Blue,White,Orange', 'Blue',
 'Co-engineered with Novak Djokovic to maximize contact area on extreme sliding recoveries. Features a three-piece TWISTRUSS outsole, MONO-SOCK construction, and FlyteFoam + GEL cushioning for elite tournament endurance.',
 '{"Upper":"MONO-SOCK Fit System + PGuard Toe Shield","Midsole":"FLYTEFOAM + Forefoot/Rearfoot GEL","Outsole":"AHARPLUS 3-Piece Split Outsole","Stability":"TWISTRUSS 2-Piece Bridge","Surface":"All-Court Tournament"}',
 'https://images.unsplash.com/photo-1560072810-1cffb09faf0f?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1560072810-1cffb09faf0f?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=1000&q=85"]',
 1, 0, 1, 19),

(15, 5, 'Adidas Barricade Grand Slam Polo & Court Short', 'adidas-barricade-grand-slam-kit', 'Adidas', 'SK-TN-003', 'Apparel',
 69.99, 89.99, 22, 4.70, 88,
 'S,M,L,XL', 'White,Black,Green', 'White',
 'Engineered with FreeLift shoulder construction so the hem never rides up during overhead serves and smashes. HEAT.RDY air-cooling yarns keep you composed during fifth-set tiebreaks.',
 '{"Technology":"Adidas HEAT.RDY + FreeLift Pattern","Fabric":"100% Parley Ocean Plastic Recycled Doubleknit","Pockets":"Deep Ball-Lock Pleated Pockets","Collar":"Bonded Low-Profile Blade Collar","Fit":"Slim Athletic Court Fit"}',
 'https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=1000&q=85"]',
 0, 1, 0, 42),

-- 6. BADMINTON
(16, 6, 'Yonex Power Cushion 65 Z3 Indoor Pro', 'yonex-power-cushion-65-z3-indoor', 'Yonex', 'SK-BD-001', 'Footwear',
 134.99, 159.99, 16, 4.95, 376,
 'UK 6,UK 7,UK 8,UK 9,UK 10,UK 11', 'White,Orange,Black', 'White',
 'The gold standard of BWF World Tour badminton footwear. A seamless Double Russel Mesh upper pairs with Yonex Power Cushion+ (which rebounds a raw egg dropped from 12 meters without breaking) and a Radial Blade non-marking gum rubber sole.',
 '{"Upper":"Seamless Durable Skin Light + Double Russel Mesh","Midsole":"Power Cushion+ & Feather Bounce Foam","Outsole":"Radial Blade Non-Marking Natural Gum Rubber","Shank":"3D Power Graphite Sheet","Court Type":"Indoor PVC / Wooden Badminton Courts"}',
 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=1000&q=85"]',
 1, 1, 0, 29),

(17, 6, 'Asics GEL-Rocket 11 Indoor Gum Court', 'asics-gel-rocket-11-indoor-gum', 'Asics', 'SK-BD-002', 'Footwear',
 84.99, 104.99, 19, 4.75, 249,
 'UK 6,UK 7,UK 8,UK 9,UK 10,UK 11', 'Black,Orange,White', 'Black',
 'Versatile indoor court shoe delivering rock-solid lateral stability and flexible forefoot pivot grooves for badminton and squash players. Features a TPU TRUSSTIC application in the midsole to resist over-twisting during lunges.',
 '{"Upper":"Breathable Open Mesh + Synthetic Overlays","Midsole":"EVA + Rearfoot GEL Cushioning Unit","Outsole":"Non-Marking Gum Rubber with Pivot Circle","Support":"Heel Wrap-Up Outrigger","Weight":"295g (UK 9)"}',
 'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=1000&q=85"]',
 0, 0, 1, 52),

(18, 6, 'Yonex Tournament VeryCOOL Dry Jersey', 'yonex-tournament-verycool-dry-jersey', 'Yonex', 'SK-BD-003', 'Apparel',
 44.99, 59.99, 25, 4.80, 143,
 'S,M,L,XL', 'Red,Black,Blue', 'Red',
 'Impregnated with Xylitol VeryCOOL technology that absorbs heat and perspiration to lower body surface temperature by 3°C during intense three-game badminton matches.',
 '{"Cooling Tech":"Yonex VeryCOOL Xylitol Fiber","Sleeve Cut":"Precision Move 165-Degree Arm Swing","Anti-Static":"Carbon-Impregnated Conductive Thread","Fabric":"100% Micro-Polyester","Fit":"Asian / Pro Tournament Fit"}',
 'https://images.unsplash.com/photo-1556906781-9a412961c28c?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1556906781-9a412961c28c?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=1000&q=85"]',
 0, 1, 1, 64),

-- 7. GYM & TRAINING
(19, 7, 'Nike Metcon 9 AMP Cross-Training Shoe', 'nike-metcon-9-amp-cross-training', 'Nike', 'SK-GYM-001', 'Footwear',
 139.99, 169.99, 18, 4.90, 465,
 'UK 6,UK 7,UK 8,UK 9,UK 10,UK 11,UK 12', 'Black,Orange,Grey', 'Black',
 'The gold standard for heavy barbell squats, rope climbs, and high-intensity conditioning. A larger Hyperlift plate in the heel provides unshakeable stability under heavy loads, while dual-density React foam keeps box jumps and sprints responsive.',
 '{"Heel Stability":"Expanded Hyperlift Wedge Plate","Midsole":"Dual-Density Firm Outer / Soft Inner Foam","Sidewall":"Extended Rubber Rope-Wrap Arch Guard","Upper":"Haptic 3D Print Breathable Mesh","Heel-to-Toe Drop":"4 mm Flat Lifting Platform"}',
 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=1000&q=85"]',
 1, 1, 1, 38),

(20, 7, 'Under Armour Project Rock 6 BSR Trainer', 'under-armour-project-rock-6-bsr', 'Under Armour', 'SK-GYM-002', 'Footwear',
 129.99, 159.99, 19, 4.80, 294,
 'UK 7,UK 8,UK 9,UK 10,UK 11', 'Grey,Black,Orange', 'Grey',
 'Built and tested by Dwayne Johnson for the hardest workers in the room. UA TriBase maximizes ground contact, promotes natural forefoot upward motion, and provides flexibility to grip the floor during deadlifts and sled pushes.',
 '{"Upper":"UA Clone Auxetic Upper Adapts to Foot Shape","Midsole":"UA HOVR Energy Return Cushioning","Outsole":"UA TriBase 3-Point Ground Contact","Heel":"External TPU Heel Clips for Squat Lock","Fit":"Snug Compression Collar"}',
 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=1000&q=85"]',
 1, 0, 0, 31),

(21, 7, 'Under Armour HeatGear Armour Pro Compression Set', 'under-armour-heatgear-compression-set', 'Under Armour', 'SK-GYM-003', 'Apparel',
 59.99, 79.99, 25, 4.75, 211,
 'S,M,L,XL,XXL', 'Black,Grey', 'Black',
 'Second-skin compression top and 3/4 training legging combo designed to lock muscles in place, boost blood flow recovery, and prevent barbell abrasion during clean-and-jerk sessions.',
 '{"Fabric":"79% Polyester, 21% Elastane Super-Light HeatGear","Stitching":"Ergonomic Flatlock Seams Outside High-Abrasion Zones","Ventilation":"Stretch-Mesh Gusset & Back Panels","Fit":"Ultra-Tight Second-Skin Compression","Odor Control":"Anti-Microbial Finish"}',
 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=1000&q=85"]',
 0, 1, 1, 70),

-- 8. HIKING
(22, 8, 'Salomon Speedcross 6 GORE-TEX Trail Shoe', 'salomon-speedcross-6-gore-tex-trail', 'Salomon', 'SK-HK-001', 'Footwear',
 154.99, 189.99, 18, 4.90, 388,
 'UK 7,UK 8,UK 9,UK 10,UK 11,UK 12', 'Green,Black,Orange', 'Green',
 'Lighter, grippier, and faster mud shedding than ever before. The Speedcross 6 GORE-TEX combines a waterproof breathable membrane with 5mm Mud Contagrip chevron lugs, SensiFit foot cradling, and Kevlar Quicklace one-pull tightening.',
 '{"Membrane":"GORE-TEX Waterproof & Breathable","Outsole":"Mud Contagrip with 5mm Deep Chevron Lugs","Lacing":"Kevlar Quicklace + Lace Pocket","Chassis":"EnergyCell+ High-Rebound Compound","Terrain":"Muddy, Technical & Loose Alpine Trails"}',
 'https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=85"]',
 1, 1, 0, 27),

(23, 8, 'Adidas Terrex Free Hiker 2.0 GORE-TEX Boot', 'adidas-terrex-free-hiker-2-gore-tex', 'Adidas', 'SK-HK-002', 'Footwear',
 179.99, 219.99, 18, 4.85, 260,
 'UK 7,UK 8,UK 9,UK 10,UK 11', 'Black,Green,Grey', 'Black',
 'Trail-runner comfort meets alpine boot protection. The Terrex Free Hiker 2.0 pairs a sock-like Primeknit collar and full-length BOOST midsole with an external EVA stability frame and Continental Rubber lugs that grip wet granite.',
 '{"Upper":"Abrasion-Resistant Ripstop + GORE-TEX Membrane","Midsole":"Full-Length BOOST + External EVA Stabilization Frame","Outsole":"Continental All-Terrain Lugged Rubber","Cut":"Mid-Cut Ankle Debris Collar","Weight":"445g (UK 9)"}',
 'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=1000&q=85"]',
 0, 0, 1, 21),

(24, 8, 'Salomon Bonatti Trail Waterproof 2.5L Shell Jacket', 'salomon-bonatti-trail-waterproof-jacket', 'Salomon', 'SK-HK-003', 'Apparel',
 144.99, 179.99, 19, 4.80, 149,
 'S,M,L,XL', 'Orange,Black,Green', 'Orange',
 'Ultralight Japanese Pertex Shield fabric delivers 20,000mm waterproof protection while packing down into its own chest pocket. Engineered with a smart back pleat so it can be worn seamlessly over a 12-liter trail hydration pack.',
 '{"Waterproof Rating":"20,000 mm / 20,000 g/m²/24h Breathability","Fabric":"2.5-Layer Pertex Shield Ripstop","Pack Compatibility":"Expandable Back Volume Over 12L Pack","Weight":"195g","Features":"Scuba Storm Hood + Waterproof AquaGuard Zip"}',
 'https://images.unsplash.com/photo-1501555088652-021faa106b9b?auto=format&fit=crop&w=1000&q=85',
 '["https://images.unsplash.com/photo-1501555088652-021faa106b9b?auto=format&fit=crop&w=1000&q=85","https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=1000&q=85"]',
 0, 1, 1, 35);

-- ============================================================================
-- SEED DATA: Sample Order for Demo User (athlete@sportkick.com)
-- ============================================================================
INSERT INTO orders (
    id, order_number, user_id, customer_name, customer_email, customer_phone,
    shipping_address, shipping_city, shipping_state, shipping_zip,
    payment_method, payment_status, order_status,
    subtotal, discount_amount, shipping_fee, total_amount
) VALUES (
    1, 'SK-2026-84920', 1, 'Aarav Sharma', 'athlete@sportkick.com', '+91 98765 43210',
    '42 Olympic Residency, Indiranagar', 'Bengaluru', 'Karnataka', '560038',
    'Credit / Debit Card', 'Paid', 'Delivered',
    329.98, 0.00, 0.00, 329.98
);

INSERT INTO order_items (order_id, product_id, product_name, brand, size, color, unit_price, quantity, line_total) VALUES
(1, 1, 'Nike Air Zoom Alphafly Next% 3 Proto', 'Nike', 'UK 9', 'Orange', 189.99, 1, 189.99),
(1, 19, 'Nike Metcon 9 AMP Cross-Training Shoe', 'Nike', 'UK 9', 'Black', 139.99, 1, 139.99);
