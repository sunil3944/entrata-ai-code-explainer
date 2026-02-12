<!DOCTYPE html>
<html>
<head>
    <title>AI Code Explainer</title>
    <style>
        
        .loader {
        width: fit-content;
        font-weight: bold;
        font-family: sans-serif;
        font-size: 30px;
        padding-bottom: 8px;
        background: linear-gradient(currentColor 0 0) 0 100%/0% 3px no-repeat;
        animation: l2 2s linear infinite;
        }
        .loader:before {
        content:"Loading..."
        }
        @keyframes l2 {to{background-size: 100% 3px}}
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
</head>

<body>
<div style="display:flex;height:100vh;">

    {{-- SIDEBAR --}}
    <div id="sidebar" style="width:260px;border-right:1px solid #ddd;padding:10px;overflow:auto">
        <h4>History</h4>
        <button id="newSnippet" style="padding:5px;margin-bottom:10px;">New Snippet</button>

        <div id="history">
            @foreach($snippets as $s)
                <div class="history-item"
                     data-id="{{ $s->id }}"
                     style="cursor:pointer;padding:6px;background:#f7f7f7;margin-bottom:4px">
                    {{ strtoupper($s->language) }} #{{ $s->id }}
                </div>
            @endforeach
        </div>
    </div>

    {{-- MAIN --}}
    <div  style="flex:1;padding:20px">

        <div id="snippetForm">
            <h3>Paste Code</h3>

            <textarea id="code" rows="10" style="width:100%"></textarea>

            <br><br>
            <div id="error-div" style="margin-bottom:10px;color:red;"></div>

            <button id="submit">Explain Code</button>

            <hr>
        </div>

        <div id="result">
            
        </div>
    </div>
</div>

<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(window).on("load", function () {

    $('#snippetForm').show();

});

$('#submit').on('click', function () {

    let code = $('#code').val();
    // if(!code || code == '')
    // {
    //     alert('Code is required');
    //     return false;
    // }

    $('#error-div').html('');

    $('#result').html('<div class="loader" style="margin-top: 40px;"></div>');

    $.ajax({
        url: '/snippets/ajax',
        type: 'POST',
        dataType: 'json',
        data: {
            code: code
        },
        success: function (res) {

            let complexity = '';
            if (res.snippet.complexity) {
                complexity = `
                    <h3>Complexity</h3>
                    <pre>${res.snippet.complexity}</pre>
                `;
            }

            let optimizedCode = '';
            if (res.snippet.optimized_code) {
                optimizedCode = `
                    <h3>Optimized Code</h3>
                    <pre>${res.snippet.optimized_code}</pre>
                `;
            }

            $('#result').html(`
                <h3>Explanation</h3>
                <p>${res.snippet.explanation}</p>
                ${complexity}
                ${optimizedCode}
            `);

            // prepend history
            $('#history').prepend(`
                <div class="history-item"
                     data-id="${res.snippet.id}"
                     style="cursor:pointer;padding:6px;background:#f7f7f7;margin-bottom:4px">
                     ${res.snippet.language.toUpperCase()} #${res.snippet.id}
                </div>
            `);
        },
        error: function (xhr) {
            $('#result').html('');
            $('#error-div').html(xhr.responseJSON?.message);
        }
    });
});

// Load history item
$(document).on('click', '.history-item', function () {
    let id = $(this).data('id');

    $('#snippetForm').hide();
    $('#error-div').html('');

    $.get('/snippets/' + id, function (res) {

        let complexity = '';
        if (res.complexity) {
            complexity = `
                <h3>Complexity</h3>
                <pre>${res.complexity}</pre>
            `;
        }

        let optimizedCode = '';
        if (res.optimized_code) {
            optimizedCode = `
                <h3>Optimized Code</h3>
                <pre>${res.optimized_code}</pre>
            `;
        }

        $('#result').html(`
            <h3>Code</h3>
            <pre>${res.code}</pre>

            <h3>Explanation</h3>
            <p>${res.explanation}</p>

            ${complexity}

            ${optimizedCode}

        `);
    });
});

$('#newSnippet').on('click', function () {
  $('#snippetForm').show();
  $('#code').val('');
  $('#result').html('');
  $('#error-div').html('');
});
</script>

