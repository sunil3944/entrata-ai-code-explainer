
# Entrata AI Code Explainer

This project is a small Laravel application that accepts a code snippet (Python or JavaScript), sends it to an AI service for validation and explanation, stores the result, and displays saved snippets.

What I implemented
- `App\Services\AiCodeExplainService`: sends a carefully constructed prompt to an AI/chat completion API (Mistral in configuration) and parses the response. It enforces a strict response format and returns structured data (language, explanation, optimized code, complexity).
- `App\Models\CodeSnippet`: Eloquent model for storing code snippets and AI responses. Fillable fields: `language`, `code`, `explanation`, `optimized_code`, `complexity`.
- Migrations: created `code_snippets` table and later added `optimized_code` and `complexity` columns (see `database/migrations`).
- `App\Http\Controllers\CodeSnippetController`: endpoints for listing snippets, storing an explanation via AJAX (`storeAjax`), and fetching a snippet (`showAjax`). The controller validates input, calls the AI service, and persists results.
- Simple Blade views under `resources/views` provide a UI to submit code and view stored snippets.

Requirements
- PHP 8+ (installed with XAMPP on Windows)
- Composer
- MySQL

Quick install (Windows + XAMPP)

1. Clone the repository and change directory:

```bash
git clone https://github.com/sunil3944/entrata-ai-code-explainer.git entrata-ai-code-explainer
cd entrata-ai-code-explainer
```

2. Install PHP dependencies with Composer:

```bash
composer install
```

3. Create environment file and set DB credentials (for XAMPP typical values shown):

```powershell
copy .env.example .env
# Edit .env and set DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=entrata_ai DB_USERNAME=root DB_PASSWORD=
```

Create the database in phpMyAdmin or via MySQL CLI before running migrations (example name: `entrata_ai`).

4. Generate the application key:

```bash
php artisan key:generate
```

5. Run migrations:

```bash
php artisan migrate
```

6. Serve the app locally — using Laravel's built-in server:

```bash
php artisan serve --host=127.0.0.1 --port=8000
# Open http://127.0.0.1:8000
```

Configuration
- AI service credentials: set the API key in `config/services.php` (key expected at `services.mistral.key`) or in your `.env` as documented in that file.
- The repository's `.env.example` already includes the AI key variable `MISTRAL_API_KEY` and other required configuration. Copy the example file to create your local environment file:

```powershell
copy .env.example .env
# then edit .env and set your MISTRAL_API_KEY and DB credentials
```

Notes about behavior
- The AI prompt (in `AiCodeExplainService`) enforces only Python or JavaScript. If the AI returns an `ERROR:` response or an unsupported language, the controller returns a validation-like JSON error to the client.
- The parsed AI response is stored in `code_snippets` with `explanation`, optional `optimized_code`, and optional `complexity` fields.


Useful files
- [app/Services/AiCodeExplainService.php](app/Services/AiCodeExplainService.php) — AI request & parsing logic
- [app/Models/CodeSnippet.php](app/Models/CodeSnippet.php) — Eloquent model
- [app/Http/Controllers/CodeSnippetController.php](app/Http/Controllers/CodeSnippetController.php) — controller endpoints
- [database/migrations](database/migrations) — migrations for `code_snippets`

If you want, I can also:
- Add detailed environment variable examples in this README
- Create a `.env.example` copy (if missing) or a small setup script for Windows

