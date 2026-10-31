@php
$pageTitle = 'Kebijakan Privasi';
$pageSubtitle = 'Komitmen kami dalam melindungi data dan privasi Anda';
$lastUpdated = '31 Oktober 2026';
@endphp
@include('legal._layout', [
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => $pageSubtitle,
    'lastUpdated'  => $lastUpdated,
    'content'      => 'legal._kebijakan_content',
])
