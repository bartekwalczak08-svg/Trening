<?php

/**
 * Service responsible for building data for home page dashboard.
 */
namespace app\services;

use app\models\WorkoutExercises;
use app\models\Workouts;

class HomePageService
{
    /**
     * Builds view model payload for site/index.
     *
     * @param int|null $userId
     * @param string $calendarFilter
     * @return array
     */
    public function buildIndexData($userId, $calendarFilter)
    {
        $calendarFilter = $this->normalizeCalendarFilter((string) $calendarFilter);

        $data = [
            'groupedWorkouts' => [],
            'monthlyWorkouts' => 0,
            'todayWorkouts' => [],
            'nextWorkoutDayLabel' => null,
            'activeDaysCount' => 0,
            'totalExercisesPlanned' => 0,
            'recentlyUpdatedWorkouts' => [],
            'calendarFilter' => $calendarFilter,
            'visibleWeekdays' => Workouts::weekdayOrder(),
            'weeklyCompletedWorkouts' => 0,
            'weeklySkippedWorkouts' => 0,
            'weeklyCompletionPercent' => 0,
        ];

        if ($userId === null) {
            return $data;
        }

        $userId = (int) $userId;

        $workouts = Workouts::allForUser($userId);

        $data['recentlyUpdatedWorkouts'] = Workouts::recentUpdatedForUser($userId, 5);

        $data['groupedWorkouts'] = Workouts::groupByWeekday($workouts);

        $monthStart = strtotime(date('Y-m-01 00:00:00'));
        $data['monthlyWorkouts'] = Workouts::countForUser($userId, $monthStart);

        $todayWeekday = Workouts::weekdayKeyFromNumber((int) date('N'));
        $data['todayWorkouts'] = $data['groupedWorkouts'][$todayWeekday] ?? [];

        $weekdayOrder = Workouts::weekdayOrder();
        $todayPosition = array_search($todayWeekday, $weekdayOrder, true);
        $daysUpToToday = $todayPosition === false ? $weekdayOrder : array_slice($weekdayOrder, 0, $todayPosition + 1);
        $plannedToDate = 0;
        $completedToDate = 0;

        foreach ($daysUpToToday as $weekday) {
            foreach ($data['groupedWorkouts'][$weekday] ?? [] as $item) {
                $plannedToDate++;
                if ($item->hasAttribute('is_completed') && (int) $item->getAttribute('is_completed') === 1) {
                    $completedToDate++;
                }
            }
        }

        $data['weeklyCompletedWorkouts'] = $completedToDate;
        $data['weeklySkippedWorkouts'] = max(0, $plannedToDate - $completedToDate);
        $data['weeklyCompletionPercent'] = $plannedToDate > 0
            ? (int) round(($completedToDate / $plannedToDate) * 100)
            : 0;

        foreach (Workouts::weekdayOrder() as $weekday) {
            if (!empty($data['groupedWorkouts'][$weekday])) {
                $data['activeDaysCount']++;
            }
        }

        $data['totalExercisesPlanned'] = (int) WorkoutExercises::find()
            ->joinWith('workout')
            ->where(['workouts.user_id' => $userId])
            ->count();

        foreach (Workouts::weekdayOrder() as $weekday) {
            if ($weekday === $todayWeekday) {
                continue;
            }
            if (!empty($data['groupedWorkouts'][$weekday])) {
                $data['nextWorkoutDayLabel'] = Workouts::weekdayOptions()[$weekday] ?? null;
                break;
            }
        }

        if ($calendarFilter === 'today') {
            $data['visibleWeekdays'] = [$todayWeekday];
        } elseif ($calendarFilter === 'weekend') {
            $data['visibleWeekdays'] = [Workouts::WEEKDAY_SATURDAY, Workouts::WEEKDAY_SUNDAY];
        }

        return $data;
    }

    /**
     * Normalizes calendar filter to allowed values.
     *
     * @param string $calendarFilter
     * @return string
     */
    private function normalizeCalendarFilter($calendarFilter)
    {
        $allowedFilters = ['all', 'today', 'weekend'];
        if (!in_array($calendarFilter, $allowedFilters, true)) {
            return 'all';
        }

        return $calendarFilter;
    }
}
