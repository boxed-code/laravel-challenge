<?php

Route::get('/tfa', 'Auth\ChallengeController@showMethodSelectionForm')->name('challenge');
Route::get('/tfa/error', 'Auth\ChallengeController@showError')->name('challenge.error');
Route::post('/tfa/dispatch', 'Auth\ChallengeController@challenge')->name('challenge.dispatch')->middleware('throttle:10,1,challenge.dispatch');
Route::get('/tfa/{method}/verify', 'Auth\ChallengeController@showVerificationForm')->name('challenge.verify.form');
Route::post('/tfa/{method}/verify', 'Auth\ChallengeController@verify')->middleware('throttle:10,1,challenge.verify');
Route::get('/tfa/{method}/enrol', 'Auth\ChallengeController@begin')->name('challenge.enrol')->middleware('throttle:10,1,challenge.enrol');
Route::get('/tfa/{method}/enrol/setup', 'Auth\ChallengeController@showSetupForm')->name('challenge.enrolment.setup')->middleware('throttle:10,1');
Route::post('/tfa/{method}/enrol/setup', 'Auth\ChallengeController@setup')->middleware('throttle:10,1,challenge.setup');
Route::get('/tfa/{method}/enrolled', 'Auth\ChallengeController@showEnrolled')->name('challenge.enrolled');
Route::get('/tfa/{method}/disenrol', 'Auth\ChallengeController@disenrol');
Route::get('/tfa/{method}/disenrolled', 'Auth\ChallengeController@showDisenrolled')->name('challenge.disenrolled');