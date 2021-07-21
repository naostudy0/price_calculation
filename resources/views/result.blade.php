@extends('extends.layout')


@section('title', '料金計算結果')


@section('body')
<main>
    <h1>料金計算結果</h1>

    <p>入店日時：{{ $enter_datetime }}</p>
    <p>退店日時：{{ $leave_datetime }}</p>
    <p>コースの種類：{{ $usage_time['text'] }}</p>
    <p>コース終了日時：{{ $usage_time['limit'] }}</p>
    <p>利用時間：{{ $usage_time['stay'] }}</p>

    @if(isset($usage_time['extension']))
    <p>延長時間合計：
        {{ $usage_time['extension']['day']['total'] }}日 
        {{ $usage_time['extension']['hour']['total'] }}時間 
        {{ $usage_time['extension']['minute']['total'] }}時間 
        ({{ $usage_time['extension']['total'] }}分)
    </p>
    <p>(通常延長時間)：
        {{ $usage_time['extension']['day']['normal'] }}日 
        {{ $usage_time['extension']['hour']['normal'] }}時間 
        {{ $usage_time['extension']['minute']['normal'] }}時間 
        ({{ $usage_time['extension']['normal'] }}分)
    </p>
    <p>(割増延長時間)：
        {{ $usage_time['extension']['day']['extra'] }}日 
        {{ $usage_time['extension']['hour']['extra'] }}時間 
        {{ $usage_time['extension']['minute']['extra'] }}時間 
        ({{ $usage_time['extension']['extra'] }}分)
    </p>
    @endif

    <table>
        <thead>
            <tr>
                <th></th><th>税抜金額</th><th>税込金額</th><th>消費税</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>コース料金</th>
                <td>{{ $price['course']['non_taxed'] }}円</td>
                <td>{{ $price['course']['taxed'] }}円</td>
                <td>{{ $price['course']['tax'] }}円</td>
            </tr>

            @if(isset($price['extension']['non_taxed']))
            <tr>
                <th>延長料金</th>
                <td>{{ $price['extension']['non_taxed']['total'] }}円</td>
                <td>{{ $price['extension']['taxed']['total'] }}円</td>
                <td>{{ $price['extension']['tax']['total'] }}円</td>
            </tr>
            <tr>
                <th>（通常延長料金）</th>
                <td>{{ $price['extension']['non_taxed']['normal'] }}円</td>
                <td>{{ $price['extension']['taxed']['normal'] }}円</td>
                <td>{{ $price['extension']['tax']['normal'] }}円</td>
            </tr>
            <tr>
                <th>（割増延長料金）</th>
                <td>{{ $price['extension']['non_taxed']['extra'] }}円</td>
                <td>{{ $price['extension']['taxed']['extra'] }}円</td>
                <td>{{ $price['extension']['tax']['extra'] }}円</td>
            </tr>
            <tr>
                <th>合計</th>
                <td>{{ $price['total']['non_taxed'] }}円</td>
                <td>{{ $price['total']['taxed'] }}円</td>
                <td>{{ $price['total']['tax'] }}円</td>
            </tr>
            @endif
        </tbody>
    </table>
    <p>※延長料金に小数が発生する場合があります。</p>
</main>
@endsection