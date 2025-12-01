@component('mail::message')
# 取引が完了しました

商品「{{ $purchase->item->title }}」の取引が完了しました。

@component('mail::button', ['url' => url('/transaction/'.$purchase->id)])
取引チャットを確認する
@endcomponent

@endcomponent