# BidBoard — Freelance Task Bidding Platform

A freelance task marketplace built with plain PHP, HTML, CSS, and vanilla JS. No frameworks, no Composer, no npm required for core functionality. Runs on XAMPP.

Clients post tasks, freelancers bid on them without needing an account, and clients accept the best bid. Admins oversee the entire platform.

---

## Tech Stack

| Layer              | Technology                      |
| ------------------ | ------------------------------- |
| Backend            | PHP (vanilla, no frameworks)    |
| Database           | MySQL via phpMyAdmin            |
| Frontend           | HTML, CSS, Vanilla JS           |
| Server             | Apache + MySQL (XAMPP)          |
| Email Verification | Abstract API (Email Reputation) |

---

## Roles

| Role       | Access                                                                     |
| ---------- | -------------------------------------------------------------------------- |
| Admin      | View/delete all tasks and bids, manage clients, review reports             |
| Client     | Register, post tasks, view bids, accept/reject, mark complete, report bids |
| Freelancer | Browse tasks, submit bids, check bid status by email, report tasks         |

---

## Getting Started

### Requirements

- XAMPP (Apache + MySQL)
- PHP 7.4 or higher
- (Optional) A free Abstract API key for email verification — see Configuration below

### Setup

1. Clone or download the repo into your XAMPP htdocs folder:

   ```
   C:\xampp\htdocs\bidboard\
   ```

   The folder **must** be named `bidboard` — all internal paths depend on it.

2. Start **Apache** and **MySQL** in the XAMPP Control Panel.

3. Open phpMyAdmin at http://localhost/phpmyadmin

4. Create a new database named `bidboard`.

5. Import `sql/bidboard.sql` into that database.

6. Copy `includes/db.sample.php` to `includes/db.php` — default XAMPP credentials are already set so no changes needed unless your MySQL uses a different password.

7. Copy `includes/email_helper.sample.php` to `includes/email_helper.php` and add your free Abstract API key (see Configuration below). If left blank, email verification is skipped automatically — the app still works.

8. Open the app at:

   ```
   http://localhost/bidboard/
   ```

Alternatively, run `setup.bat` after step 2 — it automates step 6 for you and prints the remaining steps.

### Default Admin Credentials

```
Username: admin
Password: admin123
```

> Change this after first login via phpMyAdmin if deploying anywhere beyond localhost.

---

## Configuration

### Email Verification (Abstract API)

BidBoard optionally verifies that submitted emails are real and deliverable (not just correctly formatted) using Abstract API's Email Reputation endpoint.

