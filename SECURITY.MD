# Security Policy

## Supported Versions

BidBoard is a college project and doesn't maintain parallel release branches. Only the latest version on `main` receives fixes.

| Version | Supported |
| ------- | --------- |
| Latest (main) | ✅ |
| Older tagged versions (v1–v4) | ❌ |

## Reporting a Vulnerability

If you find a security issue — for example, something related to authentication, session handling, CSRF protection, SQL injection, or the default admin credentials — please **do not open a public issue**.

Instead:

1. Open a **private** report via GitHub's "Report a vulnerability" feature under the repo's Security tab (Security → Advisories → Report a vulnerability), if enabled, **or**
2. Contact the maintainer directly (see GitHub profile: `Sarthak05-code`) with a description of the issue.

Please include:

- A description of the vulnerability and its potential impact
- Steps to reproduce it
- Any relevant file paths or code snippets

You should get an acknowledgment within a few days. Since this is a student/solo-maintained project, fixes aren't guaranteed on any specific timeline, but genuine security reports will be prioritized over feature work.

## Known, Already-Documented Risks

A few limitations are already tracked in the README's **Known Bugs & Areas to Watch** section and don't need a separate report unless you're demonstrating a new way to exploit them:

- **Default admin credentials** (`admin` / `admin123`) — meant to be changed before any deployment beyond localhost.
- **Freelancer identity is email-only** — no account/password, so anyone who knows a freelancer's email can view their bid history.
- **Duplicate bid prevention is email-based only** — a user can bid again with a different email address.
- **Abstract API free tier** fails open (skips verification) once the 100 requests/month quota is exceeded.

Reports about genuinely new issues — e.g. a CSRF bypass, a SQL injection point, a session fixation path, privilege escalation between client/admin sessions — are very welcome.