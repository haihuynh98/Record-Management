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
