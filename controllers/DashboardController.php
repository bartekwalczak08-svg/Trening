<?php

namespace app\controllers;

use app\models\generated\WorkoutExercises;
use app\models\generated\Workouts;
use Yii;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\Controller;

class DashboardController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $userId = Yii::$app->user->id;
        $now = time();
        $todayStart = strtotime('today', $now);
        $weekStart = strtotime('monday this week', $now);
        if ($weekStart === false) {
            $weekStart = $todayStart;
        }
        $monthStart = strtotime(date('Y-m-01 00:00:00', $now));

        $weeklyWorkouts = (int) Workouts::find()
            ->where(['user_id' => $userId])
            ->andWhere(['>=', 'created_at', $weekStart])
            ->count();

        $monthlyWorkouts = (int) Workouts::find()
            ->where(['user_id' => $userId])
            ->andWhere(['>=', 'created_at', $monthStart])
            ->count();

        $totalPlannedDurationSec = (int) WorkoutExercises::find()
            ->joinWith('workout')
            ->where(['workouts.user_id' => $userId])
            ->select(new Expression(
                'COALESCE(SUM(COALESCE(duration_sec, 0) * CASE WHEN sets IS NULL OR sets < 1 THEN 1 ELSE sets END), 0)'
            ))
            ->scalar();
        $avgRestSecRaw = WorkoutExercises::find()
            ->joinWith('workout')
            ->where(['workouts.user_id' => $userId])
            ->average('rest_sec');
        $avgRestSec = $avgRestSecRaw !== null ? (int) round((float) $avgRestSecRaw) : 0;

        $todayWorkout = Workouts::find()
            ->where(['user_id' => $userId])
            ->andWhere(['>=', 'created_at', $todayStart])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();

        if ($todayWorkout === null) {
            $todayWorkout = Workouts::find()
                ->where(['user_id' => $userId])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();
        }

        $recentWorkouts = Workouts::find()
            ->where(['user_id' => $userId])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        $kpi = [
            'weekWorkouts' => $weeklyWorkouts,
            'monthWorkouts' => $monthlyWorkouts,
            'plannedDuration' => $this->formatDuration($totalPlannedDurationSec),
            'avgRestSec' => $avgRestSec,
            'streakDays' => $this->calculateCreationStreakDays(),
        ];

        return $this->render('index', [
            'todayWorkout' => $todayWorkout,
            'recentWorkouts' => $recentWorkouts,
            'kpi' => $kpi,
        ]);
    }

    private function calculateCreationStreakDays()
    {
        $userId = Yii::$app->user->id;
        $timestamps = Workouts::find()
            ->where(['user_id' => $userId])
            ->select('created_at')
            ->orderBy(['created_at' => SORT_DESC])
            ->column();

        if (empty($timestamps)) {
            return 0;
        }

        $daysWithWorkouts = [];
        foreach ($timestamps as $ts) {
            $daysWithWorkouts[date('Y-m-d', (int) $ts)] = true;
        }

        $streak = 0;
        $cursor = strtotime('today');

        while (isset($daysWithWorkouts[date('Y-m-d', $cursor)])) {
            $streak++;
            $cursor = strtotime('-1 day', $cursor);
        }

        return $streak;
    }

    private function formatDuration($seconds)
    {
        $seconds = (int) $seconds;
        if ($seconds <= 0) {
            return '0 min';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours > 0 && $minutes > 0) {
            return $hours . ' h ' . $minutes . ' min';
        }
        if ($hours > 0) {
            return $hours . ' h';
        }
        return max(1, $minutes) . ' min';
    }
}
