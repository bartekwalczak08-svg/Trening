<?php

/**
 * Opis: Model wygenerowany automatycznie na podstawie schematu bazy danych.
 */


namespace app\models\generated;

use Yii;

/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $technologies
 * @property string|null $image_url
 * @property string|null $link
 * @property int $created_at
 * @property int $updated_at
 */
// Klasa Project.
class Project extends \app\models\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    // Metoda tableName.
    public static function tableName()
    {
        return 'project';
    }

    /**
     * {@inheritdoc}
     */
    // Metoda rules.
    public function rules()
    {
        return [
            [['description', 'technologies', 'image_url', 'link'], 'default', 'value' => null],
            [['title', 'created_at', 'updated_at'], 'required'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['title', 'technologies', 'image_url', 'link'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    // Metoda attributeLabels.
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'technologies' => 'Technologies',
            'image_url' => 'Image Url',
            'link' => 'Link',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
