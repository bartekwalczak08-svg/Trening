<?php

/**
 * Opis: Kontroler obsugujcy dania HTTP i logik akcji.
 */


namespace app\controllers;

use app\models\WorkoutExercises;
use app\models\Workouts;
use Yii;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Kontroler dashboardu użytkownika.
 *
 * Zbiera metryki aktywności, podsumowania treningów i dane do wykresów progresu.
 */
class DashboardController extends Controller
{
    /**
     * Ogranicza dostęp do dashboardu tylko dla zalogowanych użytkowników.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'progress'],
                'rules' => [
                    [
                        'actions' => ['index', 'progress'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Renderuje główny dashboard z KPI i ostatnimi treningami.
     */
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

    /**
     * Liczy aktualną serię dni z rzędu, w których dodano trening.
     */
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

    /**
     * Formatuje liczbę sekund do czytelnej postaci h/min.
     */
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

    /**
     * Buduje dane analityczne progresu i rekomendacje treningowe.
     */
    public function actionProgress()
    {
        $userId = Yii::$app->user->id;
        $workouts = Workouts::find()
            ->with('workoutExercises')
            ->where(['user_id' => $userId])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $totalWorkouts = count($workouts);
        $completedWorkouts = 0;
        $inProgressWorkouts = 0;
        $notStartedWorkouts = 0;

        $weekdayKeys = Workouts::weekdayOrder();
        $weekdayLabels = Workouts::weekdayOptions();
        $weekdayStats = [];
        foreach ($weekdayKeys as $key) {
            $weekdayStats[$key] = ['planned' => 0, 'completed' => 0];
        }

        $incompleteWorkouts = [];
        $weekMap = [];
        $workoutNameStats = [];

        foreach ($workouts as $workout) {
            $weekday = $workout->weekday ?: Workouts::WEEKDAY_MONDAY;
            if (!isset($weekdayStats[$weekday])) {
                $weekdayStats[$weekday] = ['planned' => 0, 'completed' => 0];
            }
            $weekdayStats[$weekday]['planned']++;

            $exerciseItems = $workout->workoutExercises;
            $totalExercises = is_array($exerciseItems) ? count($exerciseItems) : 0;
            $doneExercises = 0;

            if ($totalExercises > 0) {
                foreach ($exerciseItems as $item) {
                    if ($item->hasAttribute('is_completed') && (int) $item->getAttribute('is_completed') === 1) {
                        $doneExercises++;
                    }
                }
            }

            $isCompleted = $totalExercises > 0 && $doneExercises === $totalExercises;
            if ($isCompleted) {
                $completedWorkouts++;
                $weekdayStats[$weekday]['completed']++;
            } elseif ($doneExercises > 0) {
                $inProgressWorkouts++;
            } else {
                $notStartedWorkouts++;
            }

            if (!$isCompleted && count($incompleteWorkouts) < 6) {
                $incompleteWorkouts[] = [
                    'id' => $workout->id,
                    'name' => $workout->name,
                    'weekday' => $weekdayLabels[$weekday] ?? $weekday,
                    'done' => $doneExercises,
                    'total' => $totalExercises,
                ];
            }

            $workoutKey = trim((string) $workout->name);
            if ($workoutKey === '') {
                $workoutKey = 'Bez nazwy';
            }
            if (!isset($workoutNameStats[$workoutKey])) {
                $workoutNameStats[$workoutKey] = [
                    'name' => $workoutKey,
                    'count' => 0,
                    'rateSum' => 0,
                ];
            }
            $workoutRate = $totalExercises > 0 ? (int) round(($doneExercises / $totalExercises) * 100) : 0;
            $workoutNameStats[$workoutKey]['count']++;
            $workoutNameStats[$workoutKey]['rateSum'] += $workoutRate;

            $weekKey = date('o-\\WW', (int) $workout->created_at);
            if (!isset($weekMap[$weekKey])) {
                $weekMap[$weekKey] = [
                    'label' => date('d.m', strtotime('monday this week', (int) $workout->created_at)),
                    'planned' => 0,
                    'completed' => 0,
                ];
            }
            $weekMap[$weekKey]['planned']++;
            if ($isCompleted) {
                $weekMap[$weekKey]['completed']++;
            }
        }

        ksort($weekMap);
        $weekMap = array_slice($weekMap, -8, null, true);
        $weeklyLabels = [];
        $weeklyPlanned = [];
        $weeklyCompleted = [];
        foreach ($weekMap as $stats) {
            $weeklyLabels[] = $stats['label'];
            $weeklyPlanned[] = $stats['planned'];
            $weeklyCompleted[] = $stats['completed'];
        }

        $weekdayCompletionRate = [];
        foreach ($weekdayKeys as $key) {
            $planned = $weekdayStats[$key]['planned'];
            $completed = $weekdayStats[$key]['completed'];
            $weekdayCompletionRate[] = $planned > 0 ? (int) round(($completed / $planned) * 100) : 0;
        }

        $completionPercent = $totalWorkouts > 0 ? (int) round(($completedWorkouts / $totalWorkouts) * 100) : 0;

        $weeklyRates = [];
        foreach ($weekMap as $stats) {
            $planned = (int) $stats['planned'];
            $done = (int) $stats['completed'];
            $weeklyRates[] = $planned > 0 ? (int) round(($done / $planned) * 100) : 0;
        }

        $recommendations = [];

        $bestWeekday = null;
        $worstWeekday = null;
        foreach ($weekdayKeys as $index => $key) {
            $planned = (int) ($weekdayStats[$key]['planned'] ?? 0);
            if ($planned < 1) {
                continue;
            }
            $rate = (int) $weekdayCompletionRate[$index];
            if ($bestWeekday === null || $rate > $bestWeekday['rate']) {
                $bestWeekday = ['label' => $weekdayLabels[$key] ?? $key, 'rate' => $rate];
            }
            if ($worstWeekday === null || $rate < $worstWeekday['rate']) {
                $worstWeekday = ['label' => $weekdayLabels[$key] ?? $key, 'rate' => $rate];
            }
        }

        if ($worstWeekday !== null) {
            $recommendations[] = [
                'title' => 'Najczęściej pomijany dzień',
                'text' => $worstWeekday['label'] . ' (' . $worstWeekday['rate'] . '% realizacji)',
                'type' => 'warning',
            ];
        }

        if ($bestWeekday !== null) {
            $recommendations[] = [
                'title' => 'Najmocniejszy dzień',
                'text' => $bestWeekday['label'] . ' (' . $bestWeekday['rate'] . '% realizacji)',
                'type' => 'success',
            ];
        }

        if (count($weeklyRates) >= 2) {
            $lastRate = (int) $weeklyRates[count($weeklyRates) - 1];
            $prevRate = (int) $weeklyRates[count($weeklyRates) - 2];
            $delta = $lastRate - $prevRate;
            if ($delta !== 0) {
                $recommendations[] = [
                    'title' => 'Trend tygodniowy',
                    'text' => ($delta > 0 ? 'Wzrost' : 'Spadek') . ' vs poprzedni tydzień: ' . ($delta > 0 ? '+' : '') . $delta . ' p.p.',
                    'type' => $delta > 0 ? 'success' : 'danger',
                ];
            }
        }

        $lowestWorkout = null;
        foreach ($workoutNameStats as $item) {
            if ((int) $item['count'] < 1) {
                continue;
            }
            $avgRate = (int) round($item['rateSum'] / $item['count']);
            if ($lowestWorkout === null || $avgRate < $lowestWorkout['rate']) {
                $lowestWorkout = [
                    'name' => $item['name'],
                    'rate' => $avgRate,
                    'count' => (int) $item['count'],
                ];
            }
        }
        if ($lowestWorkout !== null) {
            $recommendations[] = [
                'title' => 'Najczęściej niedomykany trening',
                'text' => $lowestWorkout['name'] . ' (średnio ' . $lowestWorkout['rate'] . '%, prób: ' . $lowestWorkout['count'] . ')',
                'type' => 'warning',
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'title' => 'Brak rekomendacji',
                'text' => 'Dodaj więcej danych treningowych, aby zobaczyć konkretne wskazówki.',
                'type' => 'info',
            ];
        }

        return $this->render('progress', [
            'totals' => [
                'all' => $totalWorkouts,
                'completed' => $completedWorkouts,
                'inProgress' => $inProgressWorkouts,
                'notStarted' => $notStartedWorkouts,
                'completionPercent' => $completionPercent,
            ],
            'incompleteWorkouts' => $incompleteWorkouts,
            'weeklyLabels' => $weeklyLabels,
            'weeklyPlanned' => $weeklyPlanned,
            'weeklyCompleted' => $weeklyCompleted,
            'weekdayLabels' => array_values($weekdayLabels),
            'weekdayCompletionRate' => $weekdayCompletionRate,
            'recommendations' => array_slice($recommendations, 0, 4),
        ]);
    }
}
