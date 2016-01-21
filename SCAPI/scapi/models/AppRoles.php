<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "AppRolesTb".
 *
 * @property integer $AppRoleID
 * @property string $AppRoleName
 * @property string $AppRoleDescription
 * @property string $AppRoleStatus
 * @property string $AppRoleType
 * @property integer $AppRoleUserID
 * @property string $AppRoleCreateDate
 * @property string $AppRoleCreatedBy
 * @property string $AppRoleModifiedDate
 * @property string $AppRoleModifiedBy
 */
class AppRoles extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AppRolesTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AppRoleName', 'AppRoleDescription', 'AppRoleStatus', 'AppRoleType', 'AppRoleCreatedBy', 'AppRoleModifiedBy'], 'string'],
            [['AppRoleUserID'], 'integer'],
            [['AppRoleCreateDate', 'AppRoleModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AppRoleID' => 'App Role ID',
            'AppRoleName' => 'App Role Name',
            'AppRoleDescription' => 'App Role Description',
            'AppRoleStatus' => 'App Role Status',
            'AppRoleType' => 'App Role Type',
            'AppRoleUserID' => 'App Role User ID',
            'AppRoleCreateDate' => 'App Role Create Date',
            'AppRoleCreatedBy' => 'App Role Created By',
            'AppRoleModifiedDate' => 'App Role Modified Date',
            'AppRoleModifiedBy' => 'App Role Modified By',
        ];
    }
}
