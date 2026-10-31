@php
$pageTitle = 'Ketentuan Layanan';
$pageSubtitle = 'Syarat dan ketentuan penggunaan platform RedSim';
$lastUpdated = '31 Oktober 2026';
@endphp
@include('legal._layout', [
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => $pageSubtitle,
    'lastUpdated'  => $lastUpdated,
    'content'      => 'legal._ketentuan_content',
])
