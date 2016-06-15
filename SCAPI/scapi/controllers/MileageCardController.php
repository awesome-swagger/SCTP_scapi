<?php

namespace app\controllers;

use Yii;
use app\models\MileageCard;
use app\models\MileageEntry;
use app\models\SCUser;
use app\models\Project;
use app\models\ProjectUser;
use app\models\AllMileageCardsCurrentWeek;
use app\models\AllMileageCardsPriorWeek;
use app\models\AllApprovedMileageCardsCurrentWeek;
use app\models\AllUnApprovedMileageCardsCurrentWeek;
use app\models\MileageCardSumMilesCurrentWeekWithProjectNameNew;
use app\models\MileageCardSumMilesPriorWeekWithProjectNameNew;
use app\controllers\BaseActiveController;
use app\authentication\TokenAuth;
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use \DateTime;

/**
 * MileageCardController implements the CRUD actions for MileageCard model.
 */
class MileageCardController extends BaseActiveController
{
    public $modelClass = 'app\models\MileageCard'; 
	
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json Header
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['delete'],
					'update' => ['put'],
					'view-mileage-entries' => ['get'],
					'approve-mileage-cards'  => ['put'],
					'get-mileage-card-current-week' => ['get'],
					'get-mileage-cards-current-week-sum-miles' => ['get'],
					'get-mileage-cards-prior-week-sum-miles' => ['get'],
					'view-all-by-user-by-project-current' => ['get'],
					'view-all-by-user-by-project-prior' => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	 
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}

    /**
     * Displays a single MileageCard model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
		try
		{
			//set db target
			$headers = getallheaders();
			MileageCard::setClient($headers['X-Client']);
			
			$mileageCard = MileageCard::findOne($id);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $mileageCard;
			
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
    }
	
	public function actionCreate()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	public function actionUpdate()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	public function actionDelete()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}

	public function actionViewMileageEntries($id)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			MileageCard::setClient($headers['X-Client']);
			MileageEntry::setClient($headers['X-Client']);
			
			$response = Yii::$app ->response;
			$dataArray = [];
			$mileageCard = MileageCard::findOne($id);
			$date = new DateTime($mileageCard-> MileageStartDate);
			
			//get all time entries for Sunday
			$sundayDate = $date;
			$sundayStr = $sundayDate->format('Y-m-d H:i:s');
			$sundayEntries = MileageEntry::find()
				->where("MileageEntryDate ="."'"."$sundayStr". "'")
				->andWhere("MileageEntryMileageCardID = $id")
				->all();
			
			//get all time entries for Monday
			$mondayDate = $date->modify('+1 day');	
			$mondayStr = $mondayDate->format('Y-m-d H:i:s');		
			$mondayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$mondayStr". "'")
				->andWhere("MileageEntryMileageCardID = $id")
				->all();
				
			//get all time entries for Tuesday	
			$tuesdayDate = $date->modify('+1 day');
			$tuesdayStr = $tuesdayDate->format('Y-m-d H:i:s');
			$tuesdayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$tuesdayStr". "'")
				->andWhere("MileageEntryMileageCardID = $id")
				->all();
				
			//get all time entries for Wednesday	
			$wednesdayDate = $date->modify('+1 day');
			$wednesdayStr = $wednesdayDate->format('Y-m-d H:i:s');
			$wednesdayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$wednesdayStr". "'")
				->andWhere("MileageEntryMileageCardID = $id")
				->all();
				
			//get all time entries for Thursday
			$thursdayDate = $date->modify('+1 day');
			$thursdayStr = $thursdayDate->format('Y-m-d H:i:s');
			$thursdayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$thursdayStr". "'")
				->andWhere("MileageEntryMileageCardID = $id")
				->all();
				
			//get all time entries for Friday
			$fridayDate = $date->modify('+1 day');
			$fridayStr = $fridayDate->format('Y-m-d H:i:s');
			$fridayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$fridayStr". "'")
				->andWhere("MileageEntryMileageCardID = $id")
				->all();
				
			//get all time entries for Saturday
			$satudayDate = $date->modify('1 day');
			$satudayStr = $satudayDate->format('Y-m-d H:i:s');
			$saturdayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$satudayStr". "'")
				->andWhere("MileageEntryMileageCardID = $id")
				->all();
				
			//load data into array
			$dataArray["StartDate"] = $mileageCard-> MileageStartDate;
			$dataArray["EndDate"] = $mileageCard-> MileageEndDate;
			$dataArray["ApprovedFlag"] = $mileageCard-> MileageCardApprovedFlag;
			$dayArray =
			[
				"Sunday" => $sundayEntries,
				"Monday" => $mondayEntries,
				"Tuesday" => $tuesdayEntries,
				"Wednesday" => $wednesdayEntries,
				"Thursday" => $thursdayEntries,
				"Friday" => $fridayEntries,
				"Saturday" => $saturdayEntries,
			];
			$dataArray["MileageEntries"] = [$dayArray];
			
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $dataArray;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionApproveMileageCards()
	{
		try
		{
			//set db target
			$headers = getallheaders();
			MileageCard::setClient($headers['X-Client']);
			SCUser::setClient($headers['X-Client']);
			
			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//parse json
			$approvedBy = $data["approvedByID"];
			$cardIDs = $data["cardIDArray"];
			
			//get timecards
			foreach($cardIDs as $id)
			{
				$approvedCards[]= MileageCard::findOne($id);
			}
			
			//get user's name by ID
			if ($user = SCUser::findOne(['UserID'=>$approvedBy]))
			{
				$fname = $user->UserFirstName;
				$lname = $user->UserLastName;
				$approvedBy = $lname.", ".$fname;
			}
			
			//try to approve time cards
			try
			{
				//create transaction
				$connection = \Yii::$app->db;
				$transaction = $connection->beginTransaction(); 
			
				foreach($approvedCards as $card)
				{
					$card-> MileageCardApprovedFlag = "Yes";
					$card-> MileageCardApprovedBy = $approvedBy;
					$card-> MileageCardModifiedDate = Parent::getDate();
					$card-> MileageCardModifiedBy = $approvedBy;
					$card-> update();
				}
				$transaction->commit();
				$response->setStatusCode(200);
				$response->data = $approvedCards; 
				return $response;
			}
			//if transaction fails rollback changes and send error
			catch(Exception $e)
			{
				$transaction->rollBack();
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
				return $response;
			}
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionGetMileageCardCurrentWeek($id)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			AllMileageCardsCurrentWeek::setClient($headers['X-Client']);
			
			$mileageCard = AllMileageCardsCurrentWeek::findOne(['UserID'=>$id]);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			if ($mileageCard != null)
			{
				$response->setStatusCode(200);
				$response->data = $mileageCard;
				return $response;
			}
			else
			{
				$response->setStatusCode(404);
				return $response;
			}
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	//function to get all mileagecards for the current week with their sum miles
	public function actionGetMileageCardsCurrentWeekSumMiles()
	{
		try
		{
			//set db target
			$headers = getallheaders();
			MileageCardSumMilesCurrentWeekWithProjectNameNew::setClient($headers['X-Client']);
			
			$mileageCards = MileageCardSumMilesCurrentWeekWithProjectNameNew::find()->all();
			$mileageCardArray = array_map(function ($model) {return $model->attributes;},$mileageCards);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;

			$response->setStatusCode(200);
			$response->data = $mileageCardArray;
			return $response;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	//function to get all mileagecards for the prior week with their sum miles
	public function actionGetMileageCardsPriorWeekSumMiles()
	{
		try
		{
			//set db target
			$headers = getallheaders();
			MileageCardSumMilesPriorWeekWithProjectNameNew::setClient($headers['X-Client']);
			
			$mileageCards = MileageCardSumMilesPriorWeekWithProjectNameNew::find()->all();
			$mileageCardArray = array_map(function ($model) {return $model->attributes;},$mileageCards);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;

			$response->setStatusCode(200);
			$response->data = $mileageCardArray;
			return $response;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	//returns a json containing all mileage cards for projects that a user is associated with for the current week
	//used by proj managers and supervisors
	public function actionViewAllByUserByProjectCurrent($userID)
	{
		try{
			//set db target
			$headers = getallheaders();
			MileageCardSumMilesCurrentWeekWithProjectNameNew::setClient($headers['X-Client']);
			ProjectUser::setClient($headers['X-Client']);
			
			//format response
			$response = Yii::$app->response;
			$response-> format = Response::FORMAT_JSON;
			
			//get user project relations array
			$projects = ProjectUser::find()
				->where("ProjUserUserID = $userID")
				->all();
			$projectsSize = count($projects);
			
			//response array of mileage cards
			$mileageCardArray = [];
			
			//loop user project array get all mileage cards WHERE equipmentProjectID is equal
			for($i=0; $i < $projectsSize; $i++)
			{
				$projectID = $projects[$i]->ProjUserProjectID; 
				
				$mileageCards = MileageCardSumMilesCurrentWeekWithProjectNameNew::find()
				->where(['ProjectID' => $projectID])
				->all();
				$mileageCardArray = array_merge($mileageCardArray, $mileageCards);
			}
			
			$response->data = $mileageCardArray;
			$response->setStatusCode(200);
			return $response;
			
		} catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}
	
	//returns a json containing all mileage cards for projects that a user is associated with for the prior week
	//used by proj managers and supervisors	
	public function actionViewAllByUserByProjectPrior($userID)
	{
		try{
			//set db target
			$headers = getallheaders();
			MileageCardSumMilesPriorWeekWithProjectNameNew::setClient($headers['X-Client']);
			ProjectUser::setClient($headers['X-Client']);
			
			//format response
			$response = Yii::$app->response;
			$response-> format = Response::FORMAT_JSON;
			
			//get user project relations array
			$projects = ProjectUser::find()
				->where("ProjUserUserID = $userID")
				->all();
			$projectsSize = count($projects);
			
			//response array of mileage cards
			$mileageCardArray = [];
			
			//loop user project array get all mileage cards WHERE equipmentProjectID is equal
			for($i=0; $i < $projectsSize; $i++)
			{
				$projectID = $projects[$i]->ProjUserProjectID; 
				
				$mileageCards = MileageCardSumMilesPriorWeekWithProjectNameNew::find()
				->where(['ProjectID' => $projectID])
				->all();
				$mileageCardArray = array_merge($mileageCardArray, $mileageCards);
			}
			
			$response->data = $mileageCardArray;
			$response->setStatusCode(200);
			return $response;
			
		} catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}
}
