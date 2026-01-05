<?php

/** @var $this \yii\web\View */
/** @var string $content */
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= \yii\helpers\Html::encode($this->title ?? \Yii::$app->name) ?></title>
</head>

<body>
  <?= $content ?>
</body>

</html>