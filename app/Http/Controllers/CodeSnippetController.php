<?php
namespace App\Http\Controllers;

use App\Models\CodeSnippet;
use App\Services\AiCodeExplainService;
use Illuminate\Http\Request;

class CodeSnippetController extends Controller
{
    public function index()
    {
        try
        {   

            //Get All Snippets
            $snippets = CodeSnippet::latest()->get();
            $data['snippets'] = $snippets;

            return view('index', $data);

        } catch (Throwable $e) {

            Log::error('Snippet index page failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Something went wrong. Please try again.'
            ], 500);
        }
        
    }

    public function storeAjax(Request $request, AiCodeExplainService $ai)
    {
        try
        {
            $request->validate([
                'code' => 'required'
            ]);

            $result = $ai->explain($request->code);
            $explanation = isset($result['explanation']) ? $result['explanation'] : '';
            $optimizedCode = isset($result['optimized_code']) ? $result['optimized_code'] : '';
            $language = isset($result['language']) ? $result['language'] : '';
            $complexity = isset($result['complexity']) ? $result['complexity'] : '';
            $status = isset($result['status']) ? $result['status'] : '';

            if ($status === 'error') {
                return response()->json([
                    'message' => $result['message']
                ], 422);
            }

            //Store Snippet
            $snippet = CodeSnippet::create([
                'language' => $language,
                'code' => $request->code,
                'explanation' => $explanation,
                'optimized_code' => $optimizedCode,
                'complexity' => $complexity,
            ]);

            $data['snippet'] = $snippet;

            return response()->json($data);

        } catch (Throwable $e) {
            Log::error('Snippet store failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function showAjax($id)
    {
        try
        {
            $snippet = CodeSnippet::findOrFail($id);
            return response()->json($snippet);

        } catch (Throwable $e) {
            Log::error('Snippet store failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }
}
