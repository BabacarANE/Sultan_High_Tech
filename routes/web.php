<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Mail\TestMail;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-email', function () {
    try {
        Mail::to('anebabacar221@gmail.com')->send(new TestMail());
        return 'Email de test envoyé';
    } catch (\Exception $e) {
        Log::error('Email sending error: ' . $e->getMessage());
        return 'Failed to send email';
    }
});
Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    $path = $folder . '/' . $filename;
    if (!Storage::exists($path)) {
        abort(404);
    }
    return response()->file(storage_path('app/' . $path));
})->where('filename', '.*');
