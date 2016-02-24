<?php
namespace DotPlant\Currencies\actions;

use yii\base\InvalidParamException;
use yii\base\Action;
use Yii;

class ResetAction extends Action
{
    /** @var string */
    public $storage = '';

    /** @var string */
    public $className = '';

    /** @var string */
    public $itemName = 'item';

    /** @var string */
    public $returnUrl = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->storage = Yii::getAlias($this->storage);
        if (false === class_exists($this->className)) {
            throw new InvalidParamException(
                Yii::t(
                    'dotplant.currencies',
                    'Class "{className}" not found!',
                    ['className' => $this->className]
                )
            );
        }
        if (false === file_exists($this->storage)) {
            throw new InvalidParamException(
                Yii::t(
                    'dotplant.currencies',
                    '"{storage}" is not valid "{itemName}" storage file!',
                    ['storage' => $this->storage, 'itemName' => $this->itemName]

                )
            );
        }
        if (true === empty($this->returnUrl)) {
            $this->returnUrl = Yii::$app->request->get('returnUrl', '');
        }
    }

    /**
     * Removes Currency or CurrencyRateProvider storage file
     */
    public function run()
    {
        unlink($this->storage);
        $class = $this->className;
        $class::invalidateCache();
        Yii::$app->session->setFlash(
            'info', mb_convert_case($this->itemName, MB_CASE_TITLE, "UTF-8") . Yii::t('dotplant.currencies', ' reset to defaults.')
        );
        return $this->controller->redirect($this->returnUrl);
    }
}