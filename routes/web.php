<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

use Illuminate\Notifications\Messages\MailMessage;

Route::get('/', function () {
    return view('welcome');
});
