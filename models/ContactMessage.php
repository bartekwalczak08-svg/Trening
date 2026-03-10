<?php

/**
 * Opis: Model domenowy używany w aplikacji.
 */

namespace app\models;

// Klasa ContactMessage.
class ContactMessage extends ActiveRecord
{
    // Lifecycle statuses used in admin list and badge colors.
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    // Metoda tableName.
    public static function tableName()
    {
        return '{{%contact_messages}}';
    }

    // Metoda rules.
    public function rules()
    {
        return [
            [['name', 'email', 'subject', 'body'], 'required'],
            [['body'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['name', 'email', 'subject'], 'string', 'max' => 255],
            ['email', 'email'],
            ['status', 'in', 'range' => [self::STATUS_NEW, self::STATUS_IN_PROGRESS, self::STATUS_CLOSED]],
            ['status', 'default', 'value' => self::STATUS_NEW],
        ];
    }

    // Metoda attributeLabels.
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Imię',
            'email' => 'E-mail',
            'subject' => 'Temat',
            'body' => 'Wiadomość',
            'status' => 'Status',
            'created_at' => 'Data utworzenia',
            'updated_at' => 'Data aktualizacji',
            'deleted_at' => 'Data usunięcia',
        ];
    }

    // Metoda statusOptions.
    public static function statusOptions()
    {
        // User-facing labels for status values stored in DB.
        return [
            self::STATUS_NEW => 'Nowe',
            self::STATUS_IN_PROGRESS => 'W trakcie',
            self::STATUS_CLOSED => 'Zamknięte',
        ];
    }

    // Metoda getStatusLabel.
    public function getStatusLabel()
    {
        $options = self::statusOptions();

        return $options[$this->status] ?? $this->status;
    }
}
