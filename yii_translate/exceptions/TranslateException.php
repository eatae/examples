<?php

namespace app\components\exceptions;

use Yii;
use yii\web\Response;
use yii\base\ExitException;

use app\modules\admin\models\TranslateProtocol;
use app\modules\admin\controllers\TranslateController;


class TranslateException extends ExitException
{

    const STATUS_ERROR = 'error';
    const STATUS_WARNING = 'warning';
    const STATUS_SUCCESS = 'success';

    public function __construct($status, $message, $code = 0, \Exception $previous = null)
    {
        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data = TranslateController::getResponse($status, $message);

        parent::__construct($status = '', $message, $code, $previous);
    }
}