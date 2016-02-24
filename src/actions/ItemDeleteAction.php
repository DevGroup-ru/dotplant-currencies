<?php
namespace DotPlant\Currencies\actions;

use DotPlant\Currencies\models\CurrencyRateProvider;
use DotPlant\Currencies\models\Currency;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use yii\base\Action;
use Yii;

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
            throw new InvalidParamException(
                Yii::t(
                    'dotplant.currencies',
                    'Class "{className}" not found!',
                    ['className' => $this->className]
                )
            );
        }
        if (false === file_exists(Yii::getAlias($this->storage))) {
            throw new InvalidParamException(
                Yii::t(
                    'dotplant.currencies',
                    '"{storage}" is not valid "{itemName}" storage file!',
                    ['storage' => $this->storage, 'itemName' => $this->itemName]

                )
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
                mb_convert_case($this->itemName, MB_CASE_TITLE, "UTF-8") . Yii::t('dotplant.currencies', ' {name} not found!', ['name' => $itemName])
            );
        }
    }

    /**
     * Removes given item from storage
     */
    public function run()
    {

        if (true === $this->model->delete()) {
            Yii::$app->session->setFlash(
                'success',
                mb_convert_case($this->itemName, MB_CASE_TITLE, "UTF-8") . Yii::t(
                    'dotplant.currencies',
                    ' {name} successfully deleted!', ['name' => $this->model->name]
                )
            );
            return $this->controller->redirect($this->returnUrl);
        } else {
            Yii::$app->session->setFlash(
                'error',
                Yii::t('dotplant.currencies', 'An error occurred while deleting {name}!', ['name' => $this->model->name])
            );
        }
    }
}