<?php

/**
 * Opis: Migracja bazy danych odpowiedzialna za zmiany schematu.
 */


namespace app\migrations;

use yii\db\Migration;
use yii\db\Query;

// Klasa m260310_120000_add_weekday_to_workouts.
class m260310_120000_add_weekday_to_workouts extends Migration
{
    // Metoda safeUp.
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%workouts}}', true);
        if ($table === null || isset($table->columns['weekday'])) {
            return;
        }

        $this->addColumn('{{%workouts}}', 'weekday', $this->string(16)->notNull()->defaultValue('monday')->after('description'));

        $rows = (new Query())
            ->select(['id', 'created_at'])
            ->from('{{%workouts}}')
            ->all();

        foreach ($rows as $row) {
            $timestamp = isset($row['created_at']) ? (int) $row['created_at'] : 0;
            $dayNumber = (int) date('N', $timestamp > 0 ? $timestamp : time());
            $weekday = $this->toWeekdayName($dayNumber);
            $this->update('{{%workouts}}', ['weekday' => $weekday], ['id' => $row['id']]);
        }
    }

    // Metoda safeDown.
    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('{{%workouts}}', true);
        if ($table !== null && isset($table->columns['weekday'])) {
            $this->dropColumn('{{%workouts}}', 'weekday');
        }
    }

    // Metoda toWeekdayName.
    private function toWeekdayName($dayNumber)
    {
        $map = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];

        return $map[$dayNumber] ?? 'monday';
    }
}
