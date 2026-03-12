<?php

/**
 * Extended Workouts model with weekday helpers.
 */
namespace app\models\extended;

use app\helpers\WorkoutQueryHelper;
use app\helpers\WorkoutWeekdayHelper;
use Yii;
use yii\db\ActiveQuery;

class ExtendedWorkouts extends \app\models\generated\GeneratedWorkouts
{
	public const WEEKDAY_MONDAY = 'monday';
	public const WEEKDAY_TUESDAY = 'tuesday';
	public const WEEKDAY_WEDNESDAY = 'wednesday';
	public const WEEKDAY_THURSDAY = 'thursday';
	public const WEEKDAY_FRIDAY = 'friday';
	public const WEEKDAY_SATURDAY = 'saturday';
	public const WEEKDAY_SUNDAY = 'sunday';

	public static function weekdayOptions()
	{
		return WorkoutWeekdayHelper::weekdayOptions();
	}

	public static function weekdayOrder()
	{
		return WorkoutWeekdayHelper::weekdayOrder();
	}

	public function rules()
	{
		return array_merge(parent::rules(), [
			['weekday', 'default', 'value' => self::WEEKDAY_MONDAY],
			['weekday', 'in', 'range' => static::weekdayOrder()],
		]);
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), [
			'name' => Yii::t('app', 'Nazwa'),
			'description' => Yii::t('app', 'Opis'),
			'weekday' => Yii::t('app', 'Dzień tygodnia'),
		]);
	}

	public function getWeekdayLabel()
	{
		$options = static::weekdayOptions();

		return $options[$this->weekday] ?? Yii::t('app', 'Nieznany');
	}

	/**
	 * Groups workouts by weekday and keeps all weekday keys initialized.
	 *
	 * @param array $workouts
	 * @return array
	 */
	public static function groupByWeekday(array $workouts)
	{
		return WorkoutWeekdayHelper::groupByWeekday($workouts);
	}

	/**
	 * Maps ISO weekday number (1-7) to workout weekday key.
	 *
	 * @param int $dayNumber
	 * @return string
	 */
	public static function weekdayKeyFromNumber($dayNumber)
	{
		return WorkoutWeekdayHelper::weekdayKeyFromNumber($dayNumber);
	}

	/**
	 * Base query scoped to workouts owned by the given user.
	 *
	 * @param int $userId
	 * @return ActiveQuery
	 */
	public static function queryForUser($userId)
	{
		return WorkoutQueryHelper::queryForUser($userId);
	}

	/**
	 * Returns all workouts for a user ordered by creation date desc.
	 *
	 * @param int $userId
	 * @return static[]
	 */
	public static function allForUser($userId)
	{
		return WorkoutQueryHelper::allForUser($userId);
	}

	/**
	 * Returns workouts for a user with related exercises eagerly loaded.
	 *
	 * @param int $userId
	 * @return static[]
	 */
	public static function allForUserWithExercises($userId)
	{
		return WorkoutQueryHelper::allForUserWithExercises($userId);
	}

	/**
	 * Returns most recently updated workouts for a user.
	 *
	 * @param int $userId
	 * @param int $limit
	 * @return static[]
	 */
	public static function recentUpdatedForUser($userId, $limit = 5)
	{
		return WorkoutQueryHelper::recentUpdatedForUser($userId, $limit);
	}

	/**
	 * Returns most recently created workouts for a user.
	 *
	 * @param int $userId
	 * @param int $limit
	 * @return static[]
	 */
	public static function recentForUser($userId, $limit = 5)
	{
		return WorkoutQueryHelper::recentForUser($userId, $limit);
	}

	/**
	 * Returns latest workout for user, optionally only since given timestamp.
	 *
	 * @param int $userId
	 * @param int|null $sinceTimestamp
	 * @return static|null
	 */
	public static function latestForUser($userId, $sinceTimestamp = null)
	{
		return WorkoutQueryHelper::latestForUser($userId, $sinceTimestamp);
	}

	/**
	 * Counts workouts for user, optionally only since given timestamp.
	 *
	 * @param int $userId
	 * @param int|null $sinceTimestamp
	 * @return int
	 */
	public static function countForUser($userId, $sinceTimestamp = null)
	{
		return WorkoutQueryHelper::countForUser($userId, $sinceTimestamp);
	}

	/**
	 * Returns created_at timestamps for all workouts of a user.
	 *
	 * @param int $userId
	 * @return int[]
	 */
	public static function createdTimestampsForUser($userId)
	{
		return WorkoutQueryHelper::createdTimestampsForUser($userId);
	}
}