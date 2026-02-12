<h3>Original Code</h3>
<pre style="background:#f8f8f8; padding:10px;">{{ $snippet->code }}</pre>

@if ($snippet->optimized_code)
    <h3>Optimized Code (AI Suggested)</h3>
    <pre style="background:#e8fff1; padding:10px;">
{{ $snippet->optimized_code }}
    </pre>
@else
    <p><em>No optimization suggested.</em></p>
@endif