<?php

/**
 * Auto-generated model based on database schema.
 */
namespace app\models\generated;

use Yii;

/**
 * This is the model class for table "workout_exercises".
 *
 * @property int $id
 * @property int $workout_id
 * @property int $exercise_id
 * @property int|null $sets
 * @property string|null $reps
 * @property int|null $duration_sec
 * @property int|null $rest_sec
 * @property int|null $position
 * @property int $is_completed
 *
 * @property GeneratedExercises $exercise
 * @property GeneratedWorkouts $workout
 */
class GeneratedWorkoutExercises extends \app\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'workout_exercises';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sets', 'duration_sec'], 'default', 'value' => null],
            [['rest_sec'], 'default', 'value' => 60],
            [['position'], 'default', 'value' => 0],
            [['is_completed'], 'default', 'value' => 0],
            [['workout_id', 'exercise_id'], 'required'],
            [['workout_id', 'exercise_id', 'sets', 'duration_sec', 'rest_sec', 'position', 'is_completed'], 'integer'],
            [['reps'], 'string'],
            [['exercise_id'], 'exist', 'skipOnError' => true, 'targetClass' => GeneratedExercises::class, 'targetAttribute' => ['exercise_id' => 'id']],
            [['workout_id'], 'exist', 'skipOnError' => true, 'targetClass' => GeneratedWorkouts::class, 'targetAttribute' => ['workout_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
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
            'is_completed' => 'Ukończono',
        ];
    }

    /**
    * Gets query for [[GeneratedExercise]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExercise()
    {
        return $this->hasOne(GeneratedExercises::class, ['id' => 'exercise_id']);
    }

    /**
    * Gets query for [[GeneratedWorkout]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkout()
    {
        return $this->hasOne(GeneratedWorkouts::class, ['id' => 'workout_id']);
    }
}