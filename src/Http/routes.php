<?php

$authController = config()->get('challenge.routing.controller');

Route::get('/tfa', $authController . '@showMethodSelectionForm')->name('challenge');
Route::get('/tfa/error', $authController . '@showError')->name('challenge.error');
Route::post('/tfa/dispatch', $authController . '@challenge')->name('challenge.dispatch')->middleware('throttle:10,1,challenge.dispatch');
Route::get('/tfa/{method}/verify', $authController . '@showVerificationForm')->name('challenge.verify.form');
Route::post('/tfa/{method}/verify', $authController . '@verify')->middleware('throttle:10,1,challenge.verify');
Route::get('/tfa/{method}/enrol', $authController . '@begin')->name('challenge.enrol')->middleware('throttle:10,1,challenge.enrol');
Route::get('/tfa/{method}/enrol/setup', $authController . '@showSetupForm')->name('challenge.enrolment.setup')->middleware('throttle:10,1');
Route::post('/tfa/{method}/enrol/setup', $authController . '@setup')->middleware('throttle:10,1,challenge.setup');
Route::get('/tfa/{method}/enrolled', $authController . '@showEnrolled')->name('challenge.enrolled');
Route::get('/tfa/{method}/disenrol', $authController . '@disenrol');
Route::get('/tfa/{method}/disenrolled', $authController . '@showDisenrolled')->name('challenge.disenrolled');