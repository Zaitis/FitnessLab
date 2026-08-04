{{--
    Rendered by DompdfPlanExporter — see docs/DESIGN-PATTERNS.md §6. The
    watermark is a diagonal, semi-transparent, absolutely-positioned div;
    dompdf's CSS support is limited but comfortably covers this.

    font-family is set to 'DejaVu Sans' explicitly (also config/dompdf.php's
    default_font) because dompdf's built-in serif/sans-serif aliases don't
    render Polish diacritics (ą, ć, ę, ł, ń, ó, ś, ź, ż) correctly, and this
    export is bilingual.
--}}
<!DOCTYPE html>
<html lang="{{ $data->locale }}">
<head>
<meta charset="utf-8">
<style>
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 11px;
        color: #1a1a1a;
        margin: 0;
        padding: 24px 32px;
    }

    .watermark {
        position: fixed;
        top: 45%;
        left: -25%;
        width: 150%;
        text-align: center;
        transform: rotate(-30deg);
        font-size: 34px;
        font-weight: bold;
        color: #cc0000;
        opacity: 0.12;
        z-index: -1;
    }

    h1 {
        font-size: 20px;
        margin: 0 0 4px;
    }

    .summary {
        font-size: 12px;
        color: #444;
        margin: 0 0 20px;
    }

    h2 {
        font-size: 13px;
        margin: 16px 0 6px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 2px;
    }

    ul {
        margin: 0;
        padding-left: 16px;
    }

    li {
        margin-bottom: 3px;
    }

    .disclaimer {
        margin-top: 28px;
        padding-top: 8px;
        border-top: 1px solid #ccc;
        font-size: 9px;
        color: #666;
    }
</style>
</head>
<body>
    <div class="watermark">{{ $data->disclaimer }}</div>

    <h1>{{ $data->title }}</h1>
    <p class="summary">{{ $data->summary }}</p>

    @foreach ($data->sections as $section)
        <h2>{{ $section->heading }}</h2>
        <ul>
            @foreach ($section->lines as $line)
                <li>{{ $line }}</li>
            @endforeach
        </ul>
    @endforeach

    <p class="disclaimer">{{ $data->disclaimer }}</p>
</body>
</html>
