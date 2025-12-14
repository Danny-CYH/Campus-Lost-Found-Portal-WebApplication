---

# 🎒 Campus Lost & Found System — UUM

A **web-application Campus Lost and Found System** developed for the subject **Web Application Development** to help students and staff efficiently report, track, and recover lost items.
The platform promotes transparency, accountability, and community participation through real-time communication and a point-based leaderboard system.

---

## 📌 Project Overview

The Campus Lost & Found System provides a centralized platform for reporting **lost and found items** within the UUM campus.
Users can securely authenticate, publish item listings, communicate in real time, and earn points for responsible participation.

This project was developed as a **collaboration project** with another two developers.

---

## 🚀 Key Features

### 🔐 Authentication & User Management

* User registration
* Secure login
* Forgot password & password reset
* Session-based authentication

### 📦 Lost & Found Management

* Post **lost items**
* Post **found items**
* View all lost & found listings
* Full **CRUD operations** (Create, Read, Update, Delete)
* Item status tracking (lost / found / resolved)

### 📝 Item Details Page

* Detailed item information
* Item images (if applicable)
* Date, location, and description
* Owner/finder details

### 💬 Communication System

* Real-time communication between users
* Secure message exchange for item verification
* Powered by **Pusher** (development environment)

### 🏆 Gamification & Engagement

* Point award system for positive contributions
* Leaderboard to encourage responsible behavior
* Community-driven motivation system

---

## 🛠️ Tech Stack

### Frontend

* **HTML5**
* **Tailwind CSS**
* **JavaScript (Vanilla JS)**

### Backend

* **Pure PHP** (no framework)
* **MySQL** (or compatible relational database)

### Real-Time Communication

* **Pusher** (for development & testing)
---

## ⚙️ Installation & Setup

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/Danny-CYH/Campus-Lost-Found-Portal-WebApplication.git
```

### 2️⃣ Configure Database

* Create a MySQL database
* Import the provided SQL file (if available)
* Update database credentials in:

```php
config/database.php
```

### 3️⃣ Configure Pusher (Development)

* Create a Pusher account
* Get **App ID**, **Key**, **Secret**, and **Cluster**
* Update credentials in:

```php
config/pusher.php
```

### 4️⃣ Run the Project

* Place the project inside:

  * `htdocs` (XAMPP)
* Start Apache & MySQL
* Access via browser:

```
https://campuslostfound.dramran.com/
```

---

## 🔒 Security Considerations

* Password hashing for all user credentials
* Session-based access control
* Input validation & sanitization
* Protection against common attacks (XSS, SQL Injection)
* Authentication checks on protected routes

---

## 🤝 Collaboration

This project was developed as a **collaborative effort** between two developers, involving:

* Joint system design & planning
* Feature distribution and integration
* Collaborative debugging and testing
* Shared responsibility for UI/UX and backend logic

---

## 🎯 Project Objectives

* Improve lost item recovery rate on campus
* Provide a secure and organized reporting system
* Encourage honesty and responsibility among students
* Enhance campus digital services experience

---

## 📈 Future Enhancements

* Email notifications
* Mobile-responsive optimization
* Image upload for items
* Admin dashboard & moderation
* Deployment to production environment

---

## 📄 License

This project is developed for **academic and educational purposes** under subject Web Application Development.
All rights reserved unless stated otherwise.

---
