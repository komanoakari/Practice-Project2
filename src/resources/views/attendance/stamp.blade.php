@extends('layouts.header-user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
    <div class="working-status">{{ $user-> }}</div>

    <div class="stamp-today">
        {{ $today->format('Y年m月d日')}}({{ ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek] }})
    </div>
    <div class="stamp-time">
        {{ $today->format('H:i') }}
    </div>
    <form action="" method="POST" class="attendance-stamp-btn">
        @csrf
            @if( $status === '勤務外')
            <button class="working-btn">出勤</button>
            @elseif ( $status === '出勤中')
            <div class="off-duty-btn">
                <button class="clocked-out-btn">退勤</button>
                <button class="On-break-btn">休憩入</button>
            </div>
            @else ( $status === '休憩中')
            <button class="out-break-btn">休憩戻</button>
    </form>
@endsection