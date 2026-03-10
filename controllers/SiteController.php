<?php

/**
 * Opis: Kontroler obsugujcy dania HTTP i logik akcji.
 */


namespace app\controllers;

use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\ContactMessage;
use app\models\Workouts;
use app\models\WorkoutExercises;

// Klasa SiteController.
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    // Metoda behaviors.
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => [
                    'logout',
                    'profile',
                    'signup',
                    'contact-messages',
                    'contact-messages-trash',
                    'delete-contact-message',
                    'restore-contact-message',
                    'purge-contact-message',
                ],
                'rules' => [
                    [
                        'actions' => [
                            'logout',
                            'profile',
                            'delete-contact-message',
                            'restore-contact-message',
                            'purge-contact-message',
                            'contact-messages-trash',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['contact-messages'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'profile' => ['get','post'],
                    'delete-contact-message' => ['post'],
                    'restore-contact-message' => ['post'],
                    'purge-contact-message' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    // Metoda actions.
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    // Metoda actionIndex.
    public function actionIndex()
    {
        $groupedWorkouts = [];
        $monthlyWorkouts = 0;
        $todayWorkouts = [];
        $nextWorkoutDayLabel = null;
        $activeDaysCount = 0;
        $totalExercisesPlanned = 0;
        $recentlyUpdatedWorkouts = [];
        $weeklyCompletedWorkouts = 0;
        $weeklySkippedWorkouts = 0;
        $weeklyCompletionPercent = 0;
        $calendarFilter = Yii::$app->request->get('calendarFilter', 'all');
        $allowedFilters = ['all', 'today', 'weekend'];
        if (!in_array($calendarFilter, $allowedFilters, true)) {
            $calendarFilter = 'all';
        }
        $visibleWeekdays = Workouts::weekdayOrder();

        if (!Yii::$app->user->isGuest) {
            $workouts = Workouts::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();

            $recentlyUpdatedWorkouts = Workouts::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->orderBy(['updated_at' => SORT_DESC])
                ->limit(5)
                ->all();

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

            $monthStart = strtotime(date('Y-m-01 00:00:00'));
            $monthlyWorkouts = (int) Workouts::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->andWhere(['>=', 'created_at', $monthStart])
                ->count();

            $todayWeekday = $this->mapWeekdayFromNumber((int) date('N'));
            $todayWorkouts = $groupedWorkouts[$todayWeekday] ?? [];

            $weekdayOrder = Workouts::weekdayOrder();
            $todayPosition = array_search($todayWeekday, $weekdayOrder, true);
            $daysUpToToday = $todayPosition === false ? $weekdayOrder : array_slice($weekdayOrder, 0, $todayPosition + 1);
            $plannedToDate = 0;
            $completedToDate = 0;

            foreach ($daysUpToToday as $weekday) {
                foreach ($groupedWorkouts[$weekday] ?? [] as $item) {
                    $plannedToDate++;
                    if ($item->hasAttribute('is_completed') && (int) $item->getAttribute('is_completed') === 1) {
                        $completedToDate++;
                    }
                }
            }

            $weeklyCompletedWorkouts = $completedToDate;
            $weeklySkippedWorkouts = max(0, $plannedToDate - $completedToDate);
            $weeklyCompletionPercent = $plannedToDate > 0
                ? (int) round(($completedToDate / $plannedToDate) * 100)
                : 0;

            foreach (Workouts::weekdayOrder() as $weekday) {
                if (!empty($groupedWorkouts[$weekday])) {
                    $activeDaysCount++;
                }
            }

            $totalExercisesPlanned = (int) WorkoutExercises::find()
                ->joinWith('workout')
                ->where(['workouts.user_id' => Yii::$app->user->id])
                ->count();

            foreach (Workouts::weekdayOrder() as $weekday) {
                if ($weekday === $todayWeekday) {
                    continue;
                }
                if (!empty($groupedWorkouts[$weekday])) {
                    $nextWorkoutDayLabel = Workouts::weekdayOptions()[$weekday] ?? null;
                    break;
                }
            }

            if ($calendarFilter === 'today') {
                $visibleWeekdays = [$todayWeekday];
            } elseif ($calendarFilter === 'weekend') {
                $visibleWeekdays = [Workouts::WEEKDAY_SATURDAY, Workouts::WEEKDAY_SUNDAY];
            }
        }

        return $this->render('index', [
            'groupedWorkouts' => $groupedWorkouts,
            'monthlyWorkouts' => $monthlyWorkouts,
            'todayWorkouts' => $todayWorkouts,
            'nextWorkoutDayLabel' => $nextWorkoutDayLabel,
            'activeDaysCount' => $activeDaysCount,
            'totalExercisesPlanned' => $totalExercisesPlanned,
            'recentlyUpdatedWorkouts' => $recentlyUpdatedWorkouts,
            'calendarFilter' => $calendarFilter,
            'visibleWeekdays' => $visibleWeekdays,
            'weeklyCompletedWorkouts' => $weeklyCompletedWorkouts,
            'weeklySkippedWorkouts' => $weeklySkippedWorkouts,
            'weeklyCompletionPercent' => $weeklyCompletionPercent,
        ]);
    }

    /**
     * Maps numeric weekday (1-7) to workout weekday key.
     *
     * @param int $dayNumber
     * @return string
     */
    // Metoda mapWeekdayFromNumber.
    private function mapWeekdayFromNumber($dayNumber)
    {
        $map = [
            1 => Workouts::WEEKDAY_MONDAY,
            2 => Workouts::WEEKDAY_TUESDAY,
            3 => Workouts::WEEKDAY_WEDNESDAY,
            4 => Workouts::WEEKDAY_THURSDAY,
            5 => Workouts::WEEKDAY_FRIDAY,
            6 => Workouts::WEEKDAY_SATURDAY,
            7 => Workouts::WEEKDAY_SUNDAY,
        ];

        return $map[$dayNumber] ?? Workouts::WEEKDAY_MONDAY;
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    // Metoda actionSignup.
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new \app\models\SignupForm();
        if ($model->load(Yii::$app->request->post()) && ($user = $model->signup())) {
            // automatically log in newly created user
            Yii::$app->user->login($user);
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    // Metoda actionLogin.
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    // Metoda actionLogout.
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    // Metoda actionContact.
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    // Metoda actionProfile.
    public function actionProfile()
    {
        $model = new \app\models\ChangeCredentialsForm();

        // prefill current username and email
        $model->username = Yii::$app->user->identity->username;
        $model->email = Yii::$app->user->identity->email;

        if ($model->load(Yii::$app->request->post()) && $model->update()) {
            Yii::$app->session->setFlash('success', 'Your account details have been updated.');
            return $this->refresh();
        }

        return $this->render('profile', [
            'model' => $model,
        ]);
    }

    // Metoda actionAbout.
    public function actionAbout()
    {
        return $this->render('about');
    }

    // Metoda actionContactMessages.
    public function actionContactMessages()
    {
        // Paginate active messages (not in trash).
        $query = ContactMessage::find()
            ->where(['deleted_at' => null])
            ->orderBy(['created_at' => SORT_DESC]);

        $pagination = new Pagination([
            'totalCount' => (int) $query->count(),
            'pageSize' => 20,
            'pageSizeParam' => false,
        ]);

        $messages = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $session = Yii::$app->session;
        $seenIds = array_map('intval', (array) $session->get('seenContactMessageIds', []));
        $currentlySeenNewIds = [];

        foreach ($messages as $message) {
            if ($message->status === ContactMessage::STATUS_NEW) {
                $currentlySeenNewIds[] = (int) $message->id;
            }
        }

        // Move status from "new" to "in progress" after reopening the page.
        $idsToMove = array_values(array_intersect($currentlySeenNewIds, $seenIds));
        if (!empty($idsToMove)) {
            ContactMessage::updateAll(
                [
                    'status' => ContactMessage::STATUS_IN_PROGRESS,
                    'updated_at' => time(),
                ],
                ['id' => $idsToMove, 'status' => ContactMessage::STATUS_NEW, 'deleted_at' => null]
            );

            $messages = $query
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();
        }

        $session->set('seenContactMessageIds', array_values(array_unique(array_merge($seenIds, $currentlySeenNewIds))));

        $trashCount = (int) ContactMessage::find()
            ->where(['not', ['deleted_at' => null]])
            ->count();

        return $this->render('contact-messages', [
            'messages' => $messages,
            'pagination' => $pagination,
            'trashCount' => $trashCount,
        ]);
    }

    // Metoda actionDeleteContactMessage.
    public function actionDeleteContactMessage($id)
    {
        $model = ContactMessage::findOne((int) $id);
        if ($model === null || $model->deleted_at !== null) {
            Yii::$app->session->setFlash('error', 'Nie znaleziono zgłoszenia.');
            return $this->redirect(['contact-messages']);
        }

        $model->deleted_at = time();
        $model->updated_at = time();
        if ($model->save(false, ['deleted_at', 'updated_at']) !== false) {
            $seenIds = array_map('intval', (array) Yii::$app->session->get('seenContactMessageIds', []));
            $seenIds = array_values(array_filter($seenIds, static function ($seenId) use ($id) {
                return (int) $seenId !== (int) $id;
            }));
            Yii::$app->session->set('seenContactMessageIds', $seenIds);

            Yii::$app->session->setFlash('success', 'Zgłoszenie zostało przeniesione do kosza.');
        } else {
            Yii::$app->session->setFlash('error', 'Nie udało się przenieść zgłoszenia do kosza.');
        }

        return $this->redirect(['contact-messages']);
    }

    // Metoda actionContactMessagesTrash.
    public function actionContactMessagesTrash()
    {
        $query = ContactMessage::find()
            ->where(['not', ['deleted_at' => null]])
            ->orderBy(['deleted_at' => SORT_DESC, 'id' => SORT_DESC]);

        $pagination = new Pagination([
            'totalCount' => (int) $query->count(),
            'pageSize' => 20,
            'pageSizeParam' => false,
        ]);

        $messages = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('contact-messages-trash', [
            'messages' => $messages,
            'pagination' => $pagination,
        ]);
    }

    // Metoda actionRestoreContactMessage.
    public function actionRestoreContactMessage($id)
    {
        $model = ContactMessage::findOne((int) $id);
        if ($model === null || $model->deleted_at === null) {
            Yii::$app->session->setFlash('error', 'Nie znaleziono zgłoszenia w koszu.');
            return $this->redirect(['contact-messages-trash']);
        }

        $model->deleted_at = null;
        $model->updated_at = time();
        if ($model->save(false, ['deleted_at', 'updated_at']) !== false) {
            Yii::$app->session->setFlash('success', 'Zgłoszenie zostało przywrócone.');
        } else {
            Yii::$app->session->setFlash('error', 'Nie udało się przywrócić zgłoszenia.');
        }

        return $this->redirect(['contact-messages-trash']);
    }

    // Metoda actionPurgeContactMessage.
    public function actionPurgeContactMessage($id)
    {
        $model = ContactMessage::findOne((int) $id);
        if ($model === null || $model->deleted_at === null) {
            Yii::$app->session->setFlash('error', 'Nie znaleziono zgłoszenia w koszu.');
            return $this->redirect(['contact-messages-trash']);
        }

        if ($model->delete() !== false) {
            Yii::$app->session->setFlash('success', 'Zgłoszenie zostało trwale usunięte.');
        } else {
            Yii::$app->session->setFlash('error', 'Nie udało się trwale usunąć zgłoszenia.');
        }

        return $this->redirect(['contact-messages-trash']);
    }
}
