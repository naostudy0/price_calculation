<?php

namespace App\Models;

use Illuminate\Support\Facades\Config;

class Price
{
    /**
     * 選択されたコースを設定
     * 
     * @param int
     */
    public function __construct($course_number)
    {
        // configから各コースの情報を取得
        $config_course = Config::get('price.course');
        // 選択されたコースの情報
        $this->course = $config_course[$course_number];
    }

    /**
     * 料金を計算し、税抜金額、税込金額、消費税を返す
     * 
     * @param object $enter_datetime_immutable
     * @param object $leave_datetime_immutable
     * 
     * @return array
     */
    public function calcPrice($enter_datetime_immutable, $leave_datetime_immutable)
    {
        // コース料金を取得（税抜・税込・消費税）
        $course_price = $this->calcCoursePrice();

        // コース終了日時を取得時に使用する、modifyメソッドの引数を取得
        $time_limit = $this->course['time_limit'];
        // コース終了日時を取得
        $this->course_limit_datetime_immutable = $enter_datetime_immutable->modify($time_limit);
        
        // 退店日時がコース終了日時を超えていなければ、コース料金（税抜金額、税込金額、消費税）を返す
        if ($leave_datetime_immutable <= $this->course_limit_datetime_immutable) {
            return ['course' => $course_price];
        }

        // 延長料金を取得（税抜・税込・消費税）
        $extension_price = $this->calcExtensionPrice($leave_datetime_immutable);

        // 合計金額を取得（税抜・税込・消費税）
        $total_price = $this->calcTotalPrice($course_price, $extension_price);;

        // コース料金、延長料金、合計金額それぞれの税抜金額、税込金額、消費税を返す
        return ['course' => $course_price, 'extension' => $extension_price, 'total' => $total_price];
    }

    /**
     * コース料金計算
     * 
     * @return array
     */
    private function calcCoursePrice()
    {
        // コース料金（税抜）
        $course_price['non_taxed'] = $this->course['price'];
        // コース料金（税込）
        $course_price['taxed'] = $this->getTaxedPrice($course_price['non_taxed']);
        // コース料金（消費税のみ）
        $course_price['tax'] = $course_price['taxed'] - $course_price['non_taxed'];

        return $course_price;
    }

    /**
     * 消費税込金額を計算する
     * 
     * @param int $non_taxed_price
     * @return int $taxed_price
     */
    private function getTaxedPrice($non_taxed_price)
    {
        // configから消費税情報を取得
        $config_tax = Config::get('price.tax');
        // 消費税込金額の計算
        $taxed_price = $non_taxed_price * $config_tax['rate'];

        return $taxed_price;
    }

    /**
     * コース終了日時から退店日時まで、10分間隔の日時を作成
     *  
     * @param object
     * @return object
     */
    private function getDatePeriod($leave_datetime_immutable)
    {
        // 10分間隔を設定
        $date_interval = new \DateInterval('PT10M');

        // コース終了日時から退店日時まで、10分間隔の日時を作成
        $date_period = new \DatePeriod($this->course_limit_datetime_immutable, $date_interval, $leave_datetime_immutable);

        return $date_period;
    }

    /**
     * 通常料金・割増料金それぞれ、10分延長の回数をカウント
     * 
     * @param object
     * @return array
     */
    private function getExtensionCount($date_period)
    {
        $extension_count = [
            'normal' => 0,
            'extra' => 0,
        ];
        // 作成した10分間隔の日時が深夜割増の時間かどうかを判定し、カウントする
        foreach($date_period as $date_extension) {
      
            // 21:50:01〜23:59:59 または 0:00:00〜4:59:59に開始された10分延長の場合は、割増料金の延長回数を+1
            $gis = $date_extension->format('Gis');
            if ($gis >= 215001 || $gis <= 45959) {
                $extension_count['extra']++;
          
            // その他の時間は通常延長料金の回数を+1
            } else {
                $extension_count['normal']++;
            }
        }
        return $extension_count;
    }

    /**
     * 延長料金計算を計算する
     * 
     * @param object
     * @return array
     */
    private function calcExtensionPrice($leave_datetime_immutable)
    {
        // コース終了日時から退店日時までに延長開始した日時（10分間隔）を取得
        $date_period = $this->getDatePeriod($leave_datetime_immutable);

        // 通常料金・割増料金それぞれ、10分延長の回数をカウント
        $this->extension_count = $this->getExtensionCount($date_period);

        // configから延長料金情報を取得
        $config_extension = Config::get('price.extension');
        // 延長10分ごとの金額
        $base_price = $config_extension['base_price'];
        // 深夜割増割合
        $premium_rate = $config_extension['premium_rate'];
        
        // 深夜割増料金の延長料金（税抜）
        $extension_price['non_taxed']['extra'] = $this->extension_count['extra'] * $base_price * $premium_rate;
        // 通常料金の延長料金（税抜）
        $extension_price['non_taxed']['normal'] = $this->extension_count['normal'] * $base_price;
        // 延長料金合計（税抜）
        $extension_price['non_taxed']['total'] = $extension_price['non_taxed']['extra'] + $extension_price['non_taxed']['normal'];
        
        foreach($extension_price['non_taxed'] as $key => $value ){
            // 税込金額を計算
            $extension_price['taxed'][$key] = $this->getTaxedPrice($value);

            // 消費税を計算
            $extension_price['tax'][$key] = $extension_price['taxed'][$key] - $value;
        }
        
        return $extension_price;
    }

    /**
     * コース料金と延長料金の合計を計算する
     * 
     * @param array $course_price
     * @param array $extension_price
     * 
     * @return array
     */
    private function calcTotalPrice($course_price, $extension_price)
    {
        // コース料金と延長料金の合計金額（税抜）
        $price['non_taxed'] = $course_price['non_taxed'] + $extension_price['non_taxed']['total'];
        // コース料金と延長料金の合計金額（税込）
        $price['taxed'] = $course_price['taxed'] + $extension_price['taxed']['total'];
        // 消費税の合計金額
        $price['tax'] = $course_price['tax'] + $extension_price['tax']['total'];

        return $price;
    }

    /**
     * 結果画面表示用のデータを返す
     * 
     * @return array
     */
    public function calcViewTime()
    {
        // コース名
        $view_data['text'] = $this->course['text'];
        // コース終了日時
        $view_data['limit'] = $this->course_limit_datetime_immutable;

        // 延長回数が設定されていなければ、コース情報のみを返す
        if(!isset($this->extension_count)){
            return $view_data;
        }

        // 通常料金の延長時間（分）
        $view_data['extension']['normal'] = $this->extension_count['normal'] * 10;
        // 割増料金の延長時間（分）
        $view_data['extension']['extra'] = $this->extension_count['extra'] * 10;
        // トータルの延長時間（分）
        $view_data['extension']['total'] = $view_data['extension']['normal'] + $view_data['extension']['extra'];

        // 延長時間を分から日時分に変換
        foreach($view_data['extension'] as $key => $value){
            // 日
            $view_data['extension']['day'][$key] = floor($value / 1440);
            // 時
            $view_data['extension']['hour'][$key] = floor(($value - $view_data['extension']['day'][$key] *1440) / 60);
            // 分
            $view_data['extension']['minute'][$key] = $value % 60;
        }
        return $view_data;
    }
}