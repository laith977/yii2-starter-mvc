<?php

/** @var $this \yii\web\View */

$this->title = 'Home';

/* Test database connection */
$dbConnected = false;
$dbError = null;
try {
  \Yii::$app->db->open();
  $dbConnected = true;
} catch (\Exception $e) {
  $dbError = $e->getMessage();
}
?>
<h1>Welcome to Yii2 MVC Template</h1>
<p>Database Status: 
  <?php if ($dbConnected): ?>
    <strong style="color: green;">Connected ✓</strong>
  <?php else: ?>
    <strong style="color: red;">Not Connected ✗</strong>
    <?php if ($dbError): ?>
      <br><small>Error: <?= \yii\helpers\Html::encode($dbError) ?></small>
    <?php endif; ?>
  <?php endif; ?>
</p>
<p>This is a minimal Yii2 MVC template ready for your application.</p>