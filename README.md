# Online OPD System

A PHP-based web application for managing doctor-patient interactions, appointment bookings, and generating printable invoices.

## 🏥 Features

- Patient and doctor signup/login with OTP verification
- Appointment booking system with ticket generation
- Admin panel to manage users and view reports
- Excel file upload to generate printable invoices using PhpSpreadsheet
- Responsive and clean user interface
- Session-based access control

## 💻 Tech Stack

- **Frontend:** HTML, CSS, JavaScript (with AOS for animations)
- **Backend:** PHP
- **Database:** MySQL
- **Excel Handling:** PhpSpreadsheet
- **Server:** XAMPP (Apache + MySQL)

## 🚀 How to Run the Project

1. Install [XAMPP](https://www.apachefriends.org/index.html).
2. Clone or download this repository.
3. Place the project folder inside the `htdocs/` directory (e.g., `C:/xampp/htdocs/OnlineOPD`).
4. Open **phpMyAdmin** and import the included SQL file (`database.sql`) to create required tables.
5. Start Apache and MySQL via XAMPP control panel.
6. Access the app at [http://localhost/OnlineOPD](http://localhost/OnlineOPD).

## 📁 Folder Structure
OnlineOPD/
│
├── admin/
├── doctor/
├── patient/
├── assets/
│ ├── css/
│ ├── js/
│ └── images/
├── includes/
├── verify_otp.php
├── login.php
├── signup.php
└── database.sql


## 🙋‍♂️ Author

**AAVASH BISWAS**  
BSc. Computer Science Student  
Email: aavashbiswas1234@gmail.com  
GitHub: [@Vatia0026](https://github.com/Vatia0026)

## 📄 License

This project is for educational purposes only. Feel free to reuse with credit.
