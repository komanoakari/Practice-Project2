@extends('layouts.header-admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff-monthly.css') }}">
@endsection

@section('content')
<h1 class="heading">{{ $user->name }}さんの勤怠</h1>

<div class="contents">
    @error('detail')
    <div class="error-message">
        <p class="error-text">{{ $message }}</p>
    </div>
    @enderror

    <div class="calender-container">
        <a href="{{ route('staff.monthly', ['id' => $user->id, 'date' => $date->copy()->subMonth()->format('Y-m')]) }}" class="month-pagination">先月</a>
        <img src="{{ asset('images/calender-icon.png') }}" alt="カレンダーアイコン" class="calender-icon">
        <div class="this-month">{{ $date->format('Y/m') }}</div>
        <a href="{{ route('staff.monthly', ['id' => $user->id, 'date' => $date->copy()->addMonth()->format('Y-m')]) }}" class="month-pagination">翌月</a>
    </div>

    <form action="{{ route('export.monthly', ['id' => $user->id]) }}" method="get" class="export-form">
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
                        <a href="{{ route('admin.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                    @else
                        <a href="{{ route('admin.detail', ['id' => 0]) }}" class="detail-link">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>

        <button class="export-btn" type="submit">CSV出力</button>
        <input type="hidden" name="date" value="{{ $date->format('Y-m') }}">
    </form>
</div>

@endsection