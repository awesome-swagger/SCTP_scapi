<?php

namespace app\controllers;

use Yii;
use app\models\Project;
use app\models\SCUser;
use app\models\ProjectUser;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\Link;
use yii\filters\auth\TokenAuth;


/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends BaseActiveController
{
	public $modelClass = 'app\models\Project'; 
	
	/**
	* sets verb filters for http request
	* @return an array of behaviors
	*/
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'view-all-users'  => ['get'],
					'get-project-dropdowns'  => ['get'],
					'get-user-relationships'  => ['get'],
					'add-remove-users' => ['post'],
					'get-project-dropdowns' => ['get'],
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
	* The delete method is not allowed for user so the parent function is overridden to reflect that
	* @returns json body containing a method not allowed message
	*/	
	public function actionDelete()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}

	/**
	* Gets the data for a project based on a project id
	* @param $id the id of a project record
	* @returns json body of the project data
	* @throws \yii\web\HttpException
	*/	
	public function actionView($id)
    {
		try
		{
			//set db target
			$headers = getallheaders();
			Project::setClient($headers['X-Client']);
			
			$project = Project::findOne($id);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $project;
			
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	} 
	
	/**
	* Creates a new project record in the database
	* @returns json body of the project data
	* @throws \yii\web\HttpException
	*/	
	public function actionCreate()
	{
		try
		{
			//set db target
			$headers = getallheaders();
			Project::setClient($headers['X-Client']);
			SCUser::setClient($headers['X-Client']);
			
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			$model = new Project(); 
			$model->attributes = $data;  
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//create date
			$model->ProjectCreateDate = Parent::getDate();
			
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
	
	/**
	* Updates a project record in the database
	* @param $id the id of a project record
	* @returns json body of the project data
	* @throws \yii\web\HttpException
	*/	
	public function actionUpdate($id)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			Project::setClient($headers['X-Client']);
			SCUser::setClient($headers['X-Client']);
			
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);

			$model = Project::findOne($id);
			
			$model->attributes = $data;  
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			$model->ProjectModifiedDate = Parent::getDate();
			
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
	
	/**
	* Creates an associative array of project id/name pairs
	* @returns json body of id name pairs
	* @throws \yii\web\HttpException
	*/
	public function actionGetProjectDropdowns()
	{	
		try
		{
			//set db target
			$headers = getallheaders();
			Project::setClient($headers['X-Client']);
		
			$projects = Project::find()
				->all();
			$namePairs = [null => "Unassigned"];
			$projectSize = count($projects);
			
			for($i=0; $i < $projectSize; $i++)
			{
				$namePairs[$projects[$i]->ProjectID]= $projects[$i]->ProjectName;
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
	
	/**
	* Creates two arrays, one of all users associated with a project 
	* the other of all users not associated with a project
	* @param $id the id of a project record
	* @returns json containing two user arrays
	* @throws \yii\web\HttpException
    */	
	public function actionGetUserRelationships($projectID)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
			Project::setClient($headers['X-Client']);
			ProjectUser::setClient($headers['X-Client']);
			
			//get all users for the project
			$project = Project::findOne($projectID);
			$assignedUsers = $project->users;
			$assignedPairs = [];
			$assignedSize = count($assignedUsers);
			
			//create array of included user id/name pairs
			for($i=0; $i < $assignedSize; $i++)
			{
				$assignedPairs[$assignedUsers[$i]->UserID]=['content' => $assignedUsers[$i]->UserLastName. ", ". $assignedUsers[$i]->UserFirstName];
			}
			
			//get all users
			$allUsers = SCUser::find()
				->where(['UserActiveFlag' => 1])
				->all();
			
			$unassignedPairs = [];
			$unassignedSize = count($allUsers);
			
			//create array of all user id/name pairs
			for($i=0; $i < $unassignedSize; $i++)
			{
				$unassignedPairs[$allUsers[$i]->UserID]=['content' => $allUsers[$i]->UserLastName. ", ". $allUsers[$i]->UserFirstName];
			}
			
			//filter included pairs
			foreach($unassignedPairs as $uk => $uv)
			{
				foreach($assignedPairs as $ak => $av)
				{
					if($uk == $ak)
					{
						unset($unassignedPairs[$uk]);
					}
				}
			}
			
			//build response json
			$data = [];
			$data["unassignedUsers"] = $unassignedPairs;
			$data["assignedUsers"] = $assignedPairs; 
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $data;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	/**
	* Accepts two arrays one of users to add to a project and one of users to remove from a project.
	* Creates ProjectUser records for added users and deletes ProjectUser records for removed users.
	* Calls SPs to handle creation/activation of time and mileage cards for added users
	* Calls SPs to handle deactivation of time and mileage cards for users removed
	* @param $id the id of a project record
	* @returns json containing two user arrays that were processed
	* @throws \yii\web\HttpException
    */	
	public function actionAddRemoveUsers($projectID)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
			Project::setClient($headers['X-Client']);
			ProjectUser::setClient($headers['X-Client']);
			
			//create response
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			
			//get project from param
			$project = Project::findOne($projectID);
			
			//decode post data
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);
			
			//check if key exist
			if(array_key_exists("usersAdded", $data) && array_key_exists("usersRemoved", $data))
			{
				//parse post data
				$usersAdded = $data['usersAdded'];
				$usersRemoved = $data['usersRemoved'];
			} else {
				//set failure response
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
				
				return $response;
			}
			
			//loop usersAdded and create relationships and cards
			foreach($usersAdded as $i)
			{
				$user = SCUser::findOne($i);
				$user->link('projects',$project);
				//call sps to create new time cards and mileage cards
				try
				{
					$userID = $i;
					$connection = SCUser::getDb();
					$transaction = $connection-> beginTransaction();
					$timeCardCommand = $connection->createCommand("EXECUTE PopulateTimeCardTbForUserToProjectCatchErrors_proc :PARAMETER1,:PARAMETER2");
					$timeCardCommand->bindParam(':PARAMETER1', $userID,  \PDO::PARAM_INT);
					$timeCardCommand->bindParam(':PARAMETER2', $projectID,  \PDO::PARAM_INT);
					$timeCardCommand->execute();
					$mileageCardCommand = $connection->createCommand("EXECUTE PopulateMileageCardTbForUserToProjectCatchErrors_proc :PARAMETER1,:PARAMETER2");
					$mileageCardCommand->bindParam(':PARAMETER1', $userID,  \PDO::PARAM_INT);
					$mileageCardCommand->bindParam(':PARAMETER2', $projectID,  \PDO::PARAM_INT);
					$mileageCardCommand->execute();
					$transaction->commit();
					
				}
				catch(Exception $e)
				{
					$transaction->rollBack();
				}
				//call sps to ensure time/mileage cards are active.
				try
				{
					$userID = $i;
					$connection = SCUser::getDb();
					$transaction = $connection-> beginTransaction();
					$timeCardCommand = $connection->createCommand("EXECUTE ActivateTimeCardByUserByProject_proc :UserParam,:ProjectParam");
					$timeCardCommand->bindParam(':UserParam', $userID,  \PDO::PARAM_INT);
					$timeCardCommand->bindParam(':ProjectParam', $projectID,  \PDO::PARAM_INT);
					$timeCardCommand->execute();
					$mileageCardCommand = $connection->createCommand("EXECUTE ActivateMileageCardByUserByProject_proc :UserParam ,:ProjectParam");
					$mileageCardCommand->bindParam(':UserParam', $userID,  \PDO::PARAM_INT);
					$mileageCardCommand->bindParam(':ProjectParam', $projectID,  \PDO::PARAM_INT);
					$mileageCardCommand->execute();
					$transaction->commit();
					
				}
				catch(Exception $e)
				{
					$transaction->rollBack();
				}
			}
			
			//loop usersRemoved and delete relationships and deactivate cards
			foreach($usersRemoved as $i)
			{
				$projUser = ProjectUser::find()
				->where(['and', "ProjUserUserID = $i","ProjUserProjectID = $projectID"])
				->one();
				$projUser->delete();
				//call sps to deactivate time cards and mileage cards
				try
				{
					$userID = $i;
					$connection = SCUser::getDb();
					$transaction = $connection-> beginTransaction();
					$timeCardCommand = $connection->createCommand("EXECUTE DeactivateTimeCardByUserByProject_proc :PARAMETER1,:PARAMETER2");
					$timeCardCommand->bindParam(':PARAMETER1', $userID,  \PDO::PARAM_INT);
					$timeCardCommand->bindParam(':PARAMETER2', $projectID,  \PDO::PARAM_INT);
					$timeCardCommand->execute();
					$mileageCardCommand = $connection->createCommand("EXECUTE DeactivateMileageCardByUserByProject_proc :PARAMETER1,:PARAMETER2");
					$mileageCardCommand->bindParam(':PARAMETER1', $userID,  \PDO::PARAM_INT);
					$mileageCardCommand->bindParam(':PARAMETER2', $projectID,  \PDO::PARAM_INT);
					$mileageCardCommand->execute();
					$transaction->commit();
				}
				catch(Exception $e)
				{
					$transaction->rollBack();
				}		
			}
			
			//set success response 
			$response->setStatusCode(200);
			$response -> data = $data;
			
			return $response;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
}
