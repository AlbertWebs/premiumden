<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\ApplicantDocumentController as AdminApplicantDocumentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\ConversationController;
use App\Http\Controllers\Member\DirectoryController;
use App\Http\Controllers\Member\NotificationController;
use App\Http\Controllers\Member\ProfileController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ArticleController as PublicArticleController;
use App\Http\Controllers\Public\MembershipApplicationController;
use App\Http\Controllers\Public\ApplicantDocumentController;
use App\Http\Controllers\Author\ArticleController as AuthorArticleController;
use App\Http\Controllers\Payments\PaymentWebhookController;
use App\Http\Controllers\Auth\MemberFirstAccessController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Admin\MembershipPackageController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\WelcomePackageController;
use App\Http\Controllers\Admin\TaxonomyController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Admin\ContactEnquiryController;
use App\Http\Controllers\Member\MembershipController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\AuthorController as AdminAuthorController;
use App\Http\Controllers\Admin\AuditEventController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ActivityController as AdminActivityController;
use App\Http\Controllers\Admin\MembershipRenewalController;
use App\Http\Controllers\Public\ActivityController;
use App\Http\Controllers\Member\SettingsController;
use App\Http\Controllers\Member\ProfilePhotoController;
use App\Http\Controllers\Member\MembershipController as MemberMembershipController;
use App\Http\Controllers\Auth\AuthorFirstAccessController;
use App\Http\Controllers\Public\ApplicantInvoiceController;
use App\Http\Controllers\Public\ApplicantInvoicePaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/about', fn () => view('public.about'))->name('about');
Route::view('/team', 'public.team')->name('team');
Route::get('/membership', [HomeController::class, 'membership'])->name('membership');
Route::get('/membership/{package:slug}', [HomeController::class, 'package'])->name('membership.package');
Route::get('/how-to-join', fn () => view('public.how-to-join'))->name('how-to-join');
Route::view('/privacy', 'public.privacy')->name('privacy');
Route::view('/terms', 'public.terms')->name('terms');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:3,10')->name('contact.store');
Route::get('/articles', [PublicArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article:slug}', [PublicArticleController::class, 'show'])->name('articles.show');
Route::get('/authors', [PublicArticleController::class, 'authors'])->name('authors.index');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
Route::get('/activities/{slug}', [ActivityController::class, 'show'])->name('activities.show');
Route::get('/sitemap.xml', [PublicArticleController::class, 'sitemap'])->name('sitemap');
Route::post('/webhooks/payments/{driver}', PaymentWebhookController::class)->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])->middleware('throttle:60,1')->name('payments.webhook');
Route::get('/member/setup/{email}/{token}', [MemberFirstAccessController::class, 'create'])->middleware(['signed', 'throttle:10,1'])->name('member.first-access.create');
Route::post('/member/setup/{email}/{token}', [MemberFirstAccessController::class, 'store'])->middleware(['signed', 'throttle:5,1'])->name('member.first-access.store');
Route::get('/author/setup/{email}/{token}', [AuthorFirstAccessController::class, 'create'])->middleware(['signed', 'throttle:10,1'])->name('author.first-access.create');
Route::post('/author/setup/{email}/{token}', [AuthorFirstAccessController::class, 'store'])->middleware(['signed', 'throttle:5,1'])->name('author.first-access.store');
Route::get('/become-a-member', [MembershipApplicationController::class, 'create'])->name('application.intro');
Route::post('/become-a-member', [MembershipApplicationController::class, 'store'])->middleware('throttle:3,1')->name('application.store');
Route::post('/membership-reference/validate', \App\Http\Controllers\Public\MembershipReferenceValidationController::class)->middleware('throttle:20,1')->name('application.reference.validate');
Route::get('/application/{reference}/resume/{token}', [MembershipApplicationController::class, 'resume'])->middleware(['signed', 'throttle:10,1'])->name('application.resume');
Route::get('/application/{reference}/received', [MembershipApplicationController::class, 'received'])->middleware('throttle:10,1')->name('application.received');
Route::get('/application-reference/{reference}/{response}', [\App\Http\Controllers\Public\ApplicationReferenceResponseController::class, '__invoke'])->middleware(['signed', 'throttle:10,1'])->name('application.reference.respond');
Route::view('/application-reference-response/{response}', 'public.application-reference-response')->name('application.reference.response');
Route::get('/invoice/{reference}', [ApplicantInvoiceController::class, 'show'])->middleware(['signed', 'throttle:10,1'])->name('application.invoice.show');
Route::post('/invoice/{reference}/pay', ApplicantInvoicePaymentController::class)->middleware(['signed', 'throttle:5,1'])->name('application.invoice.pay');
Route::get('/application/{reference}/documents/{token}', [ApplicantDocumentController::class, 'create'])->middleware(['signed', 'throttle:10,1'])->name('application.documents.create');
Route::post('/application/{reference}/documents/{token}', [ApplicantDocumentController::class, 'store'])->middleware(['signed', 'throttle:5,1'])->name('application.documents.store');
Route::post('/application/status', [MembershipApplicationController::class, 'lookup'])->middleware('throttle:10,1')->name('application.lookup');
Route::get('/application/{reference}', [MembershipApplicationController::class, 'show'])->middleware('throttle:10,1')->name('application.status');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_administrator,risk_team,membership_administrator'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::patch('/applications/{application}', [AdminApplicationController::class, 'transition'])->name('applications.transition');
    Route::post('/applications/{application}/documents/resend', [AdminApplicationController::class, 'resendDocumentLink'])->name('applications.documents.resend');
    Route::get('/documents/{document}/view', [AdminApplicantDocumentController::class, 'show'])->name('documents.show');
    Route::patch('/documents/{document}', [AdminApplicantDocumentController::class, 'decide'])->name('documents.decide');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_administrator,membership_administrator'])->group(function (): void {
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::patch('/members/{member}/status', [MemberController::class, 'updateStatus'])->name('members.status');
    Route::patch('/cards/{card}', [MemberController::class, 'updateCard'])->name('cards.update');
    Route::post('/memberships/{membership}/renewals', [MembershipRenewalController::class, 'store'])->name('memberships.renewals.store');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
});

