﻿






CREATE View [dbo].[vWebManagementMasterLeakLog]
AS
Select 

ISNULL(LeakInfo.LeakCount, 0) [Leaks],
CASE WHEN mll.StatusType not in ('Not Approved', 'In Progress') THEN 1 else 0 END [Approved],
CASE WHEN ISNULL(LeakInfo.HCA, 0) = 0 THEN 'No' ELSE 'Yes' END [HCA],
mll.CreateDate [Date], 
u.UserLastName + ', ' + u.UserFirstName + ' (' + u.UserLANID + ')' [Surveyor],
wc.WorkCenter [WorkCenter],
mg.FuncLocMap + '/' + mg.FuncLocPlat [Map/Plat],
CASE WHEN IR.InspectionFrequencyType is not null then IR.InspectionFrequencyType else ai.InspectionFreq END [SurveyFreq],
ISNULL(Services.FeetOfMain, 0) [FeetOfMain], 
ISNULL(Services.NumofServices, 0) [NumofServices],
ISNULL(Services.Hours, 0) [Hours], 
mll.MasterLeakLogUID [MasterLeakLogUID],
wc.Division,
mg.FLOC,
mll.StatusType [Status]
From 
	(Select MasterLeakLogUID, InspectionRequestLogUID, createduseruid, ServiceDate [CreateDate], StatusType, MapGridUID
	 From [dbo].[tMasterLeakLog] where ActiveFlag = 1)
        [MLL]
Left Join (Select MasterLeakLogUID, Sum(EstimatedHours) [Hours], Sum(EstimatedFeet) [FeetOfMain], Sum(EstimatedServices) [NumofServices] 
		   from tInspectionService
		   Where ActiveFlag = 1
		   Group by MasterLeakLogUID)
		[Services] on mll.MasterLeakLogUID = Services.MasterLeakLogUID
Join (select * from UserTb where UserActiveFlag = 1) U
	on MLL.CreatedUserUID = u.UserUID
Left Join (select * from tInspectionRequest where ActiveFlag = 1) IR on IR.InspectionRequestUID = MLL.InspectionRequestLogUID
Join (Select * from [dbo].[rgMapGridLog] where ActiveFlag = 1 and StatusType = 'Active') mg
	on MLL.MapGridUID = mg.MapGridUID
Join (Select * from rWorkCenter where ActiveFlag = 1)
	wc on mg.FuncLocMWC = wc.WorkCenterAbbreviationFLOC
Left Join	(select masterleaklogUID, Count(*) LeakCount, Max(case when PotentialHCAType = 'Y' THEN 1 ELSE 0 END) HCA
		from [dbo].[tgAssetAddressIndication] aai
		Group By masterleaklogUID)
	LeakInfo on mll.MasterLeakLogUID = LeakInfo.MasterLeakLogUID
Left Join 
		(select DropDownID, DropDownType, FilterName, SortSeq, FieldDisplay, FieldDescription, FieldValue 
		from rDropDown
		where FilterName = 'ddLHLeakMgmtCurrentStatus') 
	StatusDesc on mll.StatusType = StatusDesc.FieldValue
Left Join (Select MasterLeakLogUID, Count(*) [phcount] 
			From tInspectionService
			Where ActiveFlag = 1
				And StatusType <> 'Deleted'
				And PlaceHolderFlag = 1
			Group By MasterLeakLogUID) ph on ph.MasterLeakLogUID = mll.MasterLeakLogUID
Left Join (Select * from tgAssetInspection where ActiveFlag = 1) ai on ai.MasterLeakLogsUID = mll.MasterLeakLogUID
WHERE Services.MasterLeakLogUID is not null or LeakInfo.MasterLeakLogUID is not null







