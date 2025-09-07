<?php

use Illuminate\Support\Facades\Route;
use App\Models\Profile;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect('/admin');
});

// Route để clear viewing session
Route::post('/admin/profiles/{id}/clear-session', function ($id, Request $request) {
    \Log::info('=== CLEAR SESSION REQUEST ===', [
        'profile_id' => $id,
        'user_id' => auth()->id(),
        'timestamp' => now()
    ]);
    
    $profile = Profile::find($id);
    
    if (!$profile) {
        return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
    }
    
    $user = auth()->user();
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
    }
    
    // Chỉ clear session nếu đúng user đang xem
    $result = $profile->clearViewingSessionIfOwnedBy($user->id);
    
    \Log::info('=== CLEAR SESSION RESULT ===', [
        'result' => $result,
        'profile_id' => $id,
        'user_id' => $user->id
    ]);
    
    return response()->json([
        'success' => $result,
        'message' => $result ? 'Session cleared successfully' : 'Session not cleared'
    ]);
})->middleware(['web', 'auth']);

// Route để check status của profile (for modal refresh detection)
Route::get('/admin/profiles/{id}/status', function ($id) {
    $profile = Profile::find($id);
    
    if (!$profile) {
        return response()->json(['error' => 'Profile not found'], 404);
    }
    
    return response()->json([
        'status' => $profile->status,
        'updated_at' => $profile->updated_at
    ]);
})->middleware(['web', 'auth']);

// Route để cập nhật password trong database
Route::post('/admin/profiles/{id}/update-password', function ($id, Request $request) {
    $profile = Profile::find($id);
    
    if (!$profile) {
        return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
    }
    
    $user = auth()->user();
    if (!$user || !$user->hasPermissionTo('approve_profile')) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    // Chỉ cho phép cập nhật password cho hồ sơ chờ duyệt/chờ và chưa lock
    if (!in_array($profile->status, [0, 5]) || $profile->password_lock) {
        return response()->json(['success' => false, 'message' => 'Profile is not pending approval or password is locked'], 400);
    }
    
    $password = $request->input('password');
    if (!$password || strlen($password) < 6) {
        return response()->json(['success' => false, 'message' => 'Invalid password'], 400);
    }
    
    // Cập nhật password trong database
    $profile->update(['password' => $password]);
    
    return response()->json(['success' => true, 'message' => 'Password updated successfully']);
})->middleware(['web', 'auth']);

