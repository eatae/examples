<?php


namespace app\modules\admin\models;

use app\components\exceptions\CustomException;

use app\components\exceptions\TranslateException;
use app\components\CustomHelper;
use app\modules\admin\controllers\TranslateController;




class TranslateProtocol
{

    public $sourceData = [];
    public $translatesData = [];

    public $requirementName = '';
    public $requirement = [];
    public $innerData = [];

    const REQUIREMENT_SAVE = 'save';
    const REQUIREMENT_DELETE = 'delete';
    const REQUIREMENT_DELETE_LANG = 'delete_lang';

    const REQUIREMENTS = [
        'save' => ['category', 'message'],
        'delete' => ['id'],
        'delete_lang' => ['id', 'language'],
    ];


    
    public function __construct(array $innerData, string $requirementName)
    {
        $this->requirementName = $requirementName;
        $this->requirement = $this->getRequirement($requirementName);
        $this->innerData = $this->getInnerData($innerData, $this->requirement);
        $this->sourceData = $this->getSourceData($this->innerData, $this->requirement);
        $this->translatesData = $this->getTranslatesData($this->innerData);
    }



    protected function getRequirement(string $requirementName) {
        if ( !array_key_exists($requirementName, self::REQUIREMENTS)) {
            $keys = implode(', ', array_keys(self::REQUIREMENTS));
            throw (new CustomException())->errorExcept('Please specify an existing name for requirements: '.$keys);
        }
        return self::REQUIREMENTS[$requirementName];
    }



    protected function getInnerData(array $innerData, array $requirement) {
        if ( !CustomHelper::checkArrayFields( $requirement, $innerData) ) {
            throw new TranslateException( TranslateController::RESPONSE_ERROR, 'Не заполнены необходимые поля');
        };
        return $innerData;
    }




    public function getSourceData(array $innerData, array $requirement) {
        $result = [];
        foreach ($requirement as $name) {
            if (array_key_exists($name, $innerData)) {
                $result[$name] = $innerData[$name];
            }
        }
        /* for save */
        if ( array_key_exists('id', $innerData) && !empty($innerData['id']) ) {
            $result['id'] = $innerData['id'];
        }
        return $result;
    }




    public function getTranslatesData(array $innerData) {
        $result = [];
        /* for delete_lang */
        if ($this->requirementName == self::REQUIREMENT_DELETE_LANG) {
            return [$innerData];
        }

        foreach ($innerData as $key => $val) {
            $parts = explode('_', $key);
            /* если ключ содержит lang_* и есть ключ translate_* */
            if ( 'lang' == $parts[0] && array_key_exists('translation_'.$parts[1], $innerData) ) {
                $langKey = $val;
                $translate_key = 'translation_'.$parts[1];
                $langVal = $innerData[$translate_key];
                /* проверка нет ли повторных языков */
                if (array_key_exists($langKey, $result)) {
                    throw new TranslateException('warning', 'Укажите корректно LANG');
                }
                $result[$langKey] = $langVal;
            }
        }
        if ( empty($result) ) {
            throw new TranslateException( TranslateController::RESPONSE_ERROR, 'Не заполнены необходимые поля');
        };
        return $result;
    }

}