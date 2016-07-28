<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\Client;
use app\modules\v1\models\SCUser;
use yii\web\Response;
use app\modules\v1\models\BaseActiveRecord;

/**
 * ClientController implements the CRUD actions for Client model.
 */
class ClientController extends BaseActiveController
{
	public $modelClass = 'app\modules\v1\models\Client'; 

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
	 * Gets all of the class's model's records
	 *
	 * @return Response The records in a JSON format
	 * @throws \yii\web\HttpException 400 if any exceptions are thrown
	 */
	public function actionGetAll()
	{
		// RBAC permission check
		PermissionsController::requirePermission('clientGetAll');
		
		try
		{
			//set db target
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			Client::setClient($headers['X-Client']);

			$models = Client::find()
				->all();

			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $models;

			return $response;
		}
		catch(\Exception $e)
		{
			throw new \yii\web\HttpException(400);
		}
	}

	use DeleteMethodNotAllowed;

	public function actionView($id)
	{
		// RBAC permission check
		PermissionsController::requirePermission('clientView');
		
		try
		{
			//set db target
			$headers = getallheaders();
			Client::setClient($headers['X-Client']);
			
			$client = Client::findOne($id);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $client;
			
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	
	public function actionCreate()
	{
		// RBAC permission check
		PermissionsController::requirePermission('clientCreate');
		
		try
		{
			//set db target
			$headers = getallheaders();
			Client::setClient($headers['X-Client']);
			SCUser::setClient($headers['X-Client']);
			
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			$model = new Client(); 
			$model->attributes = $data;  
			$model->ClientCreatorUserID = self::getUserFromToken()->UserID;
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//create date
			$model->ClientCreateDate = Parent::getDate();
			
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
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionUpdate($id)
	{
		// RBAC permission check
		PermissionsController::requirePermission('clientUpdate');
		
		try
		{
			//set db target
			$headers = getallheaders();
			Client::setClient($headers['X-Client']);
			SCUser::setClient($headers['X-Client']);
			
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);

			$model = Client::findOne($id);
			
			$model->attributes = $data;  
			$model->ClientModifiedBy = self::getUserFromToken()->UserID;
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			$model->ClientModifiedDate = Parent::getDate();
			
			if($model-> update())
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
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	
	//return a json containing pairs of ClientID and ClientName
	public function actionGetClientDropdowns()
	{
		// RBAC permission check
		PermissionsController::requirePermission('clientGetDropdown');
		
		try
		{
			//set db target
			$headers = getallheaders();
			Client::setClient($headers['X-Client']);
		
			$clients = Client::find()
				->orderBy('ClientName')
				->all();
			$namePairs = [];
			$clientSize = count($clients);
			
			for($i=0; $i < $clientSize; $i++)
			{
				$namePairs[$clients[$i]->ClientID]= $clients[$i]->ClientName;
			}
				
			
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $namePairs;
			
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}
