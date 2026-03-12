<?php

/**
 * Opis: Kontroler obsugujcy dania HTTP i logik akcji.
 */


namespace app\controllers;

use app\models\Exercises;
use app\models\WorkoutExercises;
use app\models\Workouts;
use app\services\WorkoutExerciseService;
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
     * @var WorkoutExerciseService|null
     */
    private $workoutExerciseService;

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

        $groupedWorkouts = Workouts::groupByWeekday($workouts);

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

        $groupedWorkouts = Workouts::groupByWeekday($workouts);

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
        $this->getWorkoutExerciseService()->prepareForCreate($model, (int) $workout_id);

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($this->getWorkoutExerciseService()->hydrateFromPost($model, $post)) {
                $this->getWorkoutExerciseService()->assignNextPosition($model, (int) $workout_id);

                if ($model->save()) {
                    $this->getWorkoutExerciseService()->syncWorkoutCompletionStatus((int) $workout_id, (int) Yii::$app->user->id);
                    Yii::$app->session->setFlash('success', 'Ćwiczenie zostało dodane.');
                    return $this->redirect(['view', 'id' => $workout_id]);
                }
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
            if ($this->getWorkoutExerciseService()->hydrateFromPost($model, $post)) {
                if ($model->save()) {
                    $this->getWorkoutExerciseService()->syncWorkoutCompletionStatus((int) $model->workout_id, (int) Yii::$app->user->id);
                    Yii::$app->session->setFlash('success', 'Ćwiczenie zostało zaktualizowane.');
                    return $this->redirect(['view', 'id' => $model->workout_id]);
                }
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
        $this->getWorkoutExerciseService()->syncWorkoutCompletionStatus((int) $workout_id, (int) Yii::$app->user->id);

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

        $this->getWorkoutExerciseService()->syncWorkoutCompletionStatus((int) $model->workout_id, (int) Yii::$app->user->id);

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
     * Lazy accessor for workout exercise service.
     *
     * @return WorkoutExerciseService
     */
    protected function getWorkoutExerciseService()
    {
        if ($this->workoutExerciseService === null) {
            $this->workoutExerciseService = new WorkoutExerciseService();
        }

        return $this->workoutExerciseService;
    }
}
