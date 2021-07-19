@extends('extends.layout')


@section('title', '料金計算結果')


@section('body')
<main>
    <h1>料金計算結果</h1>

    <p>入店日時：{{ $enter_datetime_immutable->format('Y/m/d H:i:s') }}</p>
    <p>退店日時：{{ $leave_datetime_immutable->format('Y/m/d H:i:s') }}</p>
    <p>コースの種類：{{ $view_data['text'] }}</p>
    <p>コース終了日時：{{ $view_data['limit']->format('Y/m/d H:i:s') }}</p>
    <p>利用時間：{{ $stay_time->format('%a日 %h時間 %i分%s秒') }}</p>

    @if(isset($view_data['extension']['total']))
    <p>延長時間合計：
        {{ $view_data['extension']['day']['total'] }}日 
        {{ $view_data['extension']['hour']['total'] }}時間 
        {{ $view_data['extension']['minute']['total'] }}時間 
        ({{ $view_data['extension']['total'] }}分)
    </p>
    <p>(通常延長時間)：
        {{ $view_data['extension']['day']['normal'] }}日 
        {{ $view_data['extension']['hour']['normal'] }}時間 
        {{ $view_data['extension']['minute']['normal'] }}時間 
        ({{ $view_data['extension']['normal'] }}分)
    </p>
    <p>(割増延長時間)：
        {{ $view_data['extension']['day']['extra'] }}日 
        {{ $view_data['extension']['hour']['extra'] }}時間 
        {{ $view_data['extension']['minute']['extra'] }}時間 
        ({{ $view_data['extension']['extra'] }}分)
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
                <td>{{ $price['non_taxed']['course'] }}円</td>
                <td>{{ $price['taxed']['course'] }}円</td>
                <td>{{ $price['tax']['course'] }}円</td>
            </tr>

            @if(isset($price['non_taxed']['extension']))
            <tr>
                <th>延長料金</th>
                <td>{{ $price['non_taxed']['extension']['total'] }}円</td>
                <td>{{ $price['taxed']['extension']['total'] }}円</td>
                <td>{{ $price['tax']['extension']['total'] }}円</td>
            </tr>
            <tr>
                <th>（通常延長料金）</th>
                <td>({{ $price['non_taxed']['extension']['standard_price'] }}円)</td>
                <td>({{ $price['taxed']['extension']['standard_price'] }}円)</td>
                <td>({{ $price['tax']['extension']['standard_price'] }}円)</td>
            </tr>
            <tr>
                <th>（割増延長料金）</th>
                <td>({{ $price['non_taxed']['extension']['extra_price'] }}円)</td>
                <td>({{ $price['taxed']['extension']['extra_price'] }}円)</td>
                <td>({{ $price['tax']['extension']['extra_price'] }}円)</td>
            </tr>
            <tr>
                <th>合計</th>
                <td>{{ $price['non_taxed']['total'] }}円</td>
                <td>{{ $price['taxed']['total'] }}円</td>
                <td>{{ $price['tax']['total'] }}円</td>
            </tr>
            @endif
        </tbody>
    </table>
    <p>※延長料金に小数が発生する場合があります。</p>
</main>
@endsection