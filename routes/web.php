<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\BrochureController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\IconController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\PpdbController as AdminPpdbController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\VisitController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\NewsController as GuruNewsController;
use App\Http\Controllers\Guru\ProfileController as GuruProfileController;
use App\Http\Controllers\Public\AdiwiyataController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\PpdbController;
use App\Http\Controllers\Public\SiteController;
use App\Http\Controllers\Public\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Situs publik
|--------------------------------------------------------------------------
*/

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/tentang-kami', [SiteController::class, 'about'])->name('about');
Route::get('/program', [SiteController::class, 'programs'])->name('programs.index');
Route::get('/program/{program:slug}', [SiteController::class, 'program'])->name('programs.show');
Route::get('/berita', [SiteController::class, 'news'])->name('news.index');
Route::get('/berita/{news:slug}', [SiteController::class, 'newsItem'])->name('news.show');
Route::get('/tim-guru', [SiteController::class, 'teachers'])->name('teachers.index');
Route::get('/galeri/{album:slug}', [SiteController::class, 'album'])->name('gallery.album');

Route::get('/kontak', [ContactController::class, 'index'])->name('contact');
Route::post('/kontak', [ContactController::class, 'storeMessage'])
    ->middleware('throttle:6,1')
    ->name('contact.store');
Route::post('/kunjungan', [ContactController::class, 'storeVisit'])
    ->middleware('throttle:6,1')
    ->name('visits.store');

Route::get('/ppdb', [PpdbController::class, 'create'])->name('ppdb.create');
Route::post('/ppdb', [PpdbController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('ppdb.store');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', fn () => response(
    "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /guru\nSitemap: ".route('sitemap')."\n",
    200,
    ['Content-Type' => 'text/plain'],
))->name('robots');

Route::get('/adiwiyata', [AdiwiyataController::class, 'index'])->name('adiwiyata');
Route::middleware(['auth', 'verified', 'role:admin'])->group(function (): void {
    Route::post('/adiwiyata/save', [AdiwiyataController::class, 'save'])->name('adiwiyata.save');
    Route::post('/adiwiyata/reset', [AdiwiyataController::class, 'reset'])->name('adiwiyata.reset');
});

