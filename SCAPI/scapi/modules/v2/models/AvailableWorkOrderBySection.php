<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAvailableWorkOrderBySection".
 *
 * @property string $MapGrid
 * @property string $SectionNumber
 * @property integer $AvailableWorkOrderCount
 * @property integer $LocationType
 */
class AvailableWorkOrderBySection extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vAvailableWorkOrderBySection';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('yorkDevDb');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MapGrid', 'SectionNumber', 'Location Type'], 'string'],
            [['AvailableWorkOrderCount'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MapGrid' => 'Map Grid',
            'SectionNumber' => 'Section Number',
            'AvailableWorkOrderCount' => 'Available Work Order Count',
            'LocationType' => 'Location Type',
        ];
    }
}
