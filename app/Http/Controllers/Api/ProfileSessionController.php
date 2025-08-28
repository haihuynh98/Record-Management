<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\ProfileViewingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileSessionController extends Controller
{
    protected ProfileViewingService $profileViewingService;

    public function __construct(ProfileViewingService $profileViewingService)
    {
        $this->profileViewingService = $profileViewingService;
    }

    /**
     * Bắt đầu session xem profile
     */
    public function startSession(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
        ]);

        $profile = Profile::findOrFail($request->profile_id);
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Kiểm tra xem profile có đang được xem bởi người khác không
        if ($this->profileViewingService->isProfileBeingViewed($profile)) {
            $currentViewer = $this->profileViewingService->getCurrentViewer($profile);
            return response()->json([
                'success' => false,
                'message' => 'Hồ sơ đang được xử lý bởi ' . ($currentViewer ? $currentViewer->username : 'người khác'),
                'is_being_viewed' => true,
                'current_viewer' => $currentViewer ? $currentViewer->username : null,
            ], 409);
        }

        // Bắt đầu session
        $success = $this->profileViewingService->startViewingSession($profile, $user);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Đã bắt đầu xem hồ sơ',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không thể bắt đầu session',
        ], 500);
    }

    /**
     * Kết thúc session xem profile
     */
    public function endSession(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
        ]);

        $profile = Profile::findOrFail($request->profile_id);
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $success = $this->profileViewingService->endViewingSession($profile, $user);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Đã kết thúc session' : 'Không thể kết thúc session',
        ]);
    }

    /**
     * Cập nhật activity của session
     */
    public function updateActivity(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
        ]);

        $profile = Profile::findOrFail($request->profile_id);
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $success = $this->profileViewingService->updateActivity($profile, $user);

        return response()->json([
            'success' => $success,
        ]);
    }

    /**
     * Kiểm tra trạng thái session của profile
     */
    public function checkStatus(Request $request, $profileId): JsonResponse
    {
        $profile = Profile::findOrFail($profileId);
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $isBeingViewed = $this->profileViewingService->isProfileBeingViewed($profile);
        $currentViewer = $this->profileViewingService->getCurrentViewer($profile);
        $isUserViewing = $this->profileViewingService->isUserViewing($profile, $user);

        return response()->json([
            'is_being_viewed' => $isBeingViewed,
            'current_viewer' => $currentViewer ? $currentViewer->username : null,
            'is_user_viewing' => $isUserViewing,
        ]);
    }
}
