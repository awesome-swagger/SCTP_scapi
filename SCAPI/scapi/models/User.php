<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "UserTb".
 *
 * @property integer $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property string $UserLoginID
 * @property string $UserEmployeeType
 * @property string $UserPhone
 * @property string $UserCompanyName
 * @property string $UserCompanyPhone
 * @property string $UserAppRoleType
 * @property string $UserComments
 * @property integer $UserKey
 * @property integer $UserActiveFlag
 * @property string $UserCreatedDate
 * @property string $UserModifiedDate
 * @property string $UserCreatedBy
 * @property string $UserModifiedBy
 * @property string $UserCreateDTLTOffset
 * @property integer $UserModifiedDTLTOffset
 * @property integer $UserInactiveDTLTOffset
 *
 * @property EquipmentTb[] $equipmentTbs
 * @property KeyTb[] $keyTbs
 * @property ProjectUserTb[] $projectUserTbs
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'UserTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserID'], 'required'],
            [['UserID', 'UserKey', 'UserActiveFlag', 'UserModifiedDTLTOffset', 'UserInactiveDTLTOffset'], 'integer'],
            [['UserName', 'UserFirstName', 'UserLastName', 'UserLoginID', 'UserEmployeeType', 'UserPhone', 'UserCompanyName', 'UserCompanyPhone', 'UserAppRoleType', 'UserComments', 'UserCreatedBy', 'UserModifiedBy', 'UserCreateDTLTOffset'], 'string'],
            [['UserCreatedDate', 'UserModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UserID' => 'User ID',
            'UserName' => 'User Name',
            'UserFirstName' => 'User First Name',
            'UserLastName' => 'User Last Name',
            'UserLoginID' => 'User Login ID',
            'UserEmployeeType' => 'User Employee Type',
            'UserPhone' => 'User Phone',
            'UserCompanyName' => 'User Company Name',
            'UserCompanyPhone' => 'User Company Phone',
            'UserAppRoleType' => 'User App Role Type',
            'UserComments' => 'User Comments',
            'UserKey' => 'User Key',
            'UserActiveFlag' => 'User Active Flag',
            'UserCreatedDate' => 'User Created Date',
            'UserModifiedDate' => 'User Modified Date',
            'UserCreatedBy' => 'User Created By',
            'UserModifiedBy' => 'User Modified By',
            'UserCreateDTLTOffset' => 'User Create Dtltoffset',
            'UserModifiedDTLTOffset' => 'User Modified Dtltoffset',
            'UserInactiveDTLTOffset' => 'User Inactive Dtltoffset',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentTbs()
    {
        return $this->hasMany(EquipmentTb::className(), ['EquipmentAssignedUserID' => 'UserID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeyTbs()
    {
        return $this->hasMany(KeyTb::className(), ['KeyUserID' => 'UserID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectUserTbs()
    {
        return $this->hasMany(ProjectUserTb::className(), ['ProjUserUserID' => 'UserID']);
    }
}
