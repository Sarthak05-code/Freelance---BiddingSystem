# BidBoard — Freelance Task Bidding Platform

A freelance task marketplace built with plain PHP, HTML, CSS, and vanilla JS. No frameworks, no Composer, no npm. Runs on XAMPP.

Clients post tasks, freelancers bid on them without needing an account, and clients accept the best bid. Admins oversee the entire platform.

---

## Tech Stack

| Layer    | Technology                   |
| -------- | ---------------------------- |
| Backend  | PHP (vanilla, no frameworks) |
| Database | MySQL via phpMyAdmin         |
| Frontend | HTML, CSS, Vanilla JS        |
| Server   | Apache + MySQL (XAMPP)       |

---

## Roles

| Role       | Access                                                        |
| ---------- | ------------------------------------------------------------- |
| Admin      | View/delete all tasks and bids, manage clients                |
| Client     | Register, post tasks, view bids, accept/reject, mark complete |
| Freelancer | Browse tasks, submit bids, check bid status by email          |

---

## Getting Started

### Requirements

- XAMPP (Apache + MySQL)
- PHP 7.4 or higher

### Setup

1. Clone or download the repo into your XAMPP htdocs folder:

   ```
   C:\xampp\htdocs\bidboard\
   ```

   The folder **must** be named `bidboard` — all internal paths depend on it.

2. Start **Apache** and **MySQL** in the XAMPP Control Panel.

3. Open phpMyAdmin at `http://localhost/phpmyadmin`

4. Create a new database named `bidboard`.

5. Import `sql/bidboard.sql` into that database.

6. Copy `includes/db.sample.php` to `includes/db.php` — default XAMPP credentials are already set so no changes needed unless your MySQL uses a different password.

7. Open the app at:
   ```
   http://localhost/bidboard/
   ```

### Default Admin Credentials

```
Username: admin
Password: admin123
```

> Change this after first login via phpMyAdmin if deploying anywhere beyond localhost.

---

## Folder Structure

```
bidboard/
├── index.php                  ← Public task listing (homepage)
├── task.php                   ← Single task view + bid submission form
├── bid_status.php             ← Freelancer bid history lookup by email
│
├── auth/
│   ├── admin_login.php        ← Admin login
│   ├── client_login.php       ← Client login
│   ├── client_register.php    ← Client registration
│   └── logout.php             ← Handles logout for both roles
│
├── client/
│   ├── dashboard.php          ← Client overview + task table
│   ├── post_task.php          ← Post a new task
│   ├── task_bids.php          ← View bids on a task, accept/reject
│   ├── edit_task.php          ← Edit an open task
│   └── edit_profile.php       ← Update name, email, password
│
├── admin/
│   ├── dashboard.php          ← Platform-wide stats + recent tasks
│   ├── tasks.php              ← All tasks with search + status filter
│   ├── bids.php               ← All bids with search + status filter
│   └── clients.php            ← Manage clients, activate/deactivate
│
├── actions/
│   ├── accept_bid.php         ← Accepts a bid, rejects all others, sets task in_progress
│   ├── reject_bid.php         ← Rejects a single bid
│   ├── update_task_status.php ← Marks task as completed
│   └── delete_task.php        ← Deletes an open task
│
├── includes/
│   ├── db.php                 ← MySQLi connection
│   ├── auth_admin.php         ← Admin session guard
│   ├── auth_client.php        ← Client session guard
│   ├── header.php             ← Shared HTML header + navbar + context bar
│   └── footer.php             ← Shared HTML footer
│
├── css/
│   └── style.css              ← All styles, CSS variables, layout
│
└── sql/
    └── bidboard.sql           ← Full schema + default admin seed
```

---

## Features

- **Task lifecycle** — open → in progress → completed
- **Bid lifecycle** — pending → accepted / rejected
- **Auto-reject** — accepting one bid automatically rejects all others on the same task
- **Duplicate bid prevention** — same email cannot bid twice on the same task
- **Bid history** — freelancers check their bid status by entering their email (no account needed)
- **Admin search** — search tasks by title/description/client, search bids by freelancer name/email/task
- **Client edit profile** — update name, email, password (requires current password confirmation)
- **Client edit task** — edit title, description, category, budget, deadline while task is open
- **Context bar** — colored strip below navbar shows current role (guest / client / admin)
- **Session isolation** — admin and client use separate PHP session names to prevent cross-tab logout

---

## Known Bugs & Areas to Watch

### Dates

- If a task is posted with an invalid or empty deadline, MySQL stores `0000-00-00` which renders as `Nov 30, -0001`. Always validate deadline server-side before inserting.
- The `strtotime()` function can return `false` on malformed dates — wrap date output with a null check if extending the project.

### Sessions

- Admin and client sessions use different session names (`bidboard_admin`, `bidboard_client`). If you add a third role, make sure it gets its own session name or logout from one role may not behave as expected.
- Sessions are not regenerated after login (`session_regenerate_id()`), which is fine for localhost but should be added before any live deployment to prevent session fixation.

### Freelancer identity

- Freelancers are identified only by email — there is no account, no password, no ownership verification. Anyone who knows a freelancer's email can look up their bid history. Not a problem for a local/demo app but worth noting.
- Duplicate bid prevention is email-based only. The same person can bid again with a different email.

### Admin password

- The default admin password hash in `bidboard.sql` is a known placeholder. If the login fails with `admin123`, run the UPDATE query in `sql/bidboard.sql` (commented at the bottom) to replace it with a working hash.

### File paths

- All internal links and redirects use the absolute path `/bidboard/`. If the htdocs folder is named anything other than `bidboard`, every redirect and asset link will break. Rename the folder rather than changing paths.

### No CSRF protection

- Forms use plain POST with no CSRF tokens. Fine for a college project on localhost, but a real deployment would need token validation on every state-changing form.

### No pagination

- Admin tasks and bids tables load all rows at once. With a large dataset this will get slow. Consider adding `LIMIT` + `OFFSET` pagination if the project is extended.

---

## Changelog

| Version | Changes                                                         |
| ------- | --------------------------------------------------------------- |
| v1      | Core platform — auth, task posting, bid submission, admin panel |
| v2      | Duplicate bid prevention, admin search, client edit profile     |
| v3      | Freelancer bid history, client edit task, context bar           |

---

## Author

Built as a college project by Sarthak — BCA, Kathmandu.
