# Premium Business Den launch checklist

Run `php artisan launch:check` after configuring the production environment. It reports missing settings without printing secret values. A passing configuration check does not replace the manual sign-offs below.

## Business owner

- Enter the verified organization name and public contact details in **Admin → Settings**.
- Configure the monitored enquiry inbox through `CONTACT_TO_ADDRESS` or the settings page.
- Enter approved Diamond, Gold and Platinum prices, benefits, and renewal periods through **Admin → Membership Packages**.
- Replace any draft public copy with approved society information.

## Safaricom Daraja

- Create and test a Daraja sandbox app.
- Add sandbox values for `PAYMENT_DRIVER=daraja`, `DARAJA_ENVIRONMENT=sandbox`, consumer key/secret, shortcode, passkey, and transaction type to the environment.
- Make the callback endpoint reachable over HTTPS and run an end-to-end sandbox payment.
- Complete Safaricom go-live approval, then set production credentials in the production secret manager and `DARAJA_ENVIRONMENT=production`.
- Confirm reverse proxy client IP handling and keep `DARAJA_CALLBACK_IPS` aligned with Safaricom's current allowlist.
- Verify failed, cancelled, duplicate and successful callbacks against issued invoices before collecting membership fees.

## Legal approval

- Have qualified Kenyan counsel approve the Privacy Notice, Terms, consent text, and retention schedule.
- Confirm the legal data controller identity, member/applicant rights contact, retention periods, and any required processor disclosures.
- Publish the approved wording and confirm it matches the actual processing, deployed hosting and payment provider.

## Physical membership cards

- Select the physical card and NFC vendor and agree the UID/reference format and replacement process.
- Configure each physical UID in **Admin → Members** and verify card issue, loss, suspension, replacement and renewal procedures with the hardware.
- Do not advertise tap, NFC, or QR verification until the vendor integration is tested end to end.

## Production operations

- Set `APP_ENV=production`, `APP_DEBUG=false`, a fresh `APP_KEY`, MySQL credentials, secure session settings and HTTPS.
- Configure SMTP, database queue worker, scheduler, private file storage, backups, log monitoring and trusted proxy settings.
- Build with `npm ci && npm run build`; review migrations and run `php artisan migrate --force` during the approved deployment window.
- Create real staff accounts and verify role access. Never seed local accounts in production.
