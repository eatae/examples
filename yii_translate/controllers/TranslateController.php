<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\base\Module;
use app\assets\AdminAsset;

use app\modules\admin\models\Translate;
use app\modules\admin\models\TranslateSource;
use app\modules\admin\models\TranslateProtocol;

use app\components\exceptions\CustomException;
use app\components\exceptions\TranslateException;



/**
 * TranslateController implements the CRUD actions for Translate and TranslateSource models.
 *
 * * [POST]:
 *      'id' => '1'
 *      'category' => 'test'
 *      'message' => 'test'
 *      'lang_en' => 'ru'
 *      'translation_en' => 'Test in english'
 *      'lang_ru' => 'ru'
 *      'translation_ru' => 'Тест на русском'
 *      'lang_new' => 'fr'
 *      'translation_fr' => 'Тест на france'
 *
 */
class TranslateController extends Controller
{


    const RESPONSE_ERROR = 'error';
    const RESPONSE_WARNING = 'warning';
    const RESPONSE_SUCCESS = 'success';


    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save' => ['POST'],
                    'delete' => ['POST'],
                    'delete-lang' => ['POST'],
                ],
            ],
        ];
    }



    public function actionIndex()
    {
        /* js, css files */
        $this->view->registerJsFile('@web/js/admin/translate/index.js',  ['depends' => [AdminAsset::class]] );
        $this->view->registerCssFile('@web/css/admin/translate/index.css',  ['depends' => [AdminAsset::class]] );

        $translates = TranslateSource::getAllTranslation(true);
        return $this->render('index', compact('translates'));
    }






    public function actionSave()
    {
        /* protocol */
        $protocol = new TranslateProtocol(Yii::$app->request->post(), TranslateProtocol::REQUIREMENT_SAVE);
        /* Source */
        $source = (array_key_exists('id', $protocol->sourceData)) ?  TranslateSource::findOne($protocol->sourceData['id']) : new TranslateSource();
        if ( !$source ) {
            throw new TranslateException(self::RESPONSE_ERROR, 'Нет такой записи.');
        }
        $source->setAttributes($protocol->sourceData);
        if ( !$source->validate() ) {
            throw new TranslateException(self::RESPONSE_ERROR, 'Не валидные данные ресурса');
        }
        /* save source */
        $source->save();

        /* Translates */
        $id = $source->id;
        foreach ($protocol->translatesData as $lang => $text) {
            $translate = array_shift(Translate::getTranslate($id, $lang));
            $translate->setAttributes(['id'=>$id, 'language'=>$lang, 'translation'=>$text]);
            if ( !$translate->validate() ) {
                throw new TranslateException(self::RESPONSE_ERROR, 'Не валидные данные перевода');
            }
            /* save translates */
            $translate->save();
        }

        return $this->asJson(self::getResponse(self::RESPONSE_SUCCESS,'Перевод записан', $id));
    }





    public function actionDelete()
    {
        $translates = Translate::getTranslate(Yii::$app->request->post('id'));
        $source = TranslateSource::findOne(Yii::$app->request->post('id'));

        if ( !$translates || !$source ) {
            throw new TranslateException(self::RESPONSE_ERROR, 'Данные указаны неверно.');
        }
        foreach($translates as $translate) {
            $translate->delete();
        }
        $source->delete();

        return $this->asJson(self::getResponse(self::RESPONSE_SUCCESS,'Перевод полностью удален'));
    }





    public function actionDeleteLang()
    {
        $protocol = new TranslateProtocol(Yii::$app->request->post(), TranslateProtocol::REQUIREMENT_DELETE_LANG);
        $oneData = array_shift($protocol->translatesData);
        $translate = array_shift(Translate::getTranslate($oneData['id'], $oneData['language']));
        if ( !$translate ) {
            throw new TranslateException(self::RESPONSE_ERROR, 'Данные указаны неверно.');
        }
        $translate->delete();
        return $this->asJson(self::getResponse(self::RESPONSE_SUCCESS,'Перевод удален'));
    }





    public static function getResponse($status, $message, $id = null) {
        $result = compact('status', 'message');
        if (null != $id) { $result['id'] = $id; }
        return $result;
    }




}
