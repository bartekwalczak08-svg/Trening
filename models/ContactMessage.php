<?php

/**
 * Opis: Model domenowy używany w aplikacji.
 */

namespace app\models;

/**
 * Model wiadomości z formularza kontaktowego.
 *
 * Przechowuje dane nadawcy, treść zgłoszenia i status obsługi.
 */
class ContactMessage extends ActiveRecord
{
    // Lifecycle statuses used in admin list and badge colors.
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    /**
     * Zwraca nazwę tabeli przechowującej zgłoszenia kontaktowe.
     */
    public static function tableName()
    {
        return '{{%contact_messages}}';
    }

    /**
     * Reguły walidacji danych wiadomości kontaktowej.
     */
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

    /**
     * Etykiety pól używane w formularzach i widokach administracyjnych.
     */
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

    /**
     * Mapuje wartości statusu zapisane w bazie na czytelne etykiety.
     */
    public static function statusOptions()
    {
        // User-facing labels for status values stored in DB.
        return [
            self::STATUS_NEW => 'Nowe',
            self::STATUS_IN_PROGRESS => 'W trakcie',
            self::STATUS_CLOSED => 'Zamknięte',
        ];
    }

    /**
     * Zwraca czytelną etykietę aktualnego statusu wiadomości.
     */
    public function getStatusLabel()
    {
        $options = self::statusOptions();

        return $options[$this->status] ?? $this->status;
    }
}
