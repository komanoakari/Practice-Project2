@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/index.css') }}">
@endsection

@section('content')
<div class="contents">
    <h1 class="heading">{{ $date->format('Y/m/d') }}の一覧</h1>
    @if(session('success'))
        <p class="success-message">{{ session('success') }}</p>
    @endif

    <div class="calender">
        <a href="{{ route('admin.index', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}" class="previous-day">前日</a>
        <div class="current-group">
            <img src="{{ asset('images/calender-icon.png') }}" alt="カレンダーアイコン" class="calender-icon">
            <div class="current-day">{{ $date->format('Y/m/d') }}</div>
        </div>
        <a href="{{ route('admin.index', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}" class="next-day">翌日</a>
    </div>

    <table class="table">
        <tr class="table-row">
            <th class="label">名前</th>
            <th class="label">出勤</th>
            <th class="label">退勤</th>
            <th class="label">休憩</th>
            <th class="label">合計</th>
            <th class="label">詳細</th>
        </tr>
        @foreach($attendances as $attendance)
            <tr class="table-row">
                <td class="data">{{ $attendance->user->name }}</td>
                <td class="data">{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : ''}}</td>
                <td class="data">{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : ''}}</td>
                <td class="data">{{ $attendance->break_time }}</td>
                <td class="data">{{ $attendance->total_time }}</td>
                <td class="data">
                    <a href="{{ route('admin.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection