<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%rate}}`.
 */
class m191012_105504_create_rate_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%rate}}', [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull(),
            'usd_rub' => $this->decimal(10,4),
            'eur_rub' => $this->decimal(10,4),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
            'attempt_counter' => $this->smallInteger(1)->notNull()->defaultValue(0),
        ]);

        $this->createIndex(
            'idx-post-date',
            'rate',
            'date'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-rate-date',
            'rate0'
        );
        $this->dropTable('{{%rate}}');
    }
}
