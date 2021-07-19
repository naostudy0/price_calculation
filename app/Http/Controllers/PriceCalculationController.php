<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\PriceCalculation\CalcPrice;

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
    public function showResult(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(),[
            'enter-time' => 'required | date',
            'leave-time' => 'required | date | after_or_equal:enter-time',
            'course' => 'required | numeric | regex:/^[0-3]+$/',
        ]);

        // 誤りがある場合はリダイレクト
        if($validator->fails()) {
            return redirect('/');
        }

        // 入店日時のDateTimeImmutableクラス作成
        $enter_time = $request['enter-time'];
        $enter_datetime_immutable = new \DateTimeImmutable($enter_time);

        // 退店日時のDateTimeImmutableクラス作成
        $leave_time = $request['leave-time'];
        $leave_datetime_immutable = new \DateTimeImmutable($leave_time);

        // コースの種別
        $course_number = $request['course'];


        // 料金計算クラスをインスタンス化
        $calc_price = new CalcPrice($course_number);
        
        // 料金を取得
        $price = $calc_price->calcPrice($enter_datetime_immutable, $leave_datetime_immutable);
       
        // 画面表示用のデータを作成
        $view_data = $calc_price->calcViewTime();

        // 滞在時間（画面表示用）
        $stay_time = $enter_datetime_immutable->diff($leave_datetime_immutable); 


        return view('result', [
            'enter_datetime_immutable' => $enter_datetime_immutable,
            'leave_datetime_immutable' => $leave_datetime_immutable,
            'price' => $price,
            'view_data' => $view_data,
            'stay_time' => $stay_time,
        ]);
    }
}
