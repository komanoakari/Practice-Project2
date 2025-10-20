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

    <form action="{{ route('admin.update', ['id' => $attendance->id]) }}" method="POST" class="form">
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
                        <td class="data">
                            {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}
                            <input type="hidden" name="start_time" value="{{ $attendance->start_time }}">
                        </td>
                        <td class="data-separator">〜</td>
                        <td class="data">
                            {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}
                            <input type="hidden" name="end_time" value="{{ $attendance->end_time }}">
                        </td>
                        <td></td>
                    @else
                        <td class="data">
                            <input type="time" name="start_time" value="{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}">
                        </td>
                        <td class="data-separator">〜</td>
                        <td class="data">
                            <input type="time" name="end_time" value="{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : ''}}">
                        </td>
                        <td></td>
                    @endif
                </tr>

                @if($correction && $correction->status === '承認待ち')
                    @if($restCorrections->isEmpty())
                        <tr class="table-row">
                            <th class="label">休憩</th>
                            <td class="data"> - </td>
                            <td class="data-separator">〜</td>
                            <td class="data"> - </td>
                            <td></td>
                    @else
                        @foreach($restCorrections as $restCorrection)
                            <tr class="table-row">
                                <th class="label">
                                    @if($loop->first)
                                        休憩
                                    @else
                                        休憩{{ $loop->iteration }}
                                    @endif
                                </th>
                                <td class="data">
                                    {{ \Carbon\Carbon::parse($restCorrection->start_time)->format('H:i') }}
                                    <input type="hidden" name="break_starts[]" value="{{ $restCorrection->start_time }}">
                                </td>
                                <td class="data-separator">〜</td>
                                <td class="data">
                                    {{ \Carbon\Carbon::parse($restCorrection->end_time)->format('H:i') }}
                                    <input type="hidden" name="break_ends[]" value="{{ $restCorrection->end_time }}">
                                </td>
                                <td></td>
                            </tr>
                        @endforeach
                    @endif
                @else
                    @if($rests->isEmpty())
                        <tr class="table-row">
                            <th class="label">休憩</th>
                            <td class="data">
                                <input type="time" name="break_starts[]">
                            </td>
                            <td class="data-separator">〜</td>
                            <td class="data">
                                <input type="time" name="break_ends[]">
                            </td>
                            <td></td>
                    @else
                        @foreach($rests as $rest)
                            <tr class="table-row">
                                <th class="label">
                                    @if($loop->first)
                                        休憩
                                    @else
                                        休憩{{ $loop->iteration }}
                                    @endif
                                </th>
                                <td class="data">
                                    <input type="time" name="break_starts[]" value="{{ \Carbon\Carbon::parse($rest->start_time)->format('H:i') }}">
                                </td>
                                <td class="data-separator">〜</td>
                                <td class="data">
                                    <input type="time" name="break_ends[]" value="{{ $rest->end_time ? \Carbon\Carbon::parse($rest->end_time)->format('H:i') : '' }}">
                                </td>
                                <td></td>
                            </tr>
                        @endforeach

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
                @endif

                <tr class="table-row remarks-row">
                    <th class="label">備考</th>
                    @if($correction && $correction->status === '承認待ち')
                        <td class="remarks-data" colspan="3">
                            {{ $attendance->remarks }}
                            <input type="hidden" name="remarks" value="{{ $attendance->remarks }}">
                        </td>
                        <td></td>
                    @else
                        <td class="data" colspan="3">
                            <textarea name="remarks" id="remark-textarea" cols="20" rows="5">{{ old('remarks', $attendance->remarks ?? '') }}</textarea>
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
        </form>
    </div>
@endsection