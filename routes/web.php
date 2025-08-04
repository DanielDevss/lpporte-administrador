<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/order/{folio}/download-ticket', [TicketController::class, 'download'])->name('download.ticket');