<?php

/**
 * Opis: Rozszerzony model aplikacji z logik biznesow.
 */


namespace app\models\extended;

// Klasa Workouts.
class Workouts extends \app\models\generated\Workouts
{
	public const WEEKDAY_MONDAY = 'monday';
	public const WEEKDAY_TUESDAY = 'tuesday';
	public const WEEKDAY_WEDNESDAY = 'wednesday';
	public const WEEKDAY_THURSDAY = 'thursday';
	public const WEEKDAY_FRIDAY = 'friday';
	public const WEEKDAY_SATURDAY = 'saturday';
	public const WEEKDAY_SUNDAY = 'sunday';

	// Metoda weekdayOptions.
	public static function weekdayOptions()
	{
		return [
			self::WEEKDAY_MONDAY => 'Poniedziałek',
			self::WEEKDAY_TUESDAY => 'Wtorek',
			self::WEEKDAY_WEDNESDAY => 'Środa',
			self::WEEKDAY_THURSDAY => 'Czwartek',
			self::WEEKDAY_FRIDAY => 'Piątek',
			self::WEEKDAY_SATURDAY => 'Sobota',
			self::WEEKDAY_SUNDAY => 'Niedziela',
		];
	}

	// Metoda weekdayOrder.
	public static function weekdayOrder()
	{
		return array_keys(static::weekdayOptions());
	}

	// Metoda rules.
	public function rules()
	{
		return array_merge(parent::rules(), [
			['weekday', 'default', 'value' => self::WEEKDAY_MONDAY],
			['weekday', 'in', 'range' => static::weekdayOrder()],
		]);
	}

	// Metoda attributeLabels.
	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), [
			'weekday' => 'Dzień tygodnia',
		]);
	}

	// Metoda getWeekdayLabel.
	public function getWeekdayLabel()
	{
		$options = static::weekdayOptions();

		return $options[$this->weekday] ?? 'Nieznany';
	}
}
