<?php

/**
 * Auto-generated model based on database schema.
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
 * @property GeneratedUser|null $user
 * @property GeneratedWorkoutExercises[] $workoutExercises
 */
class GeneratedWorkouts extends \app\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'workouts';
    }

    /**
     * {@inheritdoc}
     */
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
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => GeneratedUser::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'ID użytkownika',
            'name' => 'Nazwa',
            'description' => 'Opis',
            'weekday' => 'Dzień tygodnia',
            'is_completed' => 'Ukończono',
            'created_at' => 'Data utworzenia',
            'updated_at' => 'Data aktualizacji',
        ];
    }

    /**
     * Gets query for [[GeneratedUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(GeneratedUser::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[GeneratedWorkoutExercises]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkoutExercises()
    {
        return $this->hasMany(GeneratedWorkoutExercises::class, ['workout_id' => 'id']);
    }
}