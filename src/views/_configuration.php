<?php
/**
 * @var $this \yii\web\View
 */
use yii\bootstrap\Tabs;

$this->beginBlock('basicCurrenciesConfig');
echo '<div class="box-body">';
echo $form->field($model, 'currenciesStorage');
echo $form->field($model, 'providersStorage');
echo $form->field($model, 'currenciesCacheKey');
echo $form->field($model, 'providersCacheKey');
echo '</div>';
$this->endBlock('basicCurrenciesConfig');

echo Tabs::widget([
    'items' => [
        [
            'label' => Yii::t('dotplant.currencies', 'Extension settings'),
            'content' => $this->blocks['basicCurrenciesConfig'],
        ],
        [
            'label' => Yii::t('dotplant.currencies', 'Currencies'),
            'content' => $this->render('@vendor/dotplant/currencies/src/views/_currencies'),
        ],
        [
            'label' => Yii::t('dotplant.currencies', 'Providers'),
            'content' => $this->render('@vendor/dotplant/currencies/src/views/_providers'),
        ]
    ]
]);
?>
