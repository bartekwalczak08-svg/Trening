<?php

/**
 * Service responsible for workout exercise business operations.
 */
namespace app\services;

use app\models\Exercises;
use app\models\WorkoutExercises;
use app\models\Workouts;

class WorkoutExerciseService
{
    /**
     * Applies posted form data to workout exercise model.
     *
     * @param WorkoutExercises $model
     * @param array $post
     * @return bool
     */
    public function hydrateFromPost(WorkoutExercises $model, array $post)
    {
        $exerciseData = $post['WorkoutExercises'] ?? [];
        $customExerciseName = isset($post['custom_exercise_name']) ? trim((string) $post['custom_exercise_name']) : '';

        if ($customExerciseName !== '') {
            $exercise = new Exercises();
            $exercise->name = $customExerciseName;
            $exercise->type = 'custom';
            $exercise->created_at = time();
            $exercise->updated_at = time();

            if ($exercise->save(false)) {
                $model->exercise_id = $exercise->id;
            }
        } elseif (isset($exerciseData['exercise_id'])) {
            $model->exercise_id = $exerciseData['exercise_id'];
        }

        if (empty($model->exercise_id)) {
            $model->addError('exercise_id', 'Wybierz ćwiczenie z listy lub wpisz nazwę.');
            return false;
        }

        // Accept both legacy (flat) and model-based field names.
        $model->sets = $exerciseData['sets'] ?? ($post['sets'] ?? null);
        $model->reps = $exerciseData['reps'] ?? ($post['reps'] ?? null);
        $durationInput = $exerciseData['duration_sec'] ?? null;
        $durationUnit = $exerciseData['duration_unit'] ?? 'sec';
        $model->rest_sec = $exerciseData['rest_sec'] ?? null;

        if ($model->hasAttribute('duration_unit')) {
            $model->setAttribute('duration_unit', $durationUnit === 'min' ? 'min' : 'sec');
        }

        // Keep defaults/nullable values predictable when fields are submitted empty.
        if ($durationInput === '' || $durationInput === null) {
            $model->duration_sec = null;
        } else {
            $durationValue = (int) $durationInput;
            $model->duration_sec = ($durationUnit === 'min') ? ($durationValue * 60) : $durationValue;
        }

        $model->rest_sec = ($model->rest_sec === '' || $model->rest_sec === null) ? 60 : $model->rest_sec;

        return true;
    }

    /**
     * Sets default values when creating a new workout exercise.
     *
     * @param WorkoutExercises $model
     * @param int $workoutId
     * @return void
     */
    public function prepareForCreate(WorkoutExercises $model, $workoutId)
    {
        $model->workout_id = (int) $workoutId;

        if ($model->hasAttribute('duration_unit') && !$model->getAttribute('duration_unit')) {
            $model->setAttribute('duration_unit', 'sec');
        }

        if ($model->hasAttribute('is_completed')) {
            $model->setAttribute('is_completed', 0);
        }
    }

    /**
     * Calculates and assigns next exercise position in workout.
     *
     * @param WorkoutExercises $model
     * @param int $workoutId
     * @return void
     */
    public function assignNextPosition(WorkoutExercises $model, $workoutId)
    {
        $maxPosition = WorkoutExercises::find()
            ->where(['workout_id' => (int) $workoutId])
            ->max('position');

        $model->position = ($maxPosition !== null ? (int) $maxPosition : 0) + 1;
    }

    /**
     * Synchronizes workout completion flag with its exercise states.
     *
     * @param int $workoutId
     * @param int $userId
     * @return void
     */
    public function syncWorkoutCompletionStatus($workoutId, $userId)
    {
        $workout = Workouts::find()
            ->where(['id' => (int) $workoutId, 'user_id' => (int) $userId])
            ->one();

        if ($workout === null || !$workout->hasAttribute('is_completed')) {
            return;
        }

        $total = (int) WorkoutExercises::find()
            ->where(['workout_id' => (int) $workoutId])
            ->count();

        if ($total === 0) {
            $workout->setAttribute('is_completed', 0);
            $workout->save(false, ['is_completed']);
            return;
        }

        $completed = (int) WorkoutExercises::find()
            ->where(['workout_id' => (int) $workoutId, 'is_completed' => 1])
            ->count();

        $workout->setAttribute('is_completed', $completed === $total ? 1 : 0);
        $workout->save(false, ['is_completed']);
    }
}
