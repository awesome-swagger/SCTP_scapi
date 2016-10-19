<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 7/29/2016
 * Time: 12:45 PM
 */

namespace app\modules\v1\modules\pge\controllers;
use app\modules\v1\modules\pge\models\WebManagementMasterLeakLog;
use app\modules\v1\modules\pge\models\WebManagementLeaks;
use app\modules\v1\modules\pge\models\WebManagementEquipmentServices;
use Yii;
use yii\data\Pagination;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class LeakLogController extends Controller {

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
					'get-details' => ['get'],
					'get-mgnt' => ['get'],
                ],
            ];
		return $behaviors;
	}

    public function actionGetDetails($division, $workCenter, $mapPlat, $surveyor, $date)
	{
        try
		{
            $data = [];

            if($division != null && $workCenter != null && $mapPlat != null && $surveyor != null && $date != null)
            {
                $headers = getallheaders();
                WebManagementMasterLeakLog::setClient($headers['X-Client']);
                WebManagementLeaks::setClient($headers['X-Client']);
                WebManagementEquipmentServices::setClient($headers['X-Client']);

                $masterLeakLogRecords = WebManagementMasterLeakLog::find()
                    ->where(['Division' => $division])
                    ->andWhere(['WorkCenter' => $workCenter])
                    ->andWhere(['Map/Plat' => $mapPlat])
                    ->andWhere(['Surveyor' => $surveyor])
                    ->andWhere(['Date' => $date])
                    ->all();

                if(count($masterLeakLogRecords) == 1)
                {
                    $data["MasterLeakLog"] = $masterLeakLogRecords[0];
                    $freq = $data["MasterLeakLog"]['SurveyFreq'];
                    $freq = str_replace('y', ' Year', $freq);
                    $freq = str_replace('Y', ' Year', $freq);
                    $data["MasterLeakLog"]['SurveyFreq'] = $freq;
                    $uid = $masterLeakLogRecords[0]['MasterLeakLogUID'];
                    $leakValues = WebManagementLeaks::find()
                        ->where(['MasterLeakLogUID' => $uid])
                        ->all();

                    $serviceValues = WebManagementEquipmentServices::find()
                        ->where(['MasterLeakLogUID' => $uid])
                        ->all();

                    foreach ($leakValues as $leak) {
                        $data["Leaks"][] = $leak;
                    }

                    foreach ($serviceValues as $service) {
                        $data["Services"][] = $service;
                    }

                    $data["SAPExceptions"] = null;
                }
            }
            //send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $data;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
    }


    public function actionGetMgmt($division, $workCenter=null, $surveyor = null, $startDate = null, $endDate = null, $search = null, $status='', $page=1, $perPage=25)
	{
        //TODO RBAC permission check
        try{

            $headers = getallheaders();
            WebManagementMasterLeakLog::setClient($headers['X-Client']);

            $counts = [];
            $counts['notApproved'] = 0;
            $counts['approvedOrNotSubmitted'] = 0;
            $counts['submittedOrPending'] = 0;
            $counts['exceptions'] = 0;
            $counts['completed'] = 0;

            if ($division && $workCenter) {
                $query = WebManagementMasterLeakLog::find();
                $query->where(['Division' => $division]);
                $query->andWhere(["WorkCenter" => $workCenter]);

                if ($surveyor) {
                    $query->andWhere(["Surveyor" => $surveyor]);
                }

                if (trim($search)) {
                    $query->andWhere([
                    'or',
                    ['like', 'Leaks', $search],
                    ['like', 'Division', $search],
                    ['like', 'Approved', $search],
                    ['like', 'HCA', $search],
                    ['like', 'Date', $search],
                    ['like', 'Surveyor', $search],
                    ['like', 'WorkCenter', $search],
                    ['like', 'FLOC', $search],
                    ['like', 'SurveyFreq', $search],
                    ['like', 'FeetOfMain', $search],
                    ['like', 'NumofServices', $search],
                    ['like', 'Hours', $search]
                ]);
            }
                if ($startDate !== null && $endDate !== null) {
                    $query->andWhere(['between', 'Date', $startDate, $endDate]);
                }

                $countersQuery = clone $query;
                $status = trim($status);
                if ($status) {
                    $query->andWhere(["Status" => $status]);
                }
                $countQuery = clone $query;

                $totalCount = $countQuery->count();
                $pages = new Pagination(['totalCount' => $totalCount]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPage(($page));
                $pages->setPageSize($perPage);

                $offset = $perPage * ($page - 1);

                $query->orderBy(['Date' => SORT_ASC, 'Surveyor' => SORT_ASC, 'FLOC' => SORT_ASC, 'Hours' => SORT_ASC]);

                $leaks = $query->offset($offset)
                    ->limit($perPage)
                    ->all();

                if ($division && $status && $workCenter) {
                    $countQueryNA = clone $countersQuery;
                    $countQueryA = clone $countersQuery;
                    $countQuerySP = clone $countersQuery;
                    $countQueryE = clone $countersQuery;
                    $countQueryC = clone $countersQuery;
                    //TODO rewrite to improve performance
                    $counts['notApproved'] = $countQueryNA
                        ->andWhere(['Status'=>'Not Approved'])
                        ->count();
                    $counts['approvedOrNotSubmitted'] = $countQueryA
                        ->andWhere(['Status'=>'Approved / Not Submitted'])
                        ->count();
                    $counts['submittedOrPending'] = $countQuerySP
                        ->andWhere(['Status'=>'Submitted / Pending'])
                        ->count();
                    $counts['exceptions'] = $countQueryE
                        ->andWhere(['Status'=>'Exceptions'])
                        ->count();
                    $counts['completed'] = $countQueryC
                        ->andWhere(['Status'=>'Completed'])
                        ->count();
                }
            } else {
                $pages = new Pagination(['totalCount' => 0]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPage(($page));
                $pages->setPageSize($perPage);
                $leaks =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $leaks;
            $data['pages'] = $pages;
//            $data['totalCount']  = $totalCount;
//            $data['offset'] = $pages->getOffset();
//            $data['limit'] = $pages->getLimit();
//            $command = $query->createCommand();
//            $data['sql'] = $command->sql;
//            $data['page'] = $page;
//            $data['perPage'] = $perPage;

            $data['counts'] = $counts;

			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $data;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {   Yii::trace($e->getMessage());
            throw new \yii\web\HttpException(400);
        }
	}

	public function actionGetTransferFloc() {
        $data = [];

        $data['Lan ID'] = 'PGE1';
        $data['Date'] = "8/29/2016 18:16";
        $data['Map-Plat'] = '0042-D13 (3 Year)';

        $data['Approval Lan ID'] = 'SUP1';
        $data['Approval Date'] = '08/31/2016 09:43';

        $currentData = [];
        $currentData['Work Center'] = 'San Fransisco';
        $currentData['FLOC'] = 'GD.PHYS.SNFA.0042.0D13';
        $newData = [];
        $newData['Work Center'] =
            [
                'San Francisco' => 'San Francisco',
                'New York City' => 'New York City'
            ];
        $newData['FLOC'] =
            [
                'GD.PHYS.SNFC.0001.0F12' => 'GD.PHYS.SNFC.0001.0F12',
                'GD.PHYS.SNFC.0002.0F13' => 'GD.PHYS.SNFC.0002.0F13'
            ];
        $records = [];
        $toBeTransfered = [];
        $toBeTransfered['Equipment'] = 3;
        $toBeTransfered['Leaks'] = 4;
        $records['toBeTransfered'] = $toBeTransfered;
        $completed = [];
        $completed['Equipment'] = 0;
        $completed['Leaks'] = 0;
        $records['Completed'] = $completed;

        $data['approved'] = true;
        $data['currentData'] = $currentData;
        $data['newData'] = $newData;
        $data['records'] = $records;


        //send response
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }
}