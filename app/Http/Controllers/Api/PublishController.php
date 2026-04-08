<?php

namespace App\Http\Controllers\Api;

use App\Services\PublishService;
use App\Models\PublishHistory;
use Illuminate\Http\Request;

class PublishController extends BaseController
{
    public function __construct(
        protected PublishService $publishService
    ) {}

    public function publish(Request $request)
    {
        $request->validate([
            'environment' => 'required|in:dark,live',
            'notes' => 'nullable|string|max:1000',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $env = $request->input('environment');
        $notes = $request->input('notes');
        $userId = $request->user()->id;

        $this->authorize($env === 'dark' ? 'publish_dark' : 'publish_live');

        if ($request->filled('scheduled_at')) {
            $history = $this->publishService->schedule(
                $env,
                $userId,
                \Illuminate\Support\Carbon::parse($request->input('scheduled_at')),
                $notes
            );
            return $this->successResponse(
                $history,
                "Publish to {$env} scheduled for {$history->scheduled_at}."
            );
        }

        $history = $env === 'dark'
            ? $this->publishService->publishToDark($userId, $notes)
            : $this->publishService->publishToLive($userId, $notes);

        return $this->successResponse($history, "Successfully published to {$env} environment.");
    }

    public function history(Request $request)
    {
        $user = $request->user();
        if (!$user->can('publish_live') && !$user->can('publish_dark')) {
            return $this->errorResponse('Unauthorized action.', 403);
        }


        $env = $request->query('environment');
        
        $query = PublishHistory::with('user:id,name')
                    ->orderBy('created_at', 'desc');
                    
        if ($env) {
            $query->where('environment', $env);
        }
        
        $histories = $query->paginate(20);
        
        return $this->paginatedResponse($histories, 'Publish history retrieved.');
    }
}
