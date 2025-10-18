@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp.css') }}">
@endsection

@section('content')
<div class="contents">
    <div class="status">{{ $status }}</div>

    <div class="date">
        {{ $today->format('Y年m月d日')}}({{ ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek] }})
    </div>
    <div class="time">
        {{ $today->format('H:i') }}
    </div>

    @if($status === '勤務外')
        <form action="{{ route('attendance.clock-in') }}" method="POST" class="form">
            @csrf
            <button class="btn-clockin">出勤</button>
        </form>
    @endif

    @if($status === '出勤中')
        <div class="btns-group">
            <form action="{{ route('attendance.clock-out') }}" method="POST" class="form">
                @csrf
                <button class="btn-clockout">退勤</button>
            </form>
            <form action="{{ route('attendance.break-in') }}" method="POST" class="form">
                @csrf
                <button class="btn-breakin">休憩入</button>
            </form>
        </div>
    @endif

    @if($status === '休憩中')
        <form action="{{ route('attendance.break-out') }}" method="POST" class="form">
            @csrf
            <button class="btn-breakout">休憩戻</button>
        </form>
    @endif

    @if($status === '退勤済')
        <div class="message">お疲れ様でした。</div>
    @endif
</div>
@endsection