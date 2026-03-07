You are a senior full-stack web developer.
Create a complete mini project web application based on the following abstract.

The project must be structured, secure, and easy to understand for students.

Project Title

Pharmacy Inventory and Online Medicine Ordering System

Objective

Develop a web-based pharmacy management system that helps pharmacy staff manage medicines, track expiry dates, and allow customers to place medicine orders online.

The system must enforce inventory rules so customers can only order available and non-expired medicines.

Technology Stack

Frontend:

HTML5

CSS3

Basic JavaScript (optional for form validation)

Backend:

PHP (Core PHP, no frameworks)

Database:

MySQL

Server:

Apache (XAMPP compatible)

Core System Modules
1. Admin / Pharmacy Staff Module

Admin should be able to:

Authentication

Login

Logout

Medicine Management

Add new medicines

Edit medicine details

Delete medicines

View medicine list

Fields:

Medicine ID

Medicine Name

Category

Manufacturer

Batch Number

Expiry Date

Price

Stock Quantity

Inventory Monitoring

Admin dashboard should display:

Total medicines

Low stock medicines

Expired medicines

Medicines expiring soon (within 30 days)

Batch Tracking

Each medicine must support:

Batch number

Expiry date

Quantity

Expiry Enforcement Rule

Expired medicines must:

Not appear in the customer ordering page

Be highlighted in admin panel

Stock Management

When stock becomes:

0 → mark as Out of Stock

Low (<10) → highlight warning

2. Customer Module

Customers do NOT need accounts.

Features:

View Medicines

Customers can:

View available medicines

Search medicine

Filter by category

Each medicine card should display:

Medicine name

Price

Availability

Expiry validity

Order Medicines

Customers can:

Select medicines

Add quantity

Place order

Order form requires:

Customer name

Phone number

Address

Payment method selection

Payment options:

Cash on Delivery

Pay at Pharmacy

NOTE:
No online payment gateway is required.

3. Inventory Control Rules

The system must enforce:

Customers cannot order expired medicines

Customers cannot order medicines with zero stock

Order quantity cannot exceed available stock

When an order is placed:

Stock quantity must update automatically

If stock becomes zero:

Medicine should disappear from customer list

4. Order Management (Admin)

Admin must be able to:

View all orders

View order details

Update order status

Statuses:

Pending

Ready for Pickup

Completed

Cancelled

Database Design (MySQL)

Create the following tables.

Admin Table

admin

id

username

password

Medicines Table

medicines

id

name

category

manufacturer

batch_number

expiry_date

price

stock_quantity

Orders Table

orders

id

customer_name

phone

address

payment_method

order_date

status

Order Items Table

order_items

id

order_id

medicine_id

quantity

price

Website Pages
Admin Pages

admin_login.php

admin_dashboard.php

manage_medicines.php

add_medicine.php

edit_medicine.php

view_orders.php

logout.php

Customer Pages

index.php (medicine listing)

search.php

medicine_details.php

order_form.php

place_order.php

order_success.php

UI Design Requirements

Design should be simple and clean.

Use:

Header:

Website title

Navigation menu

Admin Dashboard Cards:

Total Medicines

Low Stock

Expiring Soon

Orders

Medicine table should include:

Name

Batch

Expiry

Stock

Price

Actions

Customer side should show medicines as cards or product list.

Expiry Monitoring Logic (Important)

Add backend logic that:

Automatically filters expired medicines

Highlights medicines expiring in 30 days

Example condition:

WHERE expiry_date > CURDATE()

Expiring soon:

WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
Security Requirements

Implement basic security:

Password hashing

SQL injection prevention using prepared statements

Session-based login authentication

Input validation

Folder Structure

Create a clean folder structure.

example:

project/

css/
style.css

admin/
admin_login.php
dashboard.php
add_medicine.php
manage_medicines.php

customer/
index.php
order.php

config/
database.php

includes/
header.php
footer.php

Additional Features (Optional but Recommended)

Add:

Search bar for medicines

Pagination for medicine list

Order confirmation page

Dashboard statistics

Output Requirements

Generate:

Full project folder structure

SQL file for database creation

Complete PHP files

CSS styling

Comments in code for explanation

Instructions to run in XAMPP

Important

The code must be:

Beginner friendly

Well commented

Fully working

Compatible with PHP 8+

Compatible with MySQL

Also Provide

At the end include:

Database ER diagram explanation

System workflow

Steps to run the project