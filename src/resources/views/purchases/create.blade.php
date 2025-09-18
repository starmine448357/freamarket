@extends('layouts.app')
@section('title','購入手続き')
@section('page_css')
  <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
@endsection

@section('content')
<form class="checkout" method="POST" action="{{ route('purchases.store', $item) }}">
  @csrf

  <div class="checkout__left">
    {{-- 商品情報 --}}
    <div class="row">
      <img class="thumb thumb--sm" src="{{ $item->image_url }}" alt="{{ $item->title }}">
      <div>
        <div class="title-sm">{{ $item->title }}</div>
        <div class="price">¥ {{ number_format($item->price) }}</div>
      </div>
    </div>

    <hr class="divider mt-md mb-md">

    {{-- 支払い方法 --}}
    <div class="section">
      <label class="label label--bold" for="paymentSelect">支払い方法</label>
      <select class="select" id="paymentSelect" name="payment">
        <option value="">選択してください</option>
        <option value="konbini" {{ old('payment')==='konbini' ? 'selected' : '' }}>コンビニ払い</option>
        <option value="card"    {{ old('payment')==='card' ? 'selected' : '' }}>カード払い</option>
      </select>
      @error('payment')<div class="error">{{ $message }}</div>@enderror
    </div>

    <hr class="divider mt-md mb-md">

    {{-- 配送先 --}}
    <div class="section">
      <div class="label label--bold">配送先</div>

      {{-- デフォルト表示 --}}
      <div id="defaultAddress" 
           @if($errors->has('shipping_postal_code') || $errors->has('shipping_address')) style="display:none;" @endif 
           class="address-card">
        <div>〒 {{ old('shipping_postal_code', auth()->user()->postal_code) }}</div>
        <div>{{ old('shipping_address', auth()->user()->address) }}</div>
        <div>{{ old('shipping_building', auth()->user()->building) }}</div>
        <a class="link link--blue mt-xs" href="javascript:void(0);" id="toggleAddressForm">配送先を変更する</a>

        {{-- hiddenで送信 --}}
        <input type="hidden" name="shipping_postal_code" value="{{ old('shipping_postal_code', auth()->user()->postal_code) }}">
        <input type="hidden" name="shipping_address" value="{{ old('shipping_address', auth()->user()->address) }}">
        <input type="hidden" name="shipping_building" value="{{ old('shipping_building', auth()->user()->building) }}">
      </div>

      {{-- 入力フォーム --}}
      <div id="addressForm" 
           class="address-form" 
           style="@if($errors->has('shipping_postal_code') || $errors->has('shipping_address')) display:block; @else display:none; @endif">
        
        <div class="form-group">
          <label for="shipping_postal_code">郵便番号</label>
          <input type="text" id="shipping_postal_code" name="shipping_postal_code" 
                 value="{{ old('shipping_postal_code', auth()->user()->postal_code) }}">
          @error('shipping_postal_code')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label for="shipping_address">住所</label>
          <input type="text" id="shipping_address" name="shipping_address" 
                 value="{{ old('shipping_address', auth()->user()->address) }}">
          @error('shipping_address')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label for="shipping_building">建物名</label>
          <input type="text" id="shipping_building" name="shipping_building" 
                 value="{{ old('shipping_building', auth()->user()->building) }}">
          @error('shipping_building')<div class="error">{{ $message }}</div>@enderror
        </div>

        {{-- 操作ボタン --}}
        <div class="address-actions">
          <button type="button" id="cancelAddress" class="btn btn--outline btn--small">戻る</button>
          <button type="submit" name="action" value="confirm" class="btn btn--primary btn--small">完了</button>
        </div>
      </div>
    </div>

    <hr class="divider mt-md mb-md">
  </div>

  {{-- 購入サマリー --}}
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

{{-- JS --}}
<script>
(function(){
  // 支払い方法の反映
  const select = document.getElementById('paymentSelect');
  const target = document.getElementById('summaryPayment');
  const labels = {
    'konbini': 'コンビニ払い',
    'card':    'カード払い'
  };
  function sync() { target.textContent = labels[select.value] ?? '—'; }
  select.addEventListener('change', sync);
  sync();

  // 配送先切替
  const toggleBtn = document.getElementById('toggleAddressForm');
  const form = document.getElementById('addressForm');
  const defaultAddress = document.getElementById('defaultAddress');
  const cancelBtn = document.getElementById('cancelAddress');

  if(toggleBtn){
    toggleBtn.addEventListener('click', () => {
      form.style.display = 'block';
      defaultAddress.style.display = 'none';
    });
  }

  if(cancelBtn){
    cancelBtn.addEventListener('click', () => {
      form.style.display = 'none';
      defaultAddress.style.display = 'block';
    });
  }
})();
</script>
@endsection
