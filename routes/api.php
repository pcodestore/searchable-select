<?php

use Pcode\SearchableSelect\Http\Controllers\SearchableSelectController;

Route::get('/{resource}', SearchableSelectController::class."@index");
