<?php

namespace DotPlant\Currencies\actions;

use DotPlant\Currencies\models\CurrencyRateProvider;
use DevGroup\AdminUtils\actions\FormCombinedAction;
use DotPlant\Currencies\models\Currency;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use kartik\icons\Icon;
use yii\helpers\Html;
use Yii;

class ItemEditAction extends FormCombinedAction
{
    /** @var Currency | CurrencyRateProvider */
    public $model = null;

    /** @var string */
    public $className = '';

    /** @var string */
    public $itemName = 'item';

    /** @var string */
    public $editView = '';

    /** @var string */
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
                mb_convert_case($this->itemName, MB_CASE_TITLE, "UTF-8") . Yii::t('dotplant.currencies', ' {name} not found!', ['name' => $itemName])
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
                'title' => Yii::t('dotplant.currencies', 'Edit') . ' ' . $this->itemName,
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
                    mb_convert_case($this->itemName, MB_CASE_TITLE, "UTF-8") . Yii::t('dotplant.currencies', ' {name} successfully updated!', ['name' => $this->model->name])
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
        $deleteButton = $this->model->isNewItem()
            ? ''
            : Html::a(
                Icon::show('trash-o') . '&nbsp;'
                . (Yii::t('dotplant.currencies', 'Delete')),
                [
                    $this->controller->id . '/delete',
                    'id' => $this->model->name,
                    'returnUrl' => $this->returnUrl,
                ],
                ['class' => 'btn btn-danger']
            );
        return
            Html::tag('div',
                $deleteButton
                . Html::a(
                    Yii::t('dotplant.currencies', 'Back'),
                    $this->returnUrl,
                    ['class' => 'btn btn-primary']
                )
                . Html::submitButton(
                    Icon::show('floppy-o') . '&nbsp;'
                    . (Yii::t('dotplant.currencies', 'Save')),
                    ['class' => 'btn btn-primary']
                ),
                ['class' => 'btn-group pull-right', 'role' => 'group', 'aria-label' => 'Edit buttons']
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
        return Yii::t('dotplant.currencies', 'Edit') . ' ' . $this->itemName;
    }
}
