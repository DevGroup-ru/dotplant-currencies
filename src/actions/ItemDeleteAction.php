<?php
namespace DotPlant\Currencies\actions;

use DotPlant\Currencies\helpers\CurrencyStorageHelper;
use DotPlant\Currencies\models\Currency;
use DotPlant\Currencies\models\CurrencyRateProvider;
use Yii;
use yii\base\Action;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;

class ItemDeleteAction extends Action
{
    /** @var string */
    public $storage = '';

    /** @var string */
    public $className = '';

    /** @var string */
    public $itemName = 'item';

    /** @var string */
    public $returnUrl = '';

    /** @var Currency | CurrencyRateProvider | null */
    private $model = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (false === class_exists($this->className)) {
            throw new InvalidParamException(Yii::t('dotplant.currencies', "Class \"{$this->className}\" not found!"));
        }
        if (false === file_exists(Yii::getAlias($this->storage))) {
            throw new InvalidParamException(
                Yii::t('dotplant.currencies', "\"{$this->storage}\" is not valid \"{$this->itemName}\" storage file!")
            );
        }
        $class = $this->className;
        $itemName = Yii::$app->request->get('id', '');
        if (true === empty($this->returnUrl)) {
            $this->returnUrl = Yii::$app->request->get('returnUrl', '');
        }
        $this->model = $class::getByName($itemName);
        if (null === $this->model) {
            throw new NotFoundHttpException(
                ucfirst($this->itemName) . Yii::t('dotplant.currencies', ' {name} not found!', ['name' => $itemName])
            );
        }
    }

    /**
     */
    public function run()
    {

        if (true === CurrencyStorageHelper::removeFromStorage($this->model, $this->storage)) {
            Yii::$app->session->setFlash(
                'success',
                ucfirst($this->itemName) . Yii::t(
                    'dotplant.currencies',
                    ' {name} successfully deleted!', ['name' => $this->model->name]
                )
            );
            $this->controller->redirect($this->returnUrl);
        } else {
            Yii::$app->session->setFlash(
                'error',
                Yii::t('dotplant.currencies', 'An error occurred while deleting {name}!', ['name' => $this->model->name])
            );
        }
    }
}