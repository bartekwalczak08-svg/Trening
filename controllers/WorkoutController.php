<?php

namespace app\controllers;

use app\models\generated\Workouts;
use app\models\generated\WorkoutExercises;
use app\models\generated\Exercises;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * WorkoutController implements CRUD actions for Workouts model.
 */
class WorkoutController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'create', 'update', 'delete', 'add-exercise', 'update-exercise', 'delete-exercise'],
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
                ],
            ],
        ];
    }

    /**
     * Lists all Workouts models.
     * @return string
     */
    public function actionIndex()
    {
        $workouts = Workouts::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
        
        return $this->render('index', [
            'workouts' => $workouts,
        ]);
    }

    /**
     * Displays a single Workouts model.
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
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
     * Creates a new Workouts model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Workouts();
        
        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->id;
            $model->created_at = time();
            $model->updated_at = time();
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Trening zostal utworzony.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Workouts model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post())) {
            $model->updated_at = time();
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Trening zostal zaktualizowany.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Workouts model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Delete associated workout exercises first
        WorkoutExercises::deleteAll(['workout_id' => $id]);
        
        $model->delete();
        
        Yii::$app->session->setFlash('success', 'Trening zostal usunietu.');
        return $this->redirect(['index']);
    }

/**
     * Add exercise to workout
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
                
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Cwiczenie zostalo dodane.');
                    return $this->redirect(['view', 'id' => $workout_id]);
                }
            } else {
                $model->addError('exercise_id', 'Wybierz cwiczenie z listy lub wpisz nazwe.');
            }
        }
        
        return $this->render('add-exercise', [
            'model' => $model,
            'workout' => $workout,
            'exercises' => $exercises,
        ]);
    }

/**
     * Update exercise in workout
     */
    public function actionUpdateExercise($id)
    {
        $model = $this->findWorkoutExerciseModel($id);
        
        if (!$model) {
            throw new NotFoundHttpException('Cwiczenie nie zostalo znalezione.');
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
                    Yii::$app->session->setFlash('success', 'Cwiczenie zostalo zaktualizowane.');
                    return $this->redirect(['view', 'id' => $model->workout_id]);
                }
            } else {
                $model->addError('exercise_id', 'Wybierz cwiczenie z listy lub wpisz nazwe.');
            }
        }
        
        return $this->render('update-exercise', [
            'model' => $model,
            'workout' => $workout,
            'exercises' => $exercises,
        ]);
    }

    /**
     * Delete exercise from workout
     */
    public function actionDeleteExercise($id)
    {
        $model = $this->findWorkoutExerciseModel($id);
        
        if (!$model) {
            throw new NotFoundHttpException('Cwiczenie nie zostalo znalezione.');
        }
        
        $workout_id = $model->workout_id;
        $model->delete();
        
        Yii::$app->session->setFlash('success', 'Cwiczenie zostalo usunietu.');
        return $this->redirect(['view', 'id' => $workout_id]);
    }

    /**
     * Finds the Workouts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Workouts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Workouts::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one()) !== null) {
            return $model;
        }
        
        throw new NotFoundHttpException('Strona nie zostala znaleziona.');
    }

    /**
     * Finds workout exercise only if it belongs to current user's workout.
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

        throw new NotFoundHttpException('Cwiczenie nie zostalo znalezione.');
    }
}
