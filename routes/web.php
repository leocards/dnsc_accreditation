<?php

use App\Http\Controllers\AccreditationController;
use App\Http\Controllers\AccreditorController;
use App\Http\Controllers\AttachController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FacultyStaffController;
use App\Http\Controllers\InstituteController;
use App\Http\Controllers\InstrumentCommentController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\TaskHomeController;
use App\Http\Controllers\TaskPageController;
use App\Http\Controllers\UserController;
use App\Models\AttachedDocument;
use App\Models\User;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

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

Route::get('/', function () {
    return redirect()->route('index');
});

Route::controller(AuthController::class)->group(function () {
    Route::get('/index', 'index')->name('index');
    Route::get('/forgot', 'index')->name('forgot');
    Route::get('/register', 'index')->name('register');
    Route::get('/forgot/change', 'index')->name('change');

    Route::post('/login', 'login')->name('login');

    Route::post('/pre-register', 'register');
    Route::post('/verify_username', 'verifyUsername');
    Route::Post('/change_my_password', 'changeMyPassword');
});

Route::get('csrf', function () {
    return response()->json(csrf_token());
});

Route::get('/redirect', [AuthController::class, 'redirects'])->name('redirect');

Route::post('/redirected_login', [AuthController::class, 'redirectedLogin']);

