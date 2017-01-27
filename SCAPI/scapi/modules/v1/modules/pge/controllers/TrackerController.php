<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\data\Pagination;
use app\modules\v1\modules\pge\models\WebManagementTrackerCurrentLocation;
use app\modules\v1\modules\pge\models\WebManagementTrackerHistory;
use app\modules\v1\modules\pge\models\WebManagementTrackerBreadcrumbs;
use app\modules\v1\modules\pge\models\WebManagementTrackerAOC;
use app\modules\v1\modules\pge\models\WebManagementTrackerIndications;
use app\modules\v1\modules\pge\models\WebManagementTrackerMapGridCompliance;
use app\modules\v1\modules\pge\models\AssetAddressCGE;

class TrackerController extends Controller 
{
    public $mapResultsLimit = 300; // limits the maximum returned results for map api calls
    public $downloadItemsLimit = 7000; // limits the maximum number of results the csv file will contain

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
                    'get' => ['get'],
                    'get-recent-activity' => ['get'],
                    'get-history' => ['get'],
                    'get-history-map-breadcrumbs' => ['get'],
                    'get-history-map-aocs' => ['get'],
                    'get-history-map-indications' => ['get'],
                    'get-history-map-compliance' => ['get'],
                    'get-history-map-controls' => ['get'],
                    'get-history-map-cgi' => ['get'],
                    'get-recent-activity-map-info' => ['get'],
                    'get-download-history-data' => ['get'],
                    'get-download-recent-activity-data' => ['get']

                ],  
            ];
		return $behaviors;	
	}
	
	public function actionGet($employee = null, $trackingGroup = null, $deviceID = null, $startDate, $endDate/*, $distanceFactor, $timeFactor*/)
	{
		try
		{
			$record1 = [];
			$record1["Date/Time"] = "05/10/2016 19:20:10";
			$record1["Surveyor"] = "John Doe";
			$record1["Latitude"] = "33.96323806";
			$record1["Longitude"] = "29.2019515";
			$record1["Speed"] = "2";
			$record1["Heading"] = "N";
			$record1["Street/City"] = "Corners North Ct, Peachtree Corners";
			$record1["State"] = "GA";
			$record1["Zip Code"] = "30071-1557";
			$record1["NumStats"] = "9";
			$record1["Pos Scr"] = "G";
			$record1["Landmark"] = "";
			$record1["Accuracy"] = "40";
			$record1["Tracking Group"] = "Diablo";
			$record1["Device ID"] = "12345678";
			
			$record2 = [];
			$record2["Date/Time"] = "05/09/2016 15:24:32";
			$record2["Surveyor"] = "Jane Doe";
			$record2["Latitude"] = "33.96323806";
			$record2["Longitude"] = "29.2019515";
			$record2["Speed"] = "3";
			$record2["Heading"] = "NW";
			$record2["Street/City"] = "Corners North Ct, Peachtree Corners";
			$record2["State"] = "GA";
			$record2["Zip Code"] = "30071-1557";
			$record2["NumStats"] = "5";
			$record2["Pos Scr"] = "G";
			$record2["Landmark"] = "";
			$record2["Accuracy"] = "40";
			$record2["Tracking Group"] = "Diablo";
			$record2["Device ID"] = "87654321";
			
			$record3 = [];
			$record3["Date/Time"] = "05/12/2016 07:53:45";
			$record3["Surveyor"] = "Bob Smith";
			$record3["Latitude"] = "33.96323806";
			$record3["Longitude"] = "29.2019515";
			$record3["Speed"] = "6";
			$record3["Heading"] = "E";
			$record3["Street/City"] = "Corners North Ct, Peachtree Corners";
			$record3["State"] = "GA";
			$record3["Zip Code"] = "30071-1557";
			$record3["NumStats"] = "7";
			$record3["Pos Scr"] = "G";
			$record3["Landmark"] = "";
			$record3["Accuracy"] = "40";
			$record3["Tracking Group"] = "Azmodan";
			$record3["Device ID"] = "13572468";
			
			$record4 = [];
			$record4["Date/Time"] = "05/11/2016 13:17:25";
			$record4["Surveyor"] = "Fred Milstone";
			$record4["Latitude"] = "33.96323806";
			$record4["Longitude"] = "29.2019515";
			$record4["Speed"] = "9";
			$record4["Heading"] = "SW";
			$record4["Street/City"] = "Corners North Ct, Peachtree Corners";
			$record4["State"] = "GA";
			$record4["Zip Code"] = "30071-1557";
			$record4["NumStats"] = "6";
			$record4["Pos Scr"] = "G";
			$record4["Landmark"] = "";
			$record4["Accuracy"] = "40";
			$record4["Tracking Group"] = "Malthael";
			$record4["Device ID"] = "24681357";
			
			$records = [];
			$records[] = $record1;
			$records[] = $record2;
			$records[] = $record3;
			$records[] = $record4;
			$recordCount = count($records);
			
			$data = [];
			
			//get employee
			if ($employee != null)
			{
				$employeeArray = explode(" ", $employee);
				$employeeLName = trim($employeeArray[0], ",");
				$employee = $employeeArray[1] . " " . $employeeLName;
			}
			
			//filter records
			for($i = 0; $i < $recordCount; $i++)
			{
				if($employee == null || $records[$i]["Surveyor"] == $employee)
				{
					if($trackingGroup == null || $records[$i]["Tracking Group"] == $trackingGroup)
					{
						if($deviceID == null || $records[$i]["Device ID"] == $deviceID)
						{
							if(BaseActiveController::inDateRange($records[$i]["Date/Time"], $startDate, $endDate))
							{
								$data[] = $records[$i];
							}
						}
					}
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

    public function actionGetRecentActivity($division, $workCenter=null, $surveyor = null, $startDate = null, $endDate = null, $search = null, $page=1, $perPage=25)
    {
        try{

            $headers = getallheaders();

            if ($division && $workCenter) {
                WebManagementTrackerCurrentLocation::setClient($headers['X-Client']);
                $query = WebManagementTrackerCurrentLocation::find();

                $query->where(['Division' => $division]);
                $query->andWhere(["Work Center" => $workCenter]);

                if ($surveyor) {
                    $query->andWhere(["Surveyor / Inspector" => $surveyor]);
                }

                if (trim($search)) {
                    $query->andWhere([
                        'or',
                        ['like', 'Division', $search],
                        ['like', '[Date]', $search],
                        ['like', '[Surveyor / Inspector]', $search],
                        ['like', '[Work Center]', $search],
                        ['like', 'Latitude', $search],
                        ['like', 'Longitude', $search],
                        ['like', '[Battery Level]', $search],
                        ['like', '[GPS Type]', $search],
                        ['like', '[Accuracy (Meters)]', $search]
                    ]);
                }
                if ($startDate !== null && $endDate !== null) {
                    // 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
                    $endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));

                    $query->andWhere(['between', 'Date', $startDate, $endDate]);
                }

                $countQuery = clone $query;

                /* page index is 0 based */
                $page = max($page-1,0);
                $totalCount = $countQuery->count();
                $pages = new Pagination(['totalCount' => $totalCount]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPageSize($perPage);
                $pages->setPage($page,true);
                $offset = $pages->getOffset();//$perPage * ($page - 1);
                $limit = $pages->getLimit();

//                $query->orderBy(['Date' => SORT_ASC, 'Surveyor / Inspector' => SORT_ASC]);

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->all();
//                $items = WebManagementTrackerCurrentLocation::find()->all();
//                $items = $query->offset($offset)
//                    ->limit($limit)
//                    ->createCommand();
//                $sqlString = $items->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
//                $items = $items->queryAll();
            } else {
                $pages = new Pagination(['totalCount' => 0]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPage(0);
                $pages->setPageSize($perPage);
                $items =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;
            $data['pages'] = $pages;

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $data;
            return $response;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetHistory($division, $workCenter=null, $surveyor = null, $startDate = null, $endDate = null, $search = null, $page=1, $perPage=25)
    {
        try{

            $headers = getallheaders();

            if ($division && $workCenter) {
                WebManagementTrackerHistory::setClient($headers['X-Client']);
                $query = WebManagementTrackerHistory::find();
                $query->where(['Division' => $division]);
                $query->andWhere(["Work Center" => $workCenter]);

                if ($surveyor) {
                    $query->andWhere(["Surveyor / Inspector" => $surveyor]);
                }

                if (trim($search)) {
                    $query->andWhere([
                        'or',
                        ['like', 'Division', $search],
                        ['like', 'Date', $search],
                        ['like', '[Surveyor / Inspector]', $search],
                        ['like', 'Work Center', $search],
                        ['like', 'Latitude', $search],
                        ['like', 'Longitude', $search],
                        ['like', '[Date Time]', $search],
                        ['like', 'House No', $search],
                        ['like', 'Street', $search],
                        ['like', 'Apt', $search],
                        ['like', 'City', $search],
                        ['like', 'State', $search],
                        ['like', 'Landmark', $search],
                        ['like', '[Landmark Description]', $search],
                        ['like', '[Accuracy (Meters)]', $search]
                    ]);
                }
                if ($startDate !== null && $endDate !== null) {
                    // 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
                    $endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));

                    $query->andWhere(['between', 'Date', $startDate, $endDate]);
                }

                $countQuery = clone $query;

                /* page index is 0 based */
                $page = max($page-1,0);
                $totalCount = $countQuery->count();
                $pages = new Pagination(['totalCount' => $totalCount]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPageSize($perPage);
                $pages->setPage($page,true);
                $offset = $pages->getOffset();//$perPage * ($page - 1);
                $limit = $pages->getLimit();

                $query->orderBy(['Date' => SORT_ASC, 'Surveyor / Inspector' => SORT_ASC]);

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->all();
//                $items = $query->offset($offset)
//                    ->limit($limit)
//                    ->createCommand();
//                $sqlString = $items->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
//                $items = $items->queryAll();

            } else {
                $pages = new Pagination(['totalCount' => 0]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPage(0);
                $pages->setPageSize($perPage);
                $items =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;
            $data['pages'] = $pages;

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $data;
            return $response;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetHistoryMapBreadcrumbs($division=null, $workCenter=null, $surveyor = null,
                                                   $startDate = null, $endDate = null, $search = null,
                                                   $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                                   $compliance=null, $cgi=null, $aoc=null, $indications=null, $surveyorBreadcrumbs = null)
    {
        try{

            $headers = getallheaders();

            if ($division && $workCenter) {
//                WebManagementTrackerBreadcrumbs::setClient($headers['X-Client']);
//                $query = WebManagementTrackerBreadcrumbs::find();
                WebManagementTrackerHistory::setClient($headers['X-Client']);
                $query = WebManagementTrackerHistory::find();


                $query->select([
                    'tb.UID',
                    'tb.LanID as Inspector',
                    'tb.SrcDTLT as Datetime',
                    'th.[House No] as [House No]',
                    'th.Street',
                    'th.City',
                    'th.State',
                    'tb.Latitude as Latitude',
                    'tb.Longitude as Longitude',
                    'tb.Speed as Speed',
                    'tb.GPSAccuracy as Accuracy'
                ]);
                $query->from([
                    'th'=>'['.WebManagementTrackerHistory::tableName().']',
                ]);
                $query->innerJoin(
                    ['tb'=>WebManagementTrackerBreadcrumbs::tableName()],
                    '[th].[UID]=[tb].[UID]'
                );

                $query->where(['[th].[Division]' => $division]);
                $query->andWhere(["[th].[Work Center]" => $workCenter]);

                // todo find out why the phpbuild in server crashed when also filtering by a set of lanids
                if (false && $surveyorBreadcrumbs) {
                    $sentLanIds = explode(',',$surveyorBreadcrumbs);
                    $filterConditions = null;

                    /*
                     * construct an array of the form
                     * ['LanID'=>value] for one entry
                     * [
                     *   'or',
                     *   ['LanID'=>value1],
                     *    ...
                     *   ['LanID'=>valuen]
                     * ] -- for multiple entries
                     */
                    foreach ($sentLanIds as $sentLanId) {
                        $lanId = trim(strtolower($sentLanId));//trim(strtolower($sentCgis));
                        if (''==$lanId){
                            continue;
                        }
                        if (null === $filterConditions){
                            $filterConditions = ['[tb].LanID'=>$lanId];
                        } elseif ( isset($filterConditions[0]) && $filterConditions[0]=='or') {
                            $filterConditions[]= ['[tb].LanID'=>$lanId];
                        } else {
                            $tmp = $filterConditions;
                            $filterConditions=[];
                            $filterConditions[0] = 'or';
                            $filterConditions[]= $tmp;
                            $filterConditions[]= ['[tb].LanID'=>$lanId];
                        }
                    }
                    if (null!=$filterConditions) {
                        $query->andWhere($filterConditions);
                    }

                } else if ($surveyor) {
                    $query->andWhere(["Surveyor / Inspector" => $surveyor]);
                }

                if (trim($search)) {
                    $query->andWhere([
                        'or',
                        ['like', 'th.Division', $search],
                        ['like', 'th.Date', $search],
                        ['like', 'th.[Surveyor / Inspector]', $search],
                        ['like', 'th.[Work Center]', $search],
                        ['like', 'th.Latitude', $search],
                        ['like', 'th.Longitude', $search],
                        ['like', 'th.[Date Time]', $search],
                        ['like', 'th.[House No]', $search],
                        ['like', 'th.Street', $search],
                        ['like', 'th.Apt', $search],
                        ['like', 'th.City', $search],
                        ['like', 'th.State', $search],
                        ['like', 'th.Landmark', $search],
                        ['like', 'th.[Landmark Description]', $search],
                        ['like', 'th.[Accuracy (Meters)]', $search]
                    ]);
                }
                if ($startDate !== null && $endDate !== null) {
                    // 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
                    $endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));

                    $query->andWhere(['between', 'th.Date', $startDate, $endDate]);
                }

                if (null!=$minLat){
                    $query->andWhere(['>=','tb.Latitude',$minLat]);
                }

                if (null!=$maxLat){
                    $query->andWhere(['<=','tb.Latitude',$maxLat]);
                }

                if (null!=$minLong){
                    $query->andWhere(['>=','tb.Longitude',$minLong]);
                }

                if (null!=$maxLong){
                    $query->andWhere(['<=','tb.Longitude',$maxLong]);
                }

                $limit =$this->mapResultsLimit;
                $offset = 0;

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
//                $sqlString = $items->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $items = $items->queryAll();
            } else {
                $items =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $data;
            return $response;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetHistoryMapCgi($division=null, $workCenter=null, $surveyor = null,
                                            $startDate = null, $endDate = null, $search = null,
                                            $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                            $compliance=null, $cgi=null, $aoc=null, $indications=null, $surveyorBreadcrumbs = null)
    {
        try{

            $headers = getallheaders();
            if ($aoc) {
                AssetAddressCGE::setClient($headers['X-Client']);
                $query = AssetAddressCGE::find();

                $query->select(['CreatedUserUID','SrcDTLT','Latitude','Longitude','StatusType','CGEReasonType','CGECardNo']);
                $sentCgis = explode(',',$cgi);
                $filterConditions = null;
                /*
                 * construct an array of the form
                 * ['CreatedUserUID'=>value] for one entry
                 * [
                 *   'or',
                 *   ['CreatedUserUID'=>value1],
                 *    ...
                 *   ['CreatedUserUID'=>valuen]
                 * ] -- for multiple entries
                 */
                foreach ($sentCgis as $sentCgi) {
                    $uid = trim(strtolower($sentCgi));//trim(strtolower($sentCgis));
                    if (''==$uid){
                        continue;
                    }
                    if (null === $filterConditions){
                        $filterConditions = ['CreatedUserUID'=>$uid];
                    } elseif ( isset($filterConditions[0]) && $filterConditions[0]=='or') {
                        $filterConditions[]= ['CreatedUserUID'=>$uid];
                    } else {
                        $tmp = $filterConditions;
                        $filterConditions=[];
                        $filterConditions[0] = 'or';
                        $filterConditions[]= $tmp;
                        $filterConditions[]= ['CreatedUserUID'=>$uid];
                    }
                }
                if (null!=$filterConditions) {
                    $query->andWhere($filterConditions);
                }

                if (null!=$minLat){
                    $query->andWhere(['>=','Latitude',$minLat]);
                }

                if (null!=$maxLat){
                    $query->andWhere(['<=','Latitude',$maxLat]);
                }

                if (null!=$minLong){
                    $query->andWhere(['>=','Longitude',$minLong]);
                }

                if (null!=$maxLong){
                    $query->andWhere(['<=','Longitude',$maxLong]);
                }

// TODO see if the workcenter, surveyor.... filter should be applied here

                $limit =$this->mapResultsLimit;
                $offset = 0;
//                $items = $query->offset($offset)
//                    ->limit($limit)
//                    ->all();

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
                $sqlString = $items->sql;
                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $items = $items->queryAll();

            } else {
                $items =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $data;
            return $response;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetHistoryMapAocs($division=null, $workCenter=null, $surveyor = null,
                                            $startDate = null, $endDate = null, $search = null,
                                            $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                            $compliance=null, $cgi=null, $aoc=null, $indications=null, $surveyorBreadcrumbs = null)
    {
        try{

            $headers = getallheaders();
            if ($aoc) {
                WebManagementTrackerAOC::setClient($headers['X-Client']);
                $query = WebManagementTrackerAOC::find();
                $query->select([
                    'UID',
                    'LanID as Inspector',
                    'SurveyDateTime as Datetime',
                    'HouseNo as [House No]',
                    'Street1 as Street',
                    'City',
                    'State',
                    'Latitude',
                    'Longitude',
                    'AOCType as [AOC Type]'
                ]);
//                $aocPossibleValues = ['19'=>'19',
//                    '31'=>'31',
//                    '32'=>'32',
//                    '33'=>'33',
//                    '23'=>'23',
//                    '30'=>'30',
//                    '20'=>'20',
//                    '34'=>'34',
//                    '35'=>'35',
//                    '36'=>'36',
//                    '37'=>'37',
//                    '38'=>'38',
//                    '39'=>'39',
//                    '51'=>'51',
//                    '52'=>'52',
//                    '54'=>'54',
//                    '56'=>'56',
//                    '57'=>'57',
//                    '58'=>'58',
//                    '59'=>'59',
//                    '99'=>'99',
//                ];

                $sentAocs = explode(',',$aoc);
                $filterConditions = null;
                /*
                 * construct an array of the form
                 * ['RTRIM(SUBSTRING(AOCType, 1,CHARINDEX('-',AOCType)-1))'=>value] for one entry
                 * [
                 *   'or',
                 *   ['RTRIM(SUBSTRING(AOCType, 1,CHARINDEX('-',AOCType)-1))'=>value1],
                 *    ...
                 *   ['RTRIM(SUBSTRING(AOCType, 1,CHARINDEX('-',AOCType)-1))'=>valuen]
                 * ] -- for multiple entries
                 */
                foreach ($sentAocs as $sentAoc) {
                    $aocTypeCode = intval(trim($sentAoc));
                    if (null === $filterConditions){
                        $filterConditions = ['RTRIM(SUBSTRING(AOCType, 1,CHARINDEX(\'-\',AOCType)-1))'=>$aocTypeCode];
                    } elseif ( isset($filterConditions[0]) && $filterConditions[0]=='or') {
                        $filterConditions[]= ['RTRIM(SUBSTRING(AOCType, 1,CHARINDEX(\'-\',AOCType)-1))'=>$aocTypeCode];
                    } else {
                        $tmp = $filterConditions;
                        $filterConditions=[];
                        $filterConditions[0] = 'or';
                        $filterConditions[]= $tmp;
                        $filterConditions[]= ['RTRIM(SUBSTRING(AOCType, 1,CHARINDEX(\'-\',AOCType)-1))'=>$aocTypeCode];
                    }
                }
                $query->andWhere($filterConditions);

                if ($surveyorBreadcrumbs) {
                    $sentLanIds = explode(',',$surveyorBreadcrumbs);
                    $filterConditions = null;

                    /*
                     * construct an array of the form
                     * ['LanID'=>value] for one entry
                     * [
                     *   'or',
                     *   ['LanID'=>value1],
                     *    ...
                     *   ['LanID'=>valuen]
                     * ] -- for multiple entries
                     */
                    foreach ($sentLanIds as $sentLanId) {
                        $lanId = trim(strtolower($sentLanId));//trim(strtolower($sentCgis));
                        if (''==$lanId){
                            continue;
                        }
                        if (null === $filterConditions){
                            $filterConditions = ['LanID'=>$lanId];
                        } elseif ( isset($filterConditions[0]) && $filterConditions[0]=='or') {
                            $filterConditions[]= ['LanID'=>$lanId];
                        } else {
                            $tmp = $filterConditions;
                            $filterConditions=[];
                            $filterConditions[0] = 'or';
                            $filterConditions[]= $tmp;
                            $filterConditions[]= ['LanID'=>$lanId];
                        }
                    }
                    if (null!=$filterConditions) {
                        $query->andWhere($filterConditions);
                    }
                }

                if (null!=$minLat){
                    $query->andWhere(['>=','Latitude',$minLat]);
                }

                if (null!=$maxLat){
                    $query->andWhere(['<=','Latitude',$maxLat]);
                }

                if (null!=$minLong){
                    $query->andWhere(['>=','Longitude',$minLong]);
                }

                if (null!=$maxLong){
                    $query->andWhere(['<=','Longitude',$maxLong]);
                }

// TODO see if the workcenter, surveyor.... filter should be applied here

                $limit =$this->mapResultsLimit;
                $offset = 0;
//                $items = $query->offset($offset)
//                    ->limit($limit)
//                    ->all();

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
                $sqlString = $items->sql;
                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $items = $items->queryAll();

            } else {
                $items =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $data;
            return $response;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetHistoryMapIndications($division=null, $workCenter=null, $surveyor = null,
                                                   $startDate = null, $endDate = null, $search = null,
                                                   $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                                   $compliance=null, $cgi=null, $aoc=null, $indications=null, $surveyorBreadcrumbs = null)
    {
        try{

            $headers = getallheaders();

            if ($indications) {
                WebManagementTrackerIndications::setClient($headers['X-Client']);
                $query = WebManagementTrackerIndications::find();
                $query->select([
                    'UID',
                    'LanID as Inspector',
                    'SurveyDateTime as Datetime',
                    'HouseNo as [House No]',
                    'Street1 as Street',
                    'City',
                    'State',
                    'Latitude',
                    'Longitude',
                    'AboveBelowGroundType as [Leak Source]',//'InitialLeakSourceType as [Leak Source]',
                    'SORLType as [Leak SORL]',
                    'fndEquipmentType as [Leak Found By]',//'FoundBy as [Leak Found By]',
                    'grdEquipmentType as [Leak Grade By]',//'GradeBy as [Leak Grade By]',
                    'ReadingGrade as [Leak % Gas]',
                    'GradeType as [Leak Grade]'

                ]);
                $indPossibleValues = ['1'=>'1','2p'=>'2+','2'=>'2','3'=>'3'];

                $sentIndications = explode(',',$indications);
                $indFilterConditions = null;

                /*
                 * construct an array of the form
                 * ['GradeType'=>value] for one entry
                 * [
                 *   'or',
                 *   ['GradeType'=>value1],
                 *    ...
                 *   ['GradeType'=>valuen]
                 * ] -- for multiple entries
                 */
                foreach ($sentIndications as $sentIndication) {
                    $indKey = trim(strtolower($sentIndication));
                    if (isset($indPossibleValues[$indKey])){
                        if (null === $indFilterConditions){
                            $indFilterConditions = ['GradeType'=>$indPossibleValues[$indKey]];
                        } elseif ( isset($indFilterConditions[0]) && $indFilterConditions[0]=='or') {
                            $indFilterConditions[]= ['GradeType'=>$indPossibleValues[$indKey]];
                        } else {
                            $tmp = $indFilterConditions;
                            $indFilterConditions=[];
                            $indFilterConditions[0] = 'or';
                            $indFilterConditions[]= $tmp;
                            $indFilterConditions[]= ['GradeType'=>$indPossibleValues[$indKey]];
                        }
                    }
                }
                $query->andWhere($indFilterConditions);

                if ($surveyorBreadcrumbs) {
                    $sentLanIds = explode(',',$surveyorBreadcrumbs);
                    $filterConditions = null;

                    /*
                     * construct an array of the form
                     * ['LanID'=>value] for one entry
                     * [
                     *   'or',
                     *   ['LanID'=>value1],
                     *    ...
                     *   ['LanID'=>valuen]
                     * ] -- for multiple entries
                     */
                    foreach ($sentLanIds as $sentLanId) {
                        $lanId = trim(strtolower($sentLanId));//trim(strtolower($sentCgis));
                        if (''==$lanId){
                            continue;
                        }
                        if (null === $filterConditions){
                            $filterConditions = ['LanID'=>$lanId];
                        } elseif ( isset($filterConditions[0]) && $filterConditions[0]=='or') {
                            $filterConditions[]= ['LanID'=>$lanId];
                        } else {
                            $tmp = $filterConditions;
                            $filterConditions=[];
                            $filterConditions[0] = 'or';
                            $filterConditions[]= $tmp;
                            $filterConditions[]= ['LanID'=>$lanId];
                        }
                    }
                    if (null!=$filterConditions) {
                        $query->andWhere($filterConditions);
                    }
                }

                if (null!=$minLat){
                    $query->andWhere(['>=','Latitude',$minLat]);
                }

                if (null!=$maxLat){
                    $query->andWhere(['<=','Latitude',$maxLat]);
                }

                if (null!=$minLong){
                    $query->andWhere(['>=','Longitude',$minLong]);
                }

                if (null!=$maxLong){
                    $query->andWhere(['<=','Longitude',$maxLong]);
                }

                $limit =$this->mapResultsLimit;
                $offset = 0;

//                $items = $query->offset($offset)
//                    ->limit($limit)
//                    ->all();

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
//                $sqlString = $items->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $items = $items->queryAll();
            } else {
                $items =[];
            } // end indications check

            $data = [];
            $data['results'] = $items;

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $data;
            return $response;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetHistoryMapCompliance($division=null, $workCenter=null, $surveyor = null,
                                                  $startDate = null, $endDate = null, $search = null,
                                                  $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                                  $compliance=null, $cgi=null, $aoc=null, $indications=null, $surveyorBreadcrumbs = null)
    {
        try{

            $headers = getallheaders();

//            if ($division && $workCenter) {
            WebManagementTrackerMapGridCompliance::setClient($headers['X-Client']);
            $query = WebManagementTrackerMapGridCompliance::find();
//            $query->select([
//            ]);
//            $query->where(['Division' => $division]);
//            $query->where(['Division' => $division]);
//            $query->andWhere(["Work Center" => $workCenter]);
//
//            if ($surveyor) {
//                $query->andWhere(["Surveyor / Inspector" => $surveyor]);
//            }
//
//            if (trim($search)) {
// ?????
//            }

//            if ($startDate !== null && $endDate !== null) {
                // 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
//                $endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));
//
//                $query->andWhere(['between', 'Date', $startDate, $endDate]);
//            }

            // the compliance view does not have Lat and Long
//            if (null!=$minLat){
//                $query->andWhere(['>=','Latitude',$minLat]);
//            }
//
//            if (null!=$maxLat){
//                $query->andWhere(['<=','Latitude',$maxLat]);
//            }
//
//            if (null!=$minLong){
//                $query->andWhere(['>=','Longitude',$minLong]);
//            }
//
//            if (null!=$maxLong){
//                $query->andWhere(['<=','Longitude',$maxLong]);
//            }

            $limit =$this->mapResultsLimit;
            $offset = 0;
            $items = $query->offset($offset)
                ->limit($limit)
                ->all();

//            } else {
//                $items =[];
//            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $data;
            return $response;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetRecentActivityMapInfo($division=null, $workCenter=null, $surveyor = null,
                                                   $startDate = null, $endDate = null, $search = null,
                                                   $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                                   $compliance=null, $aoc=null, $indications=null, $surveyorBreadcrumbs = null)
    {
        try{

            $headers = getallheaders();

            if ($division && $workCenter) {
                WebManagementTrackerCurrentLocation::setClient($headers['X-Client']);
                $query = WebManagementTrackerCurrentLocation::find();
                $query->select([
                    '[Surveyor / Inspector] as Inspector',
                    'Date', // Date time is not present in the view
//                    'City',
//                    'State',
                    'Latitude',
                    'Longitude',
                    '[Accuracy (Meters)] as Accuracy'
                ]);
                $query->where(['Division' => $division]);
                $query->andWhere(["Work Center" => $workCenter]);

                if ($surveyor) {
                    $query->andWhere(["Surveyor / Inspector" => $surveyor]);
                }

                if (trim($search)) {
                    $query->andWhere([
                        'or',
                        ['like', 'Division', $search],
                        ['like', '[Date]', $search],
                        ['like', '[Surveyor / Inspector]', $search],
                        ['like', '[Work Center]', $search],
                        ['like', 'Latitude', $search],
                        ['like', 'Longitude', $search],
                        ['like', '[Battery Level]', $search],
                        ['like', '[GPS Type]', $search],
                        ['like', '[Accuracy (Meters)]', $search]
                    ]);
                }
                if ($startDate !== null && $endDate !== null) {
                    // 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
                    $endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));

                    $query->andWhere(['between', 'Date', $startDate, $endDate]);
                }

                if (null!=$minLat){
                    $query->andWhere(['>=','Latitude',$minLat]);
                }

                if (null!=$maxLat){
                    $query->andWhere(['<=','Latitude',$maxLat]);
                }

                if (null!=$minLong){
                    $query->andWhere(['>=','Longitude',$minLong]);
                }

                if (null!=$maxLong){
                    $query->andWhere(['<=','Longitude',$maxLong]);
                }

                $limit =$this->mapResultsLimit;
                $offset = 0;

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
//                $sqlString = $items->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $items = $items->queryAll();


            } else {
                $items =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $data;
            return $response;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetDownloadHistoryData($division, $workCenter=null, $surveyor = null, $startDate = null,
                                              $endDate = null, $search = null)
    {
        try{
            $headers = getallheaders();

            if ($division && $workCenter) {
                WebManagementTrackerHistory::setClient($headers['X-Client']);
                $query = WebManagementTrackerHistory::find();

                $query->select(
                    [
                        '[Date Time]',
                        '[Surveyor / Inspector]',
                        '[Latitude]',
                        '[Longitude]',
                        '[House No]',
                        '[Street]',
                        '[Apt]',
                        '[City]',
                        '[State]',
                        '[Landmark]',
                        '[Landmark Description]',
                        '[Accuracy (Meters)]',
                    ]
                );
                $query->where(['Division' => $division]);
                $query->andWhere(["Work Center" => $workCenter]);

                if ($surveyor) {
                    $query->andWhere(["Surveyor / Inspector" => $surveyor]);
                }

                if (trim($search)!=='') {
                    $query->andWhere([
                        'or',
                        ['like', 'Division', $search],
                        ['like', 'Date', $search],
                        ['like', '[Surveyor / Inspector]', $search],
                        ['like', 'Work Center', $search],
                        ['like', 'Latitude', $search],
                        ['like', 'Longitude', $search],
                        ['like', '[Date Time]', $search],
                        ['like', 'House No', $search],
                        ['like', 'Street', $search],
                        ['like', 'Apt', $search],
                        ['like', 'City', $search],
                        ['like', 'State', $search],
                        ['like', 'Landmark', $search],
                        ['like', '[Landmark Description]', $search],
                        ['like', '[Accuracy (Meters)]', $search]
                    ]);
                }
                if ($startDate !== null && $endDate !== null) {
                    // 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
                    $endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));

                    $query->andWhere(['between', 'Date', $startDate, $endDate]);
                }

                $offset = 0;
                $limit = $this->downloadItemsLimit;
                $query->orderBy(['Date' => SORT_ASC, 'Surveyor / Inspector' => SORT_ASC]);


                $items = $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
//                $sqlString = $items->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $items = $items->queryAll();

            } else {
                $items =[];
            } // end division and workcenter check

            //send response
            Yii::$app->response->format = Response::FORMAT_RAW;
            $headers = Yii::$app->response->headers;
//            $headers->set('Content-Disposition', 'attachment; filename="tracker_history_'.date('Y-m-d').'.csv');
            $headers->set('Content-Type', 'text/csv');
            $firstLine = true;
            // TODO find a better way of outputting the CSV than this workaround with output buffers
            ob_start();
            $fp = fopen('php://output','w');
            foreach ($items as $line) {
                if($firstLine) {
                    $firstLine = false;
                    fputcsv($fp, array_keys($line));
                }
                fputcsv($fp, $line);
            }
            fclose($fp);
            //$a = stream_get_contents($fp);
            $csvContents = ob_get_clean();
            //var_dump($a);
            //var_dump($response);
            Yii::$app->response->content = $csvContents;

            return ;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetDownloadRecentActivityData($division, $workCenter=null, $surveyor = null, $startDate = null,
                                                     $endDate = null, $search = null)
    {
        try{

            $headers = getallheaders();

            if ($division && $workCenter) {
                WebManagementTrackerCurrentLocation::setClient($headers['X-Client']);
                $query = WebManagementTrackerCurrentLocation::find();

                $query->select(
                    [
                        '[Date]',
                        '[Surveyor / Inspector]',
                        '[Latitude]',
                        '[Longitude]',
                        '[Battery Level]',
                        '[GPS Type]',
                        '[Accuracy (Meters)]',
                    ]
                );
                $query->where(['Division' => $division]);
                $query->andWhere(["Work Center" => $workCenter]);

                if ($surveyor) {
                    $query->andWhere(["Surveyor / Inspector" => $surveyor]);
                }

                if (trim($search)) {
                    $query->andWhere([
                        'or',
                        ['like', 'Division', $search],
                        ['like', '[Date]', $search],
                        ['like', '[Surveyor / Inspector]', $search],
                        ['like', '[Work Center]', $search],
                        ['like', 'Latitude', $search],
                        ['like', 'Longitude', $search],
                        ['like', '[Battery Level]', $search],
                        ['like', '[GPS Type]', $search],
                        ['like', '[Accuracy (Meters)]', $search]
                    ]);
                }

                if ($startDate !== null && $endDate !== null) {
                    // 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
                    $endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));

                    $query->andWhere(['between', 'Date', $startDate, $endDate]);
                }

                $offset = 0;
                $limit = $this->downloadItemsLimit;

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
//                $sqlString = $items->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $items = $items->queryAll();
            } else {
                $items =[];
            } // end division and workcenter check

            //send response
            Yii::$app->response->format = Response::FORMAT_RAW;
            $headers = Yii::$app->response->headers;
//            $headers->set('Content-Disposition', 'attachment; filename="tracker_recent_activity_'.date('Y-m-d').'.csv');
            $headers->set('Content-Type', 'text/csv');
            $firstLine = true;
            // TODO find a better way of outputting the CSV than this workaround with output buffers
            ob_start();
            $fp = fopen('php://output','w');
            foreach ($items as $line) {
                if($firstLine) {
                    $firstLine = false;
                    fputcsv($fp, array_keys($line));
                }
                fputcsv($fp, $line);
            }
            fclose($fp);
            //$a = stream_get_contents($fp);
            $csvContents = ob_get_clean();
            //var_dump($a);
            //var_dump($response);
            Yii::$app->response->content = $csvContents;

            return ;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }
}