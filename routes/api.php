<?php

// use QrCode;

Route::get('/', function () {
    return response()->json(['data' => "Welcome to BacBon School! Unauthorized Access!!"], 403);
});

Route::middleware('throttle:6|60')->group(function () {
    Route::get('get-refferal-code/{id}', 'UserController@getRefferalCode');
});

Route::get('get-text/{id}', 'UserController@getRefferalCode');
Route::middleware('throttle:30|60')->group(function () {
    Route::post('sendResetPasswordCode', 'UserController@sendResetPasswordCode');
});

Route::middleware('throttle:30|60')->group(function () {
    Route::post('loginFirstStepWeb', 'APILoginController@loginFirstStepWeb');

});
Route::middleware('throttle:30|60')->group(function () {

    Route::post('sendWebUserOTP', 'APIRegisterController@sendWebUserOTP');

});

Route::middleware('throttle:10|30')->group(function () {
    Route::post('user/preRegister', 'APIRegisterController@preRegister');
});

Route::middleware('throttle:10|30')->group(function () {
    Route::post('user/verifyAndConfirm', 'APIRegisterController@VerifyCode');
    Route::post('user/register', 'APIRegisterController@register');
});

// Route::middleware('throttle:30|60')->group(function () {
Route::post('user/login', 'APILoginController@login');
Route::post('check-header', 'APILoginController@checkDataHeader');

//sendVerificationEmailOTP
Route::post('user/send-verification', 'APILoginController@sendVerificationEmailOTP');

//deleteUser
Route::post('user/account-delete', 'APILoginController@deleteUser');

// });

Route::middleware('throttle:30|60')->group(function () {
    Route::post('submitContactForm', 'UserController@submitContactForm');
});
Route::middleware('throttle:30|60')->group(function () {
    Route::post('submitCareerForm', 'UserController@applyForCareer');
});
// Route::get('/sendSmsRobi','APIRegisterController@sendSmsRobi');
Route::post('completeRegistration', 'APIRegisterController@completeRegistration');

Route::post('loginWithPassword', 'APILoginController@loginWithPassword');
Route::post('loginWithOTP', 'APILoginController@loginWithOTP');

Route::post('user/updatePassword', 'APILoginController@updatePassword');
Route::post('admin/change-password', 'AdminController@changePassword');

// Route::post('user/register', 'APIRegisterController@register');
// Route::post('user/login', 'APILoginController@login');
Route::get('user/sendMail', 'APIRegisterController@sendMail');
Route::post('user/foreignRegister', 'APIRegisterController@foreignRegister');
Route::post('user/logout', 'APILoginController@logout');
Route::post('user/verifycode', 'VerifyCodeController@VerifyCode');
Route::get('user/getChapter', 'ChapterController@getList');

Route::post('admin/login', 'AdminController@login');
Route::post('admin/register', 'AdminController@register');
Route::get('admin/user-list', 'AdminController@userList');

Route::post('/saveFeedback', 'FeedbackController@store');
//Route::post('/sendSms', 'FeedbackController@sendSms');

Route::get('createReviewTest', 'LectureController@createReviewTest');

Route::post('checkExistUser', 'UserController@checkExistUser');

