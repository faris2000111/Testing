@extends('errors.layout')

@section('title', '403 - Akses Ditolak')
@section('orb-color', '#ef4444')
@section('orb-color-2', '#dc2626')
@section('particle-color', 'rgba(239, 68, 68, 0.6)')
@section('code-gradient-from', '#ef4444')
@section('code-gradient-to', '#b91c1c')

@section('content')
  <div class="error-code">403</div>
  <h1 class="title">Akses Ditolak</h1>
  <p class="subtitle">Kamu tidak memiliki izin untuk mengakses halaman ini. Hubungi administrator jika ini adalah kesalahan.</p>
  <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="btn-back">
    <span>&#8592;</span> Kembali
  </a>
@endsection
