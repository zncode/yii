<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\MosquittoForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Mosquitto';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php
$js = <<<JS

$('form#mosquitto-form').on('beforeSubmit', function(e) {
   var form = $(this);
    mac = $('#mosquittoform-mac').val();
    msg = $('#mosquittoform-msg').val();
$.ajax({
    type: 'POST',
    async: false,
    url: "?r=site/mosquittoajax",
    dataType: "json",
    data: {
        'mac': mac,
        'msg': msg,
    },
    success: function( data ) {
        $('#mosquittoform-response').val(data.data);
    }
});
}).on('submit', function(e){
    e.preventDefault();
});

JS;
$this->registerJs($js);
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('mosquittoFormSubmitted')): ?>

        <div class="alert alert-success">
            Thank you for contacting us. We will respond to you as soon as possible.
        </div>

    <?php else: ?>

        <p>
            发送Message, 返回信息等待几秒.
        </p>

        <div class="row">
            <div class="col-lg-5">

                <?php $form = ActiveForm::begin(['id' => 'mosquitto-form']); ?>

                    <?= $form->field($model, 'mac')->textInput(['autofocus' => true]) ?>
                    <?= $form->field($model, 'msg')->textInput(['autofocus' => true]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'mosquitto-button']) ?>
        </div>
                    <?= $form->field($model, 'response')->textArea(['rows' => 6]) ?>
                <?php ActiveForm::end(); ?>

            </div>
        </div>

    <?php endif; ?>
</div>
