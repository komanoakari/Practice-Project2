@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/corrections.css') }}">
@endsection

@section('content')
<h1 class="heading">申請一覧</h1>

<div class="contents">
    <nav class="tabs">
        <a href="{{ route('correction.index', ['tab' => 'pending-approval']) }}" class="tab {{ $tab === 'pending-approval' ? 'active' : '' }}">承認待ち</a>
        <a href="{{ route('correction.index', ['tab' => 'approved']) }}" class="tab {{ $tab === 'approved' ? 'active' : '' }}">承認済み</a>
    </nav>

    @if ($tab === 'pending-approval')
        <div class="panel-listed">
            <table class="table">
                <tr class="table-row">
                    <th class="label">状態</th>
                    <th class="label">名前</th>
                    <th class="label">対象日時</th>
                    <th class="label">申請理由</th>
                    <th class="label">申請日時</th>
                    <th class="label">詳細</th>
                </tr>
                @foreach($pendingCorrections as $correction)
                <tr class="table-row">
                    <td class="data">{{ $correction->status }}</td>
                    <td class="data">{{ $correction->attendance->user->name }}</td>
                    <td class="data">{{ \Carbon\Carbon::parse($correction->attendance->date)->format('Y/m/d') }}</td>
                    <td class="data">{{ $correction->remarks }}</td>
                    <td class="data">{{ \Carbon\Carbon::parse($correction->applied_at)->format('Y/m/d') }}</td>
                    <td class="data">
                        <a href="{{ route('corrections.show', ['id' => $correction->attendance_id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>

    @elseif ($tab === 'approved')
        <div class="panel-listed">
            <table class="table">
                <tr class="table-row">
                    <th class="label">状態</th>
                    <th class="label">名前</th>
                    <th class="label">対象日時</th>
                    <th class="label">申請理由</th>
                    <th class="label">申請日時</th>
                    <th class="label">詳細</th>
                </tr>
                @foreach($approvedCorrections as $correction)
                <tr class="table-row">
                    <td class="data">{{ $correction->status }}</td>
                    <td class="data">{{ $correction->attendance->user->name }}</td>
                    <td class="data">{{ \Carbon\Carbon::parse($correction->attendance->date)->format('Y/m/d') }}</td>
                    <td class="data">{{ $correction->remarks }}</td>
                    <td class="data">{{ \Carbon\Carbon::parse($correction->applied_at)->format('Y/m/d') }}</td>
                    <td class="data">
                        <a href="{{ route('corrections.show', ['id' => $correction->attendance_id, 'correction_id' => $correction->id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    @endif
</div>
@endsection