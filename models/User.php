<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * User Model
 *
 * Example ActiveRecord model for user management.
 * Customize this model according to your application needs.
 */
class User extends ActiveRecord
{
  /**
   * Returns the table name for this model.
   *
   * @return string The table name
   */
  public static function tableName(): string
  {
    return 'users';
  }
}