Route::prefix('admin/membership-packages')->name('admin.packages.')->middleware(['auth', 'role:super_administrator,membership_administrator'])->group(function (): void {
    Route::get('/', [MembershipPackageController::class, 'index'])->name('index');
    Route::get('/{package}/edit', [MembershipPackageController::class, 'edit'])->name('edit');
    Route::put('/{package}', [MembershipPackageController::class, 'update'])->name('update');
});

Route::prefix('admin/welcome-packages')->name('admin.welcome-packages.')->middleware(['auth', 'role:super_administrator,membership_administrator'])->group(function (): void {
    Route::get('/', [WelcomePackageController::class, 'index'])->name('index');
    Route::patch('/{welcomePackage}', [WelcomePackageController::class, 'update'])->name('update');
});

Route::prefix('admin/member-deals')->name('admin.deals.')->middleware(['auth', 'role:super_administrator,membership_administrator,content_administrator'])->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Admin\MemberDealController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\MemberDealController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\MemberDealController::class, 'store'])->name('store');
    Route::get('/{deal}/edit', [\App\Http\Controllers\Admin\MemberDealController::class, 'edit'])->name('edit');
    Route::put('/{deal}', [\App\Http\Controllers\Admin\MemberDealController::class, 'update'])->name('update');
    Route::get('/{deal}/applications', [\App\Http\Controllers\Admin\MemberDealController::class, 'applications'])->name('applications');
    Route::patch('/{deal}/applications/{application}', [\App\Http\Controllers\Admin\MemberDealController::class, 'updateApplication'])->name('applications.update');
    Route::get('/{deal}/documents/{document}', [\App\Http\Controllers\Admin\MemberDealController::class, 'document'])->name('documents.show');
    Route::delete('/{deal}/documents/{document}', [\App\Http\Controllers\Admin\MemberDealController::class, 'destroyDocument'])->name('documents.destroy');
});

Route::prefix('admin/content')->name('admin.articles.')->middleware(['auth', 'role:super_administrator,content_administrator'])->group(function (): void {
    Route::get('/articles', [AdminArticleController::class, 'index'])->name('index');
    Route::get('/articles/create', [AdminArticleController::class, 'create'])->name('create');
    Route::post('/articles', [AdminArticleController::class, 'store'])->name('store');
    Route::get('/articles/{article}/edit', [AdminArticleController::class, 'edit'])->name('edit');
    Route::put('/articles/{article}', [AdminArticleController::class, 'update'])->name('update');
    Route::post('/articles/{article}/review', [AdminArticleController::class, 'review'])->name('review');
});

Route::prefix('admin/content/events')->name('admin.events.')->middleware(['auth', 'role:super_administrator,content_administrator'])->group(function (): void {
    Route::get('/', [AdminEventController::class, 'index'])->name('index');
    Route::get('/create', [AdminEventController::class, 'create'])->name('create');
    Route::post('/', [AdminEventController::class, 'store'])->name('store');
    Route::get('/{event}/edit', [AdminEventController::class, 'edit'])->name('edit');
    Route::put('/{event}', [AdminEventController::class, 'update'])->name('update');
});

Route::prefix('admin/content/announcements')->name('admin.announcements.')->middleware(['auth', 'role:super_administrator,content_administrator'])->group(function (): void {
    Route::get('/', [AnnouncementController::class, 'index'])->name('index');
    Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
    Route::post('/', [AnnouncementController::class, 'store'])->name('store');
    Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
    Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
});

