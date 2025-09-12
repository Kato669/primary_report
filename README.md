# 📚 School Management System (PHP & MySQL)

This project is a **comprehensive School Management System** built with **PHP, MySQL, and Bootstrap**.  
It’s designed to simplify day-to-day school operations like student registration, class management, teacher-subject assignments, term fees, and more — all from an **easy-to-use admin dashboard**.

## ✨ Project Overview

This is my first **fully-featured complex PHP project**, moving beyond simple CRUD apps to include:
- Dynamic relationships between tables (students, classes, streams, subjects, teachers).
- Real-time filters and dropdowns using AJAX.
- Validation and duplicate checks to prevent data errors.
- Clean UI with Toastr notifications for feedback.

## 🛠️ Admin Features

### 👩‍🎓 **Student Management**
- Add, edit, delete students with profile pictures.
- View students table with **instant filtering by class & stream**.
- Download student lists as **CSV/Excel** directly from the dashboard.

### 📚 **Class & Stream Management**
- Manage classes and streams.
- Each class can have multiple streams linked dynamically.

### 🧑‍🏫 **Teacher-Subject Assignments**
- Assign teachers to classes, streams, and subjects.
- Store teacher initials to display on report cards.
- Prevent duplicate assignments with smart validation.
- Edit or delete assignments when changes occur.

### 📝 **Subject & Curriculum Management**
- Assign subjects to classes (curriculum mapping).
- Dynamically load only relevant subjects per class in forms.

### 💰 **Fees Management**
- Enter termly fees for each class (day & boarding).
- Auto-check for existing class/term/year combinations:
  - Updates if found ✅
  - Inserts if not found ➕
- Allows future updates without losing history.

### 📊 **Dashboard Analytics**
- Display total students, teachers, gender breakdown (male/female).
- **Dynamic graph** showing students per class.

### ⏳ **Term Management**
- Manage school terms and academic years.
- Automatically link term info to fees and reporting.

## 🚀 What Makes It Unique
- **AJAX-powered dropdowns** for real-time filtering (e.g., streams load only when class is selected).
- **Dynamic report generation** with correct teacher initials per subject.
- **Data integrity first:** prevents duplicate entries and keeps term history intact.
- **User-friendly feedback:** Toastr alerts for success, errors, and warnings.
- **Scalable design:** easy to extend with new modules (like exams, grading, or timetables).

## 🖥️ Tech Stack
- **Backend:** PHP 8.x, MySQL  
- **Frontend:** Bootstrap 5, jQuery, AJAX  
- **Extras:** DataTables for filtering & exporting, Toastr for notifications  

## 🙌 Author
Developed by katojkalemba — this project pushed my PHP skills to a new level, from basic CRUD to building a full, multi-table, interactive school system.  
More features & improvements coming soon 🚀
