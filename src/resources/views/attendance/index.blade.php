@extends('layouts.header-user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<h1 class="index-heading">勤怠一覧</h1>

<div class="index-contents">
    <div class="calender-container">
        <a href="{{ route('attendance.index', ['date' => $date->copy()->subMonth()->format('Y-m')]) }}" class="last-month-pagination">先月</a>
        <img src="{{ asset('images/calender-icon.png') }}" alt="カレンダーアイコン" class="calender-icon">
        <div class="this-month">{{ $date->format('Y/m') }}</div>
        <a href="{{ route('attendance.index', ['date' => $date->copy()->addMonth()->format('Y-m')]) }}" class="next-month-pagination">翌月</a>
    </div>

    <table class="index-table">
        <tr class="index-table-row">
            <th class="index-table-label">日付</th>
            <th class="index-table-label">出勤</th>
            <th class="index-table-label">退勤</th>
            <th class="index-table-label">休憩</th>
            <th class="index-table-label">合計</th>
            <th class="index-table-label">詳細</th>
        </tr>
        @foreach($attendances as $attendance)
        <tr class="index-table-row">
            <td class="index-data">{{ \Carbon\Carbon::parse($attendance->date)->format('m/d') }}({{ ['日', '月', '火', '水', '木', '金', '土'][\Carbon\Carbon::parse($attendance->date)->dayOfWeek] }})</td>
            <td class="index-data">{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : ''}}</td>
            <td class="index-data">{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : ''}}</td>
            <td class="index-data">{{ $attendance->break_time ?: '' }}</td>
            <td class="index-data">{{ $attendance->total_time ?: '' }}</td>
            <td class="index-data">
                <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="index-detail-btn">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>

@endsection