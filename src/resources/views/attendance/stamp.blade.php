@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp.css') }}">
@endsection

@section('content')
    <div class="working-status">{{ $status }}</div>

    <div class="stamp-today">
        {{ $today->format('Y年m月d日')}}({{ ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek] }})
    </div>
    <div class="stamp-time">
        {{ $today->format('H:i') }}
    </div>

    @if($status === '勤務外')
        <form action="{{ route('attendance.clock-in') }}" method="POST" class="attendance-stamp-btn">
            @csrf
            <button class="clocked-in-btn">出勤</button>
        </form>
    @endif

    @if($status === '出勤中')
        <div class="on-duty-btns">
            <form action="{{ route('attendance.clock-out') }}" method="POST">
                @csrf
                <button class="clocked-out-btn">退勤</button>
            </form>
            <form action="{{ route('attendance.break-in') }}" method="POST">
                @csrf
                <button class="on-break-btn">休憩入</button>
            </form>
        </div>
    @endif

    @if($status === '休憩中')
        <form action="{{ route('attendance.break-out') }}" method="POST" class="attendance-stamp-btn">
            @csrf
            <button class="out-break-btn">休憩戻</button>
        </form>
    @endif

    @if($status === '退勤済')
        <div class="checked-out-messaged">お疲れ様でした。</div>
    @endif
@endsection