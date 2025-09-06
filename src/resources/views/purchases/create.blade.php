@extends('layouts.app')
@section('title','購入手続き')
@section('page_css')
  <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
@endsection

@section('content')
<form class="checkout" method="POST" action="{{ route('purchases.store', $item) }}">
  @csrf

  <div class="checkout__left">
    <div class="row">
      <img class="thumb thumb--sm" src="{{ $item->image_url }}" alt="">
      <div>
        <div class="title-sm">{{ $item->title }}</div>
        <div class="price">¥ {{ number_format($item->price) }}</div>
      </div>
    </div>

    <hr class="divider mt-md mb-md">

    <div class="section">
      <label class="label label--bold" for="paymentSelect">支払い方法</label>
      <select class="select" id="paymentSelect" name="payment" required>
        <option value="">選択してください</option>
        <option value="konbini" {{ old('payment')==='konbini' ? 'selected' : '' }}>コンビニ払い</option>
        <option value="card"    {{ old('payment','card')==='card' ? 'selected' : '' }}>カード払い</option>
      </select>
      @error('payment')<div class="error">{{ $message }}</div>@enderror
    </div>

    <hr class="divider mt-md mb-md">

    <div class="section">
      <div class="label label--bold">配送先</div>
      <div class="address">
        <div>〒 {{ auth()->user()->postal_code }}</div>
        <div>{{ auth()->user()->address }}</div>
        <div>{{ auth()->user()->building }}</div>
      </div>
      <a class="link link--blue mt-xs" href="{{ route('mypage.profile.edit') }}#address-form">変更する</a>

      {{-- 将来のために保持（いまはControllerで未使用でもOK） --}}
      <input class="hidden" type="text" name="shipping_postal_code" value="{{ old('shipping_postal_code', auth()->user()->postal_code) }}">
      <input class="hidden" type="text" name="shipping_address"     value="{{ old('shipping_address',     auth()->user()->address) }}">
      <input class="hidden" type="text" name="shipping_building"    value="{{ old('shipping_building',    auth()->user()->building) }}">
      @error('shipping_postal_code')<div class="error">{{ $message }}</div>@enderror
      @error('shipping_address')     <div class="error">{{ $message }}</div>@enderror
      @error('shipping_building')    <div class="error">{{ $message }}</div>@enderror
    </div>

    <hr class="divider mt-md mb-md">
  </div>

  <div class="checkout__right">
    <div class="summary">
      <div class="summary__row"><div>商品代金</div><div>¥ {{ number_format($item->price) }}</div></div>
      <div class="summary__row">
        <div>支払い方法</div>
        <div><span id="summaryPayment">—</span></div>
      </div>
    </div>
    <button class="btn btn--primary full mt-md" type="submit">購入する</button>
  </div>
</form>

{{-- セレクト変更でサマリーを更新 --}}
<script>
  (function(){
    const select = document.getElementById('paymentSelect');
    const target = document.getElementById('summaryPayment');
    const labels = {
      'konbini': 'コンビニ払い',
      'card':    'カード払い'
    };
    function sync() { target.textContent = labels[select.value] ?? '—'; }
    select.addEventListener('change', sync);
    // 初期反映
    sync();
  })();
</script>
@endsection
