<?php
namespace app\helpers;

use app\models\Rate;
use Yii;
use DateTime;

class RateHelper
{
    public static $config = [];

    /**
     * Запрос данных из конфигурационного файла /config/rate.php
     * @param string $param название параметра в конфигурационном файле
     * @return string|array значение параметра из конфигурационного файла
     */
    public static function config($param)
    {
        if (empty(self::$config)) {
            self::$config = require(Yii::getAlias('@app') . '/config/rate.php');
        }

        return self::$config[$param];
    }

    /**
     * Запрос к удаленному серверу с курсами валют
     * Все данные для запроса берутся из конфигурационного файла /config/rate.php
     * @uses RateHelper::config()
     * @return array|bool ассоативный массив с курсами валют или false в случае недостпности сервиса
     */
    public static function requestRate()
    {
        $rate = file_get_contents(self::config('RATE_SOURCE'));

        if (empty($rate) || strpos($rate, '<?xml') === false) {
            return false;
        }

        $result = [];
        $rateXml = simplexml_load_string($rate);
        $usd_rubArray = (array)$rateXml->xpath('//Valute[@ID="' . self::config('USD_ID') . '"]/Value')[0];
        $result['usd_rub'] = str_replace(',','.', $usd_rubArray['0']);

        $eur_rubArray = (array)$rateXml->xpath('//Valute[@ID="' . self::config('EUR_ID') . '"]/Value')[0];
        $result['eur_rub'] = str_replace(',','.', $eur_rubArray['0']);

        return $result;
    }

    /**
     * Получение, данных о курсах валют из удаленного сервера и сохранение их в БД
     * @return bool результат запроса
     */
    public static function getRate()
    {
        $result = false;
        $timeRequest = new DateTime();
        //плановое время для запроса курсов валют
        $timeScheduleRequest = new DateTime(date('Y-m-d') . ' ' . self::config('TIME_REQUEST'));
        //Отсекаем запросы на текущие сутки до времени планового запроса курсов валют
        if ($timeRequest >= $timeScheduleRequest) {
            //Определяем загружен ли курс валют на текущее число
            $rate = Rate::findOne(['date' => $timeRequest->format('Y-m-d')]);

            if($rate === null) {
                $rate = new Rate();
                $rateRequest = self::requestRate();

                if($rateRequest === false) {
                    $rate->date = $timeRequest->format('Y-m-d');
                    $rate->status = Rate::STATUS_FAILURE;
                    $rate->attempt_counter= 1;

                    $rate->save();
                } else {
                    $rate->date = $timeRequest->format('Y-m-d');
                    $rate->usd_rub = $rateRequest['usd_rub'];
                    $rate->eur_rub = $rateRequest['eur_rub'];
                    $rate->status = Rate::STATUS_DONE;
                    $rate->attempt_counter = 0;

                    $rate->save();
                    $result = empty($rate->errors) ? true : false;
                }
            } elseif ($rate->status == Rate::STATUS_FAILURE) {
                $attemptInterval = self::config('ATTEMPT_INTERVAL');
                $timeInterval = $attemptInterval[$rate->attempt_counter];

                if ($timeRequest >= ($timeScheduleRequest + $timeInterval)) {
                    $rateRequest = self::requestRate();

                    if($rateRequest === false) {
                        $rate->attempt_counter++;
                        $rate->save();
                    } else {
                        $rate->date = $timeRequest->format('Y-m-d');
                        $rate->usd_rub = $rateRequest['usd_rub'];
                        $rate->eur_rub = $rateRequest['eur_rub'];
                        $rate->status = Rate::STATUS_DONE;
                        $rate->attempt_counter = 0;
                        $rate->save();

                        $result = empty($rate->errors) ? true : false;
                    }
                }
            }
        }

        return $result;
    }
}