Route::group(['middleware' => ['cors']], function () {

    Route::post('resetPassword', 'UserController@resetPassword');

    Route::get('getCareerAppliedListPaginated/{pageSize}/{pageNumber}', 'CareerController@getCareerAppliedListPaginated');

    Route::post('deleteCareerApplication', 'CareerController@deleteCareerApplication');

    Route::post('setIsEEduPhase3', 'UserController@setIsEEduPhase3');
    Route::post('submitCareerFormMobile', 'UserController@submitCareerFormMobile');

    Route::post('checkJobPosted', 'CareerController@checkJobPosted');
    Route::post('submitCareerFormMobileLatest', 'CareerController@submitCareerForm');

    Route::post('sendTopStudentsToEmails', 'CareerController@sendTopStudentsToEmails');

    Route::post('smsTest', 'APILoginController@smsTest');

    Route::get('copyLecturesHSC', 'LectureController@copyLecturesHSC');
    Route::get('deleteLecturesFile/{id}', 'LectureController@deleteLecturesFile');

    Route::get('getUserNumberByGender', 'UserController@getUserNumberByGender');
    Route::get('getUserNumberByCountry', 'UserController@getUserNumberByCountry');
    Route::get('getUserNumberByCourse', 'UserController@getUserNumberByCourse');
    Route::get('getSubjectNumberByCourse', 'CourseController@getSubjectNumberByCourse');
    Route::get('getChapterNumberByCourse', 'ChapterController@getChapterNumberByCourse');

    Route::get('getLecturesNumberForLMS', 'LectureController@getLecturesNumberForLMS');
    Route::get('getLecturesExamNumberForLMS', 'LectureController@getLecturesExamNumberForLMS');
    Route::get('getLecturesSumForLMS', 'LectureController@getLecturesSumForLMS');
    Route::get('getLecturesNumberByFree', 'LectureController@getLecturesNumberByFree');

    Route::get('getLecturesNumberByCourse/{course_id}', 'LectureController@getLecturesNumberByCourse');
    Route::get('getLecturesSumForLMSByCourse/{course_id}', 'LectureController@getLecturesSumForLMSByCourse');
    Route::get('getLecturesNumberIsFreeByParams/{isFree}', 'LectureController@getLecturesNumberIsFreeByParams');

    Route::get('getLecturesScriptNumberForLMS', 'LectureScriptController@getLecturesScriptNumberForLMS');

    Route::get('broadcastLecture/{id}', 'LectureController@broadcastLecture');

    Route::post('getLampRegisteredUserAll/{shortName}', 'UniversityController@getLampRegisteredUserAll');
    Route::post('getLampRegisteredUser/{shortName}', 'UniversityController@getLampRegisteredUser');

    Route::get('getQRCodeByUserId/{id}', 'UserController@getQRCodeByUserId');
    Route::get('getVideo/{fileName}', 'LectureController@getVideo');
    Route::get('getVideoById/{id}', 'LectureController@getVideoById');
    Route::get('demoCountWithDate', 'LectureController@demoCountWithDate');
    Route::get('getExcelLampByUniversity/{shortName}', 'ResultSubjectController@getExcelLampByUniversity');
    Route::get('getExcelLampByUniversityAll', 'ResultSubjectController@getExcelLampByUniversityAll');
    Route::get('getUniversityByShortName/{shortName}', 'UniversityController@getUniversityByShortName');

    Route::get('getDivisionList', 'DivisionController@getDivisionList');
    Route::get('getDistrictListByDivision/{id}', 'DistrictController@getDistrictListByDivision');
    Route::post('storeDistrict', 'DistrictController@storeDistrict');
    Route::post('updateDistrict', 'DistrictController@updateDistrict');
    Route::post('deleteDistrict', 'DistrictController@deleteDistrict');
    Route::get('importExport', 'ResultSubjectController@importExport');
    Route::get('downloadExcel/{type}', 'MaatwebsiteDemoController@downloadExcel');
    Route::post('importExcel', 'MaatwebsiteDemoController@importExcel');

    Route::get('getUniversityList', 'UniversityController@getUniversityList');
    Route::get('getUniversityListWithLimit/{limit}', 'UniversityController@getUniversityListWithLimit');

    Route::get('get-e-education-phase-3-study-history', 'LogLectureVideoController@getEEucationPhase3StudyHistory');

    Route::get('getSLCStudents', 'ResultSubjectController@getSLCStudents');
    // Get Methods

    // User..............
    Route::get('user/get-referral-number', 'UserController@getReferralNumber');

    Route::get('user/getRegisteredUserList', 'UserController@getRegisteredUserList');

    Route::get('user/getUserDetails', 'UserController@getUserDetails');
    Route::get('user/getUserDetailsWithSubjectsByUserId/{id}', 'UserController@getUserDetailsWithSubjectsByUserId');
    Route::get('user/getUserImage/{id}', 'UserController@getUserImage');
    Route::get('user/getRefferalCode/{id}', 'UserController@getRefferalCode');
    Route::get('user/createReferralCode/{previous_code}', 'UserController@createReferralCode');

    // Course ........
    Route::get('course/getCourseTypeList', 'CourseController@getCourseTypeList');
    Route::get('course/getCourseListWithType', 'CourseController@getCourseListWithType');
    Route::get('V2/course/getCourseListWithType', 'CourseController@getCourseListWithTypeV2');
    Route::get('course/getScholarShipCourseListWithType', 'CourseController@getScholarShipCourseListWithType');
    Route::get('course/getcourselist', 'CourseController@GetCourseList');
    Route::get('course/getChapterListBySubjectId/{CourseId}/{SubId}', 'SubjectController@getChapterListBySubjectId');
    Route::get('course/getChapterListBySubjectIdAndUserId/{CourseId}/{SubId}/{userId}', 'SubjectController@getChapterListBySubjectIdAndUserId');

    Route::get('course/getChapterListBySubjectIdAndUserIdLatest/{CourseId}/{SubId}/{userId}', 'SubjectController@getChapterListBySubjectIdAndUserIdLatest');

    Route::get('V2/course/getChapterListBySubjectIdAndUserId/{CourseId}/{SubId}/{userId}', 'SubjectController@getChapterListBySubjectIdAndUserIdV2');
    //web/course/getChapterListBySubjectId - gp_product_id
    // DEMO

    Route::get('getAudioBooklistByCourseId/{courseId}/{pageSize}/{pageNumber}', 'LectureController@getAudioBooklistByCourseId');

    Route::get('geteBooklist', 'CourseController@geteBooklist');
    Route::get('getChapterJson', 'CourseController@chapterEnterJson');

    Route::post('storeEbook', 'EBookController@storeEbook');
    Route::get('geteBooklistByCourseId/{courseId}', 'EBookController@geteBooklistByCourseId');

    Route::get('V2/geteBooklistByCourseId', 'EBookController@geteBooklistByCourseIdV2');

    Route::post('/create-user-ebook-payment', 'EBookController@createUserEBookPayment');

    Route::get('V3/geteBooklistByCourseId', 'EBookController@geteBooklistByCourseIdV3');

    Route::get('course/geteBooklistByCourseId/{id}', 'CourseController@geteBooklistByCourseId');

    Route::get('V2/UpdateeBookGPlayID', 'EBookController@UpdateeBookGPlayID');

    Route::get('course/courseDetail/{id}', 'CourseController@courseDetail');
    Route::get('course/getSubjectlistByUserIdCourseId/{course_id}/{user_id}', 'CourseController@getSubjectlistByUserIdCourseId');
    Route::get('course/getSubjectlistWithoutChapterByCourseId/{id}', 'CourseController@getSubjectlistWithoutChapterByCourseId');
    Route::get('course/getSubjectlistByCourseId/{id}', 'CourseController@getSubjectlistByCourseId');
    Route::get('course/getSubjectListNotInCourse/{id}', 'CourseController@getSubjectListNotInCourse');
    Route::get('subject/getSubjectlist', 'SubjectController@GetSubjectList');

    Route::get('/lesson/getvideolist', 'LessonController@GetVideoList');

    //  Questions .......
    Route::get('/question/getSubjectExamQuestionsById/{examId}/{pageSize}', 'SubjectController@getSubjectExamQuestionsById');
    Route::get('/question/getChapterExamQuestionsById/{examId}/{pageSize}', 'ChapterController@getChapterExamQuestionsById');
    Route::get('/question/getLectureExamQuestionsById/{examId}/{pageSize}', 'LectureController@getLectureExamQuestionsById');

    Route::get('getChapterExamIds', 'ChapterController@getChapterExamIds');

    Route::get('/getSubjectHistoryByUserId/{userId}','ResultSubjectController@getSubjectHistoryByUserId');

    Route::get('/getExamHistoryByUserId/{userId}', 'ResultSubjectController@getExamHistoryByUserId');

    Route::get('/getSubjectExamDetailsByExamIdUserId/{examId}/{userId}', 'ResultSubjectAnswerController@getSubjectExamDetailsByExamIdUserId');
    Route::get('/getChapterExamDetailsByExamIdUserId/{examId}/{userId}', 'ResultChapterAnswerController@getChapterExamDetailsByExamIdUserId');
    Route::get('/getLectureExamDetailsByExamIdUserId/{examId}/{userId}', 'ResultLectureAnswerController@getLectureExamDetailsByExamIdUserId');

    Route::get('/getExamHistoryByUserId/{userId}','ResultSubjectController@getExamHistoryByUserId');


    Route::get('/getSubjectExamDetailsByExamIdUserId/{examId}/{userId}','ResultSubjectAnswerController@getSubjectExamDetailsByExamIdUserId');
    Route::get('/getChapterExamDetailsByExamIdUserId/{examId}/{userId}','ResultChapterAnswerController@getChapterExamDetailsByExamIdUserId');
    Route::get('/getLectureExamDetailsByExamIdUserId/{examId}/{userId}','ResultLectureAnswerController@getLectureExamDetailsByExamIdUserId');


    Route::get('/getChapterExamForVAB/{examId}','ChapterController@getChapterExamForVAB');
    Route::get('/getLectureExamForVAB/{examId}','LectureController@getLectureExamForVAB');

    Route::get('/getExamQuestionCount/{courseId}','LectureController@getExamQuestionCount');

    // Price
    Route::get('/price/getChapterPrice/{chapterId}/{userId}', 'ChapterController@getChapterPrice');
    Route::get('/price/getSubjectPriceByUserId/{courseId}/{subjectId}/{userId}', 'SubjectController@getSubjectPriceByUserId');
    Route::get('/price/getCoursePriceByUserId/{courseId}/{userId}', 'SubjectController@getCoursePriceByUserId');

    // Get Favorite Lecture
    Route::get('/getFavoriteLectureByUserId/{id}', 'LectureFavoriteController@getFavoriteLectureByUserId');

    Route::get('/getFavoriteLectureByUserIdLatest/{id}', 'LectureFavoriteController@getFavoriteLectureByUserIdLatest');

    // Get Exam History
    Route::get('/getUserExamHistory', 'ResultSubjectController@getUserExamHistory');

    // Post Methos.......
    Route::post('user/updateUserById/{id}', 'UserController@updateUserById');
    Route::post('user/updateUserImage/{id}', 'UserController@updateUserImage');
    Route::post('user/savePoint', 'UserController@savePoint');
    Route::post('user/updateFCM', 'UserController@updateFCM');

    Route::post('/course/createCourse', 'CourseController@createCourse'); // ADMIN
    Route::post('/course/updateCourse', 'CourseController@updateCourse'); // ADMIN

    Route::post('/course/updateCurrentCourse', 'CourseController@CouresSelectByUser');
    Route::post('/course/addSubjectToCourse', 'CourseController@addSubjectToCourse');

    // Subject ......
    Route::post('/subject/createSubject', 'SubjectController@createSubject');

    Route::post('/subject/getcoursewisesubjectlist', 'SubjectController@GetCourseWiseSubjectList');
    Route::post('/lesson/getlessonlist', 'LessonController@GetLessonList');
    Route::post('/lesson/getlessonvideolist', 'LessonController@GetLessonVideoList');
    Route::post('/lesson/getlessonvideo', 'LessonController@GetLessonVideo');

    Route::get('getChapterBySubjectCourse', 'ChapterController@getChapterBySubjectCourse');

    // Chapter ......

    Route::post('/youtube-corner/store', 'BmoocCornerController@store');
    Route::post('/youtube-corner/update/{id}', 'BmoocCornerController@update');
    Route::post('/youtube-corner/delete/{id}', 'BmoocCornerController@destroy');
    Route::post('/chapter/storeChapter', 'ChapterController@storeChapter');
    Route::post('/chapter/updateChapter', 'ChapterController@updateChapter');

    // Lecture Video ......
    Route::post('/lecture/storeLectureVideo', 'LectureController@storeLectureVideo');
    Route::post('/lecture/updateLectureVideo', 'LectureController@updateLectureVideo');
    Route::post('/lecture/deleteLectureVideo', 'LectureController@deleteLectureVideo');

    Route::post('storeLectureLog', 'LectureViewLogController@storeLectureLog');
    Route::get('getTodayVideos', 'LectureViewLogController@getTodayVideos');
    Route::get('getTopVideosByDate/{date}', 'LectureViewLogController@getTopVideosByDate');
    Route::get('getUserAndLecture/{userId}/{lectureId}', 'LectureController@getUserAndLecture');
    Route::get('getUserName/{userId}', 'LectureController@getUserName');
    Route::post('getUserNameByIdArray', 'LectureController@getUserNameByIdArray');

    // Lecture Rating ====================================
    Route::post('/rateLecture', 'LectureRatingController@store');

    // Lecture Favorite ====================================
    Route::post('/makeFavoriteLecture', 'LectureFavoriteController@store');

    // Exam Results
    Route::post('/submitReviewExamResult', 'ResultLectureController@submitReviewExamResults');
    Route::post('/submitLectureExamResult', 'ResultLectureController@submitLectureExamResult');
    Route::post('/submitChapterExamResult', 'ResultChapterController@submitChapterExamResult');
    Route::post('/submitSubjectExamResult', 'ResultSubjectController@submitSubjectExamResult');
    Route::post('/saveSubjectExamResultWeb', 'ResultSubjectController@saveSubjectExamResultWeb');
    Route::post('/saveChapterExamResultWeb', 'ResultChapterController@saveChapterExamResultWeb');
    Route::post('/saveLectureExamResultWeb', 'ResultLectureController@saveLectureExamResultWeb');

    // Question......... admin
    Route::post('/question/storeSubjectQuestions', 'SubjectController@storeSubjectQuestions');
    Route::post('/question/storeChapterQuestions', 'ChapterController@storeChapterQuestions');
    Route::post('/question/storeLectureQuestions', 'LectureController@storeLectureQuestions');
    Route::post('/question/storeQuestions', 'ChapterController@storeQuestions');
    Route::post('/question/storeQuestionsMultiple', 'ChapterController@storeQuestionsMultiple');
    Route::post('/question/updateQuestions', 'ChapterController@updateQuestions');

    // Exam.........  admin
    Route::post('/exam/storeSubjectExam', 'SubjectController@storeSubjectExam');
    Route::post('/exam/storeChapterExam', 'ChapterController@storeChapterExam');
    Route::post('/exam/storeLectureExam', 'LectureController@storeLectureExam');
    Route::post('/exam/storeExamGeneric', 'LectureController@storeExamGeneric');
    Route::post('/exam/updateExamGeneric', 'LectureController@updateExamGeneric');

    Route::post('/getQuestionsByPostMethod', 'LectureController@getQuestionsByPostMethod');

    // Payment OR Buy This block should be deleted
    Route::post('/payment/buyLecture', 'LectureController@buyLecture');
    Route::post('/payment/buySubject', 'SubjectController@buySubject');
    Route::post('/payment/buyCourse', 'CourseController@buyCourse');
    Route::post('/payment/buyChapter', 'ChapterController@buyChapter');

    Route::post('/lampFormSubmission', 'UserController@lampFormSubmission');
    Route::get('/getLampDeadline', 'UserController@getLampDeadline');

    Route::post('/applyForScholarship', 'ScholarshipApplicationController@applyForScholarship');

// Updated Purchase API====================================

    Route::post('/payment/purchaseLecture', 'SearchController@purchaseLecture');
    Route::post('/payment/purchaseChapter', 'SearchController@purchaseChapter');
    Route::post('/payment/purchaseSubject', 'SearchController@purchaseSubject');
    Route::post('/payment/purchaseCourse', 'SearchController@purchaseCourse');

    Route::post('/payment/purchaseList', 'PaymentController@purchaseList');

    // File upload

    Route::post('renameChapterScript', 'ChapterScriptController@renameChapterScript');

    Route::post('/file/storeChapterScript', 'ChapterScriptController@storeChapterScript');
    Route::post('/file/storeLectureScript', 'LectureScriptController@storeLectureScript');

    Route::post('/file/deleteChapterScript', 'ChapterScriptController@deleteChapterScript');
    Route::post('/file/deleteLectureScript', 'LectureScriptController@deleteLectureScript');

    Route::post('/script/storeLectureScriptText', 'ScriptTextController@storeLectureScriptText');

    Route::post('/assistance/upload', 'AdmissionAidController@store');
    Route::get('/assistance/getAidListByCourse/{id}', 'AdmissionAidController@getAidListByCourse');

    // Admin Get

    Route::get('course/getSubjectsByCourseId/{id}', 'CourseController@getSubjectsByCourseId');
    Route::get('/lecture/getLecturelistByChapterId/{chapter_id}', 'LectureController@getLecturelistByChapterId');
    Route::get('/lecture/getLectureDetails/{lecture_id}', 'LectureController@getLectureDetails');
    Route::get('/script/getScriptlistByChapterId/{chapter_id}', 'ChapterScriptController@getScriptlistByChapterId');

    Route::get('course/getChapterNameListBySubjectId/{CourseId}/{SubId}', 'SubjectController@getChapterNameListBySubjectId');

    Route::get('/script/getScriptlistByLectureId/{chapter_id}', 'LectureScriptController@getScriptlistByLectureId');

    Route::get('/subject/getExamListBySubject/{course_id}/{subject_id}', 'SubjectController@getExamListBySubject');
    Route::get('/chapter/getExamListByChapter/{id}', 'ChapterController@getExamListByChapter');
    Route::get('/chapter/getExamListWithChapterDetailsByChapter/{id}', 'ChapterController@getExamListWithChapterDetailsByChapter');
    Route::get('/chapter/getChapterDetailsChapterId/{id}', 'ChapterController@getChapterDetailsChapterId');
    Route::get('/lecture/getExamListByLecture/{id}', 'LectureController@getExamListByLecture');

    Route::get('/exam/getExamDetailById/{id}', 'ChapterController@getExamDetailById');
    Route::get('/exam/getSubjectExamDetailById/{id}', 'SubjectController@getSubjectExamDetailById');
    Route::get('/exam/getLectureExamDetailById/{id}', 'LectureController@getLectureExamDetailById');

    // Route::get('/search/searchByCode/{search}','SubjectController@searchByCode');
    Route::get('/search/searchByCode/{search}/{user_id}', 'SearchController@searchByCode');

    //  Demo Route Should be delete Later
    Route::get('/search/getGivenPriceOfBoughtLecturesByChapter/{chapter_id}/{user_id}', 'SearchController@getGivenPriceOfBoughtLecturesByChapter');
    Route::get('/search/getPreviousPaymentOfSubject/{subject_id}/{course_id}/{user_id}', 'SearchController@getPreviousPaymentOfSubject');
    Route::get('/search/getPreviousPaymentOfCourse/{course_id}/{user_id}', 'SearchController@getPreviousPaymentOfCourse');
    Route::get('/search/checkLectureBought/{lecture_id}/{user_id}', 'SearchController@checkLectureBought');

    Route::get('/search/searchUserByPhone/{search}', 'UserController@searchUserByPhone');
    Route::get('/user/getUserList', 'UserController@getUserList');
    Route::get('/user/getUserListPaginated/{pageSize}/{pageNumber}', 'UserController@getUserListPaginated');

    Route::post('/user/searchUserListPaginated', 'UserController@searchUserListPaginated');

    Route::get('/user/get-e-edu-3-student-list', 'UserController@getEEdu3StudentList');
    Route::get('/user/get-e-edu-3-student-list-new', 'UserController@getEEdu3StudentListNew');
    Route::get('/user/get-e-edu-3-student-list-chandpur', 'UserController@getEEdu3StudentListChandpur');
    Route::get('/user/get-e-edu-jicj-teacher-list', 'UserController@getEEduJICFTeacherList');
    Route::get('/user/get-e-edu-admission-student-list', 'UserController@getEEduAdmissionStudentList');
    Route::post('statusUpdateEduStudent', 'UserController@statusUpdateEduStudent');
    Route::post('statusUpdateEduStudentChandpur', 'UserController@statusUpdateEduStudentChandpur');
    Route::post('statusUpdateEduAdmissionStudent', 'UserController@statusUpdateEduAdmissionStudent');

    Route::post('/user/send-message', 'UserController@sendUserMessage');
    Route::post('/user/send-single-message', 'UserController@sendSingleMessage'); // Test Message
    Route::get('/user/get-message/{user_id}', 'UserFCMMessagesController@messageList');
    Route::post('/user/delete-message', 'UserFCMMessagesController@deleteMessage');

    Route::get('/user/get-message-by-id/{id}', 'UserFCMMessagesController@getMessageDetails');
    Route::post('/user/message-mark-as-seen', 'UserFCMMessagesController@markAsSeen');

    Route::post('/user/delete-message-by-date', 'UserFCMMessagesController@deleteUserMessageByDate');

    // Web API

    Route::get('/getArticleList/{page}/{size}', 'BlogArticleController@getArticleList');

    Route::get('/web/getExamListByCourse/{courseId}', 'SubjectController@getExamListByCourse');
    Route::get('/web/getDetailsByExamId/{id}', 'SubjectController@getDetailsByExamId');
    Route::get('/web/getChapterExamDetailsByExamId/{id}', 'ChapterController@getChapterExamDetailsByExamId');
    Route::get('/web/getLectureExamDetailsByExamId/{id}', 'LectureController@getLectureExamDetailsByExamId');

    Route::get('/getCategoryList', 'BlogCategoryController@index');
    Route::get('/web/blog-details/{id}', 'BlogArticleController@details');
    Route::get('/web/blogs/{page}', 'BlogArticleController@index');
    Route::post('/storeBlog', 'BlogArticleController@store');

    Route::get('/web/getCourseSubjectList', 'CourseController@getCourseSubjectList');
    Route::get('/web/lecture/getFreeLectureList', 'LectureController@getFreeLectureList');
    Route::get('/web/lecture/getAllFreeLectureList', 'LectureController@getAllFreeLectureList');
    Route::get('/web/lecture/getAllFreeLectureListPagination/{page_size}/{page_number}', 'LectureController@getAllFreeLectureListPagination');
    Route::get('/web/subject/getCourseSubjectList', 'SubjectController@getCourseSubjectList');

    Route::get('/web/course/getChapterListBySubjectId/{CourseId}/{SubId}', 'SubjectController@getChapterListBySubjectIdForWeb');
    Route::get('/web/course/getChapterListBySubjectIdUserID/{CourseId}/{SubId}/{UserId}', 'SubjectController@getChapterListBySubjectIdUserIDForWeb');

    Route::get('makeSequenceScriptQuiz', 'SubjectController@makeSequence');

    Route::post('/web/subject/searchCourseSubjectList', 'SubjectController@searchCourseSubjectList');

    Route::get('V2/UpdateGPIDCourseSubject', 'SubjectController@UpdateGPIDCourseSubject');

    /* Blog Comment and  Replies*/

    Route::post('store-blog-comment', 'BlogCommentController@store');
    Route::post('calculate-result', 'CalculatorSubjectRequirementController@calculateResult');

    Route::get('calculator-university-list', 'CalculatorUniversityController@calculatorUniversityList');
    Route::get('getJobList', 'CalculatorUniversityController@getJobList');

    Route::get('/lecture_url_rename', 'LectureController@lecture_url_rename');

    // 9th August, 2020 Store user watch information

    Route::post('store-lecture-watch-history', 'LogLectureVideoController@storeLectureWatchHistory');
    Route::post('store-audio-listen-history', 'LogAudioBookController@storeAudioListenHistory');
    Route::post('store-script-history', 'LogScriptController@storeScriptHistory');
    Route::post('store-e-book-read-history', 'LogEBookController@storeEBookReadHistory');

    Route::get('get-item-count', 'LMSDashboard@getItemCount');
    Route::get('get-lecture-watch-list-by-user-id/{id}/{courseId}', 'LogLectureVideoController@getLectureWatchListByUserId');

    Route::get('get-top-user-list/{pageSize}', 'LogLectureVideoController@getTopUserListBasedOnLectureWatching');
    Route::get('get-top-user-list-paginated/{pageSize}/{pageNumber}', 'LogLectureVideoController@getUserListBasedOnLectureWatchingPaginated');

    Route::post('get-top-user-list-specific-students', 'LogLectureVideoController@getTopUserListBasedOnLectureWatchingSpecificStudents');

    Route::get('get-user-short-history/{userId}', 'LogLectureVideoController@getUserShortHistory');

    Route::get('get-user-short-history-by-phone/{phone}', 'LogLectureVideoController@getUserShortHistoryByPhone');

    Route::get('get-user-history/{userId}', 'LogLectureVideoController@getUserHistory');

    Route::get('getParticipatedUserNumber', 'ResultRevisionExamController@getParticipatedUserNumber');

    Route::get('get-revision-exam-result-test/{userId}/{examId}', 'ResultRevisionExamController@getRevisionExamResultTest');

    Route::get('getEEducationUserResults', 'ResultRevisionExamController@getEEducationUserResults');

    Route::get('get-lecture-exam-result/{userId}/{examId}', 'ResultLectureController@getLectureExamResult');
    Route::get('get-chapter-exam-result/{userId}/{examId}', 'ResultChapterController@getChapterExamResult');
    Route::get('get-revision-exam-result/{userId}/{examId}', 'ResultRevisionExamController@getRevisionExamResult');
    Route::get('get-model-exam-result/{userId}/{examId}', 'ResultModelTestController@getModelExamResult');

    Route::get('get-lecture-exam-result-v2/{userId}/{examId}', 'ResultLectureController@getLectureExamResult');
    Route::get('get-chapter-exam-result-v2/{userId}/{examId}', 'ResultChapterController@getChapterExamResult');
    Route::get('get-review-exam-result-v2/{userId}/{examId}', 'ResultLectureController@getReviewExamResult');
    Route::get('get-model-exam-result-v2/{userId}/{examId}', 'ResultModelTestController@getModelExamResult');

    Route::get('download-e-book/{id}', 'EBookController@downloadEbook');

    Route::get('getSchoolAssistance/{pageSize}/{pageNumber}', 'SchoolAssistanceController@getSchoolAssistanceList');

    Route::get('copy-quiz', 'LectureController@copyQuiz');

    Route::post('save-app-rating', 'AppRatingController@saveAppRating');

    Route::get('get-goal-status', 'LogLectureVideoController@getGoalStatus');

    Route::get('get-contest-details-by-admin/{id}', 'QuizContestController@getContestDetailsAdmin');

    Route::post('upload-winner-banner-image', 'QuizContestAnswerController@uploadWinnerBannerImage');
    Route::post('make-winner', 'QuizContestAnswerController@makeWinner');
    Route::post('delete-banner-image', 'QuizContestAnswerController@deleteBannerImage');

    Route::get('get-daily-quiz-list', 'QuizContestController@getDailyQuizList');
    Route::post('store-daily-quiz', 'QuizContestController@storeDailyQuiz');
    Route::get('get-contest-quiz', 'QuizContestController@getContestQuiz');
    Route::post('submit-contest-quiz-answer', 'QuizContestAnswerController@submitContestQuizAnswer');

    Route::get('get-contest-quiz-winner-list', 'QuizContestController@getContestQuizWinnerList');

    Route::get('editRevisionQuestion', 'RevisionExamController@editRevisionQuestion');

    Route::get('totalQuestionNumber', 'RevisionExamController@totalQuestionNumber');

    Route::get('getTestListByType', 'RevisionExamController@getTestListByType');

    //Selection Test
    Route::get('getSelectionTestList/{userId}', 'SelectionTestController@getSelectionTestList');
    Route::get('getSelectionTestQuestionsById/{selectionTestID}/{userId}', 'SelectionTestController@getSelectionTestQuestionList');
    Route::post('selectionTestStart', 'SelectionTestController@selectionTestStart');

    Route::post('selectionTestSubmit', 'SelectionTestController@submitSelectionExamResult');
    Route::post('selectionTestWrittenSubmit', 'SelectionTestController@selectionTestWrittenSubmit');

    Route::post('createRevisionTestLectureSheetWise', 'RevisionExamController@createRevisionTestLectureSheetWise');
    Route::get('getRevisionTestList', 'RevisionExamController@getRevisionTestList');

    Route::get('getWeeklyTestList', 'RevisionExamController@getWeeklyTestList');
    Route::get('getMonthlyTestList', 'RevisionExamController@getMonthlyTestList');

    Route::get('getReviewExamQuestionsById/{examId}/{userId}', 'RevisionExamController@getReviewExamQuestionsById');
    Route::get('getReviewExamQuestionsByIdWeb/{examId}/{userId}', 'RevisionExamController@getReviewExamQuestionsByIdWeb');

    Route::post('createModelTest', 'ModelTestController@createModelTest');
    Route::get('getModelTestList', 'ModelTestController@getModelTestList');
    Route::get('getModelTestQuestionsById/{examId}/{userId}', 'ModelTestController@getModelTestQuestionsById');

    Route::get('getModelTestListWeb', 'ModelTestController@getModelTestListWeb');
    Route::get('getModelTestQuestionsByIdWeb', 'ModelTestController@getModelTestQuestionsByIdWeb');

    Route::post('/submitRevisionExamResult', 'ResultRevisionExamController@submitRevisionExamResult');
    Route::post('/submitRevisionExamResultWeb', 'ResultRevisionExamController@submitRevisionExamResultWeb');
    Route::post('/submitModelTestResult', 'ResultModelTestController@submitModelTestResult');
    Route::post('/submitModelTestResultWeb', 'ResultModelTestController@submitModelTestResultWeb');

    Route::post('/start-c-unit', 'UserController@startCUnit');
    Route::post('/start-b-unit', 'UserController@startBUnit');
    Route::post('/start-d-unit', 'UserController@startDUnit');
    Route::post('/select-optional-subject', 'UserController@selectOptionalSubject');

    Route::post('/update-jicf-tutor-status', 'UserController@jicfTutorStatusUpdate');

    Route::post('copyChapterWithLecture','ChapterController@copyChapterWithLecture'); // for video ----

    Route::post('copyChapterExam','ChapterController@copyChapterExam'); // for Chapter Script -----
    Route::post('copyChapterExamOnlyOne','ChapterController@copyChapterExamOnlyOne'); // for Chapter Script -----


    Route::post('makeSequence', 'ChapterController@makeSequence');
    Route::post('makeSequenceLectureScript', 'ChapterController@makeSequenceLectureScript');

    Route::get('getBscsExamList', 'BscsExamController@getBscsExamList');
    Route::get('getBscsExamListV2', 'BscsExamController@getBscsExamListV2');
    Route::get('getBscsExamQuestionsById/{examId}', 'BscsExamController@getBscsExamQuestionsById');
    Route::post('submitBscsExamResult', 'BscsResultsController@submitBscsExamResult');

    Route::post('startBscsWrittenExam', 'BscsWrittenExamController@startBscsWrittenExam');
    Route::post('submitBscsWrittenExamAnswer', 'BscsWrittenExamController@submitBscsWrittenExamAnswer');
    Route::get('getWrittenExamDetails', 'BscsExamController@getWrittenExamDetails');

    Route::middleware('throttle:30|60')->group(function () {
        Route::post('guardian/foreignRegister', 'GuardianController@foreignRegister');
    });
    Route::middleware('throttle:3|30')->group(function () {
        Route::post('guardian/preRegister', 'GuardianController@preRegister');
    });
    Route::middleware('throttle:30|60')->group(function () {
        Route::post('guardian/verifyAndConfirm', 'GuardianController@VerifyCode');
    });
    Route::middleware('throttle:30|60')->group(function () {
        Route::post('guardian/login', 'GuardianController@login');
    });
    Route::middleware('throttle:30|60')->group(function () {
        Route::post('guardian/verifyOtp', 'GuardianController@verifyOtp');
    });

    Route::get('get-guardian-details', 'GuardianController@getGuardianDetails');
    Route::post('connect-student-with-guardian', 'GuardianChildController@connectStudentWithGuardian');

    Route::get('cleanLogLectureWatchComplete', 'LogLectureVideoController@cleanLogLectureWatchComplete');

    Route::post('saveBulkQuestion', 'BscsExamQuestionController@saveBulkQuestion');

    Route::get('getEnglishAdmissionQuestions', 'ChapterController@getEnglishAdmissionQuestions');

    Route::get('get-weekly-test-ranking', 'ResultRevisionExamController@getParticipatedUserIds');

    Route::get('get-model-test-ranking', 'ResultModelTestController@getParticipatedUserIds');

    Route::get('makeExamSequence', 'ChapterController@makeExamSequence');

    Route::get('getEEducationUserModelTestResults', 'ResultModelTestController@getEEducationUserModelTestResults');

    Route::get('get-divisions-with-districts', 'DivisionController@getDivisionListWithDistricts');

    Route::post('upload-video-downloadable-url', 'LectureController@uploadVideoDownloadableUrl');

    Route::post('upload-video-to-s3', 'LectureController@uploadLectureVideosToS3');
    Route::get('filter-lecture-video', 'LectureController@filterLectureVideoList');
    
    Route::get('resultDetails/{examId}/{userId}', 'ResultLectureController@resultDetails');

    Route::post('createSpecialModelTest', 'SpecialModelTestController@createSpecialModelTest');
    Route::get('get-special-model-list-admin', 'SpecialModelTestController@getSpecialModelTestList');
    Route::get('get-special-model-list', 'SpecialModelTestController@getSpecialModelTestListByType');
    Route::get('getSpecialModelTestQuestionsById/{examId}/{userId}', 'SpecialModelTestController@getSpecialModelTestQuestionsById');
    Route::post('/startSpecialModelTest', 'ResultSpecialModelTestController@startSpecialModelTest');
    Route::post('/submitSpecialModelTestResult', 'ResultSpecialModelTestController@submitSpecialModelTestResult');
    Route::get('get-special-model-test-ranking', 'ResultSpecialModelTestController@getParticipatedUserIds');

    Route::get('get-institute-list', 'InstituteController@getList');

    /**
     * Routes for resource promotions
     */
    Route::get('promotions', 'PromotionController@all');
    Route::get('user-promotion', 'PromotionController@getRandomPromotionForUser');
    Route::get('guardian-promotion', 'PromotionController@getRandomPromotionForGuardian');
    Route::get('promotion/{id}', 'PromotionController@get');
    Route::post('promotion', 'PromotionController@add');
    Route::delete('promotion/{id}', 'PromotionController@remove');
    Route::get('getReferrelDetails', 'PromotionController@getRefDetails');

    /**
     * Crash Course
     */
    //   Route::get('get-user-crash-courses/{userId}', 'CrashCourseController@getUserCourses');

    Route::get('get-crash-courses/{userId}', 'CrashCourseController@getCrashCourseList');
    Route::get('get-crash-courses-details/{crashCourseId}/{userId}', 'CrashCourseController@getCrashCourseDetails');

    Route::get('get-crash-courses-V2/{userId}', 'CrashCourseController@getCrashCourseListV2');
    Route::get('get-crash-courses-details-V2/{crashCourseId}/{userId}', 'CrashCourseController@getCrashCourseDetailsV2');

    Route::get('get-crash-course-quiz-questions-by-id/{examId}', 'CrashCourseController@getCrashCourseQuizQuestionsById');

    Route::post('/submit-crash-course-quiz-result', 'ResultCrashCourseQuizController@submitCrashCourseQuizResult');
    Route::post('/start-crash-course-quiz', 'ResultCrashCourseQuizController@startCrashCouseQuiz');

    Route::post('/save-user-payment', 'CrashCourseController@saveUserPayment');

    Route::post('/create-user-crash-course-payment', 'CrashCourseController@createUserCrashCoursePayment');
    Route::post('/save-user-trail', 'CrashCourseController@addUserToTrail');

    Route::get('get-user-purchase-list/{userId}', 'CrashCourseController@getUserPurchaseList');
    Route::get('get-user-transaction-list/{userId}', 'CrashCourseController@getUsertransactions');

    /**
     * Crash Course For Admin Panel
     */

    Route::get('crash-course/get-all-crash-courses', 'CrashCourseController@getAllCrashCourse');
    Route::get('crash-course/get-all-crash-course-subjects', 'CrashCourseController@getAllCrashCourseSubject');
    Route::post('crash-course/create-crash-course', 'CrashCourseController@createCourse');
    Route::post('crash-course/create-crash-course-subject', 'CrashCourseController@createCourseSubject');
    Route::get('crash-course/get-course-list', 'CrashCourseController@getCrashCourseListAdmin');
    Route::get('crash-course/get-subject-list-by-course/{courseId}', 'CrashCourseController@getSubjectListByCourse');
    Route::get('crash-course/get-subject-materials-list/{subjectId}', 'CrashCourseController@getSubjectMaterials');
    Route::post('crash-course/create-quiz', 'CrashCourseController@createQuiz');
    Route::post('crash-course/file/store-script', 'CrashCourseController@createScript');
    Route::post('crash-course/store-video', 'CrashCourseController@createVideo');

    Route::post('crash-course/get-user-payment-list', 'CrashCourseController@getUserPaymentList');
    Route::post('crash-course/update-user-payment', 'CrashCourseController@updateUserPayment');

    Route::post('crash-course/delete-user-payment', 'CrashCourseController@deleteUserPayment');
    Route::post('crash-course/get-student', 'CrashCourseController@getStudentAdmin');
    //  Route::post('/lecture/storeLectureVideo','LectureController@storeLectureVideo');

    Route::get('crash-course/copy-quiz-questions/{copy_from_id}/{copy_to_id}', 'CrashCourseController@copyQuizQuestions');

    /**
     * Crash Course Web API
     */
    Route::get('web/crash-course/get-latest-crash-courses', 'CrashCourseController@getLatestCrashCourseListWeb');
    Route::get('web/crash-course/get-crash-courses', 'CrashCourseController@getCrashCourseListWeb');

    Route::get('web/crash-course/get-crash-courses-quiz-details/{quizId}', 'CrashCourseController@getCrashCourseQuizDetails');

    Route::get('web/crash-course/get-crash-courses-details/{crashCourseId}', 'CrashCourseController@getCrashCourseDetailsWeb');
    Route::get('web/crash-course/get-crash-courses-details/{crashCourseId}/{userId}', 'CrashCourseController@getCrashCourseDetailsV2');

    Route::post('web/crash-course/get-my-purchases', 'CrashCourseController@getUserPurchases');
    Route::post('web/crash-course/get-my-transactions', 'CrashCourseController@getUsertransactionsWeb');
    Route::post('web/purchase-crash-course', 'SslCommerzPaymentController@purchaseCrashCourse');

    Route::post('web/get-all-transactions', 'CrashCourseController@getAllTransactoins');

    Route::post('saveCrashCourseExamResultWeb', 'ResultCrashCourseQuizController@saveCrashCourseExamResultWeb');

    //   Route::get('web/crash-course/copyParticipantToAllPayment', 'CrashCourseController@copyParticipantToAllPayment');

    /**
     * study progress apis
     */
    Route::get('course/getCourseListWithSubject', 'CourseController@getCourseListWithSubject');
    Route::get('course/getChapterListWithDetails/{CourseId}/{SubId}/{userId}', 'SubjectController@getChapterListWithDetails');
    Route::post('store-script-history', 'LogScriptController@storeScriptHistory');
    Route::post('save-script-history', 'LogScriptController@saveScriptHistory');

});

