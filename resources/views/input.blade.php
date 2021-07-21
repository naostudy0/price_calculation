@extends('extends.layout')


@section('title', '料金計算')


@section('body')
<main>
    <h1>料金計算</h1>
    <form method="POST" action="{{ route('price_calculation.result') }}">
        @csrf
        <p><label>入店日時<input type="datetime-local" step="1" name="enter-time"></label></p>
        @if ($errors->has('enter-time'))
        <p class="caution">{{$errors->first('enter-time')}}</p>
        @endif
        
        <p><label>退店日時<input type="datetime-local" step="1" name="leave-time"></label><p>
        @if ($errors->has('leave-time'))
        <p class="caution">{{$errors->first('leave-time')}}</p>
        @endif

        <p>
            <label>コースの種類
                <select name="course">
                    @foreach($course_name as $number => $course)
                    <option value="{{ $number }}">{{ $course['text'] }}</option>
                    @endforeach
                </select>
            </label>
        </p>
        @if ($errors->has('course'))
        <p class="caution">{{$errors->first('course')}}</p>
        @endif

        <button type="submit">送信</button>
    </form>
</main>
@endsection