# 💊 PharmaCare - Pharmacy Inventory & Online Medicine Ordering System

A comprehensive, beginner-friendly web application built with **Core PHP** and **MySQL**. It features a robust administration panel for pharmacists and a clean, responsive storefront for customers.

## 🚀 Key Features

### 👤 Customer Module

- **Browse Medicines**: View only available, non-expired medicines.
- **Search & Filter**: Find medicines by name or category.
- **Real-time Stock**: See unit availability before ordering.
- **Checkout Flow**: Simple order form with multiple payment methods (COD/Pickup).
- **Stock Guard**: System prevents ordering more than what is in stock or expired items.

### 🔐 Admin / Pharmacist Module

- **Secure Login**: Session-based authentication with bcrypt password hashing.
- **Inventory Management**: Full CRUD (Create, Read, Update, Delete) for medicines.
- **Expiry Monitoring**: Automatic highlighting of expired medicines and those expiring within 30 days.
- **Stock Warnings**: Visual indicators for low stock ( < 10 units) and out-of-stock items.
- **Order Management**: View customer orders, track items, and update status (Pending -> Ready -> Completed).

---

## 🛠 Technology Stack

- **Frontend**: HTML5, CSS3 (Vanilla), Google Fonts (Inter).
- **Backend**: PHP 8.0+ (using PDO for secure database interactions).
- **Database**: MySQL / MariaDB.
- **Compatibility**: Fully compatible with XAMPP / WAMP / MAMP.

---

## 🏗 System Workflow

1. **Inventory Setup**: Admin logs in and adds medicines with batch numbers, expiry dates, and stock quantities.
2. **Browsing**: Customers visit the home page. The system runs an automated filter: `WHERE expiry_date > CURDATE() AND stock_quantity > 0`.
3. **Ordering**: Customer selects a medicine and quantity. The system validates the requested quantity against current stock.
4. **Transaction**: During checkout, the system uses a **MySQL Transaction** with row-level locking (`FOR UPDATE`) to subtract stock and save the order simultaneously. This prevents "overselling" if two customers order at the exact same millisecond.
5. **Fulfillment**: Admin sees the new order on their dashboard, prepares the medicine, and marks the order as "Ready for Pickup" or "Completed".

---

## 📊 Database ER Diagram (Simplified)

- **admin**: (id, username, password)
- **medicines**: (id, name, category, manufacturer, batch_number, expiry_date, price, stock_quantity)
- **orders**: (id, customer_name, phone, address, payment_method, status, order_date)
- **order_items**: (id, order_id, medicine_id, quantity, price)

**Relationships**:

- One `orders` record can have multiple `order_items`.
- Each `order_items` record links to one `medicines` record.

---

## ⚙️ How to Run (XAMPP Instructions)

1. **Move Files**: Copy the project folder into your XAMPP `htdocs` directory (e.g., `C:/xampp/htdocs/PharmacyManagement`).
2. **Start Services**: Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. **Setup Database**:
   - Open your browser to `http://localhost/phpmyadmin`.
   - Create a new database named `pharmacy_db`.
   - Click the "Import" tab and select the `database.sql` file provided in the project root.
4. **Access the Website**:
   - **Customer View**: `http://localhost/PharmacyManagement/index.php`
   - **Admin Panel**: `http://localhost/PharmacyManagement/admin/admin_login.php`
5. **Default Admin Credentials**:
   - **Username**: `admin`
   - **Password**: `admin123`

---

## 🛡 Security Implementations

- **SQL Injection**: All queries use **PDO Prepared Statements**. No raw variables are passed into SQL.
- **Password Safety**: Admin passwords use PHP's `password_hash()` (Bcrypt).
- **XSS Prevention**: User-generated content is escaped using `htmlspecialchars()` before rendering.
- **Atomicity**: Order placement uses `beginTransaction()` and `commit()` to ensure data integrity.
