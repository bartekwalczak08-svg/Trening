<?php

/**
 * Helper for reusable user-scoped workout queries.
 */
namespace app\helpers;

use app\models\Workouts;
use yii\db\ActiveQuery;

class WorkoutQueryHelper
{
    /**
     * Base query scoped to workouts owned by the given user.
     *
     * @param int $userId
     * @return ActiveQuery
     */
    public static function queryForUser($userId)
    {
        return Workouts::find()->where(['user_id' => (int) $userId]);
    }

    /**
     * Returns all workouts for a user ordered by creation date desc.
     *
     * @param int $userId
     * @return Workouts[]
     */
    public static function allForUser($userId)
    {
        return static::queryForUser($userId)
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
    }

    /**
     * Returns workouts for a user with related exercises eagerly loaded.
     *
     * @param int $userId
     * @return Workouts[]
     */
    public static function allForUserWithExercises($userId)
    {
        return static::queryForUser($userId)
            ->with('workoutExercises')
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
    }

    /**
     * Returns most recently updated workouts for a user.
     *
     * @param int $userId
     * @param int $limit
     * @return Workouts[]
     */
    public static function recentUpdatedForUser($userId, $limit = 5)
    {
        return static::queryForUser($userId)
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit((int) $limit)
            ->all();
    }

    /**
     * Returns most recently created workouts for a user.
     *
     * @param int $userId
     * @param int $limit
     * @return Workouts[]
     */
    public static function recentForUser($userId, $limit = 5)
    {
        return static::queryForUser($userId)
            ->orderBy(['created_at' => SORT_DESC])
            ->limit((int) $limit)
            ->all();
    }

    /**
     * Returns latest workout for user, optionally only since given timestamp.
     *
     * @param int $userId
     * @param int|null $sinceTimestamp
     * @return Workouts|null
     */
    public static function latestForUser($userId, $sinceTimestamp = null)
    {
        $query = static::queryForUser($userId)
            ->orderBy(['created_at' => SORT_DESC]);

        if ($sinceTimestamp !== null) {
            $query->andWhere(['>=', 'created_at', (int) $sinceTimestamp]);
        }

        return $query->one();
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
        $query = static::queryForUser($userId);

        if ($sinceTimestamp !== null) {
            $query->andWhere(['>=', 'created_at', (int) $sinceTimestamp]);
        }

        return (int) $query->count();
    }

    /**
     * Returns created_at timestamps for all workouts of a user.
     *
     * @param int $userId
     * @return int[]
     */
    public static function createdTimestampsForUser($userId)
    {
        return static::queryForUser($userId)
            ->select('created_at')
            ->orderBy(['created_at' => SORT_DESC])
            ->column();
    }
}