/**
 * Lecture Sheet
 */

Route::get('lecture-sheet/get-lecture-sheet-list', 'LectureSheetController@getLectureSheetList');
Route::post('create-user-lecture-sheet-payment', 'LectureSheetController@createUserLectureSheetPayment');
Route::get('download-lecture-sheet/{id}', 'LectureSheetController@downloadLectureSheet');

Route::get('web/lecture-sheet/get-latest-lecture-sheets', 'LectureSheetController@getLatestLectureSheetListWeb');
Route::get('web/lecture-sheet/get-lecture-sheets-details/{lectureSheetId}', 'LectureSheetController@getLectureSheetDetailsWeb');
Route::get('web/lecture-sheet/get-lecture-sheets-details/{lectureSheetId}/{userId}', 'LectureSheetController@getLectureSheetDetailsV2');
Route::post('web/purchase-lecture-sheet', 'SslCommerzPaymentController@purchaseLectureSheet');

/**
 * Lecture Video Payment API for website
 */

Route::post('web/purchase-lecture-videos-a-unit', 'SslCommerzPaymentController@purchaseLectureVideo');
Route::post('V2/mobile/purchase-lecture-videos-a-unit', 'SubjectController@createUserLectureVideoPaymentMobile');

/**
 * End of Lecture Video Payment API for website
 */

