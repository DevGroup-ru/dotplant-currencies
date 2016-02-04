<?php

namespace DotPlant\Currencies\actions;

use DevGroup\AdminUtils\actions\FormCombinedAction;
use DotPlant\Currencies\models\Currency;
use DotPlant\Currencies\models\CurrencyRateProvider;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;

class ItemEditAction extends FormCombinedAction
{
    /** @var Currency | CurrencyRateProvider */
    public $model = null;

    /** @var string */
    public $className = '';

    /** @var string  */
    public $itemName = 'item';

    /** @var string  */
    public $editView = '';

    /** @var string  */
    public $storage = '';
    /** @var  string */
    private $returnUrl = '';

    public $formOptions = [];

    /**
     * @inheritdoc
     */
    public function beforeActionRun()
    {
        parent::beforeActionRun();
        if (false === class_exists($this->className)) {
            throw new InvalidParamException(Yii::t('dotplant.currencies', "Class \"{$this->className}\" not found!"));
        }
        if (false === file_exists(Yii::getAlias($this->storage))) {
            throw new InvalidParamException(
                Yii::t('dotplant.currencies', "\"{$this->storage}\" is not valid \"{$this->itemName}\" storage file!")
            );
        }
        $itemName = Yii::$app->request->get('id', '');
        $this->returnUrl = Yii::$app->request->get('returnUrl', '');
        $class = $this->className;
        if (true === empty($itemName)) {
            $this->model = new $class(['scenario' => $class::SCENARIO_NEW]);
            $this->model->setDefaults();
        } else {
            $this->model = $class::getByName($itemName);
        }
        if (null === $this->model) {
            throw new NotFoundHttpException(
                ucfirst($this->itemName) . Yii::t('dotplant.currencies', ' {name} not found!', ['name' => $itemName])
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function defineParts()
    {
        return [
            'saveData' => [
                'function' => 'saveData',
            ],
            'renderSectionForm' => [
                'function' => 'renderSectionForm',
                'title' => Yii::t('dotplant.currencies', "Edit ") . $this->itemName,
                'icon' => 'fa fa-cogs',
                'footer' => $this->getFooter(),
            ],
        ];
    }

    /**
     * Saves item model
     */
    public function saveData()
    {
        if ($this->model->load(Yii::$app->request->post()) && $this->model->validate()) {
            if (true === $this->model->save()) {
                Yii::$app->session->setFlash(
                    'success',
                    ucfirst($this->itemName) . Yii::t('dotplant.currencies', ' {name} successfully updated!', ['name' => $this->model->name])
                );
                $this->controller->redirect($this->returnUrl);
            } else {
                Yii::$app->session->setFlash(
                    'error',
                    Yii::t('dotplant.currencies', 'An error occurred while saving {name}!', ['name' => $this->model->name])
                );
            }
        }
    }

    /**
     * Renders form for given model
     *
     * @return string
     */
    public function renderSectionForm()
    {
        return $this->render(
            $this->editView,
            [
                'model' => $this->model,
                'form' => $this->form,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getFooter()
    {
        return Html::submitButton(
            '<i class="fa fa-floppy-o"></i>&nbsp;' .
            (Yii::t('dotplant.currencies', 'Save')),
            ['class' => 'btn btn-primary pull-right']
        );
    }

    /**
     * @inheritdoc
     */
    public function breadcrumbs()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function title()
    {
        return Yii::t('dotplant.currencies', "Edit ") . $this->itemName;
    }
}
