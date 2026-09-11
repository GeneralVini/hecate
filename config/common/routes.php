<?php

declare(strict_types=1);

use App\Web;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create()->routes(
        Route::get('/')
            ->action(Web\HomePage\Action::class)
            ->name('home'),
        Route::get('/printers')
            ->action(Web\Printer\Index\Action::class)
            ->name('printer/index'),
        Route::methods(['GET', 'POST'], '/printers/create')
            ->action(Web\Printer\Create\Action::class)
            ->name('printer/create'),
        Route::post('/printers/detect')
            ->action(Web\Printer\Detect\Action::class)
            ->name('printer/detect'),
    ),
];
