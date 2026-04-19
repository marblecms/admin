<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Marble\Admin\Models\MarbleSetting;

class AiAssistantController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'prompt'  => 'required|string|max:2000',
            'context' => 'nullable|string|max:10000',
        ]);

        $provider = MarbleSetting::get('ai_provider', 'disabled');
        $apiKey   = MarbleSetting::get('ai_api_key', '');
        $model    = MarbleSetting::get('ai_model', '');

        if ($provider === 'disabled' || empty($apiKey)) {
            return response()->json(['error' => 'AI assistant is not configured. Add your API key in Admin → Configuration.'], 422);
        }

        $prompt  = $request->input('prompt');
        $context = $request->input('context', '');

        $systemPrompt = 'You are a helpful content writing assistant for a CMS. '
            . 'Respond with clean, well-formatted HTML suitable for a rich text editor. '
            . 'Use <p>, <h2>, <h3>, <ul>, <ol>, <li>, <strong>, <em> tags as appropriate. '
            . 'Do not wrap in <html>/<body>/<div>. No markdown. No code fences. Just the content HTML.';

        $userMessage = $context
            ? "The editor currently contains this text:\n\n{$context}\n\nTask: {$prompt}"
            : $prompt;

        try {
            if ($provider === 'openai') {
                $model    = $model ?: 'gpt-4o';
                $response = Http::withToken($apiKey)
                    ->timeout(30)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model'      => $model,
                        'messages'   => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user',   'content' => $userMessage],
                        ],
                        'max_tokens' => 2000,
                    ]);

                if (!$response->successful()) {
                    $msg = $response->json('error.message') ?? ('HTTP ' . $response->status());
                    return response()->json(['error' => 'OpenAI error: ' . $msg], 500);
                }

                $result = $response->json('choices.0.message.content') ?? '';

            } elseif ($provider === 'anthropic') {
                $model    = $model ?: 'claude-sonnet-4-6';
                $response = Http::withHeaders([
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                ])
                    ->timeout(30)
                    ->post('https://api.anthropic.com/v1/messages', [
                        'model'      => $model,
                        'max_tokens' => 2000,
                        'system'     => $systemPrompt,
                        'messages'   => [
                            ['role' => 'user', 'content' => $userMessage],
                        ],
                    ]);

                if (!$response->successful()) {
                    $msg = $response->json('error.message') ?? ('HTTP ' . $response->status());
                    return response()->json(['error' => 'Anthropic error: ' . $msg], 500);
                }

                $result = $response->json('content.0.text') ?? '';

            } else {
                return response()->json(['error' => 'Unknown AI provider configured.'], 422);
            }

            return response()->json(['result' => trim($result)]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
