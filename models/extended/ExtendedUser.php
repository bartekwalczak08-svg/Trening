<?php

/**
 * Extended User model with authentication helpers.
 */
namespace app\models\extended;

use Yii;
use yii\behaviors\TimestampBehavior;

class ExtendedUser extends \app\models\generated\GeneratedUser implements \yii\web\IdentityInterface
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DEACTIVATED = 'deactivated';
    public const STATUS_PENDING_DELETE = 'pending_delete';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Sets password hash from plain-text password.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" cookie hash.
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        $query = static::find()->where(['id' => $id]);
        $model = new static();
        if ($model->hasAttribute('status')) {
            $query->andWhere(['status' => self::STATUS_ACTIVE]);
        }

        return $query->one();
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $query = static::find()->where(['access_token' => $token]);
        $model = new static();
        if ($model->hasAttribute('status')) {
            $query->andWhere(['status' => self::STATUS_ACTIVE]);
        }

        return $query->one();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Returns current account status or active fallback when column is unavailable.
     *
     * @return string
     */
    public function getAccountStatus()
    {
        if (!$this->hasAttribute('status')) {
            return self::STATUS_ACTIVE;
        }

        $status = (string) $this->getAttribute('status');

        return $status !== '' ? $status : self::STATUS_ACTIVE;
    }

    /**
     * Marks account as deactivated.
     *
     * @return bool
     */
    public function deactivateAccount()
    {
        if (!$this->hasAttribute('status')) {
            return true;
        }

        $this->setAttribute('status', self::STATUS_DEACTIVATED);
        if ($this->hasAttribute('deactivated_at')) {
            $this->setAttribute('deactivated_at', time());
        }
        if ($this->hasAttribute('delete_requested_at')) {
            $this->setAttribute('delete_requested_at', null);
        }

        return $this->save(false, ['status', 'deactivated_at', 'delete_requested_at', 'updated_at']);
    }

    /**
     * Marks account as pending delete and starts grace-period timer.
     *
     * @return bool
     */
    public function requestAccountDeletion()
    {
        if (!$this->hasAttribute('status')) {
            return false;
        }

        $this->setAttribute('status', self::STATUS_PENDING_DELETE);
        if ($this->hasAttribute('deactivated_at')) {
            $this->setAttribute('deactivated_at', null);
        }
        if ($this->hasAttribute('delete_requested_at')) {
            $this->setAttribute('delete_requested_at', time());
        }

        return $this->save(false, ['status', 'deactivated_at', 'delete_requested_at', 'updated_at']);
    }

    /**
     * Restores account to active state and clears pending deletion timestamp.
     *
     * @return bool
     */
    public function activateAccount()
    {
        if (!$this->hasAttribute('status')) {
            return true;
        }

        $this->setAttribute('status', self::STATUS_ACTIVE);
        if ($this->hasAttribute('deactivated_at')) {
            $this->setAttribute('deactivated_at', null);
        }
        if ($this->hasAttribute('delete_requested_at')) {
            $this->setAttribute('delete_requested_at', null);
        }

        return $this->save(false, ['status', 'deactivated_at', 'delete_requested_at', 'updated_at']);
    }

    /**
     * Returns true when account is marked as deactivated.
     *
     * @return bool
     */
    public function isDeactivated()
    {
        return $this->getAccountStatus() === self::STATUS_DEACTIVATED;
    }

    /**
     * Returns true when account is marked for delayed deletion.
     *
     * @return bool
     */
    public function isPendingDelete()
    {
        return $this->getAccountStatus() === self::STATUS_PENDING_DELETE;
    }

    /**
     * Returns true when pending-delete grace period has expired.
     *
     * @param int $days
     * @return bool
     */
    public function isPendingDeleteExpired($days = 30)
    {
        if (!$this->isPendingDelete() || !$this->hasAttribute('delete_requested_at')) {
            return false;
        }

        $requestedAt = (int) ($this->getAttribute('delete_requested_at') ?? 0);
        if ($requestedAt <= 0) {
            return false;
        }

        return $requestedAt <= (time() - ((int) $days * 86400));
    }

    /**
     * Returns true when account has been deactivated for the given number of days.
     *
     * @param int $days
     * @return bool
     */
    public function isDeactivatedForDays($days = 60)
    {
        if (!$this->isDeactivated()) {
            return false;
        }

        $sourceTimestamp = 0;
        if ($this->hasAttribute('deactivated_at')) {
            $sourceTimestamp = (int) ($this->getAttribute('deactivated_at') ?? 0);
        }
        if ($sourceTimestamp <= 0 && $this->hasAttribute('updated_at')) {
            $sourceTimestamp = (int) ($this->getAttribute('updated_at') ?? 0);
        }
        if ($sourceTimestamp <= 0) {
            return false;
        }

        return $sourceTimestamp <= (time() - ((int) $days * 86400));
    }
}