@extends('layouts.app')
@section('title', 'Papan Peringkat — RedSim')
@section('header', 'Papan Peringkat')
@section('subheader', 'Peringkat pengguna berdasarkan poin tantangan keamanan')

@section('content')
<div class="space-y-6">
    @include('edukasi._leaderboard_content', ['leaders' => $leaders, 'isAuth' => true])
</div>
@endsection
