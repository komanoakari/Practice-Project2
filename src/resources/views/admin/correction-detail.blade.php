@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="contents">
    <h1 class="heading">勤怠詳細</h1>
    @if(session('success'))
    <p class="success-message">{{ session('success') }}</p>
    @endif

    <form action="{{ route('corrections.approve', ['id' => $attendance->id]) }}" method="POST" class="form">
        @csrf

        <table class="table">
            <tr class="table-row">
                <th class="label">名前</th>
                <td class="data">{{ $attendance->user->name }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            <tr class="table-row">
                <th class="label">日付</th>
                <td class="data">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</td>
                <td></td>
                <td class="data">{{ \Carbon\Carbon::parse($attendance->date)->format('m月d日') }}</td>
                <td></td>
            </tr>

            <tr class="table-row">
                <th class="label">出勤・退勤</th>
                <td class="data">{{ \Carbon\Carbon::parse($correction->start_time)->format('H:i') }}</td>
                <td class="data-separator">〜</td>
                <td class="data">{{ \Carbon\Carbon::parse($correction->end_time)->format('H:i') }}</td>
                <td></td>
            </tr>

            @foreach($restCorrections as $restCorrection)
            <tr class="table-row">
                <th class="label">
                    @if($loop->first)
                        休憩
                    @else
                        休憩{{ $loop->iteration }}
                    @endif
                </th>
                <td class="data">{{ \Carbon\Carbon::parse($restCorrection->start_time)->format('H:i') }}</td>
                <td class="data-separator">〜</td>
                <td class="data">{{ \Carbon\Carbon::parse($restCorrection->end_time)->format('H:i') }}</td>
                <td></td>
            </tr>
            @endforeach

            <tr class="table-row remarks-row">
                <th class="label">備考</th>
                <td class="remarks-data" colspan="3">{{ $correction->remarks ?? '' }}</td>
            </tr>
        </table>

        @if($correction && $correction->status === '承認待ち')
            <button class="submit-btn" type="submit">承認</button>
        @elseif($correction && $correction->status === '承認済み')
            <div class="approved-sign">承認済み</div>
        @endif
    </form>
</div>
@endsection