/**
 * Paid Course  api for website
 */

Route::get('question-sets', 'PaidCourseController@getQuizQuestionSettList');
Route::get('web/paid-course/all-paid-courses', 'PaidCourseController@getAllPaidCourse');
Route::get('web/paid-course/get-paid-courses-details/{paid_course_id}', 'PaidCourseController@getPaidCourseDetailsWeb');
Route::get('web/paid-course/get-paid-courses-details/{paid_course_id}/{user_id}', 'PaidCourseController@getPaidCourseDetailsWeb');
Route::post('web/purchase-paid-course', 'SslCommerzPaymentController@purchasePaidCourse');
Route::get('web/paid-course/get-paid-courses-quiz-details/{paid_course_material_id}', 'PaidCourseController@getPaidCourseQuizDetails');

Route::post('web/purchase-paid-course-with-coupon', 'SslCommerzPaymentController@purchasePaidCourseWithCoupon');
Route::post('web/manualy-enrol-paid-course-with-coupon', 'SslCommerzPaymentController@ManuallyEnrollIntoPaidCourseWithCoupon');

Route::post('web/start-paid-course-quiz', 'PaidCourseController@startPaidCouseQuiz');
Route::post('web/submit-paid-course-quiz-result', 'PaidCourseController@submitPaidCourseQuizResult');
Route::get('web/get-paid-course-quiz-questions-by-id/{paid_course_material_id}', 'PaidCourseController@getPaidCourseQuizQuestionsByIdWeb');

