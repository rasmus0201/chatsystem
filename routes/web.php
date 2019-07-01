<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Bundsgaard\ChatSupport\Storage\Room;

Route::get('/', function () {
    return view('chatsupport::user', [
        'rooms' => Room::all()
    ]);
});

Route::get('/agent', function (){
    return view('chatsupport::agent');
});
