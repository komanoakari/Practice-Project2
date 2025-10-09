@extends('layouts.header-user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<h1 class="heading">勤怠詳細</h1>

    <div class="contents">
        @if(session('success'))
        <p class="success-message">{{ session('success') }}</p>
        @endif

        @if($errors->has('start_time') || $errors->has('end_time'))
        <div class="error-message">
            <p class="error-text">{{ $errors->first('start_time') ?: $errors->first('end_time') }}</p>
        </div>
        @endif

        @error('break_error')
        <div class="error-message">
            <p class="error-text">{{ $message }}</p>
        </div>
        @enderror

        @error('remarks')
        <div class="error-message">
            <p class="error-text">{{ $message }}</p>
        </div>
        @enderror

        <form action="{{ route('attendance.update', ['id' => $attendance->id]) }}" method="POST" class="attendance-form">
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
                    @if($correction && $correction->status === '承認待ち')
                        <td class="data">{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}</td>
                        <td class="data-separator">〜</td>
                        <td class="data">{{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}</td>
                        <td></td>
                        <input type="hidden" name="start_time" value="{{ $attendance->start_time }}">
                        <input type="hidden" name="end_time" value="{{ $attendance->end_time }}">
                    @else
                        <td class="data">
                            <input type="time" name="start_time" value="{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}">
                        </td>
                        <td class="data-separator">〜</td>
                        <td class="data">
                            <input type="time" name="end_time" value="{{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}">
                        </td>
                        <td></td>
                    @endif
                </tr>

                @foreach($rests as $rest)
                <tr class="table-row">
                    <th class="label">休憩{{ $loop->iteration }}</th>
                    @if($correction && $correction->status === '承認待ち')
                        <td class="data">{{ \Carbon\Carbon::parse($rest->start_time)->format('H:i') }}</td>
                        <td class="data-separator">〜</td>
                        <td class="data">{{ \Carbon\Carbon::parse($rest->end_time)->format('H:i') }}</td>
                        <td></td>
                        <input type="hidden" name="break_starts[]" value="{{ $rest->start_time }}">
                        <input type="hidden" name="break_ends[]" value="{{ $rest->end_time }}">
                    @else
                        <td class="data">
                            <input type="time" name="break_starts[]" value="{{ \Carbon\Carbon::parse($rest->start_time)->format('H:i') }}">
                        </td>
                        <td class="data-separator">〜</td>
                        <td class="data">
                            <input type="time" name="break_ends[]" value="{{ \Carbon\Carbon::parse($rest->end_time)->format('H:i') }}">
                        </td>
                        <td></td>
                    @endif
                </tr>
                @endforeach

                @if($correction && $correction->status !== '承認待ち')
                <tr class="table-row">
                    <th class="label">休憩{{ $rests->count() + 1 }}</th>
                    <td class="data">
                        <input type="time" name="break_starts[]">
                    </td>
                    <td class="data-separator">〜</td>
                    <td class="data">
                        <input type="time" name="break_ends[]">
                    </td>
                    <td></td>
                </tr>
                @endif

                <tr class="table-row">
                    <th class="label">備考</th>
                    @if($correction && $correction->status === '承認待ち')
                        <td class="data" colspan="3">{{ $correction->remarks }}</td>
                        <input type="hidden" name="remarks" value="{{ $correction->remarks }}">
                        <td>
                            @error('remarks')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </td>
                    @else
                        <td class="data" colspan="3">
                            <textarea name="remarks" id="remark-textarea" cols="20" rows="5">{{ old('remarks', $correction->remarks ?? '') }}</textarea>
                        </td>
                        <td></td>
                    @endif
                </tr>
            </table>

            @if($correction && $correction->status === '承認待ち')
                <p class="pending-text"><span class="pending-mark">*</span>承認待ちのため修正はできません。</p>
            @else
                <button class="submit-btn" type="submit">修正</button>
            @endif
        </div>
    </form>
@endsection