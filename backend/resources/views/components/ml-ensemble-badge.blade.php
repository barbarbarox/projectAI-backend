{{-- 
    views/components/ml-ensemble-badge.blade.php
    Badge kompak untuk menampilkan hasil Ensemble ML (RF + XGBoost)
    di kartu temuan / laporan scan RedSim.

    Usage:
      <x-ml-ensemble-badge :prediction="$mlResult" />

    Props:
      $prediction : array|null — hasil dari MLPredictionService::predict()
--}}

@props(['prediction' => null])

@if($prediction)
    @php
        $ensembleSev  = $prediction['ensemble_severity']    ?? $prediction['ml_predicted_severity'] ?? 'UNKNOWN';
        $ensembleConf = $prediction['ensemble_confidence']  ?? $prediction['ml_confidence'] ?? 0;
        $rfSev        = data_get($prediction, 'rf.severity',  '?');
        $xgbSev       = data_get($prediction, 'xgb.severity', '?');
        $modelsAgree  = $prediction['models_agree'] ?? ($rfSev === $xgbSev);
        $konsensus    = $prediction['konsensus']    ?? $ensembleSev;
        $agreement    = $prediction['agreement']    ?? false;
        $confPct      = round($ensembleConf * 100, 1);

        $colorMap = [
            'CRITICAL' => ['bg' => '#ff4444', 'text' => '#fff',    'glow' => 'rgba(255,68,68,0.4)'],
            'HIGH'     => ['bg' => '#ff8800', 'text' => '#fff',    'glow' => 'rgba(255,136,0,0.4)'],
            'MEDIUM'   => ['bg' => '#ffc107', 'text' => '#1a1a1a', 'glow' => 'rgba(255,193,7,0.3)'],
            'LOW'      => ['bg' => '#28a745', 'text' => '#fff',    'glow' => 'rgba(40,167,69,0.3)'],
            'NONE'     => ['bg' => '#6c757d', 'text' => '#fff',    'glow' => 'none'],
        ];
        $colors = $colorMap[$ensembleSev] ?? $colorMap['NONE'];

        $labelId = 'ml-badge-' . uniqid();
    @endphp

    <div class="ml-ensemble-badge" id="{{ $labelId }}"
         title="Ensemble: RF={{ $rfSev }} | XGB={{ $xgbSev }} | Konsensus={{ $konsensus }}">

        {{-- Severity chip --}}
        <span class="ml-severity-chip"
              style="background:{{ $colors['bg'] }};color:{{ $colors['text'] }};
                     box-shadow: 0 0 8px {{ $colors['glow'] }};">
            🤖 {{ $ensembleSev }}
        </span>

        {{-- Confidence bar --}}
        <span class="ml-conf-bar" title="Confidence: {{ $confPct }}%">
            <span class="ml-conf-fill"
                  style="width:{{ $confPct }}%;
                         background:{{ $colors['bg'] }};opacity:0.8;">
            </span>
            <span class="ml-conf-label">{{ $confPct }}%</span>
        </span>

        {{-- Agreement indicators --}}
        <span class="ml-model-pills">
            <span class="ml-pill" title="Random Forest: {{ $rfSev }}">
                RF {{ $rfSev === $ensembleSev ? '✅' : '⚠️' }}
            </span>
            <span class="ml-pill" title="XGBoost: {{ $xgbSev }}">
                XGB {{ $xgbSev === $ensembleSev ? '✅' : '⚠️' }}
            </span>
            @if($agreement)
                <span class="ml-pill ml-pill-llm" title="LLM sepakat dengan Ensemble">LLM ✅</span>
            @else
                <span class="ml-pill ml-pill-llm ml-pill-warn" title="LLM tidak sepakat dengan Ensemble">LLM ⚠️</span>
            @endif
        </span>
    </div>

    <style>
    #{{ $labelId }} {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        font-family: 'Inter', sans-serif;
        font-size: 11px;
    }
    #{{ $labelId }} .ml-severity-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 11px;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    #{{ $labelId }} .ml-conf-bar {
        position: relative;
        display: inline-flex;
        align-items: center;
        width: 80px;
        height: 16px;
        background: rgba(255,255,255,0.1);
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.15);
    }
    #{{ $labelId }} .ml-conf-fill {
        position: absolute;
        left: 0; top: 0; bottom: 0;
        border-radius: 8px;
        transition: width 0.4s ease;
    }
    #{{ $labelId }} .ml-conf-label {
        position: relative;
        z-index: 1;
        font-size: 9px;
        font-weight: 600;
        color: #fff;
        width: 100%;
        text-align: center;
    }
    #{{ $labelId }} .ml-model-pills {
        display: flex;
        gap: 4px;
    }
    #{{ $labelId }} .ml-pill {
        padding: 2px 7px;
        border-radius: 10px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        color: rgba(255,255,255,0.75);
        font-size: 10px;
        font-weight: 500;
    }
    #{{ $labelId }} .ml-pill-warn {
        border-color: rgba(255,193,7,0.5);
        color: #ffc107;
    }
    </style>
@endif
