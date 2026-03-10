<?php

/**
 * Opis: Model wygenerowany automatycznie na podstawie schematu bazy danych.
 */


namespace app\models\generated;

use Yii;

/**
 * This is the model class for table "workouts".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $description
 * @property string $weekday
 * @property int $is_completed
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User|null $user
 * @property WorkoutExercises[] $workoutExercises
 */
// Klasa Workouts.
class Workouts extends \app\models\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    // Metoda tableName.
    public static function tableName()
    {
        return 'workouts';
    }

    /**
     * {@inheritdoc}
     */
    // Metoda rules.
    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['weekday'], 'default', 'value' => 'monday'],
            [['is_completed'], 'default', 'value' => 0],
            [['name', 'created_at', 'updated_at'], 'required'],
            [['description'], 'string'],
            [['user_id', 'is_completed', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['weekday'], 'string', 'max' => 16],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'user_id' => 'User ID',
            'name' => 'Name',
            'description' => 'Description',
            'weekday' => 'Weekday',
            'is_completed' => 'Is Completed',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    // Metoda getUser.
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[WorkoutExercises]].
     *
     * @return \yii\db\ActiveQuery
     */
    // Metoda getWorkoutExercises.
    public function getWorkoutExercises()
    {
        return $this->hasMany(WorkoutExercises::class, ['workout_id' => 'id']);
    }

}
