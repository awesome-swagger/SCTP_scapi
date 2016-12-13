<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementTrackerHistoryDropDown".
 *
 * @property string $Division
 * @property string $WorkCenter
 * @property string $Surveyor
 * @property string $Date
 */
class WebManagementTrackerHistoryDropDown extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementTrackerHistoryDropDown';
    }
}