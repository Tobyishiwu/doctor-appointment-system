# MedBook SaaS: Advanced Patient-Doctor Scheduling System

[![Stack](https://img.shields.io/badge/Stack-PHP_8.x_|_MySQL_|_JS-blue)](https://github.com/tobyishiwu/doctor-appointment-system)
[![Status](https://img.shields.io/badge/Status-Project_5_Completed-success)](https://github.com/tobyishiwu/doctor-appointment-system)

## 📌 Project Overview
MedBook is a high-performance SaaS solution designed to streamline clinical operations in the Nigerian healthcare sector. Developed as part of a professional sprint, this platform eliminates manual scheduling errors and double-bookings through a real-time availability engine.

## 🚀 Key Technical Features
- **Real-Time Slot Validation:** Developed an asynchronous (AJAX/Fetch) availability checker that queries the database live to filter out booked appointments without page reloads.
- **Data Integrity & Security:** - Implemented **Database Transactions** (`BEGIN TRANSACTION`) to ensure atomic booking operations.
  - Built-in **CSRF Protection** and **PDO Prepared Statements** to defend against Top 10 OWASP vulnerabilities.
- **Dynamic Slot Generation:** Automated logic to generate 30-minute consultation windows within standard Nigerian clinic hours (09:00 - 17:00).
- **Responsive Dashboard:** A mobile-first interface designed for accessibility across all devices, from desktop to low-end smartphones.

## 🛠️ Tech Stack
- **Backend:** PHP 8.x (Vanilla for high performance)
- **Database:** MySQL (Relational architecture)
- **Frontend:** Bootstrap 5.3, JavaScript (ES6+), Feather Icons
- **Security:** Session-based Auth, CSRF Tokens, Input Sanitization

## 📂 System Architecture


## 🔧 Installation & Setup
1. Clone the repository:
   ```bash
   git clone [https://github.com/tobyishiwu/doctor-appointment-system.git](https://github.com/tobyishiwu/doctor-appointment-system.git)