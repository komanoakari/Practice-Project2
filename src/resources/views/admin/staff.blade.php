@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff.css') }}">
@endsection

@section('content')
<h1 class="heading">スタッフ一覧</h1>

    <div class="contents">
        <table class="table">
            <tr class="table-row">
                <th class="label">名前</th>
                <td class="data">メールアドレス</td>
                <td>月次勤怠</td>
            </tr>

            @foreach($users as $user)
            <tr class="table-row">
                <td class="data">{{ $user->name }}</td>
                <td class="data">{{ $user->email }}</td>
                <td class="data">
                    <a href="{{ route('staff.monthly', ['id' => $user->id]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
@endsection