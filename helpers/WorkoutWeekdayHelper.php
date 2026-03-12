<?php

/**
 * Helper for workout weekday-related utility logic.
 */
namespace app\helpers;

use app\models\Workouts;

class WorkoutWeekdayHelper
{
    /**
     * Returns localized weekday options used by workouts.
     *
     * @return array<string, string>
     */
    public static function weekdayOptions()
    {
        return [
            Workouts::WEEKDAY_MONDAY => 'Poniedziałek',
            Workouts::WEEKDAY_TUESDAY => 'Wtorek',
            Workouts::WEEKDAY_WEDNESDAY => 'Środa',
            Workouts::WEEKDAY_THURSDAY => 'Czwartek',
            Workouts::WEEKDAY_FRIDAY => 'Piątek',
            Workouts::WEEKDAY_SATURDAY => 'Sobota',
            Workouts::WEEKDAY_SUNDAY => 'Niedziela',
        ];
    }

    /**
     * Returns weekday keys in display order.
     *
     * @return string[]
     */
    public static function weekdayOrder()
    {
        return array_keys(static::weekdayOptions());
    }

    /**
     * Maps ISO weekday number (1-7) to workout weekday key.
     *
     * @param int $dayNumber
     * @return string
     */
    public static function weekdayKeyFromNumber($dayNumber)
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

        return $map[(int) $dayNumber] ?? Workouts::WEEKDAY_MONDAY;
    }

    /**
     * Groups workouts by weekday and keeps all weekday keys initialized.
     *
     * @param array $workouts
     * @return array<string, array>
     */
    public static function groupByWeekday(array $workouts)
    {
        $groupedWorkouts = [];
        foreach (static::weekdayOrder() as $weekday) {
            $groupedWorkouts[$weekday] = [];
        }

        foreach ($workouts as $workout) {
            $weekday = $workout->weekday ?: Workouts::WEEKDAY_MONDAY;
            if (!isset($groupedWorkouts[$weekday])) {
                $groupedWorkouts[$weekday] = [];
            }

            $groupedWorkouts[$weekday][] = $workout;
        }

        return $groupedWorkouts;
    }
}
