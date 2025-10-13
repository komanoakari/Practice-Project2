@extends('layouts.header-admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/correction-detail.css') }}">
@endsection

@section('content')
<h1 class="heading">勤怠詳細</h1>

    <div class="contents">
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

                @foreach($correctionRests as $correctionRest)
                <tr class="table-row">
                    <th class="label">休憩{{ $loop->iteration }}</th>
                    <td class="data">{{ \Carbon\Carbon::parse($correctionRest->start_time)->format('H:i') }}</td>
                    <td class="data-separator">〜</td>
                    <td class="data">{{ \Carbon\Carbon::parse($correctionRest->end_time)->format('H:i') }}</td>
                    <td></td>
                </tr>
                @endforeach

                <tr class="table-row">
                    <th class="label">備考</th>
                    <td class="data" colspan="3">{{ $correction->remarks ?? '' }}</td>
                </tr>
            </table>

            @if($correction && $correction->status === '承認待ち')
                <button class="submit-btn" type="submit">承認</button>
            @elseif($correction && $correction->status === '承認済み')
                <div class="approved-sign">承認済み</div>
            @endif
        </div>
    </form>
@endsection