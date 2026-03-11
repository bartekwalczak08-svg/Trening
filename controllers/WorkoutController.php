<?php

/**
 * Opis: Kontroler obsugujcy dania HTTP i logik akcji.
 */


namespace app\controllers;

use app\models\Exercises;
use app\models\WorkoutExercises;
use app\models\Workouts;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Kontroler planów treningowych i ćwiczeń wchodzących w skład treningu.
 */
class WorkoutController extends Controller
{
    /**
     * Definiuje autoryzację oraz metody HTTP wymagane dla operacji modyfikujących.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'calendar', 'view', 'create', 'update', 'delete', 'add-exercise', 'update-exercise', 'delete-exercise', 'toggle-exercise-completion'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'delete-exercise' => ['POST'],
                    'toggle-exercise-completion' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Wyświetla listę treningów użytkownika, pogrupowaną po dniach tygodnia.
        *
        * @return string
     */
    public function actionIndex()
    {
        $workouts = Workouts::find()
            ->with('workoutExercises')
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $groupedWorkouts = [];
        foreach (Workouts::weekdayOrder() as $weekday) {
            $groupedWorkouts[$weekday] = [];
        }

        foreach ($workouts as $workout) {
            $weekday = $workout->weekday ?: Workouts::WEEKDAY_MONDAY;
            if (!isset($groupedWorkouts[$weekday])) {
                $groupedWorkouts[$weekday] = [];
            }
            $groupedWorkouts[$weekday][] = $workout;
        }

        return $this->render('index', [
            'workouts' => $workouts,
            'groupedWorkouts' => $groupedWorkouts,
        ]);
    }

    /**
     * Renderuje tygodniowy widok kalendarza treningów.
     *
     * @return string
     */
    public function actionCalendar()
    {
        $workouts = Workouts::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $groupedWorkouts = [];
        foreach (Workouts::weekdayOrder() as $weekday) {
            $groupedWorkouts[$weekday] = [];
        }

        foreach ($workouts as $workout) {
            $weekday = $workout->weekday ?: Workouts::WEEKDAY_MONDAY;
            if (!isset($groupedWorkouts[$weekday])) {
                $groupedWorkouts[$weekday] = [];
            }
            $groupedWorkouts[$weekday][] = $workout;
        }

        return $this->render('calendar', [
            'groupedWorkouts' => $groupedWorkouts,
        ]);
    }

    /**
     * Pokazuje szczegóły jednego treningu wraz z przypisanymi ćwiczeniami.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $workout = $this->findModel($id);
        $exercises = Exercises::find()->all();

        // Get workout exercises with their details
        $workoutExercises = WorkoutExercises::find()
            ->where(['workout_id' => $id])
            ->orderBy(['position' => SORT_ASC])
            ->all();

        return $this->render('view', [
            'workout' => $workout,
            'exercises' => $exercises,
            'workoutExercises' => $workoutExercises,
        ]);
    }

    /**
     * Tworzy nowy trening dla zalogowanego użytkownika.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Workouts();
        if (empty($model->weekday)) {
            $model->weekday = Workouts::WEEKDAY_MONDAY;
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->id;
            $model->created_at = time();
            $model->updated_at = time();

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Trening został utworzony.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Aktualizuje dane istniejącego treningu.
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->updated_at = time();

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Trening został zaktualizowany.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Usuwa trening oraz jego ćwiczenia zależne.
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Delete associated workout exercises first
        WorkoutExercises::deleteAll(['workout_id' => $id]);

        $model->delete();

        Yii::$app->session->setFlash('success', 'Trening został usunięty.');
        return $this->redirect(['index']);
    }

    /**
     * Dodaje ćwiczenie do wybranego treningu.
     *
     * Obsługuje zarówno wybór ćwiczenia z listy, jak i utworzenie nowego.
     *
     * @param int $workout_id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionAddExercise($workout_id)
    {
        $workout = $this->findModel($workout_id);
        $exercises = Exercises::find()->all();

        $model = new WorkoutExercises();
        $model->workout_id = $workout_id;
        if ($model->hasAttribute('duration_unit') && !$model->getAttribute('duration_unit')) {
            $model->setAttribute('duration_unit', 'sec');
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $exerciseData = $post['WorkoutExercises'] ?? [];

            // Check for custom exercise name
            $customExerciseName = isset($post['custom_exercise_name']) ? trim($post['custom_exercise_name']) : '';

            if (!empty($customExerciseName)) {
                // Create new exercise with required fields
                $exercise = new Exercises();
                $exercise->name = $customExerciseName;
                $exercise->type = 'custom';
                $exercise->created_at = time();
                $exercise->updated_at = time();

                if ($exercise->save(false)) {
                    $model->exercise_id = $exercise->id;
                }
            } else {
                // Use exercise_id from dropdown
                if (isset($exerciseData['exercise_id'])) {
                    $model->exercise_id = $exerciseData['exercise_id'];
                }
            }

            // Only proceed if we have a valid exercise_id
            if (!empty($model->exercise_id)) {
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

                // Get max position for this workout
                $maxPosition = WorkoutExercises::find()
                    ->where(['workout_id' => $workout_id])
                    ->max('position');
                $model->position = ($maxPosition !== null ? $maxPosition : 0) + 1;
                if ($model->hasAttribute('is_completed')) {
                    $model->setAttribute('is_completed', 0);
                }

                if ($model->save()) {
                    $this->syncWorkoutCompletionStatus($workout_id);
                    Yii::$app->session->setFlash('success', 'Ćwiczenie zostało dodane.');
                    return $this->redirect(['view', 'id' => $workout_id]);
                }
            } else {
                $model->addError('exercise_id', 'Wybierz ćwiczenie z listy lub wpisz nazwę.');
            }
        }

        return $this->render('add-exercise', [
            'model' => $model,
            'workout' => $workout,
            'exercises' => $exercises,
        ]);
    }

    /**
     * Edytuje ćwiczenie przypisane do treningu.
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdateExercise($id)
    {
        $model = $this->findWorkoutExerciseModel($id);

        if (!$model) {
            throw new NotFoundHttpException('Ćwiczenie nie zostało znalezione.');
        }

        $workout = $this->findModel($model->workout_id);
        $exercises = Exercises::find()->all();

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $exerciseData = $post['WorkoutExercises'] ?? [];

            // Check for custom exercise name
            $customExerciseName = isset($post['custom_exercise_name']) ? trim($post['custom_exercise_name']) : '';

            if (!empty($customExerciseName)) {
                // Create new exercise with required fields
                $exercise = new Exercises();
                $exercise->name = $customExerciseName;
                $exercise->type = 'custom';
                $exercise->created_at = time();
                $exercise->updated_at = time();

                if ($exercise->save(false)) {
                    $model->exercise_id = $exercise->id;
                }
            } else {
                // Use exercise_id from dropdown
                if (isset($exerciseData['exercise_id'])) {
                    $model->exercise_id = $exerciseData['exercise_id'];
                }
            }

            // Only proceed if we have a valid exercise_id
            if (!empty($model->exercise_id)) {
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

                if ($model->save()) {
                    $this->syncWorkoutCompletionStatus((int) $model->workout_id);
                    Yii::$app->session->setFlash('success', 'Ćwiczenie zostało zaktualizowane.');
                    return $this->redirect(['view', 'id' => $model->workout_id]);
                }
            } else {
                $model->addError('exercise_id', 'Wybierz ćwiczenie z listy lub wpisz nazwę.');
            }
        }

        return $this->render('update-exercise', [
            'model' => $model,
            'workout' => $workout,
            'exercises' => $exercises,
        ]);
    }

    /**
     * Usuwa ćwiczenie z treningu.
     */
    public function actionDeleteExercise($id)
    {
        $model = $this->findWorkoutExerciseModel($id);

        if (!$model) {
            throw new NotFoundHttpException('Ćwiczenie nie zostało znalezione.');
        }

        $workout_id = $model->workout_id;
        $model->delete();
        $this->syncWorkoutCompletionStatus((int) $workout_id);

        Yii::$app->session->setFlash('success', 'Ćwiczenie zostało usunięte.');
        return $this->redirect(['view', 'id' => $workout_id]);
    }

