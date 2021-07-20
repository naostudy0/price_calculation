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
        // コース料金（税抜）
        $non_taxed_price['course'] = $this->course['price'];
        // コース料金（税込）
        $taxed_price['course'] = $this->getTaxedPrice($non_taxed_price['course']);
        // コース料金（消費税のみ）
        $tax['course'] = $taxed_price['course'] - $non_taxed_price['course'];

        // コース終了日時を取得時に使用する、modifyメソッドの引数を取得
        $time_limit = $this->course['time_limit'];
        // コース終了日時を取得
        $this->course_limit_datetime_immutable = $enter_datetime_immutable->modify($time_limit);

        // 退店日時がコース終了日時を超えていなければ料金を返す
        if ($leave_datetime_immutable <= $this->course_limit_datetime_immutable) {
            // 税抜金額、税込金額、消費税
            return ['non_taxed' => $non_taxed_price, 'taxed' => $taxed_price, 'tax' => $tax];
        }

        // コース終了日時から退店日時までに延長開始した日時（10分間隔）を取得
        $date_period = $this->getDatePeriod($leave_datetime_immutable);

        // 通常料金・割増料金それぞれ、10分延長の回数をカウント
        $this->getExtensionCount($date_period);

        // configから延長料金情報を取得
        $config_extension = Config::get('price.extension');
        // 延長10分ごとの金額
        $base_price = $config_extension['base_price'];
        // 深夜割増割合
        $premium_rate = $config_extension['premium_rate'];

        // 深夜割増料金の延長料金（税抜）
        $non_taxed_price['extension']['extra_price'] = $this->extension_count['extra'] * $base_price * $premium_rate;
        // 通常料金の延長料金（税抜）
        $non_taxed_price['extension']['standard_price'] = $this->extension_count['normal'] * $base_price;
        // 延長料金合計（税抜）
        $non_taxed_price['extension']['total'] = $non_taxed_price['extension']['extra_price'] + $non_taxed_price['extension']['standard_price'];

        foreach($non_taxed_price['extension'] as $key => $value ){
            // 税込金額を計算
            $taxed_price['extension'][$key] = $this->getTaxedPrice($value);

            // 消費税を計算
            $tax['extension'][$key] = $taxed_price['extension'][$key] - $value;
        }

        // コース料金と延長料金の合計金額（税抜）
        $non_taxed_price['total'] = $non_taxed_price['course'] + $non_taxed_price['extension']['total'];
        // コース料金と延長料金の合計金額（税込）
        $taxed_price['total'] = $taxed_price['course'] + $taxed_price['extension']['total'];
        // 消費税の合計金額
        $tax['total'] = $tax['course'] + $tax['extension']['total'];

        // 税抜金額、税込金額、消費税を返す
        return ['non_taxed' => $non_taxed_price, 'taxed' => $taxed_price, 'tax' => $tax];
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
     */
    private function getExtensionCount($date_period)
    {
        $this->extension_count = [
            'normal' => 0,
            'extra' => 0,
        ];
        // 作成した10分間隔の日時が深夜割増の時間かどうかを判定し、カウントする
        foreach($date_period as $date_extension) {
      
            // 21:50:01〜23:59:59 または 0:00:00〜4:59:59に開始された10分延長の場合は、割増料金の延長回数を+1
            $gis = $date_extension->format('Gis');
            if ($gis >= 215001 || $gis <= 45959) {
                $this->extension_count['extra']++;
          
            // その他の時間は通常延長料金の回数を+1
            } else {
                $this->extension_count['normal']++;
            }
        }
        return;
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