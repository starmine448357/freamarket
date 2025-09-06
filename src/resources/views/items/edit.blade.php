@extends('layouts.app')
@section('title','商品を編集')
@section('page_css')
<link rel="stylesheet" href="{{ asset('css/items.css') }}">
@endsection

@section('content')
<h1 class="page-title center">商品を編集</h1>

<form class="form form--narrow" method="POST" action="{{ route('items.update',$item) }}" enctype="multipart/form-data" novalidate>
  @csrf
  @method('PUT')

  {{-- 一時ファイル名保持（差し替え時に使用） --}}
  <input type="hidden" name="temp_image" id="temp_image" value="{{ old('temp_image', session('temp_image')) }}">

  {{-- 画像の変更（選択→即時プレビュー→一時保存） --}}
  <div class="section">
    <div class="label">画像を変更</div>
    <label class="btn btn--outline btn--small mt-xs" for="image">画像を選択する</label>
    <input class="hidden" id="image" type="file" name="image" accept="image/png,image/jpeg">

    {{-- プレビュー出力先 --}}
    <output id="list" class="image_output"></output>

    @error('temp_image')<div class="error">{{ $message }}</div>@enderror
    @error('image')<div class="error">{{ $message }}</div>@enderror
  </div>

  <div class="section">
    <div class="label label--bold mb-xs">商品名と説明</div>

    <div class="field">
      <label class="label">商品名</label>
      <input class="input" type="text" name="title" value="{{ old('title', $item->title) }}">
      @error('title')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label class="label">ブランド名</label>
      <input class="input" type="text" name="brand" value="{{ old('brand', $item->brand) }}">
      @error('brand')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label class="label">商品の説明</label>
      <textarea class="textarea" name="description" rows="5">{{ old('description', $item->description) }}</textarea>
      @error('description')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="row">
      <div class="field">
        <label class="label">販売価格</label>
        <input class="input" type="number" name="price" value="{{ old('price', $item->price) }}">
        @error('price')<div class="error">{{ $message }}</div>@enderror
      </div>
      <div class="field">
        <label class="label">状態</label>
        <select class="select" name="condition">
          @php $cond = old('condition', $item->condition); @endphp
          <option value="new"       @selected($cond==='new')>新品</option>
          <option value="like_new"  @selected($cond==='like_new')>未使用に近い</option>
          <option value="used"      @selected($cond==='used')>中古</option>
          <option value="bad"       @selected($cond==='bad')>状態が悪い</option>
        </select>
        @error('condition')<div class="error">{{ $message }}</div>@enderror
      </div>
    </div>
  </div>

  <div class="section">
    <div class="label label--bold">カテゴリ</div>
    <div class="chips">
      @php $oldCats = collect(old('categories', $selected)); @endphp
      @foreach($categories as $cat)
        <label class="chip chip--select">
          <input class="checkbox" type="checkbox" name="categories[]" value="{{ $cat->id }}" @checked($oldCats->contains($cat->id))>
          <span class="chip__text">{{ $cat->name }}</span>
        </label>
      @endforeach
    </div>
    @if($errors->has('categories') || $errors->has('categories.*'))
      <div class="error">{{ $errors->first('categories') ?? $errors->first('categories.*') }}</div>
    @endif
  </div>

  {{-- 販売ステータス --}}
  <div class="section">
    <div class="label">販売ステータス</div>
    @php $status = old('status', $item->status); @endphp
    <select class="select" name="status">
      <option value="selling" @selected($status==='selling')>販売中</option>
      <option value="sold"    @selected($status==='sold')>売却済み</option>
    </select>
    @error('status')<div class="error">{{ $message }}</div>@enderror
  </div>

  <button class="btn btn--primary full mt-md" type="submit">更新する</button>
</form>
@endsection

@push('scripts')
<script>
(function () {
  const fileInput = document.getElementById('image');
  let   list      = document.getElementById('list');
  const hidden    = document.getElementById('temp_image');

  // 無ければ自動生成（保険）
  if (!list && fileInput) {
    const section = fileInput.closest('.section');
    list = document.createElement('output');
    list.id = 'list';
    list.className = 'image_output';
    section?.appendChild(list);
  }

  function csrfToken() {
    const m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function clearPreview() { if (list) list.innerHTML = ''; }

  function addLocalPreview(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      clearPreview();
      const div = document.createElement('div');
      div.className = 'reader_file';
      div.innerHTML = '<img class="reader_image" src="' + e.target.result + '" alt="プレビュー画像">';
      list.appendChild(div);
    };
    reader.readAsDataURL(file);
  }

  async function uploadTemp(file) {
    const formData = new FormData();
    formData.append('image', file);

    const res = await fetch('{{ route('items.image.temp') }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
      body: formData,
      redirect: 'follow',
      credentials: 'same-origin'
    });

    const ct = res.headers.get('content-type') || '';
    let data = null;
    if (res.ok && ct.includes('application/json')) data = await res.json();
    return { ok: res.ok, status: res.status, data, ct };
  }

  fileInput?.addEventListener('change', async (e) => {
    const file = e.target.files?.[0];
    if (!file) return;

    // ① ローカル即時プレビュー
    addLocalPreview(file);

    // ② 一時アップロード（プレビューは差し替えない）
    try {
      const r = await uploadTemp(file);
      if (r.ok && r.data?.filename) {
        hidden.value = r.data.filename; // ← これだけ
        return; // プレビューはローカルのまま
      }
      hidden.value = '';
      console.warn('[temp upload failed or invalid JSON]', r);
    } catch (err) {
      console.error(err);
      hidden.value = '';
    }
  });
})();
</script>
@endpush
