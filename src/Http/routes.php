<?php

Route::get('/challenge', 'Auth\ChallengeController@showMethodSelectionForm')->name('challenge');
Route::get('/challenge/error', 'Auth\ChallengeController@showError')->name('challenge.error');
Route::post('/challenge/dispatch', 'Auth\ChallengeController@challenge')->name('challenge.dispatch')->middleware('throttle:10,1,challenge.dispatch');
Route::get('/challenge/{method}/verify', 'Auth\ChallengeController@showVerificationForm')->name('challenge.verify.form');
Route::post('/challenge/{method}/verify', 'Auth\ChallengeController@verify')->middleware('throttle:10,1,challenge.verify');
Route::get('/challenge/{method}/enrol', 'Auth\ChallengeController@begin')->name('challenge.enrol')->middleware('throttle:10,1,challenge.enrol');
Route::get('/challenge/{method}/enrol/setup', 'Auth\ChallengeController@showSetupForm')->name('challenge.enrolment.setup')->middleware('throttle:10,1');
Route::post('/challenge/{method}/enrol/setup', 'Auth\ChallengeController@setup')->middleware('throttle:10,1,challenge.setup');
Route::get('/challenge/{method}/enrolled', 'Auth\ChallengeController@showEnrolled')->name('challenge.enrolled');
Route::get('/challenge/{method}/disenrol', 'Auth\ChallengeController@disenrol');
Route::get('/challenge/{method}/disenrolled', 'Auth\ChallengeController@showDisenrolled')->name('challenge.disenrolled');