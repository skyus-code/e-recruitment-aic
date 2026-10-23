<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\JobController as AdminJobController;
use App\Http\Controllers\Admin\KeywordController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\ScreeningController as AdminScreening;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\StatisticsController as AdminStatistics;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\Manajer\DashboardController as ManajerDashboard;
use App\Http\Controllers\Manajer\DecisionController as ManajerDecisionController;
use App\Http\Controllers\Manajer\DossierController as ManajerDossierController;
use App\Http\Controllers\Manajer\InterviewController as ManajerInterviewController;
use App\Http\Controllers\Pelamar\ApplicationController as PelamarApplicationController;
use App\Http\Controllers\Pelamar\CvFileController;
use App\Http\Controllers\Pelamar\DashboardController as PelamarDashboard;
use App\Http\Controllers\Pelamar\InterviewController as PelamarInterviewController;
use App\Http\Controllers\Pelamar\JobController as PelamarJobController;
use App\Http\Controllers\Pelamar\ProfileController as PelamarProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Publik
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/lowongan', [JobPostingController::class, 'index'])->name('jobs.index');
Route::get('/lowongan/{job}', [JobPostingController::class, 'show'])->name('jobs.show');

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Rute Terproteksi
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------
    | Berkas terproteksi (CV & ijazah)
    |--------------------------------------------------------------------
    */

    Route::get('/files/cv/{application}/preview', [FileController::class, 'previewCv'])->name('files.cv.preview');
    Route::get('/files/cv/{application}', [FileController::class, 'cv'])->name('files.cv');
    Route::get('/files/ijazah/{application}', [FileController::class, 'ijazah'])->name('files.ijazah');
    Route::get('/files/ijazah/{application}/preview', [FileController::class, 'previewIjazah'])->name('files.ijazah.preview');

    /*
    |--------------------------------------------------------------------
    | A. Pelamar
    |--------------------------------------------------------------------
    */

    Route::middleware(['role:pelamar'])->prefix('pelamar')->name('pelamar.')->group(function () {
        Route::get('/dashboard', [PelamarDashboard::class, 'index'])->name('dashboard');
        Route::get('/profile', [PelamarProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [PelamarProfileController::class, 'update'])->name('profile.update');

        Route::post('/profile/educations', [PelamarProfileController::class, 'storeEducation'])->name('profile.educations.store');
        Route::delete('/profile/educations/{education}', [PelamarProfileController::class, 'destroyEducation'])->name('profile.educations.destroy');
        Route::post('/profile/experiences', [PelamarProfileController::class, 'storeExperience'])->name('profile.experiences.store');
        Route::delete('/profile/experiences/{experience}', [PelamarProfileController::class, 'destroyExperience'])->name('profile.experiences.destroy');
        Route::post('/profile/skills', [PelamarProfileController::class, 'storeSkill'])->name('profile.skills.store');

        Route::get('/jobs', [PelamarJobController::class, 'index'])->name('jobs.index');

        // Titik masuk pembuatan lamaran. Penjagaan "email harus terverifikasi"
        // dipasang di server lewat middleware "verified.email" sehingga tidak
        // dapat dilewati lewat POST manual atau pengulangan form.
        Route::middleware('verified.email')->group(function () {
            Route::get('/apply/{job}', [PelamarApplicationController::class, 'create'])->name('apply.create');
            Route::post('/apply/{job}', [PelamarApplicationController::class, 'store'])->name('apply.store');
        });

        Route::get('/applications', [PelamarApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{application}', [PelamarApplicationController::class, 'show'])->name('applications.show');
        Route::get('/analysis/{application}', [PelamarApplicationController::class, 'showAnalysis'])->name('applications.analysis');

        Route::resource('cv-files', CvFileController::class)->only(['index', 'store', 'destroy']);
        Route::get('/cv-files/{cvFile}/preview', [CvFileController::class, 'preview'])->name('cv-files.preview');
        Route::get('/cv-files/{cvFile}/download', [CvFileController::class, 'download'])->name('cv-files.download');
        Route::delete('/degree-files/{degreeCertificationFile}', [CvFileController::class, 'destroyDegree'])->name('degree-files.destroy');

        Route::get('/interviews', [PelamarInterviewController::class, 'index'])->name('interviews.index');
    });

    /*
    |--------------------------------------------------------------------
    | B. Admin
    |--------------------------------------------------------------------
    */

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        Route::resource('jobs', AdminJobController::class);
        Route::post('/jobs/{job}/keywords', [AdminJobController::class, 'storeKeyword'])->name('jobs.keywords.store');
        Route::put('/jobs/{job}/keywords/{keyword}', [AdminJobController::class, 'updateKeyword'])->name('jobs.keywords.update');
        Route::delete('/jobs/{job}/keywords/{keyword}', [AdminJobController::class, 'destroyKeyword'])->name('jobs.keywords.destroy');

        Route::get('/screening', [AdminScreening::class, 'index'])->name('screening.index');
        Route::post('/screening/{application}', [AdminScreening::class, 'startNlp'])->name('screening.start');
        Route::get('/screening/{application}', [AdminScreening::class, 'show'])->name('screening.show');
        Route::post('/decide/{application}', [AdminScreening::class, 'decide'])->name('screening.decide');

        Route::get('/schedules', [AdminScheduleController::class, 'index'])->name('schedules.index');
        Route::post('/schedule/{application}', [AdminScheduleController::class, 'store'])->name('schedule.store');
        Route::delete('/schedules/{schedule}', [AdminScheduleController::class, 'destroy'])->name('schedules.destroy');

        Route::get('/statistics', [AdminStatistics::class, 'index'])->name('statistics.index');
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');

        Route::post('/departments', [AdminSettingsController::class, 'storeDepartment'])->name('settings.departments.store');
        Route::put('/departments/{department}', [AdminSettingsController::class, 'updateDepartment'])->name('settings.departments.update');
        Route::delete('/departments/{department}', [AdminSettingsController::class, 'destroyDepartment'])->name('settings.departments.destroy');

        Route::resource('keywords', KeywordController::class)->except(['show']);
        Route::get('/files/ijazah/{application}', [FileController::class, 'ijazah'])->name('files.ijazah.admin');
    });

    /*
    |--------------------------------------------------------------------
    | C. Manajer
    |--------------------------------------------------------------------
    */

    Route::middleware(['role:manajer'])->prefix('manajer')->name('manajer.')->group(function () {
        Route::get('/dashboard', [ManajerDashboard::class, 'index'])->name('dashboard');

        Route::get('/interviews', [ManajerInterviewController::class, 'index'])->name('interviews.index');
        Route::get('/interviews/{application}', [ManajerInterviewController::class, 'show'])->name('interviews.show');
        Route::post('/interviews/{application}/assessment', [ManajerInterviewController::class, 'storeAssessment'])->name('interviews.assessment.store');

        Route::get('/dossier', [ManajerDossierController::class, 'index'])->name('dossier.index');
        Route::get('/dossier/{application}', [ManajerDossierController::class, 'show'])->name('dossier.show');

        Route::get('/decisions', [ManajerDecisionController::class, 'index'])->name('decisions.index');
        Route::get('/decisions/{application}/rekap', [ManajerDecisionController::class, 'rekap'])->name('decisions.rekap');
        Route::post('/decide/{application}', [ManajerDecisionController::class, 'update'])->name('decisions.update');
    });
});
