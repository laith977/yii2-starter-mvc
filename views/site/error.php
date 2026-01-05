<?php

/** @var $this \yii\web\View */
/** @var $name string */
/** @var $message string */
/** @var $exception \Exception */
/** @var int $statusCode */

$this->title = $name;
?>
<div class="site-error">
  <h1><?= \yii\helpers\Html::encode($name) ?></h1>
  <div class="alert alert-danger">
    <?= nl2br(\yii\helpers\Html::encode($message)) ?>
  </div>
  <p>
    The above error occurred while the Web server was processing your request.
  </p>
  <p>
    Please contact us if you think this is a server error. Thank you.
  </p>
</div>

