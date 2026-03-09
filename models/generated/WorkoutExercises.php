<?php

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
 *
 * @property Exercises $exercise
 * @property Workouts $workout
 */
class WorkoutExercises extends \app\models\ActiveRecord
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
            [['workout_id', 'exercise_id'], 'required'],
            [['workout_id', 'exercise_id', 'sets', 'duration_sec', 'rest_sec', 'position'], 'integer'],
            [['reps'], 'string'],
            [['exercise_id'], 'exist', 'skipOnError' => true, 'targetClass' => Exercises::class, 'targetAttribute' => ['exercise_id' => 'id']],
            [['workout_id'], 'exist', 'skipOnError' => true, 'targetClass' => Workouts::class, 'targetAttribute' => ['workout_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'workout_id' => 'Workout ID',
            'exercise_id' => 'Exercise ID',
            'sets' => 'Sets',
            'reps' => 'Powtorzenia',
            'duration_sec' => 'Duration Sec',
            'rest_sec' => 'Rest Sec',
            'position' => 'Position',
        ];
    }

    /**
     * Gets query for [[Exercise]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExercise()
    {
        return $this->hasOne(Exercises::class, ['id' => 'exercise_id']);
    }

    /**
     * Gets query for [[Workout]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkout()
    {
        return $this->hasOne(Workouts::class, ['id' => 'workout_id']);
    }
}
