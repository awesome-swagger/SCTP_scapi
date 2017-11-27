<?php

namespace app\modules\v2\controllers;

use Yii;
use app\modules\v2\models\SCUser;
use app\modules\v2\models\Auth;
use app\modules\v2\models\Project;
use app\modules\v2\controllers\BaseActiveController;
use app\authentication\CTUser;
use yii\data\ActiveDataProvider;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\helpers\Json;
use yii\base\ErrorException;

class LoginController extends Controller
{
		
	public function actionUserLogin()
	{
		try
		{
			//get client header to find project landing page
			$headers = getallheaders();
			$client = '';
			if(array_key_exists('X-Client', $headers))
			{
				$client = $headers['X-Client'];
			}
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//read the post input (use this technique if you have no post variable name):
			$post = file_get_contents("php://input");

			//decode json post input as php array:
			$data = json_decode($post, true);

			//archive json
			BaseActiveController::archiveWebJson(
				json_encode($data),
				'Login',
				//ternary check if username is in data set to prevent potential error on bad json
				(array_key_exists('UserName', $data) ? $data['UserName'] : null),
				$client);
			
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			
			//login is a Yii model:
			$userName = new SCUser();

			//load json data into model:
			$userName->UserName = $data['UserName'];  

			if($user = SCUser::findOne(['UserName'=>$userName->UserName, 'UserActiveFlag'=>1]))
				{
				$securedPass = $data["Password"];
				
				//decrypt password
				$decryptedPass = BaseActiveController::decrypt($securedPass);

				$hash = $user->UserPassword;
				Yii::trace('Hash: '.$hash);
				//Check the Hash
				if (password_verify($decryptedPass, $hash)) 
				{
					Yii::trace('Password is valid.');
					
					//Pass
					Yii::$app->user->login($user);
					//Generate Auth Token
					$auth = new Auth();
					$userID = $user->UserID;
					$auth->AuthUserID = $userID;
					$auth->AuthCreatedBy = $userID;
					$auth-> beforeSave(true);
					//Store Auth Token
					$auth-> save();
				}
				else
				{
					$response->data = "Password is invalid.";
					$response->setStatusCode(401);
					return $response;
					Yii::trace('Password is invalid.');
				}
			}
			else
			{
				$response->data = "User not found or inactive.";
				$response->setStatusCode(401);
				return $response;
			}
			
			$authArray = ArrayHelper::toArray($auth);
			$authArray['UserFirstName'] = $user->UserFirstName;
			$authArray['UserLastName'] = $user->UserLastName;
			$authArray['UserUID'] = $user->UserUID;
			$authArray['ProjectLandingPage'] = self::getProjectLandingPage($client);
			
			//add auth token to response
			$response->data = $authArray;
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	// User logout
	public function actionUserLogout()
	{
		try
		{
			//get request headers
			$headers = getallheaders();
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			//create response object
			$response = Yii::$app->response;
			
			//archive request
			//check if header contains xclient
			$client = (array_key_exists('X-Client', $headers) ? $headers['X-Client'] : null);
			BaseActiveController::archiveWebJson(
				null,
				'Logout',
				($client !== null ? BaseActiveController::getClientUser($client)->UserName : null),
				$client);
			
			if(array_key_exists('Authorization', $headers))
			{
				//pull token from Authorization header, base 64 decode, and parse.
				$token = substr(base64_decode(explode(" ", $headers['Authorization'])[1]), 0, -1);
			}
			else
			{
				//return unauthorized if token is not available 
				$response->statusCode = 401;
				$response->data = 'You are requesting with invalid credentials.';
				return $response;
			}

			//call CTUser\logout()
			Yii::$app->user->logout($destroySession = true, null, $token);
			$response->data = 'Logout Successful!';
			return $response;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	private static function getProjectLandingPage($client)
	{
		$projectLandingPage = Project::find()
			->select('ProjectLandingPage')
			->where(['ProjectUrlPrefix' => $client])
			->one();
		
		return $projectLandingPage['ProjectLandingPage'];
	}
}
