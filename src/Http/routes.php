<?php

Route::get('/tfa', 'Auth\TwoFactorAuthController@showMethodSelectionForm')->name('tfa');
Route::get('/tfa/error', 'Auth\TwoFactorAuthController@showError')->name('tfa.error');
Route::post('/tfa/challenge', 'Auth\TwoFactorAuthController@challenge')->name('tfa.challenge')->middleware('throttle:5,1,tfa.challenge');
Route::get('/tfa/{method}/verify', 'Auth\TwoFactorAuthController@showVerificationForm')->name('tfa.verify.form');
Route::post('/tfa/{method}/verify', 'Auth\TwoFactorAuthController@verify')->middleware('throttle:10,1,tfa.verify');
Route::get('/tfa/{method}/enrol', 'Auth\TwoFactorAuthController@begin')->name('tfa.enrol')->middleware('throttle:10,1,tfa.enrol');
Route::get('/tfa/{method}/enrol/setup', 'Auth\TwoFactorAuthController@showSetupForm')->name('tfa.enrolment.setup')->middleware('throttle:10,1');
Route::post('/tfa/{method}/enrol/setup', 'Auth\TwoFactorAuthController@setup')->middleware('throttle:10,1,tfa.setup');
Route::get('/tfa/{method}/enrolled', 'Auth\TwoFactorAuthController@showEnrolled')->name('tfa.enrolled');
Route::get('/tfa/{method}/disenrol', 'Auth\TwoFactorAuthController@disenrol');
Route::get('/tfa/{method}/disenrolled', 'Auth\TwoFactorAuthController@showDisenrolled')->name('tfa.disenrolled');