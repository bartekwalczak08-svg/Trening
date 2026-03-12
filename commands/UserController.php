<?php

/**
 * Console commands for user account lifecycle maintenance.
 */

namespace app\commands;

use app\models\ContactMessage;
use app\models\User;
use yii\console\Controller;

class UserController extends Controller
{
    /**
     * Deletes accounts pending deletion older than 30 days.
     *
     * Usage: php yii user/delete-old
     *
     * @return int
     */
    public function actionDeleteOld()
    {
        $schema = User::getDb()->schema->getTableSchema(User::tableName(), true);
        if ($schema === null || !isset($schema->columns['status']) || !isset($schema->columns['delete_requested_at']) || !isset($schema->columns['deactivated_at'])) {
            $this->stderr('Required columns status/delete_requested_at/deactivated_at are missing. Run migrations first.' . PHP_EOL);

            return self::EXIT_CODE_ERROR;
        }

        $now = time();
        $deactivatedThreshold = $now - (60 * 86400);
        $deactivatedUsers = User::find()
            ->where(['status' => User::STATUS_DEACTIVATED])
            ->andWhere(['<=', 'deactivated_at', $deactivatedThreshold])
            ->all();

        $promotedCount = 0;
        foreach ($deactivatedUsers as $deactivatedUser) {
            if ($deactivatedUser->requestAccountDeletion()) {
                $promotedCount++;
            }
        }

        $threshold = $now - (30 * 86400);

        $users = User::find()
            ->where(['status' => User::STATUS_PENDING_DELETE])
            ->andWhere(['<=', 'delete_requested_at', $threshold])
            ->all();

        $deletedCount = 0;

        foreach ($users as $user) {
            $transaction = User::getDb()->beginTransaction();

            try {
                // Remove user-owned contact messages before deleting account.
                ContactMessage::deleteAll(['email' => (string) $user->email]);

                if ($user->delete() === false) {
                    throw new \RuntimeException('Failed to delete user #' . (int) $user->id);
                }

                $transaction->commit();
                $deletedCount++;
            } catch (\Throwable $e) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }

                $this->stderr('Error deleting user #' . (int) $user->id . ': ' . $e->getMessage() . PHP_EOL);
            }
        }

        $this->stdout('Moved deactivated -> pending_delete: ' . $promotedCount . PHP_EOL);
        $this->stdout('Deleted users: ' . $deletedCount . PHP_EOL);

        return self::EXIT_CODE_NORMAL;
    }
}