    /**
     * Przełącza status wykonania ćwiczenia i odświeża status całego treningu.
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionToggleExerciseCompletion($id)
    {
        $model = $this->findWorkoutExerciseModel($id);
        $completed = (int) Yii::$app->request->post('completed', 0) === 1;

        if ($model->hasAttribute('is_completed')) {
            $model->setAttribute('is_completed', $completed ? 1 : 0);
            $model->save(false, ['is_completed']);
        }

        $this->syncWorkoutCompletionStatus((int) $model->workout_id);

        $returnUrl = Yii::$app->request->post('returnUrl');
        if (is_string($returnUrl) && $returnUrl !== '') {
            return $this->redirect($returnUrl);
        }

        return $this->redirect(['view', 'id' => $model->workout_id]);
    }

    /**
     * Pobiera trening po ID, pilnując własności rekordu przez aktualnego użytkownika.
        *
        * @param int $id
        * @return Workouts
        * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Workouts::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Strona nie została znaleziona.');
    }

    /**
     * Pobiera ćwiczenie treningowe tylko wtedy, gdy należy do treningu użytkownika.
     *
     * @param int $id
     * @return WorkoutExercises
     * @throws NotFoundHttpException
     */
    protected function findWorkoutExerciseModel($id)
    {
        $model = WorkoutExercises::find()
            ->joinWith('workout')
            ->where([
                'workout_exercises.id' => $id,
                'workouts.user_id' => Yii::$app->user->id,
            ])
            ->one();

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Ćwiczenie nie zostało znalezione.');
    }

    /**
     * Synchronizuje pole is_completed treningu na podstawie stanu wszystkich ćwiczeń.
        *
        * @param int $workoutId
        * @return void
     */
    protected function syncWorkoutCompletionStatus($workoutId)
    {
        $workout = Workouts::find()
            ->where(['id' => $workoutId, 'user_id' => Yii::$app->user->id])
            ->one();

        if ($workout === null || !$workout->hasAttribute('is_completed')) {
            return;
        }

        $total = (int) WorkoutExercises::find()
            ->where(['workout_id' => $workoutId])
            ->count();

        if ($total === 0) {
            $workout->setAttribute('is_completed', 0);
            $workout->save(false, ['is_completed']);
            return;
        }

        $completed = (int) WorkoutExercises::find()
            ->where(['workout_id' => $workoutId, 'is_completed' => 1])
            ->count();

        $workout->setAttribute('is_completed', $completed === $total ? 1 : 0);
        $workout->save(false, ['is_completed']);
    }
}
