<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('newLecture', function(){
    return true;
});
Broadcast::channel('updateLecture', function(){
    return true;
});
Broadcast::channel('lectureRemoved', function(){
    return true;
});
Broadcast::channel('countLecture', function(){
    return true;
});
Broadcast::channel('sumLecture', function(){
    return true;
});
Broadcast::channel('freeLecture', function(){
    return true;
});
Broadcast::channel('newExam', function(){
    return true;
});
Broadcast::channel('removeExam', function(){
    return true;
});
Broadcast::channel('newScript', function(){
    return true;
});
Broadcast::channel('removeScript', function(){
    return true;
});
Broadcast::channel('newUser', function(){
    return true;
});
Broadcast::channel('updateUser', function(){
    return true;
});
Broadcast::channel('viewLecture', function(){
    return true;
});
