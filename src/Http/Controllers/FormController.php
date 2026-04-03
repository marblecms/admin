<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Marble\Admin\Events\MarbleFormSubmitted;
use Marble\Admin\Facades\Marble;
use Marble\Admin\Models\FormSubmission;
use Marble\Admin\Models\Item;

class FormController extends Controller
{
    public function submit(Request $request, Item $item)
    {
        if (!$item->blueprint->is_form) {
            abort(404);
        }

        // Map fields[field_id][lang_id] → [field_identifier => value]
        $langId = Marble::primaryLanguageId();
        $raw    = $request->input('fields', []);
        $data   = [];
        $systemFields = ['name', 'slug'];
        foreach ($item->blueprint->fields as $field) {
            if (in_array($field->identifier, $systemFields)) continue;
            if (isset($raw[$field->id][$langId])) {
                $data[$field->identifier] = $raw[$field->id][$langId];
            }
        }

        $submission = FormSubmission::create([
            'item_id'    => $item->id,
            'data'       => $data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'read'       => false,
        ]);

        MarbleFormSubmitted::dispatch($submission, $item, $data);

        // Send e-mail notification if recipients configured
        $recipients = array_filter(
            array_map('trim', explode(',', $item->blueprint->form_recipients ?? '')),
            fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        );
        if (!empty($recipients)) {
            $subject = 'New form submission: ' . $item->name();
            $body = "A new form submission was received.\n\n";
            foreach ($data as $key => $val) {
                $body .= "{$key}: " . (is_array($val) ? implode(', ', $val) : $val) . "\n";
            }

            foreach ($recipients as $recipient) {
                Mail::raw($body, function ($msg) use ($recipient, $subject) {
                    $msg->to($recipient)->subject($subject);
                });
            }
        }

        $successMessage = $item->blueprint->form_success_message ?: 'Thank you for your message!';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMessage]);
        }

        // Redirect to success item if configured
        if ($item->blueprint->form_success_item_id) {
            $successItem = \Marble\Admin\Models\Item::find($item->blueprint->form_success_item_id);
            if ($successItem && $successItem->isPublished()) {
                return redirect(\Marble\Admin\Facades\Marble::url($successItem));
            }
        }

        return back()->with('marble_form_success', $successMessage);
    }
}
