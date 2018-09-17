<?php

namespace app\modules\admin\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "translate_source".
 *
 * @property int $id
 * @property string $category
 * @property string $message
 *
 * @property Translate[] $translates
 */
class TranslateSource extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'translate_source';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category', 'message'], 'required'],
            [['id'], 'integer'],
            [['message'], 'string', 'min' => 2],
            [['category'], 'string', 'max' => 255, 'min' => 2],
        ];
    }



    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category' => Yii::t('app', 'Category'),
            'message' => Yii::t('app', 'Message'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslates()
    {
        return $this->hasMany(Translate::class, ['id' => 'id']);
    }

    public static function getOneTranslation($id)
    {
        return self::find()->with('translates')->
            where(['id'=>$id])->One();
    }



    public static function getAllTranslation($sort = false)
    {
        if ($sort) {
            return self::find()->with('translates')->groupBy('category, message')->All();
        }
        return self::find()->with('translates')->All();
    }
}
