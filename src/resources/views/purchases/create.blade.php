@extends('layouts.app')
@section('title','購入手続き')
@section('page_css')<link rel="stylesheet" href="{{ asset('css/items.css') }}">@endsection

@section('content')
<form class="checkout" method="POST" action="{{ route('purchases.store',$item) }}">
  @csrf

  <div class="checkout__left">
    <div class="row">
      <img class="thumb thumb--sm" src="{{ $item->image_path ? asset('storage/'.$item->image_path) : asset('img/placeholder.png') }}" alt="">
      <div>
        <div class="title-sm">{{ $item->title }}</div>
        <div class="price">¥ {{ number_format($item->price) }}</div>
      </div>
    </div>

    <hr class="divider mt-md mb-md">

    <div class="section">
      <div class="label label--bold">支払い方法</div>
      <select class="select" name="payment_method">
        <option value="">選択してください</option>
        <option value="convenience_store" @selected(old('payment_method')==='convenience_store')>コンビニ払い</option>
        <option value="credit_card" @selected(old('payment_method')==='credit_card')>カード払い</option>
        <option value="bank_transfer" @selected(old('payment_method')==='bank_transfer')>銀行振込</option>
      </select>
      @error('payment_method')<div class="error">{{ $message }}</div>@enderror
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

      {{-- Controllerのバリデーションに合わせてhidden投入 --}}
      <input class="hidden" type="text" name="shipping_postal_code" value="{{ old('shipping_postal_code', auth()->user()->postal_code) }}">
      <input class="hidden" type="text" name="shipping_address" value="{{ old('shipping_address', auth()->user()->address) }}">
      <input class="hidden" type="text" name="shipping_building" value="{{ old('shipping_building', auth()->user()->building) }}">
      @error('shipping_postal_code')<div class="error">{{ $message }}</div>@enderror
      @error('shipping_address')<div class="error">{{ $message }}</div>@enderror
      @error('shipping_building')<div class="error">{{ $message }}</div>@enderror
    </div>

    <hr class="divider mt-md mb-md">
  </div>

  <div class="checkout__right">
    <div class="summary">
      <div class="summary__row"><div>商品代金</div><div>¥ {{ number_format($item->price) }}</div></div>
      <div class="summary__row"><div>支払い方法</div>
        <div>
          @php $pm = old('payment_method'); @endphp
          <span>{{ ['convenience_store'=>'コンビニ払い','credit_card'=>'カード払い','bank_transfer'=>'銀行振込'][$pm] ?? '—' }}</span>
        </div>
      </div>
    </div>
    <button class="btn btn--primary full mt-md" type="submit">購入する</button>
  </div>
</form>
@endsection
