# CLAUDE.md — Primary Report System

## Project Overview

A school management system for a Ugandan primary school. Covers student registration, class/stream/subject management, teacher assignments, exam marks declaration, report card generation, and fees tracking.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.0.30, MySQLi (procedural + OOP mixed) |
| Database | MySQL / MariaDB 10.4 |
| Frontend | Bootstrap 5.2.3, jQuery 3.6.0, DataTables 2.3.3 |
| Notifications | Toastr.js |
| Icons | FontAwesome 6.x |
| Charts | Chart.js |
| Spreadsheet parsing | phpoffice/phpspreadsheet ^5.5 (Composer) |

No framework — plain PHP with procedural patterns and an include-based layout system.

---

## Directory Structure

```
/
├── constants/constants.php     DB credentials, site URL, session_start()
├── partials/
│   ├── header.php              Top navbar + sidebar (included on every page)
│   ├── footer.php              Closing scripts + JS
│   ├── adminOnly.php           Redirects non-admins
│   └── NotLoggedin.php         Redirects unauthenticated users
├── css/style.css               Custom styles (sidebar, brand colours)
├── js/app.js                   DataTable init, sidebar toggle
├── img/
│   ├── stdent_image/           Uploaded student profile photos
│   └── (logo/icon files)
├── uploads/                    Temp dir for bulk student import files
├── files/student.ods           Blank registration template download
├── vendor/                     Composer packages
└── *.php                       All pages at root level (~70 files)
```

---

## Database Schema (key tables)

| Table | Purpose |
|---|---|
| `classes` | id, class_name, prefix (P.1–P.7), LEVEL |
| `streams` | id, class_id, stream_name |
| `students` | student_id, first_name, last_name, gender, dob, LIN, class_id, stream_id, image, status (day/boarding), level |
| `subjects` | subject_id, subject_name |
| `class_subjects` | class_id ↔ subject_id mapping |
| `users` | user_id, fullname, username, password (bcrypt), role, is_deleted |
| `teacher_assignments` | teacher → class/stream mapping |
| `teacher_subject_assignments` | teacher → subject → class/stream → term/year + initials |
| `terms` | term_id, term_name (Term 1/2/3) |
| `exams` | exam_id, exam_name (B.O.T/M.O.T/E.O.Y), term_id, academic_year, class_id |
| `marks` | mark_id, student_id, exam_id, subject_id, score (0–100) |
| `grading_scale` | grade_name (D1–F9), min_score, max_score, comment |
| `student_comments` | class_teacher_comment, head_teacher_comment per student per exam |
| `school_profile` | school_name, address, phone, email, motto, logo |
| `term_info` | fees_day, fees_boarding, term_end, next_start per class/term/year |
| `positions` | class_position, stream_position, total_marks, average per student per exam |

Student registration numbers follow the format `JPM/YEAR/###` (auto-generated).

---

## Roles & Access Control

Three roles stored in `users.role`:

| Role | Access |
|---|---|
| `admin` | Full access to all pages |
| `class_teacher` | Own class/stream: marks, comments, promote students |
| `teacher` | Only subjects assigned to them |

- `partials/adminOnly.php` — redirects if `$_SESSION['role'] !== 'admin'`
- `partials/NotLoggedin.php` — redirects if `$_SESSION['login']` is not set
- Session vars: `login`, `username`, `user_id`, `role`, `class_id`, `stream_id`, `assignments`

---

## Key Page Groups

### Student Management
- `students.php` — DataTable list with AJAX filtering by class/stream
- `add_student.php` — Single student form; validates name as letters-only UPPERCASE
- `upload_students.php` — Bulk import via XLS/XLSX/ODS/CSV using phpspreadsheet
- `edit_stdnt.php`, `delete_stdnt.php`

### Marks Declaration
- `select_exam.php` → `addScore.php` — grid of students × subjects, POST to `save_scores.php`
- `declare_marks.php` — view/edit existing marks (role-gated per subject)
- `save_scores.php` — validates 0–100, uses `begin_transaction`/`commit`/`rollback`

### Reports
- `reports.php` — report cards by class/stream/term/year
- `add_comments.php` — class_teacher and head_teacher comments

### AJAX Endpoints (return HTML fragments, not JSON)
- `get_students.php` — filtered student table rows
- `get_streams.php` — `<option>` tags for a class
- `get_exams.php`, `get_students_options.php`

---

## Coding Conventions

- **File names:** snake_case (`add_student.php`, `declare_marks.php`)
- **Table/column names:** snake_case
- **PHP variables:** mixed — camelCase and snake_case both appear; follow the style of the file being edited
- **Page structure:** every page does `require_once 'constants/constants.php'`, then optionally `partials/NotLoggedin.php` or `partials/adminOnly.php`, then `partials/header.php` at the top and `partials/footer.php` at the bottom
- **Forms:** POST only, redirect on success (`header('Location: ...')` after `ob_start()`)
- **Feedback:** Toastr toasts triggered by GET params (`?msg=...` or `?error=...`) or inline PHP echoes
- **DB connection:** `$conn` from `constants.php` via `mysqli_connect()`, used as procedural `mysqli_query($conn, ...)` or OOP `$conn->prepare()`

### SQL Style

Newer files (`declare_marks.php`, `save_scores.php`) use prepared statements:
```php
$stmt = $conn->prepare("SELECT ... WHERE id = ?");
$stmt->bind_param("i", $id);
```

Older files use `mysqli_real_escape_string()` — prefer prepared statements for any new code.

---

## File Upload Conventions

- Student images → `/img/stdent_image/student_image_{timestamp}_{rand}.{ext}`
- Bulk import files → `/uploads/` (deleted after processing)
- Allowed image types: JPG, JPEG, PNG
- Spreadsheet types: XLS, XLSX, ODS, CSV (max 5 MB)

---

## Colours (brand)

```css
Primary:  #001870   (dark navy)
Success:  #009549   (green)
```

---

## Local Dev Setup

1. PHP 8.0+ and MySQL/MariaDB 10.4+
2. `composer install` (installs phpspreadsheet)
3. Import database dump (look for `.sql` file in project or ask)
4. Set DB credentials + `SITE_URL` in `constants/constants.php`
5. Web root: `http://localhost/primary_report/`
6. Ensure write permissions on `/uploads/` and `/img/stdent_image/`

---

## Things to Watch Out For

- No CSRF tokens — forms are vulnerable to CSRF; add if extending the auth system
- Several older pages use `mysqli_real_escape_string()` instead of prepared statements — do not copy that pattern for new code
- `ob_start()` is called at the top of pages that do redirects; missing it causes "headers already sent" errors
- The `is_deleted` column in `users` is a soft-delete flag (0 = active, 1 = deleted)
- Exam visibility is controlled by a separate `EXAM_VISIBILITY` table toggle — report cards won't show until the exam is set visible
