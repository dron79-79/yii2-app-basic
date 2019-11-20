<?php

namespace app\models;

use Yii;
use \yii\db\ActiveRecord;

/**
 * Класс модели для работы с таблицей "rate".
 *
 * @property int $id
 * @property string $date
 * @property string $usd_rub
 * @property string $eur_rub
 * @property int $status
 * @property int $attempt_counter
 */
class Rate extends ActiveRecord
{
    const STATUS_DONE = 1;
    const STATUS_FAILURE = 0;

    /**
     * Возвращвет название таблицы в БД с которой работает модель
     * @static
     * @return string название таблицы с которой работает класс Rate
     */
    public static function tableName()
    {
        return 'rate';
    }

    /**
     * Правила проверки свойств класса Rate
     * @return array валидаторы свойств класса
     */
    public function rules()
    {
        return [
            [['date', 'status', 'attempt_counter'], 'required'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['usd_rub', 'eur_rub'], 'number'],
            [['status', 'attempt_counter'], 'integer'],
            [['status'], 'in', 'range' => [0, 1]],
            [['attempt_counter'], 'in', 'range' => [0, 1, 2, 3, 4]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'usd_rub' => 'Usd Rub',
            'eur_rub' => 'Eur Rub',
            'status' => 'Status',
            'attempt_counter' => 'Attempt counter',
        ];
    }
}
