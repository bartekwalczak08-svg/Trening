<?php

/**
 * Helper for workout weekday-related utility logic.
 */
namespace app\helpers;

use app\models\Workouts;
use Yii;

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
            Workouts::WEEKDAY_MONDAY => Yii::t('app', 'Poniedziałek'),
            Workouts::WEEKDAY_TUESDAY => Yii::t('app', 'Wtorek'),
            Workouts::WEEKDAY_WEDNESDAY => Yii::t('app', 'Środa'),
            Workouts::WEEKDAY_THURSDAY => Yii::t('app', 'Czwartek'),
            Workouts::WEEKDAY_FRIDAY => Yii::t('app', 'Piątek'),
            Workouts::WEEKDAY_SATURDAY => Yii::t('app', 'Sobota'),
            Workouts::WEEKDAY_SUNDAY => Yii::t('app', 'Niedziela'),
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
