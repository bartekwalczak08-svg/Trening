<?php

/**
 * Opis: Model domenowy uywany w aplikacji.
 */


namespace app\models;

use Yii;

/**
 * This is the model class for table "workout_exercises".
 *
 * @property int $id
 * @property int $workout_id
 * @property int $exercise_id
 * @property int|null $sets
 * @property int|null $reps
 * @property int|null $duration_sec
 * @property int|null $rest_sec
 * @property int|null $position
 *
 * @property Exercises $exercise
 * @property Workouts $workout
 */
/**
 * Model łączący trening z konkretnym ćwiczeniem i jego parametrami wykonania.
 */
class WorkoutExercise extends \yii\db\ActiveRecord
{


    /**
     * Nazwa tabeli mapującej ćwiczenia przypisane do treningów.
     */
    public static function tableName()
    {
        return 'workout_exercises';
    }

    /**
     * Reguły walidacji parametrów ćwiczenia (serie, powtórzenia, czas, kolejność).
     */
    public function rules()
    {
        return [
            [['sets', 'reps', 'duration_sec'], 'default', 'value' => null],
            [['rest_sec'], 'default', 'value' => 60],
            [['position'], 'default', 'value' => 0],
            [['workout_id', 'exercise_id'], 'required'],
            [['workout_id', 'exercise_id', 'sets', 'reps', 'duration_sec', 'rest_sec', 'position'], 'integer'],
            [['workout_id'], 'exist', 'skipOnError' => true, 'targetClass' => Workouts::class, 'targetAttribute' => ['workout_id' => 'id']],
            [['exercise_id'], 'exist', 'skipOnError' => true, 'targetClass' => Exercises::class, 'targetAttribute' => ['exercise_id' => 'id']],
        ];
    }

    /**
     * Etykiety atrybutów modelu.
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'workout_id' => 'ID treningu',
            'exercise_id' => 'ID ćwiczenia',
            'sets' => 'Serie',
            'reps' => 'Powtórzenia',
            'duration_sec' => 'Czas trwania (s)',
            'rest_sec' => 'Przerwa (s)',
            'position' => 'Pozycja',
        ];
    }

    /**
     * Relacja do słownika ćwiczeń.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExercise()
    {
        return $this->hasOne(Exercises::class, ['id' => 'exercise_id']);
    }

    /**
     * Relacja do treningu, do którego przypisano to ćwiczenie.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkout()
    {
        return $this->hasOne(Workouts::class, ['id' => 'workout_id']);
    }

}
