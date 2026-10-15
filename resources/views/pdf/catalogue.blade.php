<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catalogue — {{ $shop->name }}</title>
    <style>
        @page {
            margin: 40px 30px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #171717;
            font-size: 11px;
            line-height: 1.5;
        }

        /* ═══════ COUVERTURE ═══════ */
        .cover {
            text-align: center;
            padding: 80px 20px;
            border-bottom: 2px solid #171717;
            margin-bottom: 40px;
        }

        .cover-label {
            font-size: 9px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #78716C;
            margin-bottom: 20px;
        }

        .cover-title {
            font-size: 38px;
            font-weight: bold;
            color: #171717;
            margin: 10px 0 20px;
            line-height: 1.1;
        }

        .cover-description {
            font-size: 12px;
            color: #57534E;
            max-width: 400px;
            margin: 0 auto 20px;
        }

        .cover-contact {
            font-size: 10px;
            color: #78716C;
            margin-top: 30px;
        }

        /* ═══════ SECTION TITRE ═══════ */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #171717;
            margin: 30px 0 20px;
            text-align: center;
        }

        /* ═══════ GRILLE MODÈLES ═══════ */
        .grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px;
        }

        .grid-cell {
            width: 33.33%;
            vertical-align: top;
            text-align: center;
        }

        .card {
            border: 1px solid #E7E5E0;
            padding: 10px;
            background: #FFFFFF;
        }

        /* Conteneur d'image à hauteur fixe, centrage horizontal */
        .card-image-wrapper {
            height: 180px;
            margin-bottom: 10px;
            text-align: center;
        }

        .card-image {
            display: inline-block;
            vertical-align: middle;
        }

        .card-image-placeholder {
            width: 120px;
            height: 180px;
            background: #F5F4F1;
            margin: 0 auto;
        }

        .card-name {
            font-size: 12px;
            font-weight: bold;
            color: #171717;
            margin: 0 0 4px;
            line-height: 1.3;
        }

        .card-price {
            font-size: 11px;
            color: #171717;
            margin: 0;
        }

        .card-meta {
            font-size: 9px;
            color: #78716C;
            margin: 4px 0 0;
        }

        .card-category {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #A8A29E;
            margin-bottom: 6px;
        }

        /* ═══════ PIED DE PAGE ═══════ */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            letter-spacing: 3px;
            color: #A8A29E;
            text-transform: uppercase;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- ═══════════ COUVERTURE ═══════════ -->
    <div class="cover">
        <p class="cover-label">Collection</p>
        <h1 class="cover-title">{{ $shop->name }}</h1>

        @if ($shop->description)
            <p class="cover-description">{{ $shop->description }}</p>
        @endif

        <p class="cover-contact">
            @if ($shop->phone)
                Tél : {{ $shop->phone }}
            @endif
            @if ($shop->whatsapp)
                &nbsp;·&nbsp; WhatsApp : {{ $shop->whatsapp }}
            @endif
        </p>

        <p class="cover-contact" style="margin-top: 60px;">
            Catalogue de {{ $models->count() }} création{{ $models->count() > 1 ? 's' : '' }}
        </p>
    </div>

    <!-- ═══════════ CATALOGUE ═══════════ -->
    <div class="section-title">Nos créations</div>

    <table class="grid">
        @foreach ($models->chunk(3) as $chunk)
            <tr>
                @foreach ($chunk as $model)
                    <td class="grid-cell">
                        <div class="card">
                            @php
                                $mainImage = $model->images->firstWhere('type', 'main') ?? $model->images->first();
                                $fullPath = $mainImage ? storage_path('app/public/' . $mainImage->path) : null;

                                $imgWidth = null;
                                $imgHeight = null;

                                if ($mainImage && file_exists($fullPath)) {
                                    $size = @getimagesize($fullPath);
                                    if ($size) {
                                        [$origW, $origH] = $size;

                                        // Cadre max dans la carte
                                        $maxW = 160;
                                        $maxH = 180;

                                        // Ratio pour conserver les proportions
                                        $ratio = min($maxW / $origW, $maxH / $origH);

                                        $imgWidth  = round($origW * $ratio);
                                        $imgHeight = round($origH * $ratio);
                                    }
                                }
                            @endphp

                            <div class="card-image-wrapper">
                                @if ($mainImage && file_exists($fullPath))
                                    <img
                                        src="data:image/{{ pathinfo($mainImage->path, PATHINFO_EXTENSION) }};base64,{{ base64_encode(file_get_contents($fullPath)) }}"
                                        width="{{ $imgWidth }}"
                                        height="{{ $imgHeight }}"
                                        class="card-image"
                                        alt="{{ $model->name }}"
                                    >
                                @else
                                    <div class="card-image-placeholder"></div>
                                @endif
                            </div>

                            @if ($model->category)
                                <div class="card-category">{{ $model->category->name }}</div>
                            @endif

                            <p class="card-name">{{ $model->name }}</p>

                            @if ($model->price)
                                <p class="card-price">
                                    {{ number_format($model->price, 0, ',', ' ') }} {{ $model->currency }}
                                </p>
                            @endif

                            @if ($model->color)
                                <p class="card-meta">{{ $model->color }}</p>
                            @endif
                        </div>
                    </td>
                @endforeach

                {{-- Complète la ligne si moins de 3 modèles --}}
                @for ($i = $chunk->count(); $i < 3; $i++)
                    <td class="grid-cell"></td>
                @endfor
            </tr>
        @endforeach
    </table>

    <!-- ═══════════ FOOTER ═══════════ -->
    <div class="footer">
        Propulsé par Stylward — L'art de créer
    </div>

</body>
</html>
