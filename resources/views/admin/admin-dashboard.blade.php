@extends('admin.layouts.app_first', [
  'title'     => 'Super Admin Dashboard',
  'sub_title' => ''
])

@section('css')
<style>
  /* Page header like the screenshot */
  .page-hero {
    background: #fff;
    border-radius: .75rem;
    padding: 1.75rem 2rem;
    box-shadow: 0 1px 2px rgba(16,24,40,.04), 0 1px 1px rgba(16,24,40,.04);
  }
  .page-hero h2 {
    font-weight: 700;
    letter-spacing: .2px;
    margin-bottom: .5rem;
  }
  .page-hero .lead {
    font-size: 1.25rem;
    color: #1f2937;
    margin: 0;
  }

  /* Optional: zebra rows from your snippet */
  .table>tbody>tr:nth-child(odd){ background-color:#fff4ef!important; }

  /* Keep spacing consistent across the dashboard */
  .content-gap { gap: 1rem; }
</style>
@endsection

@section('breadcrumb')
<div class="breadcrumb-header">
  <div class="container-fluid">
    <h5 class="breadcrumb-line">
      <i class="bi bi-pin"></i>
      <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    </h5>
  </div>
</div>
@endsection

@section('content')
<div class="row gy-3">
  <div class="col-12">
    <div class="page-hero">
      <h2 class="display-6 mb-1">Super Admin Dashboard</h2>
      <p class="lead">
        Hello, {{ strtoupper(Auth::user()->name ?? 'SUPER ADMIN') }},
        Welcome to Super Admin Dashboard
      </p>
    </div>
  </div>

  {{-- Keep an empty working canvas like the screenshot.
       You can add blocks/cards below later as needed. --}}
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body" style="min-height: 55vh;">
        {{-- Future widgets / quick links / stats can go here --}}
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
{{-- No JS needed for this simple header layout --}}
@endsection