Route::prefix('admin/content/taxonomy')->name('admin.taxonomy.')->middleware(['auth', 'role:super_administrator,content_administrator'])->group(function (): void {
    Route::get('/', [TaxonomyController::class, 'index'])->name('index');
    Route::post('/categories', [TaxonomyController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [TaxonomyController::class, 'updateCategory'])->name('categories.update');
    Route::post('/tags', [TaxonomyController::class, 'storeTag'])->name('tags.store');
});

Route::prefix('admin/content/enquiries')->name('admin.enquiries.')->middleware(['auth', 'role:super_administrator,content_administrator'])->group(function (): void {
    Route::get('/', [ContactEnquiryController::class, 'index'])->name('index');
    Route::patch('/{enquiry}', [ContactEnquiryController::class, 'update'])->name('update');
});

Route::prefix('admin/content/settings')->name('admin.settings.')->middleware(['auth', 'role:super_administrator,content_administrator'])->group(function (): void {
    Route::get('/', [SiteSettingsController::class, 'edit'])->name('edit');
    Route::put('/', [SiteSettingsController::class, 'update'])->name('update');
});

Route::prefix('admin/content/authors')->name('admin.authors.')->middleware(['auth', 'role:super_administrator,content_administrator'])->group(function (): void {
    Route::get('/', [AdminAuthorController::class, 'index'])->name('index');
    Route::post('/', [AdminAuthorController::class, 'store'])->name('store');
});

Route::prefix('admin/content/activities')->name('admin.activities.')->middleware(['auth', 'role:super_administrator,content_administrator'])->group(function (): void {
    Route::get('/', [AdminActivityController::class, 'index'])->name('index');
    Route::get('/create', [AdminActivityController::class, 'create'])->name('create');
    Route::post('/', [AdminActivityController::class, 'store'])->name('store');
    Route::get('/{activity}/edit', [AdminActivityController::class, 'edit'])->name('edit');
    Route::put('/{activity}', [AdminActivityController::class, 'update'])->name('update');
});

Route::get('/admin/audit', [AuditEventController::class, 'index'])->middleware(['auth', 'role:super_administrator,risk_team,membership_administrator'])->name('admin.audit.index');

Route::prefix('author/articles')->name('author.articles.')->middleware(['auth', 'role:author'])->group(function (): void {
    Route::get('/', [AuthorArticleController::class, 'index'])->name('index');
    Route::get('/create', [AuthorArticleController::class, 'create'])->name('create');
    Route::post('/', [AuthorArticleController::class, 'store'])->name('store');
    Route::get('/{article}/preview', [AuthorArticleController::class, 'preview'])->name('preview');
    Route::get('/{article}/edit', [AuthorArticleController::class, 'edit'])->name('edit');
    Route::put('/{article}', [AuthorArticleController::class, 'update'])->name('update');
    Route::post('/{article}/submit', [AuthorArticleController::class, 'submit'])->name('submit');
});

Route::prefix('member')->name('member.')->middleware(['auth', 'active.member'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/deals', [\App\Http\Controllers\Member\DealController::class, 'index'])->name('deals.index');
    Route::post('/deals/{deal}/apply', [\App\Http\Controllers\Member\DealController::class, 'apply'])->middleware('throttle:10,1')->name('deals.apply');
    Route::get('/deals/{deal}/documents/{document}', [\App\Http\Controllers\Member\DealController::class, 'document'])->name('deals.documents.show');
    Route::get('/membership', [MembershipController::class, 'show'])->name('membership.show');
    Route::get('/billing', [\App\Http\Controllers\Member\BillingController::class, 'index'])->name('billing.index');
    Route::get('/benefits', [MemberMembershipController::class, 'benefits'])->name('benefits');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/card', [\App\Http\Controllers\Member\CardController::class, 'show'])->name('card.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/photo', [ProfilePhotoController::class, 'own'])->name('profile.photo');
    Route::get('/directory', [DirectoryController::class, 'index'])->name('directory.index');
    Route::get('/directory/{member}', [DirectoryController::class, 'show'])->middleware('can:view,member')->name('directory.show');
    Route::get('/directory/{member}/photo', [ProfilePhotoController::class, 'member'])->name('directory.photo');
    Route::get('/messages', [ConversationController::class, 'index'])->name('messages.index');
    Route::post('/messages/start/{member}', [ConversationController::class, 'start'])->middleware('throttle:20,1')->name('messages.start');
    Route::get('/messages/{conversation}', [ConversationController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}', [ConversationController::class, 'store'])->middleware('throttle:30,1')->name('messages.store');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/events', [EventController::class, 'memberIndex'])->name('events.index');
    Route::get('/events/{slug}', [EventController::class, 'memberShow'])->name('events.show');
    Route::get('/activities', [ActivityController::class, 'memberIndex'])->name('activities.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
});
