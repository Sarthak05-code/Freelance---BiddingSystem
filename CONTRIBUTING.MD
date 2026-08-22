# Contributing to BidBoard

Thanks for considering a contribution! BidBoard is a plain PHP/MySQL project (no frameworks, no Composer/npm needed) built on XAMPP, so the setup is intentionally simple.

## Getting Set Up

1. Fork and clone the repo into your XAMPP `htdocs` folder — it **must** be named `bidboard`, since internal paths depend on it:
   ```
   C:\xampp\htdocs\bidboard\
   ```
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Create a `bidboard` database in phpMyAdmin and import `sql/bidboard.sql`.
4. Copy `includes/db.sample.php` → `includes/db.php` and `includes/email_helper.sample.php` → `includes/email_helper.php`.
5. Visit `http://localhost/bidboard/` to confirm it's running.

Full setup details are in the main `README.md`.

## Project Structure

Before making changes, skim the **Folder Structure** section in the README. Roughly:

- `auth/` — login/registration for admins and clients
- `client/` — client-facing dashboard, task posting/editing
- `admin/` — admin dashboard, task/bid/client/report management
- `actions/` — POST-only scripts that mutate state (accept bid, delete task, etc.)
- `includes/` — shared DB connection, session guards, header/footer, email helper
- `sql/` — schema and seed data

## Coding Conventions

- **Plain PHP only** — no frameworks or Composer dependencies for core functionality. If you want to propose adding one, open an issue to discuss first.
- **Prepared statements** for every database query — no raw string interpolation into SQL.
- **CSRF tokens** on every state-changing form — follow the existing pattern in `includes/db.php` for generating/verifying tokens.
- **Session isolation** — admin and client sessions use separate session names; don't merge them.
- **Server-side validation first** — client-side JS validation is a UX nice-to-have, not a substitute. Validate on the server regardless of what the frontend does.
- Match the existing code style (naming, indentation) in the file you're editing rather than introducing a new style.

## Making a Change

1. Create a branch off `main`: `git checkout -b fix/short-description` or `feature/short-description`.
2. Make your change, and test it manually against a local XAMPP setup (there's no automated test suite yet beyond the PHP lint CI check).
3. Confirm `.github/workflows/php-lint.yml` would pass — i.e., your PHP files have no syntax errors (`php -l yourfile.php`).
4. Commit with a clear message describing *what* and *why*.
5. Open a pull request using the PR template and fill it out — this makes review much faster.

## Reporting Bugs / Requesting Features

Use the issue templates (`Bug report` or `Feature request`) when opening an issue — they ask for the details needed to actually act on it (steps to reproduce, PHP version, etc.).

## Reporting Security Issues

Do **not** open a public issue for a security vulnerability. See `SECURITY.md` for how to report it privately.

## Known Areas That Could Use Help

Check the **Known Bugs & Areas to Watch** section in the README — things like admin table pagination and the freelancer-identity-by-email limitation are good starting points if you're looking for something to work on.