1. Sign up free at [abstractapi.com](https://www.abstractapi.com)
2. Subscribe to the **Email Reputation** product (free tier: 100 requests/month)
3. Copy your API key into `includes/email_helper.php`:

   ```php
   define("ABSTRACT_API_KEY", "your_key_here");
   ```

4. If no key is set, the check fails open (always passes) so the app remains fully functional without it.

---

## Folder Structure

```
bidboard/
├── index.php ← Public task listing (homepage)
├── task.php ← Single task view + bid submission form + report task link
├── bid_status.php ← Freelancer bid history lookup by email
├── report.php ← Shared report form for tasks (public) and bids (client-only)
├── setup.bat ← Automates first-time db.php setup
│
├── auth/
│   ├── admin_login.php ← Admin login
│   ├── client_login.php ← Client login
│   ├── client_register.php ← Client registration
│   └── logout.php ← Handles logout for both roles (session-isolated)
│
├── client/
│   ├── dashboard.php ← Client overview + task table
│   ├── post_task.php ← Post a new task
│   ├── task_bids.php ← View bids on a task, accept/reject, report bids
│   ├── edit_task.php ← Edit an open task
│   └── edit_profile.php ← Update name, email, password
│
├── admin/
│   ├── dashboard.php ← Platform-wide stats + recent tasks + pending reports
│   ├── tasks.php ← All tasks with search + status filter
│   ├── bids.php ← All bids with search + status filter
│   ├── clients.php ← Manage clients, activate/deactivate
│   └── reports.php ← Review and manage task/bid reports
│
├── actions/
│   ├── accept_bid.php ← Accepts a bid, rejects all others, sets task in_progress
│   ├── reject_bid.php ← Rejects a single bid
│   ├── update_task_status.php ← Marks task as completed
│   ├── delete_task.php ← Deletes an open task
│   └── update_report_status.php ← Admin reviews or dismisses a report
│
├── includes/
│   ├── db.php ← MySQLi connection + CSRF token helpers
│   ├── email_helper.php ← Abstract API email verification helper
│   ├── auth_admin.php ← Admin session guard
│   ├── auth_client.php ← Client session guard
│   ├── header.php ← Shared HTML header + navbar + context bar
│   └── footer.php ← Shared HTML footer
│
├── css/
│   └── style.css ← All styles, CSS variables, layout
│
├── sql/
│   └── bidboard.sql ← Full schema + default admin seed
│
└── .github/workflows/
    └── php-lint.yml ← CI: PHP syntax check on every push/PR
```

---

## Features

### Core

- **Task lifecycle** — open → in progress → completed
- **Bid lifecycle** — pending → accepted / rejected
- **Auto-reject** — accepting one bid automatically rejects all others on the same task
- **Duplicate bid prevention** — same email cannot bid twice on the same task
- **Bid history** — freelancers check their bid status by entering their email (no account needed)
- **Admin search** — search tasks by title/description/client, search bids by freelancer name/email/task
- **Client edit profile** — update name, email, password (requires current password confirmation)
- **Client edit task** — edit title, description, category, budget, deadline while task is open
- **Context bar** — colored strip below navbar shows current role (guest / client / admin)

### Reporting

- **Report a task** — anyone can report a public task from the task detail page (no login needed)
- **Report a bid** — clients can report bids on their own tasks from the bid management page
- **Admin review** — admins see all reports in a dedicated page with pending/reviewed/dismissed status
- **Dashboard alert** — pending report count shown on admin dashboard with quick link

### Security

- **CSRF protection** — every state-changing form includes a per-session token, verified server-side before processing
- **Session isolation** — admin and client use separate PHP session names to prevent cross-tab logout
- **Session regeneration** — a fresh session ID is issued on every successful login to prevent session fixation
- **Password hashing** — bcrypt via `password_hash()` / `password_verify()`
- **Prepared statements** — all database queries use parameterized queries throughout

### Validation

- **Server-side validation** on every form field (required, defense-in-depth even if JS is bypassed)
- **Real-time client-side validation** — instant feedback as the user types for name (letters only), email format, password length/match, and numeric fields (budget, bid price)
- **Email deliverability check** — integrates Abstract API to verify submitted emails are real and deliverable, not just correctly formatted (applied at client registration, profile email changes, bid submission, and report submission)

---

## Known Bugs & Areas to Watch

### Freelancer identity

- Freelancers are identified only by email — there is no account or password. Anyone who knows a freelancer's email can look up their bid history. Not a problem for a local/demo app but worth noting.
- Duplicate bid prevention is email-based only. The same person can bid again with a different email — email deliverability checking reduces but doesn't eliminate this, since a real person can own multiple valid addresses.

### Admin password

- The default admin password hash in `bidboard.sql` is a known placeholder. If login fails with `admin123`, run the UPDATE query in `sql/bidboard.sql` (commented at the bottom) to replace it with a working hash.

### File paths

- All internal links and redirects use the absolute path `/bidboard/`. If the htdocs folder is named anything other than `bidboard`, every redirect and asset link will break. Rename the folder rather than changing paths.

### No pagination

- Admin tasks, bids, and reports tables load all rows at once. With a large dataset this will get slow. Consider adding `LIMIT + OFFSET` pagination if the project is extended.

### Email verification API limits

- Abstract API's free tier caps at 100 requests/month. If exceeded, `is_email_deliverable()` fails open (returns true) so the app keeps working, but verification is effectively skipped until the quota resets.

### Dates

- The `strtotime()` function can return `false` on malformed dates in edge cases — deadlines are validated server-side before insert, but if extending the project with new date fields, wrap date output with a null check.

---

## Changelog

| Version | Changes                                                                                                   |
| ------- | --------------------------------------------------------------------------------------------------------- |
| v1      | Core platform — auth, task posting, bid submission, admin panel                                           |
| v2      | Duplicate bid prevention, admin search, client edit profile                                               |
| v3      | Freelancer bid history, client edit task, context bar                                                     |
| v4      | CSRF protection, session regeneration, real-time + name/email validation, Abstract API email verification |
| v5      | Reporting system — task reports (public), bid reports (client), admin review panel, session isolation fix |

---

## Author

Built as a college project by Sarthak — BCA, Kathmandu.
