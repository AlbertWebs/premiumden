# Premium Business Den

Private business society website and membership portal, built with Laravel 12, Blade, Alpine.js, Tailwind CSS 4 and Vite. Local development uses SQLite. Production configuration supports MySQL. The product specification and delivery checklist are in [`PREMIUM BUSINESS DEN.md`](PREMIUM%20BUSINESS%20DEN.md).

## Local setup

Requirements: PHP 8.2+, Composer 2, Node.js 20+, npm and SQLite. Enable PDO SQLite locally and PDO MySQL in production.

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
npm ci
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`. Development role accounts are seeded only when `APP_ENV=local` or `testing`; each uses `PremiumDen-Local-2026!`:

| Role | Email |
| --- | --- |
| Super administrator | `admin@premiumden.test` |
| Risk team | `risk@premiumden.test` |
| Membership administrator | `membership@premiumden.test` |
| Content administrator | `content@premiumden.test` |
| Author | `author@premiumden.test` |
| Diamond member | `diamond@premiumden.test` |
| Gold member | `gold@premiumden.test` |
| Platinum member | `platinum@premiumden.test` |

These accounts and credentials are for local development only. Production seeding creates membership packages and article categories, but no staff or member accounts.

## Implemented areas

- Premium public home, about, membership and package pages, application guidance, articles, authors and event calendar.
- Multi-step membership application, two references, consent, generated reference and applicant-safe status page.
- Risk review workflow, status timeline, private internal notes, role-separated decisions and private applicant document upload/review.
- Membership package configuration for price, benefits, active state, renewal period and networking level.
- Invoice and item records, payment references and attempts, signed callback verification, event idempotency, and payment amount/currency checks.
- Verified payment activates a membership and provisions a member account, membership number, digital card record, welcome package tracking record, and a single-use, expiring password setup link.
- Member directory and profile editing, with access checked at query, policy and service levels. Diamond, Gold and Platinum networking access uses each package's configured networking level.
- Private member conversations, message notifications, notification center and targeted society notices.
- Public, member and package-targeted events managed by content administrators.
- Article drafts, author submissions, editorial review and publishing.
- Content administration for author invitations, categories and tags, event visibility and artwork, member notices, contact enquiries, and public contact/SEO settings.
- Admin member and payment registers with searchable filters, membership suspension/reactivation, callback-only payment state changes and dashboard summaries.
- Membership renewal invoices and verified renewal callbacks that extend the membership term without replaying initial activation.
- Scheduled membership term expiry with immediate request-time access checks, card deactivation and renewal-based reactivation.
- CMS-editable homepage, About, Membership and joining copy, FAQs, and a society activity/news section with public or member-only visibility.
- Signed application draft resume links, reference matching to active members when submitted name and email both match, and application date/payment filters.
- Member profile photos, dedicated benefits/settings pages, optional email preferences, and branded application/payment/welcome emails.
- Contact page enquiries with spam throttling and a honeypot field; privacy and terms pages; XML sitemap and crawler restrictions for private portal routes.
- Welcome package fulfilment tracking for membership administrators.
- Central staff audit history for application status changes, applicant document decisions, package updates, verified payment confirmations and author invitations.

## Author onboarding

Content administrators and super administrators can invite authors from **Admin → Authors**. Authors receive a signed, one-time setup link that expires after 48 hours, choose a password, and then enter the author studio. Authors can save drafts and submit them for editorial review; only content administrators can publish. Public page copy, FAQs and society activities are managed in the content administration area.

## Email and queues

Set `MAIL_MAILER`, SMTP host, port, username, password and sender in the environment before expecting transactional messages. Invitation, first-access, document request and invoice links are signed and expire. Laravel's database queue tables are included; use `QUEUE_CONNECTION=database` with a continuously running `php artisan queue:work --tries=3` worker. Current mail sends use Laravel's configured transport. Configure Laravel's scheduler (`* * * * * php artisan schedule:run`) to prune 14-day application drafts, send renewal reminders and expire ended membership terms daily.

## Contact and content settings

Public contact details, SEO defaults, key public page copy and FAQs are managed in **Admin → Settings**. Configure a real monitored recipient and SMTP transport before launch. Article, event and activity artwork uses the public disk; member profile photos and applicant identity documents use private storage and are served only through authorized routes.

## Payment integration

Payment collection is disabled by default (`PAYMENT_DRIVER=disabled`). Package prices are intentionally unset in seed data. A membership administrator can enter verified prices in the package settings before an invoice can be issued.

`PaymentGatewayInterface` provides payment initiation and callback verification. Daraja M-PESA Express is implemented for sandbox and production configuration: the invoice link accepts a Kenyan phone number, requests an STK prompt, correlates callbacks to the recorded checkout request, checks transaction status with Daraja, validates amount/currency, and only then activates membership. Configure `PAYMENT_DRIVER=daraja` and the `DARAJA_*` values below. Start with sandbox credentials and verify the Daraja app, shortcode, transaction type, and callback URL before go-live. Safaricom's callbacks are asynchronous; configure a public HTTPS URL and keep the documented callback IP allowlist current. The included mock handler works only outside production and requires `PAYMENT_DRIVER=mock` plus a private `PAYMENT_CALLBACK_SECRET`. Never enable mock in production. Browser redirects do not confirm payment.

Daraja configuration: `DARAJA_ENVIRONMENT=sandbox|production`, `DARAJA_CONSUMER_KEY`, `DARAJA_CONSUMER_SECRET`, `DARAJA_SHORTCODE`, `DARAJA_PASSKEY`, and `DARAJA_TRANSACTION_TYPE=CustomerPayBillOnline|CustomerBuyGoodsOnline`. Safaricom issues the credentials and business shortcode; these are intentionally not included in the repository. Set live credentials only in the production secret manager. See the [Safaricom Daraja Authorization guide](https://developer.safaricom.co.ke/apis/Authorization) and [Getting Started guide](https://developer.safaricom.co.ke/apis/GettingStarted).

## Production configuration

Use `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://premiumden.co.ke`, a fresh `APP_KEY`, and `DB_CONNECTION=mysql` with the correct credentials. Configure SMTP, Daraja live credentials, database-backed queue workers, backups, HTTPS, trusted proxy settings, and monitoring before launch. Build assets with `npm ci && npm run build`, then run `php artisan migrate --force`. Never seed local accounts in production. Applicant identity documents are stored on the private filesystem disk and served only through authenticated admin routes. Set actual package prices, public contact details, and monitored contact recipient in the CMS/environment; these have no safe default in development. The Privacy Notice and Terms page are implementation drafts and need review and approval by qualified Kenyan counsel, including the controller identity and retention schedule, before real applications are accepted.

## Checks

```sh
php artisan test
npm run build
php artisan launch:check
```

The launch check exits nonzero while required production or business configuration is missing. It does not print secrets and always lists the manual sign-offs that still need completion. See [`LAUNCH-CHECKLIST.md`](LAUNCH-CHECKLIST.md) for the release handoff.
# premiumden