/** Paid Course Coupon  */
Route::post('web/create-update-coupon', 'PaidCourseController@paidCourseCouponSaveUpdate');
Route::get('web/coupon-list', 'PaidCourseController@couponList');
Route::get('web/coupon-dropdown-list', 'PaidCourseController@couponDropdownList');
Route::post('web/check-coupon-validity', 'PaidCourseController@checkCouponValidity');

Route::get('web/my-paid-course-list/{user_id}', 'PaidCourseController@myPaidCourselist');
Route::get('web/paid-course/test-list-by-id/{paid_course_id}', 'PaidCourseController@getAllTestList');
Route::get('web/updateSubjectIdAccordingToSetup', 'PaidCourseController@updateSubjectIdAccordingToSetup');
Route::get('web/subject-wise-result/{result_id}', 'PaidCourseController@getSubjectWiseResult');
Route::get('web/paid-course-subject-wise-all-result/{paid_course_material_id}', 'PaidCourseController@getPaidCourseSubjectWiseAllResultByID');
Route::get('web/add-permission-of-purchased-user', 'PaidCourseController@AddPermissionOfPurchasedUser');

Route::get('web/get-otp-details/{mobile}', 'APILoginController@GetOTPForDB');

Route::get('mobile/getNestedCourseStructureList', 'SubjectController@getNestedCourseStructureList');

