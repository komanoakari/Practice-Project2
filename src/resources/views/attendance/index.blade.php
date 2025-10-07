@extends('layouts.header-user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<h1 class="index-heading">勤怠一覧</h1>

<div class="index-contents">
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
            <td class="index-data">{{ $attendance->date }}</td>
            <td class="index-data">{{ $attendance->start_time }}</td>
            <td class="index-data">{{ $attendance->end_time }}</td>
            <td class="index-data">{{ $attendance->break_time }}</td>
            <td class="index-data">{{ $attendance->total_time }}</td>
            <td class="index-data">
                <a href="#{{ $attendance->id }}" class="index-detail-btn">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>

@endsection