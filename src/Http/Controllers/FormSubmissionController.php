<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Marble\Admin\Models\FormSubmission;
use Marble\Admin\Models\Item;

class FormSubmissionController extends Controller
{
    public function index(Item $item)
    {
        $submissions = FormSubmission::where('item_id', $item->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('marble::form.index', compact('item', 'submissions'));
    }

    public function show(Item $item, FormSubmission $submission)
    {
        $submission->update(['read' => true]);

        return view('marble::form.show', compact('item', 'submission'));
    }

    public function markRead(Item $item, FormSubmission $submission)
    {
        $submission->update(['read' => true]);

        return redirect()->route('marble.item.edit', $item);
    }

    public function destroy(Item $item, FormSubmission $submission)
    {
        $submission->delete();

        return redirect()->route('marble.form.index', $item);
    }
}
