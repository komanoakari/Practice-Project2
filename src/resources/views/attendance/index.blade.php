@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="contents">
    <h1 class="heading">勤怠一覧</h1>
    @error('detail')
        <div class="error-message">
            <p class="error-text">{{ $message }}</p>
        </div>
    @enderror

    <div class="calender">
        <a href="{{ route('attendance.index', ['date' => $date->copy()->subMonth()->format('Y-m')]) }}" class="previous-month">先月</a>
        <div class="current-group">
            <img src="{{ asset('images/calender-icon.png') }}" alt="カレンダーアイコン" class="calender-icon">
            <div class="current-month">{{ $date->format('Y/m') }}</div>
        </div>
        <a href="{{ route('attendance.index', ['date' => $date->copy()->addMonth()->format('Y-m')]) }}" class="next-month">翌月</a>
    </div>

    <table class="table">
        <tr class="table-row">
            <th class="label">日付</th>
            <th class="label">出勤</th>
            <th class="label">退勤</th>
            <th class="label">休憩</th>
            <th class="label">合計</th>
            <th class="label">詳細</th>
        </tr>
        @foreach($attendances as $attendance)
            <tr class="table-row">
                <td class="data">{{ \Carbon\Carbon::parse($attendance->date)->format('m/d') }}({{ ['日', '月', '火', '水', '木', '金', '土'][\Carbon\Carbon::parse($attendance->date)->dayOfWeek] }})</td>
                <td class="data">{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : ''}}</td>
                <td class="data">{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : ''}}</td>
                <td class="data">{{ $attendance->break_time }}</td>
                <td class="data">{{ $attendance->total_time }}</td>
                <td class="data">
                    @if($attendance->id)
                        <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                    @else
                        <a href="{{ route('attendance.detail', ['id' => 0]) }}" class="detail-link">詳細</a>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection