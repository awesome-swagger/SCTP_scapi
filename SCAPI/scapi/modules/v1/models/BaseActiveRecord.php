<?php

namespace app\modules\v1\models;

use Yii;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
	
	private static $CLIENT_ID = '';
	private static $CT_DEV_DB = 'apidev';	
	private static $CT_STAGE_DB = 'apistage';
	private static $CT_PROD_DB = 'api';
	private static $PGE_DEV_DB = 'pgedev';
	private static $PGE_STAGE_DB = 'pgestage';
	private static $PGE_PROD_DB = 'pge';
	
	public static function getClient()
	{
		return self::$CLIENT_ID;
	}

	public static function setClient($id)
	{
		self::$CLIENT_ID = $id;
	}
	
	public static function getDb()
	{
		if (self::$CLIENT_ID == self::$CT_DEV_DB)
		{
			return Yii::$app->ctDevDb;
		}
		if (self::$CLIENT_ID == self::$PGE_DEV_DB)
		{
			return Yii::$app->pgeDevDb;
		}
	}
}