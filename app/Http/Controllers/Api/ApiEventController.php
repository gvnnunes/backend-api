<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ApiEventController extends Controller
{
    public function index()
    {
        return EventResource::collection(Event::all());
    }

    public function show(Event $event)
    {
        return new EventResource($event->loadMissing(['participants', 'speakers', 'eventCategories']));
    }

    public function store(StoreEventRequest $request)
    {
        try {
            $event = Event::create($request->validated());
            $eventResource = new EventResource($event);

            return response()->json($eventResource, Response::HTTP_OK);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Failed to create event'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        try {
            $event->update($request->validated());
            $event = $event->load(['participants', 'speakers', 'eventCategories']);
            $eventResource = new EventResource($event);

            return response()->json($eventResource, Response::HTTP_OK);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Failed to update event'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Event $event)
    {
        DB::beginTransaction();

        try {
            $event->delete();

            DB::commit();

            return response()->json([
                'message' => 'Event deleted successfully'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete event'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
