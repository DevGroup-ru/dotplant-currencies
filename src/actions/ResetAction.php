<?php
namespace DotPlant\Currencies\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidParamException;

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
            throw new InvalidParamException(Yii::t('dotplant.currencies', "Class \"{$this->className}\" not found!"));
        }
        if (false === file_exists($this->storage)) {
            throw new InvalidParamException(
                Yii::t('dotplant.currencies', "\"{$this->storage}\" is not valid \"{$this->itemName}\" storage file!")
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
            'info',  ucfirst($this->itemName) . Yii::t('dotplant.currencies', ' reset to defaults.')
        );
        $this->controller->redirect($this->returnUrl);
    }
}