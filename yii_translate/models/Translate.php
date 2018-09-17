<?php

namespace app\modules\admin\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "translate".
 *
 * @property int $id
 * @property string $language
 * @property string $translation
 *
 */
class Translate extends ActiveRecord
{
    /**
     * {@inheritdoc}
     *
     */
    public static function tableName()
    {
        return 'translate';
    }

    /**
     * {@inheritdoc}
     *
     */
    public function rules()
    {
        return [
            [['id', 'language', 'translation'], 'required'],
            [['id'], 'integer'],
            [['language'], 'string', 'max' => 16],
            [['translation'], 'string', 'min' => 2],
            [['id', 'language'], 'unique', 'targetAttribute' => ['id', 'language']],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => TranslateSource::class, 'targetAttribute' => ['id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     *
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'language' => Yii::t('app', 'Language'),
            'translation' => Yii::t('app', 'Translation'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     *
     */
    public function getId()
    {
        return $this->hasOne(TranslateSource::class, ['id' => 'id']);
    }


    /**
     * @param null $id
     * @param null $lang
     * @return array
     *
     */
    public static function getTranslate($id = null, $lang = null)
    {
        if ($id == null && $lang == null) {
            return [new self()];
        }
        $query = self::find();
        if ($id) {
            $query->where(['id'=>$id]);
        }
        if ($lang) {
            $query->andWhere(['language'=>$lang]);
        }
        $result = $query->all();

        return $result ?: [new self()];
    }

}
