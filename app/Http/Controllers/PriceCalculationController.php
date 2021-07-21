<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Price;
use App\Http\Requests\InputPriceDataRequest;

class PriceCalculationController extends Controller
{
    /**
     * 入店日時・退店日時・コースの種別を入力する画面を表示する
     * 
     */
    public function showInput()
    {
        return view('input');
    }

    /**
     * 料金計算結果を表示する
     * 
     */
    public function showResult(InputPriceDataRequest $request)
    {
        // 入店日時のDateTimeImmutableクラス作成
        $enter_time = $request['enter-time'];
        $enter_datetime_immutable = new \DateTimeImmutable($enter_time);

        // 退店日時のDateTimeImmutableクラス作成
        $leave_time = $request['leave-time'];
        $leave_datetime_immutable = new \DateTimeImmutable($leave_time);

        // コースの種別
        $course_number = $request['course'];

        // 料金計算クラスをインスタンス化
        $price_model = new Price($course_number);
        // 料金を取得
        $price = $price_model->calcPrice($enter_datetime_immutable, $leave_datetime_immutable);
        // 画面表示用のデータを取得
        $usage_time = $price_model->getUsageTime($enter_datetime_immutable, $leave_datetime_immutable);

        return view('result', [
            'enter_datetime' => $enter_datetime_immutable->format('Y/m/d H:i:s'),
            'leave_datetime' => $leave_datetime_immutable->format('Y/m/d H:i:s'),
            'price' => $price,
            'usage_time' => $usage_time,
        ]);
    }
}
