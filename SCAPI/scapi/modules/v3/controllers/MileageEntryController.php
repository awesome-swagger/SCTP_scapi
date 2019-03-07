<?php

namespace app\modules\v3\controllers;

use Yii;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\MileageEntry;
use app\modules\v3\models\MileageEntryEventHistory;
use app\modules\v3\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\db\query;

/**
 * MileageEntryController implements the CRUD actions for MileageEntry model.
 */
class MileageEntryController extends BaseActiveController
{
    public $modelClass = 'app\modules\v3\models\MileageEntry'; 

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'create-task' => ['post'],
					'deactivate' => ['put'],
					'view-entries' => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	use ViewMethodNotAllowed;
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;
	
	/**
     * Create New Mileage Entry and Activity in CT DB
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function actionCreateTask()
    {
        try {
            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permissions check
			PermissionsController::requirePermission('createTaskEntry');

			$successFlag = 0;
			$warningMessage = '';
			
            //get body data
            $body = file_get_contents("php://input");
            $data = json_decode($body, true);
			
			// set up db connection
			$connection = BaseActiveRecord::getDb();
			$processJSONCommand = $connection->createCommand("EXECUTE spAddMileage :MileageCardID , :Date, :TotalMiles, :MileageType, :CreatedByUserName");
			$processJSONCommand->bindParam(':MileageCardID', $data['MileageCardID'], \PDO::PARAM_INT);
			$processJSONCommand->bindParam(':Date', $data['Date'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':TotalMiles', $data['TotalMiles']);
			$processJSONCommand->bindParam(':MileageType', $data['MileageType'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':CreatedByUserName', $data['CreatedByUserName'], \PDO::PARAM_STR);
			$processJSONCommand->execute();
			$successFlag = 1;			
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], [
                'MileageCardID' => $data['MileageCardID'],
                'Date' => $data['Date'],
                'CreatedByUserName' => $data['CreatedByUserName'],
                'SuccessFlag' => $successFlag
            ]);
			$warningMessage = 'An error occurred.';
        }
		
		//build response format
		$dataArray =  [
			'MileageCardID' => $data['MileageCardID'],
			'SuccessFlag' => $successFlag,
			'WarningMessage' => $warningMessage,
		];
		$response = Yii::$app->response;
		$response->format = Response::FORMAT_JSON;
		$response->data = $dataArray;
		
		return $response;
    }
	
	public function actionDeactivate()
	{
		try{
			//set db target
			MileageEntry::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageEntryDeactivate');
			
			//capture put body
			$put = file_get_contents("php://input");
			$entries = json_decode($put, true)['entries'];
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//get current user to set deactivated by
			$username = BaseActiveController::getUserFromToken()->UserName;
			
			foreach ($entries as $entry) {
				//SPROC has no return so just in case we need a flag.
				$success = 0;
				//add variables to avoid pass by reference error
				$taskString = json_encode($entry['taskName']);
				$day = array_key_exists('day', $entry) ? $entry['day'] : null;
				//call SPROC to deactivateTimeEntry
				try {
					$connection = BaseActiveRecord::getDb();
					//TODO may want to move the transaction outside of the loop to allow full rollback of the request
					$transaction = $connection->beginTransaction(); 
					$timeCardCommand = $connection->createCommand("EXECUTE spDeactivateMileageEntry :PARAMETER1,:PARAMETER2,:PARAMETER3,:PARAMETER4");
					$timeCardCommand->bindParam(':PARAMETER1', $entry['mileageCardID'], \PDO::PARAM_INT);
					$timeCardCommand->bindParam(':PARAMETER2', $taskString, \PDO::PARAM_STR);
					$timeCardCommand->bindParam(':PARAMETER3', $day, \PDO::PARAM_STR);
					$timeCardCommand->bindParam(':PARAMETER4', $username, \PDO::PARAM_STR);
					$timeCardCommand->execute();
					$transaction->commit();
					$success = 1;
				} catch (Exception $e) {
					$transaction->rollBack();
				}
			}
			//TODO could update response to be formated with success flag per entry if we keep individual transactions
			$response->data = $success; 
			return $response;
		} catch (ForbiddenHttpException $e) {
			throw new ForbiddenHttpException;
		} catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionViewEntries($cardID, $date)
	{
		try{
			//set db target
			MileageEntry::setClient(BaseActiveController::urlPrefix());
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
				
			//RBAC permission check
			PermissionsController::requirePermission('mileageEntryView');
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			$entriesQuery = new Query;
			$entriesQuery->select('*')
				->from(["fnMileageCardEntryDetailsByMileageCardAndDate(:cardID, :date)"])
				->addParams([':cardID' => $cardID, ':date' => $date]);
			$entries = $entriesQuery->all(BaseActiveRecord::getDb());
			
			$transaction->commit();

			$dataArray['entries'] = $entries;
				
			$response->data = $dataArray; 
			return $response;
		} catch (ForbiddenHttpException $e) {
			throw new ForbiddenHttpException;
		} catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionUpdate(){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

			//get body object
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//create response object
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageEntryUpdate');

			//get date and current user
			$modifiedBy = self::getUserFromToken()->UserName;
			$modifiedDate = Parent::getDate();
			
			//get current record
			$entryModel = MileageEntry::findOne($data['MileageEntryID']);
			//pass current data to new history record
			$historyModel = new MileageEntryEventHistory;
			$historyModel->Attributes = $entryModel->attributes;
			$historyModel->ChangeMadeBy = $modifiedBy;
			$historyModel->ChangeDateTime = $modifiedDate;
			$historyModel->Change = 'Updated';
			//updated record with new data
			$entryModel->attributes = $data;  
			$entryModel->MileageEntryModifiedBy = $modifiedBy;
			$entryModel->MileageEntryModifiedDate = $modifiedDate;
			$successFlag = 0;
			try{
				if($entryModel-> update()){
					//insert history record
					if($historyModel->save()){
						$successFlag = 1;
					}
				}
			}catch(Exception $e){
				$transaction->rollBack();
			}
			
			$transaction->commit();
			
			$responseData = [
				'EntryID' => $entryModel->MileageEntryID,
				'SuccessFlag' => $successFlag
			];
			$response->data = $responseData;
			return $response;
		} catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
			throw new \yii\web\HttpException(400);
		}
	}
}
