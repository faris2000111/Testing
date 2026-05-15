@extends('errors.layout')

@section('title', '500 - Server Error')
@section('orb-color', '#dc2626')
@section('orb-color-2', '#7f1d1d')
@section('particle-color', 'rgba(220, 38, 38, 0.5)')
@section('code-gradient-from', '#dc2626')
@section('code-gradient-to', '#7f1d1d')

@section('content')
  <div class="error-code">500</div>
  <h1 class="title">Terjadi Kesalahan</h1>
  <p class="subtitle">Server mengalami masalah internal. Tim kami sudah diberitahu. Silakan coba lagi nanti.</p>
  <a href="{{ url('/') }}" class="btn-back">
    <span>&#8592;</span> Kembali ke Beranda
  </a>
@endsection