/*
|--------------------------------------------------------------------------
| Panel admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('news', [AdminNewsController::class, 'index'])->name('news.index');
        Route::get('news/create', [AdminNewsController::class, 'create'])->name('news.create');
        Route::post('news', [AdminNewsController::class, 'store'])->name('news.store');
        Route::get('news/{news:slug}/edit', [AdminNewsController::class, 'edit'])->name('news.edit');
        Route::put('news/{news:slug}', [AdminNewsController::class, 'update'])->name('news.update');
        Route::delete('news/{news:slug}', [AdminNewsController::class, 'destroy'])->name('news.destroy');

        Route::get('programs', [ProgramController::class, 'index'])->name('programs.index');
        Route::get('programs/create', [ProgramController::class, 'create'])->name('programs.create');
        Route::post('programs', [ProgramController::class, 'store'])->name('programs.store');
        Route::get('programs/{program:slug}/edit', [ProgramController::class, 'edit'])->name('programs.edit');
        Route::put('programs/{program:slug}', [ProgramController::class, 'update'])->name('programs.update');
        Route::delete('programs/{program:slug}', [ProgramController::class, 'destroy'])->name('programs.destroy');

        Route::get('teachers', [TeacherController::class, 'index'])->name('teachers.index');
        Route::get('teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
        Route::post('teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::get('teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
        Route::put('teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

        Route::get('brochures', [BrochureController::class, 'index'])->name('brochures.index');
        Route::get('brochures/create', [BrochureController::class, 'create'])->name('brochures.create');
        Route::post('brochures', [BrochureController::class, 'store'])->name('brochures.store');
        Route::get('brochures/{brochure}/edit', [BrochureController::class, 'edit'])->name('brochures.edit');
        Route::put('brochures/{brochure}', [BrochureController::class, 'update'])->name('brochures.update');
        Route::delete('brochures/{brochure}', [BrochureController::class, 'destroy'])->name('brochures.destroy');

        Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
        Route::get('gallery/create', [GalleryController::class, 'create'])->name('gallery.create');
        Route::post('gallery', [GalleryController::class, 'store'])->name('gallery.store');
        Route::get('gallery/{album:slug}/edit', [GalleryController::class, 'edit'])->name('gallery.edit');
        Route::put('gallery/{album:slug}', [GalleryController::class, 'update'])->name('gallery.update');
        Route::delete('gallery/{album:slug}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
        Route::get('gallery/{album:slug}/photos', [GalleryController::class, 'photos'])->name('gallery.photos');
        Route::post('gallery/{album:slug}/photos', [GalleryController::class, 'storePhotos'])->name('gallery.photos.store');
        Route::put('gallery-photos/{photo}', [GalleryController::class, 'updatePhoto'])->name('gallery.photos.update');
        Route::delete('gallery-photos/{photo}', [GalleryController::class, 'destroyPhoto'])->name('gallery.photos.destroy');

        Route::get('videos', [VideoController::class, 'index'])->name('videos.index');
        Route::get('videos/create', [VideoController::class, 'create'])->name('videos.create');
        Route::post('videos', [VideoController::class, 'store'])->name('videos.store');
        Route::get('videos/{video}/edit', [VideoController::class, 'edit'])->name('videos.edit');
        Route::put('videos/{video}', [VideoController::class, 'update'])->name('videos.update');
        Route::delete('videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');

        Route::get('icons', IconController::class)->name('icons');

        Route::get('contacts', [AdminContactController::class, 'index'])->name('contacts.index');
        Route::put('contacts/{contact}/read', [AdminContactController::class, 'markRead'])->name('contacts.read');
        Route::delete('contacts/{contact}', [AdminContactController::class, 'destroy'])->name('contacts.destroy');

        Route::get('visits', [VisitController::class, 'index'])->name('visits.index');
        Route::put('visits/{visit}', [VisitController::class, 'update'])->name('visits.update');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/{user}/password-reset', [UserController::class, 'sendPasswordReset'])->name('users.password-reset');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings');
        Route::put('settings/identity', [SettingController::class, 'updateIdentity'])->name('settings.identity');
        Route::put('settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
        Route::post('settings/home', [SettingController::class, 'updateHome'])->name('settings.home');
        Route::put('settings/ppdb', [SettingController::class, 'updatePpdb'])->name('settings.ppdb');
        Route::post('settings/branding', [SettingController::class, 'updateBranding'])->name('settings.branding');

        Route::get('ppdb', [AdminPpdbController::class, 'index'])->name('ppdb.index');
        Route::get('ppdb/export', [AdminPpdbController::class, 'export'])->name('ppdb.export');
        Route::get('ppdb/{registration:registration_number}', [AdminPpdbController::class, 'show'])->name('ppdb.show');
        Route::put('ppdb/{registration:registration_number}', [AdminPpdbController::class, 'update'])->name('ppdb.update');
        Route::get('ppdb/{registration:registration_number}/document/{type}', [AdminPpdbController::class, 'document'])->name('ppdb.document');
    });

/*
|--------------------------------------------------------------------------
| Panel guru
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:guru'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function (): void {
        Route::get('/', GuruDashboardController::class)->name('dashboard');

        Route::get('news', [GuruNewsController::class, 'index'])->name('news.index');
        Route::get('news/create', [GuruNewsController::class, 'create'])->name('news.create');
        Route::post('news', [GuruNewsController::class, 'store'])->name('news.store');
        Route::get('news/{news:slug}/edit', [GuruNewsController::class, 'edit'])->name('news.edit');
        Route::put('news/{news:slug}', [GuruNewsController::class, 'update'])->name('news.update');
        Route::delete('news/{news:slug}', [GuruNewsController::class, 'destroy'])->name('news.destroy');

        Route::get('profile', [GuruProfileController::class, 'edit'])->name('profile');
        Route::put('profile', [GuruProfileController::class, 'update'])->name('profile.update');
    });

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