Route::middleware(['auth'])->group(function () {
    Route::get('/verify_user', [AuthController::class, 'verifyUserAuth'])->name('verify_user');
    Route::get('/logout', [AuthController::class, 'logOut']);
    
    Route::middleware(['admin'])->group(function () {
        //accreditation routes
        Route::controller(AccreditationController::class)->group(function () {
            Route::get('/accreditation', 'index')->name('accreditation_indx');
            Route::get('/accreditation/tagged/{id}', 'programTagged');
            Route::get('/accreditation/mark_as_complete_self_survey/{accred}', 'markAsCompleteSelfSurvey');
            Route::get('/unverified', 'unverified_accred');
            
            Route::post('/accreditation/create', 'tagProgram');
            Route::post('/accreditation/open_survey', 'openSurvey');
            Route::post('/accreditation/close_survey', 'closeCurrentSurvey');
            Route::post('/accreditation/restrict', 'restrictUpload');
            Route::post('/accreditation/areas_certification', 'getAccredAreas');
            Route::post('/accreditation/remove_selfSurvey', 'removeMarkAsCompleteSelfSurvey');
            Route::post('/accreditation/confirmVerify', 'confirmVerified');
            Route::post('/accreditation/update_tag_program', 'updateTag');
            Route::post('/accreditation/set_status', 'setAccredStatus');
            Route::Post('/accreditation/unverify_rates', 'unverify');
        });
        
        //instrument routes
        Route::controller(InstrumentController::class)->group(function () {
            Route::get('/accreditation/instrument', 'index')->name('instrument_indx');
            Route::get('/accreditation/instrument/inst/{id?}', 'index')->name('instrument_indx2');
            Route::get('/accreditation/instrument/exclude/{inst}', 'excludeInComputation');
            
            Route::post('/accreditation/instrument/create/{id?}', 'store');
            Route::post('/accreditation/instrument/delete', 'destroy');
        });
    });

    Route::controller(AccreditorController::class)->group(function () {
        Route::get('/accreditor', 'accreditor')->name('accreditor');
        Route::get('/self_accreditor', 'selfAccreditor')->name('self_accreditor');
        Route::get('/accreditor/verify', 'verifyAccreditor')->name('accreditor_verify');

        Route::post('/self_accreditor/rate', 'rateInstrument');
    });

    Route::controller(AttachController::class)->group(function () {
        Route::post('/attach', 'attachDocument')->middleware('uploadRestriction');
    });

    Route::controller(ChatController::class)->group(function () {
        Route::get('/chat', 'index')->name('chat_indx');
        Route::get('/chat/getMessages/{id}', 'getMessages');
        Route::get('/chat/search/{search}', 'chatUserSearch');

        Route::get('/chat/seen_msg', 'setSeenMsg');
        Route::post('/chat/send', 'store');

    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard')->middleware('authUser');
        Route::get('/data_to_cluster/{surveyId}', 'area_indicators');
        
        Route::post('/announcements', 'announcement');
        Route::post('/dashboard/remove_announce', 'removeAnnounce');
        Route::post('/announcement/new', 'addAnnounce')->middleware('admin');
    });

    Route::controller(DocumentController::class)->group(function () {
        Route::get('/document/uploads', 'index')->name('document_indx');
        Route::get('/document/share', 'sharedIndex');
        Route::get('/document/load', 'lazyLoadDocument');
        Route::get('/download/{id}', 'downloadDocu');

        Route::post('/document/tmp/{accredlvl}', 'temporaryUpload')->middleware('uploadRestriction');
        Route::post('/document/tmp_revert', 'revertTemporaryUpload');
        Route::post('/document/upload', 'store')->middleware('uploadRestriction');
        Route::post('/document/update', 'update')->middleware('uploadRestriction');
        //validate document
        Route::post('/document/validate', 'validateDocument');
        //comment
        Route::post('/document/comment', 'storeDocument');
        Route::post('/document/get/comment', 'getComment');
        Route::post('/document/get/versions', 'getVersions');
        Route::post('/document/upload/ne_version', 'uploadNewVersion')->middleware('uploadRestriction');
        Route::post('/document/set_as_current', 'setAsCurrent')->middleware('uploadRestriction');
        //get documents
        Route::post('/document/getDocuments', 'getInstrumentDocuments');
        Route::post('/document/remove', 'removeDocument');
    });
    
    //facultyStaff routes
    Route::controller(FacultyStaffController::class)->group(function () {
        Route::get('/facultyStaff/{program}/{institute}', 'index')->name('facultyStaff_indx');

        Route::post('/facultyStaff/create', 'createFS');
        Route::post('/facultyStaff/updated/{id}', 'updateFS');
        Route::post('/facultyStaff/suggestNames', 'suggestNameFS');
    });

    //institute routes
    Route::controller(InstituteController::class)->group(function () {
        Route::get('/institute', 'index')->name('institute_indx')->middleware('authUser:false,true');
        Route::get('/institute/getInstitutes', 'getInstitutes');
        Route::get('/institute/deans', 'getDeans');
        
        Route::post('/institute/create', 'store')->middleware('authUser:false,true');
        Route::post('/institute/update/{id}', 'update')->middleware('authUser:false,true');
    });

    Route::controller(InstrumentCommentController::class)->group(function () {
        Route::post('/instrument/comment', 'store');
        Route::post('/instrument/get/comments', 'getComments');
    });

    Route::controller(NotificationController::class)->group(function () {
        Route::get('/notifications', 'index')->name('notif_indx');
        Route::post('/notification/mark_as_read', 'markAsRead');
    });

    //program routes
    Route::controller(ProgramController::class)->group(function () {
        Route::get('/program', 'index')->name('program_indx')->middleware('authUser:true,false');
        Route::post('/program/create', 'store')->middleware('authUser:true,false');
        Route::post('/program/update/{id}', 'update')->middleware('authUser:true,false');
        Route::get('/program/search/{search?}', 'searchProgram');
        
        //open program route
        Route::get('/program/level', 'programLevel')->middleware('authUser:true,false');
        Route::get('/program/level/prog/{id}/level/{lvl?}/{current?}', 'programLevel');
        Route::get('/program/getPrograms', 'getPrograms');

        //Task Force route
        Route::get('/program/tfassigned/{accredlvl}/{area}');
        Route::post('/program/remove/taskforce/member', 'removeMember');
        Route::get('/program/getAreaAssigned/{accredlvl}/{area}', 'getAreaAssigned');
        Route::post('/program/assign/taskforce/{accredlvl}/{area}', 'assignTaskForce');
        Route::post('/program/add_member/taskforce/{accredlvl}/{area}', 'addMemberTaskForce');
        Route::post('/program/change_chair/taskforce/{accredlvl}/{area}', 'changeTaskForceChair');//changeTaskForceChair
        Route::post('/program/change_member/taskforce/{accredlvl}/{area}', 'changeMemberTaskForce');

        //Area Self accreditor
        Route::post('/program/assign/selfAccreditor', 'assignSelfAccreditor');
        Route::post('/program/getassigned/selfAccreditor', 'getAssignSelfAccreditor');
    });

    Route::controller(ShareController::class)->group(function () {
        Route::post('/share/share_document', 'store');
        Route::get('/share/shared_with/{docuId}', 'getShared');
    });
    
    Route::controller(TaskHomeController::class)->group(function () {
        Route::get('/task', 'index')->name('task_home_indx');
        Route::get('/task/navigate/{inst}/{area}/{accredlvl}', 'getInstrumentLocation');
        Route::post('/task/drilldown', 'getParameterProgress');

    });

    Route::controller(TaskPageController::class)->group(function () {
        Route::get('/task/page/level/{accredId}/area/{areaId}', 'index')->name('task_page_indx');

        Route::get('/task/task_force_members/{area}/{accredlvl}', 'getMembers');

        Route::post('/task/assignMember', 'assignMember');
        Route::get('/task/assigned_member/{instrument}/{accredlvl}', 'getassignedMember');

        Route::post('/task/set_as_complete', 'setAsComplete');
        Route::post('/task/assignMember/edit', 'updateAssign');

    });

    //user routes
    Route::controller(UserController::class)->group(function () {
        Route::get('/user', 'index')->name('user_indx')->middleware('admin');
        Route::get('/user/get_des_and_auth', 'designation_auth');
        Route::get('/user/profile/{id}', 'userProfile');
        Route::get('/user/request', 'index')->name('user_req_indx')->middleware('admin');
        //search user to assign in area
        Route::get('/user/search/area_toAssign/{search}/{tfc?}', 'searchUser_AreaAssign');
        //search user to assign in area as self accreditor
        Route::get('/user/search/selfaccreditor/{search}', 'searchSA_AreaAssign');
        //search user to share
        Route::get('/user/search/shareWith/{search}', 'searchUserToShare');
        //user logs
        Route::get('/userlogs/{accred}/{instrument}', 'getUserLogs');

        Route::post('/user/get_profile', 'get_user_profile');
        Route::post('/user/update', 'updateUser');
        Route::post('/user/profile_update', 'updateProfile');
        Route::post('/user/request/register', 'storeUser')->middleware('admin');
        //profile
        Route::post('/user/profile/tmp', 'avatarTemporaryUpload');
        Route::post('/user/profile/tmp_revert', 'avatarRevertUpload');
        Route::post('/user/setTheme', 'setTheme');
        Route::post('/user/removeRequest', 'removeRequest');
        Route::post('/user/restoreRequest', 'restoreRequest');
        ROute::post('/user/change_password', 'changePassword');
    });

});

/* Route::get('/dfdsfsdfg', function () {
    dd(Hash::make('AnnaDoe@12345'));
    //dd(Hash::make('IanSomosot@1234'));
}); */