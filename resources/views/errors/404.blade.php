@extends('errors.layout')

@section('title', '404 - Halaman Tidak Ditemukan')
@section('orb-color', '#f59e0b')
@section('orb-color-2', '#ef4444')
@section('particle-color', 'rgba(245, 158, 11, 0.6)')
@section('code-gradient-from', '#f59e0b')
@section('code-gradient-to', '#ef4444')

@section('content')
  <div class="error-code">404</div>
  <h1 class="title">Halaman Tidak Ditemukan</h1>
  <p class="subtitle">Halaman yang kamu cari tidak ada atau sudah dipindahkan. Periksa kembali URL-nya.</p>
  <a href="{{ url('/') }}" class="btn-back">
    <span>&#8592;</span> Kembali ke Beranda
  </a>
@endsection
