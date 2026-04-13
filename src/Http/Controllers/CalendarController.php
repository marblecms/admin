<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\Item;

class CalendarController extends Controller
{
    public function index()
    {
        $blueprints = Blueprint::orderBy('name')->get();
        return view('marble::calendar.index', compact('blueprints'));
    }

    public function events(Request $request): JsonResponse
    {
        $start       = $request->query('start');
        $end         = $request->query('end');
        $blueprintId = $request->query('blueprint_id');

        $query = Item::with('blueprint')
            ->whereNotNull('published_at')
            ->orWhereNotNull('expires_at');

        if ($blueprintId) {
            $query->where('blueprint_id', $blueprintId);
        }

        $items = Item::with('blueprint')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('published_at', [$start, $end])
                  ->orWhereBetween('expires_at', [$start, $end]);
            });

        if ($blueprintId) {
            $items->where('blueprint_id', $blueprintId);
        }

        $events = [];

        foreach ($items->get() as $item) {
            $name = $item->name() ?: "#{$item->id}";
            $url  = route('marble.item.edit', $item);

            if ($item->published_at) {
                $events[] = [
                    'id'              => 'pub-' . $item->id,
                    'title'           => $name,
                    'start'           => $item->published_at->toDateTimeString(),
                    'url'             => $url,
                    'color'           => $item->status === 'published' ? '#388E3C' : '#1976D2',
                    'textColor'       => '#fff',
                    'extendedProps'   => [
                        'blueprint' => $item->blueprint?->name,
                        'status'    => $item->status,
                        'type'      => 'publish',
                        'itemId'    => $item->id,
                    ],
                ];
            }

            if ($item->expires_at) {
                $events[] = [
                    'id'              => 'exp-' . $item->id,
                    'title'           => '⏎ ' . $name,
                    'start'           => $item->expires_at->toDateTimeString(),
                    'url'             => $url,
                    'color'           => '#C62828',
                    'textColor'       => '#fff',
                    'extendedProps'   => [
                        'blueprint' => $item->blueprint?->name,
                        'status'    => $item->status,
                        'type'      => 'expire',
                        'itemId'    => $item->id,
                    ],
                ];
            }
        }

        return response()->json($events);
    }

    public function reschedule(Request $request, Item $item): JsonResponse
    {
        $request->validate([
            'field' => 'required|in:published_at,expires_at',
            'date'  => 'required|date',
        ]);

        $item->{$request->input('field')} = $request->input('date');
        $item->save();

        return response()->json(['ok' => true]);
    }
}
