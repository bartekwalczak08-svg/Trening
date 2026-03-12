<?php

/**
 * Auto-generated model based on database schema.
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
 * @property GeneratedWorkoutExercises[] $workoutExercises
 */
class GeneratedExercises extends \app\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'exercises';
    }

    /**
     * {@inheritdoc}
     */
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
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Nazwa',
            'type' => 'Typ',
            'sets' => 'Serie',
            'reps' => 'Powtórzenia',
            'duration_sec' => 'Czas trwania (s)',
            'rest_sec' => 'Przerwa (s)',
            'created_at' => 'Data utworzenia',
            'updated_at' => 'Data aktualizacji',
        ];
    }

    /**
     * Gets query for [[GeneratedWorkoutExercises]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkoutExercises()
    {
        return $this->hasMany(GeneratedWorkoutExercises::class, ['exercise_id' => 'id']);
    }
}