// Professional Course
Route::get('web/paid-course/professional-courses', 'PaidCourseController@getAllProfessionalCourse');

Route::group(['prefix' => 'mobile'], function () {
    Route::get('paid-course/all-paid-courses/{user_id}', 'PaidCourseController@getAllPaidCourseForMobile');
    Route::get('paid-course/all-paid-courses/{user_id}/{course_type}', 'PaidCourseController@getAllPaidCourseForMobile');
    Route::get('paid-course/get-paid-courses-details/{paid_course_id}/{user_id}', 'PaidCourseController@getPaidCourseDetailsWeb');
    Route::get('paid-course/filter-by-unit/{user_id}/{is_cunit}', 'PaidCourseController@getPaidCourseFilterListForMobile');

    //Paid Course Question List
    Route::get('get-paid-course-quiz-questions-by-id/{paid_course_material_id}', 'PaidCourseController@getPaidCourseQuizQuestionsById');
    Route::get('v2/get-paid-course-quiz-questions-by-id/{paid_course_material_id}', 'PaidCourseController@getPaidCourseQuizQuestionsByIdV2');

    Route::post('create-user-paid-course-payment', 'PaidCourseController@createUserPaidCoursePaymentMobile');
    Route::post('start-paid-course-quiz', 'PaidCourseController@startPaidCouseQuiz');
    Route::post('submit-paid-course-quiz-result', 'PaidCourseController@submitPaidCourseQuizResultMobile');
    Route::get('paid-course/result-list-by-user-id/{paid_course_material_id}/{user_id}', 'PaidCourseController@getPaidCourseResultByUserId');

    Route::get('paid-course/merit-list/{paid_course_material_id}', 'PaidCourseController@getPaidCourseMeritListForMobile');

    /** Paid Course Coupon  */
    Route::post('check-coupon-validity', 'PaidCourseController@checkCouponValidityFromMobile');

    /** LC Integration */
    Route::get('lc/get-mentor-course-list/{user_id}', 'ClassScheduleController@mentorCourseList');
    Route::get('lc/student-list/{paid_course_id}/{user_id}', 'ClassScheduleController@mentorStudentList');
    Route::get('lc/class-schedule-list/{mapping_id}', 'ClassScheduleController@mentorClassScheduleList');
    Route::post('lc/add-class-schedule', 'ClassScheduleController@addClassSchedule');
    Route::post('lc/update-class-schedule', 'ClassScheduleController@updateClassSchedule');
    Route::post('lc/delete-class-schedule', 'ClassScheduleController@deleteClassSchedule');
    Route::get('lc/student-class-schedule-list/{user_id}', 'ClassScheduleController@studentClassScheduleList');
    
    Route::post('lc/update-zoom-link', 'ClassScheduleController@updateZoomLink');
    Route::get('lc/get-zoom-details/{user_id}', 'ClassScheduleController@getZoomLink');

    //Join & End class
    Route::post('lc/start-live-class', 'ClassScheduleController@startLiveClass');
    Route::post('lc/end-live-class', 'ClassScheduleController@endLiveClass');
    Route::post('lc/student-end-live-class', 'ClassScheduleController@studentEndLiveClass');
    Route::post('lc/join-live-class', 'ClassScheduleController@studentJoinClass');

    Route::get('lc/student-join-history/{schedule_id}', 'ClassScheduleController@studentClassJoinHistory');
    Route::get('lc/mentor-ongoing-class-list/{user_id}', 'ClassScheduleController@mentorOngoingClassList');
    Route::get('lc/mentor-completed-class-list', 'ClassScheduleController@mentorCompletedClassList');    
});

