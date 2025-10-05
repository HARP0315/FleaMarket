@extends('layouts/app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="item-list">
    {{-- フラッシュメッセージ（購入成功） --}}
    @if (session('success'))
        <div class="item-list__success-message--flash">
            {{ session('success') }}
        </div>
    @endif
    {{-- タブの切替え --}}
    <div class="item-list__tab">
        <a
         href="/?keyword={{ request('keyword') }}"
         class="item-list__tab-link
            {{ request()->query('tab') !== 'mylist' ? 'item-list__tab-link--active' : ''}}"
        >おすすめ</a>
        <a
         href="/?tab=mylist&keyword={{ request('keyword') }}"
         class="item-list__tab-link
            {{ request()->query('tab') === 'mylist' ? 'item-list__tab-link--active' : '' }}"
        >マイリスト</a>
    </div>
    {{-- 商品一覧 --}}
    <div class="item-list__content">
        @foreach($items as $item)
        <div class="item-list__item">
            <a href="/item/{{$item->id}}"
             class="item-list__item-link
             @if($item->purchase)
              --sold
             @endif"
             style="background-image: url({{ $item->image_url }});"
            >
                <div class="item-list__sold-box">
                    <span>SOLD</span>
                </div>
            </a>
            <div class="item-list__item-info">
            <p class=item-list__item-name>{{$item->name}}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
