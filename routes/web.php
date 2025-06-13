<?php

use App\Http\Controllers\CurrentDutyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PatternController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReserveController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCurrentDutyController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\IntentionController;
use App\Http\Controllers\NotificationController;
use App\Http\Middleware\AdminMiddleware;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('mailto', function(){
Mail::to('mmikusek2211@gmail.com')->send(new TestEmail());
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/intentions', [IntentionController::class, 'index'])->name('intentions');
Route::post('/intention', [IntentionController::class, 'save'])->name('intention.save');

Route::prefix('admin')->middleware(AdminMiddleware::class)->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('intentions', [AdminController::class, 'intentions'])->name('admin.intentions');
    Route::post('confirm-intention', [AdminController::class, 'confirmIntention'])->name('admin.confirm-intention');
    Route::post('intentions-remove', [AdminController::class, 'removeIntention'])->name('admin.intentions.remove');

    Route::post('users/delete', [AdminUserController::class, 'destroy'])->name('admin.users.delete');
    Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::post('users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::get('users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::post('verify-user', [AdminUserController::class, 'verifyUser'])->name('admin.user.verify');
    Route::get('users/create', [AdminUserController::class, 'createUser'])->name('admin.users.create');
    Route::post('users/search', [AdminUserController::class, 'searchUser'])->name('admin.users.search');
    Route::post('users', [AdminUserController::class, 'store'])->name('admin.users.store');

    Route::get('users/{user}/patterns', [AdminUserController::class, 'showUserDuties'])->name('admin.users.patterns');
    Route::post('users/{user}/patterns', [AdminUserController::class, 'userPatternsStore'])->name('admin.user.patterns.store');

    Route::get('current-duty/{duty}/edit', [AdminCurrentDutyController::class, 'edit'])->name('admin.current-duty.edit');
    Route::post('current-duty', [AdminCurrentDutyController::class, 'addUser'])->name('admin.current-duty.store');
    Route::post('delete-current-duty', [AdminCurrentDutyController::class, 'removeCurrentDuty'])->name('admin.current-duty.delete');

    Route::post('messages', [NotificationController::class, 'sendMessages'])->name('admin.messages');
    Route::get('admins', [AdminController::class, 'index'])->name('admin.admins');
    Route::post('admins/update-duty-hours', [AdminController::class, 'updateDutyHours'])->name('admin.admins.updateDutyHours');
    Route::get('duty-hours', [AdminController::class, 'dutyHours'])->name('admin.duty_hours');
    Route::post('assign-duty-hours', [AdminController::class, 'assignDutyHours'])->name('admin.assign_duty_hours');
    Route::post('update-color', [AdminController::class, 'updateColor'])->name('admin.update_color');

    // Add more admin routes here
});

Route::middleware('auth')->group(function () {
    Route::get('remove_account', [ProfileController::class, 'removeAccount'])->name('remove_account');
    Route::post('intention/is_prayer', [IntentionController::class, 'isPrayer'])->name('intentions.is_prayer');
    Route::get('duties', [CurrentDutyController::class, 'index'])->name('current-duty.index');
    Route::post('current-duty', [CurrentDutyController::class, 'store'])->name('current-duty.store');
    Route::post('current-duty-remove', [CurrentDutyController::class, 'destroy'])->name('current-duty.remove');
    Route::delete('patterns/{dutyPattern}', [PatternController::class, 'destroy'])->name('patterns.delete');
    Route::post('patterns', [PatternController::class, 'store'])->name('patterns.store');
    Route::get('patterns', [PatternController::class, 'index'])->name('patterns.index');
    Route::post('suspend_patterns', [PatternController::class, 'suspend'])->name('patterns.suspend');
    Route::post('reserves', [ReserveController::class, 'store'])->name('reserves.store');
    Route::delete('reserves', [ReserveController::class, 'destroy'])->name('reserves.delete');

    Route::get('/reserves', [ReserveController::class, 'index'])->name('reserves.index');
    Route::post('/suspend', [ProfileController::class, 'saveSuspend'])->name('profile.save-suspend');
    Route::get('/suspend', [ProfileController::class, 'editSuspend'])->name('profile.edit-suspend');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
