<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiCodeExplainService
{
    public function explain(string $code)
    {
        $prompt = $this->buildPrompt($code);

        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.mistral.key'),
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.mistral.ai/v1/chat/completions', [
                'model' => 'mistral-small-latest',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.2,
            ]);

        if (!$response->successful()) {
            \Log::error('Mistral API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
    
            return [
                'status' => 'error',
                'message' => 'AI service is temporarily unavailable. Please try again later.'
            ];
        }

        $content = $response->json('choices.0.message.content');

        if (!$content) {
            return [
                'status' => 'error',
                'message' => 'Unable to generate explanation due to insufficient AI response.'
            ];
        }
        
        \Log::info('Mistral API Success', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return $this->parseResponse($content);
    }

    private function buildPrompt(string $code)
    {
        return <<<PROMPT
        You are a senior software engineer and a strict code validator.

        Your task is to FIRST validate and detect the programming language, and ONLY then explain the code.
        
        Rules:
        - Accept ONLY valid Python or JavaScript code.
        - If the input is NOT code, or is NOT Python or JavaScript, respond with ERROR format only.
        - Do NOT guess the language.
        - Do NOT explain invalid input.
        - Do NOT add extra text outside the defined format.

        If valid, perform the following:
        1. Detect the language (python or javascript).
        2. Explain what the code does in plain English (2–4 sentences).
        3. Suggest an optimized or cleaner version IF POSSIBLE.
        4. Explain the time and space complexity **ONLY if it can be reasonably detected from the code**.
            - If not clearly detectable, say: "Complexity depends on external factors or input size."
        5. If no optimization is needed, say "No optimization needed".

        Respond in this EXACT format:

        ERROR:
        Only valid Python or JavaScript code is allowed.

        If valid, respond EXACTLY in this format:

        LANGUAGE:
        <python | javascript>

        EXPLANATION:
        <text>

        COMPLEXITY:
        Time: <Big-O or explanation>
        Space: <Big-O or explanation>

        OPTIMIZED_CODE:
        <code or "No optimization needed">

        Code:
        {$code}
        PROMPT;
    }

    private function parseResponse(string $content): array
    {
        if (str_starts_with(trim($content), 'ERROR:')) {
            return [
                'status' => 'error',
                'message' => 'Only valid Python or JavaScript code is allowed'
            ];
        }

        $language = '';
        $explanation = '';
        $optimizedCode = null;
        $complexity = '';

        //Indentiy the Language
        if (preg_match('/LANGUAGE:\s*(python|javascript)/i', $content, $matches)) {
            $language = strtolower($matches[1]);
        }
        
        //Get the Explanation from response
        if (preg_match('/EXPLANATION:\s*(.*?)\n\s*COMPLEXITY:/s', $content, $matches)) {
            $explanation = trim($matches[1]);
        }

        //Get the Complexity from response
        if (preg_match('/COMPLEXITY:\s*(.*?)\n\s*OPTIMIZED_CODE:/s', $content, $matches)) {
            $complexity = trim($matches[1]);
        }
        
        //Get Optimized Code 
        if (preg_match('/OPTIMIZED_CODE:(.*)/s', $content, $matches)) {
            $optimized = trim($matches[1]);
            if (stripos($optimized, 'no optimization') === false) {
                $optimizedCode = $optimized;
            }
        } 


        if (!$language || !$explanation) {
            return [
                'status' => 'error',
                'message' => 'Invalid or unsupported code input'
            ];
        }

        return [
            'status' => 'ok',
            'language' => $language,
            'explanation' => $explanation,
            'optimized_code' => $optimizedCode,
            'complexity' => $complexity,
        ];
    }
}
