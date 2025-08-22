@extends('layouts.app')
@section('title','Welcome')

@section('content')
<div class="center">
  <h1 class="page-title">ようこそ！</h1>
  <a class="btn btn--primary" href="{{ route('items.index') }}">商品一覧へ</a>
</div>
@endsection
