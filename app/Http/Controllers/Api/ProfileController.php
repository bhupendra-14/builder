<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Asset;

class ProfileController extends BaseController
{
    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();
        
        $data = $request->only(['name', 'email']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return $this->successResponse($user, 'Profile updated successfully.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048' // 2MB max
        ]);

        $user = $request->user();
        $file = $request->file('avatar');

        // Simple upload handling (later integrated seamlessly with Media/Asset Manager)
        $path = $file->store('avatars', 'public');
        
        // Save to Assets table to maintain referential integrity
        $asset = Asset::create([
            'user_id' => $user->id,
            'file_name' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'asset_type' => 'image',
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'folder' => 'avatars',
        ]);

        $user->update(['avatar_id' => $asset->id]);

        return $this->successResponse([
            'avatar_url' => asset('storage/' . $path)
        ], 'Avatar updated successfully.');
    }
}
