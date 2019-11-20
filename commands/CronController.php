<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Rate;
use app\helpers\RateHelper;
use Yii;
use DateTime;


/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CronController extends Controller
{
    /**
     * Обработчик плановыч задач, вызывается ежеминутно
     * 0-59 * * * * для Крона
     * @return int Exit code
     */
    public function actionIndex()
    {
        if (RateHelper::getRate()){
            $currentDate = new DateTime();
            $yesterdayDate = new DateTime('-1 days');
            $currentRate = Rate::findOne(['date' => $currentDate->format('Y-m-d')]);
            $yesterdayRate = Rate::findOne(['date' => $yesterdayDate->format('Y-m-d')]);
            $differenceUsd = abs($currentRate->usd_rub - $yesterdayRate->usd_rub);
            $differenceEur = abs($currentRate->eur_rub - $yesterdayRate->eur_rub);
            if ($differenceUsd > 2 || $differenceEur > 2) {
                $message = "Настоящим письмом уведомляю Вас, что сегодня "
                    . $currentDate->format('d.m.Y')
                    . ", произошло изменение курсов валют более чем на 2 рубля";
                Yii::$app->mailer->compose()
                    ->setTo(Yii::$app->params['adminEmail'])
                    ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                    ->setSubject('Уведомление об изменении курса валют более чем на 2 рубля')
                    ->setTextBody($message)
                    ->send();
            }
        }
echo Yii::$app->params['adminEmail'];

        return ExitCode::OK;
    }
}
