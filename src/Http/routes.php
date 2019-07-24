<?php

Route::get('/tfa', 'Auth\TwoFactorAuthController@showMethodSelectionForm')->name('tfa');
Route::get('/tfa/error', 'Auth\TwoFactorAuthController@showError')->name('tfa.error');
Route::post('/tfa/challenge', 'Auth\TwoFactorAuthController@challenge')->name('tfa.challenge');
Route::get('/tfa/{method}/verify', 'Auth\TwoFactorAuthController@showVerificationForm')->name('tfa.verify.form');
Route::post('/tfa/{method}/verify', 'Auth\TwoFactorAuthController@verify');
Route::get('/tfa/{method}/enrol', 'Auth\TwoFactorAuthController@begin')->name('tfa.enrol');
Route::get('/tfa/{method}/enrol/setup', 'Auth\TwoFactorAuthController@showSetupForm')->name('tfa.enrolment.setup');
Route::post('/tfa/{method}/enrol/setup', 'Auth\TwoFactorAuthController@setup');
Route::get('/tfa/{method}/enrolled', 'Auth\TwoFactorAuthController@showEnrolled')->name('tfa.enrolled');
Route::get('/tfa/{method}/disenrol', 'Auth\TwoFactorAuthController@disenrol');
Route::get('/tfa/{method}/disenrolled', 'Auth\TwoFactorAuthController@showDisenrolled')->name('tfa.disenrolled');