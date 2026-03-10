<?php

/**
 * Opis: Model wygenerowany automatycznie na podstawie schematu bazy danych.
 */


namespace app\models\generated;

use Yii;

/**
 * This is the model class for table "exercises".
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int|null $sets
 * @property int|null $reps
 * @property int|null $duration_sec
 * @property int|null $rest_sec
 * @property int $created_at
 * @property int $updated_at
 *
 * @property WorkoutExercises[] $workoutExercises
 */
// Klasa Exercises.
class Exercises extends \app\models\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    // Metoda tableName.
    public static function tableName()
    {
        return 'exercises';
    }

    /**
     * {@inheritdoc}
     */
    // Metoda rules.
    public function rules()
    {
        return [
            [['sets', 'reps', 'duration_sec'], 'default', 'value' => null],
            [['rest_sec'], 'default', 'value' => 60],
            [['name', 'type', 'created_at', 'updated_at'], 'required'],
            [['sets', 'reps', 'duration_sec', 'rest_sec', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    // Metoda attributeLabels.
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'type' => 'Type',
            'sets' => 'Sets',
            'reps' => 'Reps',
            'duration_sec' => 'Duration Sec',
            'rest_sec' => 'Rest Sec',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[WorkoutExercises]].
     *
     * @return \yii\db\ActiveQuery
     */
    // Metoda getWorkoutExercises.
    public function getWorkoutExercises()
    {
        return $this->hasMany(WorkoutExercises::class, ['exercise_id' => 'id']);
    }

}
