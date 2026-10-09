<?php

use App\Enums\ApplicationStatus;
use App\Models\MembershipApplication;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\ExpireMemberships;
use App\Services\SendMembershipRenewalReminders;
use App\Models\MembershipPackage;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

Artisan::command('application-drafts:prune', function (): void {
    $deleted = MembershipApplication::where('status', ApplicationStatus::Draft)
        ->whereNotNull('draft_expires_at')->where('draft_expires_at', '<=', now())->delete();
    $this->info("Removed {$deleted} expired application drafts.");
})->purpose('Remove expired application drafts and their reference data');

Artisan::command('memberships:expire', function (ExpireMemberships $expire): void {
    $count = $expire->handle();
    $this->info("Expired {$count} memberships.");
})->purpose('Deactivate memberships whose terms have ended');

Artisan::command('memberships:renewal-reminders', function (SendMembershipRenewalReminders $reminders): void {
    $count = $reminders->handle();
    $this->info("Sent {$count} renewal reminders.");
})->purpose('Notify members whose terms are approaching expiry');

Schedule::command('application-drafts:prune')->daily();
Schedule::command('memberships:expire')->dailyAt('01:10');
Schedule::command('memberships:renewal-reminders')->dailyAt('08:00');

Artisan::command('launch:check', function (): int {
    $checks = [
        'Production environment' => app()->environment('production'),
        'Debug disabled' => ! config('app.debug'),
        'Application key configured' => filled(config('app.key')),
        'HTTPS application URL' => str_starts_with((string) config('app.url'), 'https://'),
        'MySQL database selected' => config('database.default') === 'mysql',
        'Database queue selected' => config('queue.default') === 'database',
        'Transactional mail configured' => ! in_array(config('mail.default'), ['log', 'array'], true),
        'Daraja selected' => config('services.payment.driver') === 'daraja',
        'Daraja live environment selected' => config('services.payment.daraja.environment') === 'production',
        'Daraja consumer key configured' => filled(config('services.payment.daraja.consumer_key')),
        'Daraja consumer secret configured' => filled(config('services.payment.daraja.consumer_secret')),
        'Daraja shortcode configured' => filled(config('services.payment.daraja.shortcode')),
        'Daraja passkey configured' => filled(config('services.payment.daraja.passkey')),
        'Daraja callback IP allowlist configured' => count((array) config('services.payment.daraja.callback_ips', [])) > 0,
    ];

    $requiredSettings = ['public_contact_email', 'public_contact_phone', 'office_location'];
    foreach ($requiredSettings as $key) {
        $checks['Website setting: '.$key] = Schema::hasTable('site_settings') && filled(SiteSetting::value($key));
    }
    $checks['Contact enquiry recipient configured'] = Schema::hasTable('site_settings')
        && (filled(SiteSetting::value('contact_recipient_email')) || filled(config('services.contact.to_address')));

    $packagesReady = Schema::hasTable('membership_packages') && MembershipPackage::where('is_active', true)
        ->whereNotNull('price')->where('price', '>', 0)->count() === MembershipPackage::where('is_active', true)->count()
        && MembershipPackage::where('is_active', true)->exists();
    $checks['All active packages have verified prices'] = $packagesReady;

    foreach ($checks as $label => $ready) {
        $this->line(($ready ? '<info>PASS</info> ' : '<error>TODO</error> ').$label);
    }
    $this->newLine();
    $this->warn('Manual release gates: counsel-approved Privacy Notice and Terms; Safaricom sandbox and production sign-off; verified business/package copy; physical card vendor testing. See LAUNCH-CHECKLIST.md.');
    $failed = count(array_filter($checks, fn (bool $ready): bool => ! $ready));
    if ($failed > 0) {
        $this->error("{$failed} launch configuration item(s) remain.");
        return self::FAILURE;
    }
    $this->info('Configuration checks passed. Complete each manual release gate before launch.');
    return self::SUCCESS;
})->purpose('Report production launch configuration gaps without displaying secrets');
