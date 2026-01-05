<?php

namespace app\controllers;

use yii\web\Controller;

/**
 * Site Controller
 *
 * Handles default site requests and diagnostics.
 */
class SiteController extends Controller
{
  /**
   * Index Action
   *
   * Renders the home page.
   *
   * @return string Rendered view
   */
  public function actionIndex()
  {
    return $this->render('index');
  }

  /**
   * Error Action
   *
   * Handles application errors and exceptions.
   *
   * @return string Error page view
   */
  public function actionError()
  {
    $exception = \Yii::$app->errorHandler->exception;
    if ($exception !== null) {
      $statusCode = 500;
      $name = 'Error';
      
      if ($exception instanceof \yii\web\HttpException) {
        $statusCode = $exception->statusCode;
        $name = $exception->getName();
      } else {
        $code = $exception->getCode();
        if ($code > 0) {
          $statusCode = $code;
        }
        $name = 'Error ' . $statusCode;
      }
      
      return $this->render('error', [
        'exception' => $exception,
        'name' => $name,
        'message' => $exception->getMessage(),
        'statusCode' => $statusCode,
      ]);
    }
    return '';
  }
}