Route::post('keep-lecture-video', 'LectureController@keepLectureVideo');



Route::post('jwt-login', 'APILoginController@jwtLogin');

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::get('/jwt-test', 'SelectionTestController@jwtTest');

    Route::get('dashboard-analytics', 'DashboardController@dashboardCourseAnalytics');
    Route::get('payment-analytics', 'DashboardController@paymentAnalytics');

    Route::get('selectionTestSummary', 'SelectionTestController@SelectionTestSummary');
    Route::get('getAllSelectionTestList', 'SelectionTestController@getAllSelectionTestList');
    Route::post('createSelectionTest', 'SelectionTestController@createSelectionTest');
    Route::post('deleteSelectionTest', 'SelectionTestController@deleteSelectionTest');

    Route::get('getSelectionTestWrittenQuestionList/{selection_test_id}', 'SelectionTestController@getSelectionTestWrittenQuestionList');
    Route::post('addUpdateSelectionTestWrittenQuestion', 'SelectionTestController@addUpdateSelectionTestWrittenQuestion');

    Route::get('getAllSelectionTestQuotaList/{id}', 'SelectionTestController@getAllSelectionTestQuotaList');
    Route::post('updateQuota', 'SelectionTestController@updateQuota');

    Route::get('getSelectionTestDetailsByID/{selection_test_id}', 'SelectionTestController@getSelectionTestDetails');
    Route::get('getSelectionTestAllQuestionListByID/{selection_test_id}', 'SelectionTestController@getSelectionTestAllQuestionList');
    Route::post('deleteSelectionTestQuestion', 'SelectionTestController@deleteSelectionTestQuestion');

    Route::post('addSelectionTestQuestion', 'SelectionTestController@addSelectionTestQuestion');
    Route::post('uploadSelectionTestQuestion', 'SelectionTestController@uploadSelectionTestQuestion');
    Route::post('updateSelectionTestQuestion', 'SelectionTestController@updateSelectionTestQuestion');

    // ******************  Paid Course  ******************
    Route::post('paid-course/create-paid-course', 'PaidCourseController@createPaidCourse');
    Route::post('paid-course/paid-course-update', 'PaidCourseController@updatePaidCourse');
    Route::get('paid-course/get-all-paid-courses', 'PaidCourseController@getAllPaidCourseAdmin');
    Route::get('paid-course/get-all-active-courses', 'PaidCourseController@getAllActivePaidCourseAdmin');
    Route::get('paid-course/get-all-test', 'PaidCourseController@getAllTestList');
    Route::get('paid-course/details-by-id/{id}', 'PaidCourseController@getPaidCourseDetailsByID');

    Route::get('paid-course/test-list-by-id/{paid_course_id}', 'PaidCourseController@adminGetAllTestList');

    Route::post('paid-course-meterials/add-update-subject', 'PaidCourseController@createUpdatePaidCourseMeterialSubject');
    Route::post('paid-course-meterials/add-bulk-subject', 'PaidCourseController@createPaidCourseMeterialSubjectBulk');

    Route::get('paid-course-meterials-subject-list/{paid_course_material_id}', 'PaidCourseController@PaidCourseMeterialSubjectList');
    Route::post('paid-course-meterials/delete-subject', 'PaidCourseController@deleteMeterialSubject');

    Route::post('paid-course/create-test', 'PaidCourseController@createPaidTest');
    Route::post('paid-course/update-test', 'PaidCourseController@updatePaidTest');
    Route::post('paid-course/upload-test-via-excel', 'PaidCourseController@uploadPaidTestViaExcel');

    Route::get('getPaidCourseTestAllQuestionListByID/{paid_course_material_id}', 'PaidCourseController@getPaidCourseTestAllQuestionList');
    Route::get('getPaidCourseTestDetailsByID/{paid_course_material_id}', 'PaidCourseController@getPaidCourseTestDetails');
    Route::post('updatePaidCourseTestQuestion', 'PaidCourseController@updatePaidCourseTestQuestion');
    Route::post('addPaidCourseTestQuestion', 'PaidCourseController@addPaidCourseTestQuestion');

    Route::post('updateTestQuestionAttachment', 'PaidCourseController@updateTestQuestionAttachment');
    Route::post('deleteCourseTestQuestionImage', 'PaidCourseController@deleteCourseTestQuestionImage');

    Route::post('addPaidCourseQuizQuestionExcel', 'PaidCourseController@addPaidCourseQuizQuestionExcel');
    Route::get('get-optional-subject-list', 'PaidCourseController@getOptionalSubjectList');
    Route::get('get-core-subject-list', 'PaidCourseController@getCoreSubjectList');

    Route::post('addUpdatePaidCourseTestWrittenQuestion', 'PaidCourseController@addUpdatePaidCourseTestWrittenQuestion');

    Route::post('deleteCourseTestQuestion', 'PaidCourseController@deleteCourseTestQuestion');
    Route::post('paid-course/add-update-subject', 'PaidCourseController@AddUpdateSubject');
    Route::get('paid-course/subject-list-ByID/{paid_course_id}', 'PaidCourseController@SubjectList');
    Route::post('paid-course/attachment-upload', 'PaidCourseController@uploadPaidCourseTestWrittenAttachment');

    Route::get('paid-course/payment-list', 'PaidCourseController@getPaidCoursePaymentHistory');
    Route::get('paid-course/payment-list-download', 'PaidCourseController@downloadPaidCoursePaymentHistory');
    Route::get('paid-course/purchase-report-download', 'PaidCourseController@downloadPaidCoursePurchaseHistory');

    Route::get('paid-course/applied-coupon-list', 'PaidCourseController@getPaidCourseApplyCouponHistory');
    Route::get('paid-course/applied-coupon-list/{coupon_id}', 'PaidCourseController@getPaidCourseApplyCouponHistory');
    Route::get('paid-course/result-list/{paid_course_material_id}', 'PaidCourseController@getPaidCourseResultList');
    Route::get('paid-course/merit-list/{paid_course_material_id}', 'PaidCourseController@getPaidCourseMeritList');
    Route::get('paid-course/quota-list/{paid_course_material_id}', 'PaidCourseController@getPaidCourseQuotaList');
    Route::post('paid-course/update-quota', 'PaidCourseController@updateQuota');


    Route::get('paid-course/test-result-excel/{paid_course_material_id}', 'PaidCourseController@getPaidCourseTestResult');
    Route::get('paid-course/download-pc-quiz-result-pdf/{paid_course_result_id}', 'PaidCourseController@downloadPCQuizResultmPdf');
    Route::get('paid-course/download-pc-quiz-result-excel/{paid_course_result_id}', 'PaidCourseController@pcQuizResultExcelDownload');
    Route::get('paid-course/download-pc-quiz-all-result-excel/{paid_course_meterial_id}', 'PaidCourseController@downloadAllStudentResultsInExcel');

    Route::get('paid-course/coupon-uses-report', 'PaidCourseController@getPaidCourseCouponUsesReport');
    Route::get('paid-course/download-coupon-list', 'PaidCourseController@downloadCouponList');

    Route::get('paid-course/written-details/{paid_course_result_id}', 'PaidCourseController@getPaidCourseWrittenResultDetails');

    Route::post('paid-course/update-written-marks', 'PaidCourseController@getPaidCourseWrittenMarksUpdate');


    //LC Integration
    Route::post('lc/add-update-teacher', 'TeacherController@addNewTeacher');
    Route::get('lc/teachers', 'TeacherController@teacherList');
    Route::post('lc/upload-teacher-excel', 'TeacherController@teacherUploadExcel');

    Route::post('lc/assign-teacher', 'TeacherController@assignTeacher');
    Route::get('lc/paid-course-mentors/{paid_course_id}', 'TeacherController@teacherListbyCourseID');
    Route::post('lc/paid-course-remove-mentor', 'TeacherController@removeMentorFromPaidCourse');

    Route::get('lc/paid-course-lc-students/{paid_course_id}', 'PaidCourseController@getPaidCourseLCStudentList');
    Route::get('lc/paid-course-lc-students-by-mentor/{paid_course_id}/{mentor_id}', 'PaidCourseController@getPaidCourseLCStudentListByMentor');
    Route::get('lc/paid-course-students/{paid_course_id}', 'PaidCourseController@getPaidCourseStudentList');
    Route::post('lc/activate', 'PaidCourseController@activateLC');
    Route::post('lc/deactive', 'PaidCourseController@deactivateLC');
    Route::post('lc/add-mapping', 'PaidCourseController@paidCourseMapping');
    Route::get('lc/mapping-list/{paid_course_id}', 'PaidCourseController@getPaidCourseMappingList');
    Route::post('lc/remove-mapping', 'PaidCourseController@removeMappingFromPaidCourse');

    Route::get('lc/completed-class-list', 'ClassScheduleController@adminCompletedClassList');
});

Route::get('getSelectionTestResult/{id}', 'SelectionTestController@getSelectionTestResult');

// SSLCOMMERZ Start
Route::get('/example1', 'SslCommerzPaymentController@exampleEasyCheckout');
Route::get('/example2', 'SslCommerzPaymentController@exampleHostedCheckout');

Route::post('/pay', 'SslCommerzPaymentController@index');
Route::post('/pay-via-ajax', 'SslCommerzPaymentController@payViaAjax');

Route::post('/success', 'SslCommerzPaymentController@success');
Route::post('/fail', 'SslCommerzPaymentController@fail');
Route::post('/cancel', 'SslCommerzPaymentController@cancel');

Route::post('/ipn', 'SslCommerzPaymentController@ipn');
//SSLCOMMERZ END


