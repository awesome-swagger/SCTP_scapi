<?php

namespace app\controllers;

use Yii;
use app\models\Equipment;
use app\models\Project;
use app\models\Client;
use app\models\SCUser;
use app\models\ProjectUser;
use app\models\GetEquipmentByClientProjectVw;
use app\controllers\BaseActiveController;
use app\authentication\TokenAuth;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\base\ErrorException;

class EquipmentController extends BaseActiveController
{
	public $modelClass = 'app\models\Equipment'; 
	public $equipment;
	
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json  Header
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'accept-equipment'  => ['put'],
					'get-equipment-by-manager' => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		return $actions;
	}
	
	public function actionView($id)
    {
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		//set db target
		$headers = getallheaders();
		Equipment::setClient($headers['X-Client']);
			
		if($equipment = Equipment::findOne($id))
		{
			$response->data = $equipment;
			$response->setStatusCode(200);
		}
		else
		{
			$response->setStatusCode(404);
		}
		
		return $response;
	} 
	
	public function actionCreate()
	{
		//set db target
		$headers = getallheaders();
		Equipment::setClient($headers['X-Client']);
		SCUser::setClient($headers['X-Client']);
		
		$post = file_get_contents("php://input");
		$data = json_decode($post, true);

		$model = new Equipment(); 
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		//created by
		if ($user = SCUSer::findOne(['UserID'=>$model->EquipmentCreatedByUser]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$model->EquipmentCreatedByUser = $lname.", ".$fname;
		}
		
		//create date
		$model->EquipmentCreateDate = date('Y-m-d H:i:s');
		
		if($model-> save())
		{
			$response->setStatusCode(201);
			$response->data = $model; 
		}
		else
		{
			$response->setStatusCode(400);
			$response->data = "Http:400 Bad Request";
		}
		return $response;
	}
	
	public function actionUpdate($id)
	{
		//set db target
		$headers = getallheaders();
		Equipment::setClient($headers['X-Client']);
		SCUser::setClient($headers['X-Client']);
		
		$put = file_get_contents("php://input");
		$data = json_decode($put, true);

		$model = Equipment::findOne($id);
		$currentProject = $model->EquipmentProjectID;
		
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		if ($user = SCUSer::findOne(['UserID'=>$model->EquipmentModifiedBy]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$model->EquipmentModifiedBy = $lname.", ".$fname;
		}
		
		$model->EquipmentModifiedDate = date('Y-m-d H:i:s');
		
		//TODO set flag to "Pending" when the project information is changed.
		if($model->EquipmentProjectID != $currentProject)
		{
			$model-> EquipmentAcceptedFlag = "Pending";
			$model-> EquipmentAcceptedBy = "Pending";
		}
		
		if($model-> update())
		{
			$response->setStatusCode(200);
			$response->data = $model; 
		}
		else
		{
			$response->setStatusCode(400);
			$response->data = "Http:400 Bad Request";
		}
		return $response;
	}

	//return json array of all equipment for a project.
	public function actionViewEquipmentByProject($projectID)
	{
		//set db target
		$headers = getallheaders();
		Equipment::setClient($headers['X-Client']);
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		if($equipArray = Equipment::findAll(['EquipmentProjectID'=>$projectID]))
		{
			$equipData = array_map(function ($model) {return $model->attributes;},$equipArray);
			$response->data = $equipData;
			$response->setStatusCode(200);
		}
		else
		{
			$response->setStatusCode(404);
		}
		return $response;
	}
	
	public function actionViewEquipmentByUser($userID)
	{
		//set db target
		$headers = getallheaders();
		Equipment::setClient($headers['X-Client']);
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		if($equipArray = Equipment::findAll(['EquipmentAssignedUserID'=>$userID]))
		{
			$equipData = array_map(function ($model) {return $model->attributes;},$equipArray);
			$response->data = $equipData;
			$response->setStatusCode(200);
		}
		else
		{
			$response->setStatusCode(404);
		}
		return $response;
	}

	//return json array of all equipment.
	public function actionViewAll()
	{
		//set db target
		$headers = getallheaders();
		Equipment::setClient($headers['X-Client']);
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		if($equipArray = Equipment::find()->all())
		{
			$equipData = array_map(function ($model) {return $model->attributes;},$equipArray);
			$response->data = $equipData;
			$response->setStatusCode(200);
		}
		else
		{
			$response->setStatusCode(404);
		}
		return $response;
	}
	
	//return db view for equipment index
	public function actionEquipmentView()
	{
		//set db target
		$headers = getallheaders();
		Equipment::setClient($headers['X-Client']);
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		if($equipArray = GetEquipmentByClientProjectVw::find()->all())
		{
			$equipData = array_map(function ($model) {return $model->attributes;},$equipArray);
			$response->data = $equipData;
			$response->setStatusCode(200);
		}
		else
		{
			$response->setStatusCode(404);
		}
		return $response;
	}
	
	public function actionAcceptEquipment()
	{
		//set db target
		$headers = getallheaders();
		Equipment::setClient($headers['X-Client']);
		SCUser::setClient($headers['X-Client']);
		
		//capture put body
		$put = file_get_contents("php://input");
		$data = json_decode($put, true);
		
		//create response
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		//parse json
		$acceptedBy = $data["acceptedByID"];
		$equipmentIDs = $data["equipmentIDArray"];
		
		//get equipment
		foreach($equipmentIDs as $id)
		{
			$approvedEquipment[]= Equipment::findOne($id);
		}
		
		//get user's name by ID
		if ($user = SCUser::findOne(['UserID'=>$acceptedBy]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$acceptedBy = $lname.", ".$fname;
		}
		
		//try to accept equipment
		try
		{
			//create transaction
			$connection = \Yii::$app->db;
			$transaction = $connection->beginTransaction(); 
		
			foreach($approvedEquipment as $equipment)
			{
				$equipment-> EquipmentAcceptedFlag = "Yes";
				$equipment-> EquipmentAcceptedBy = $acceptedBy;
				$equipment-> update();
			}
			$transaction->commit();
			$response->setStatusCode(200);
			$response->data = $approvedEquipment; 
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
	
	public function actionGetEquipmentByManager($userID)
	{
		//set db target
		$headers = getallheaders();
		Equipment::setClient($headers['X-Client']);
		ProjectUser::setClient($headers['X-Client']);
		
		//get all projects for manager
		$projects = ProjectUser::find()
			->where("ProjUserUserID = $userID")
			->all();
		$projectsSize = count($projects);
		
		$equipment = [];
		
		//get all equipment associated with projects
		for($i = 0; $i < $projectsSize; $i++)
		{
			$projectID = $projects[$i]->ProjUserProjectID; 
			$newEquipment = Equipment::find()
				->where("EquipmentProjectID = $projectID")
				->all();
			$equipment = array_merge($equipment, $newEquipment);
		}
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->setStatusCode(200);
		$response->data = $equipment;
		return $response;
	}
}
