﻿


CREATE View [dbo].[vWebManagementLeaks]
AS
select
aai.StatusType [Status],
CASE WHEN aai.StatusType not in ('In Progress', 'NotApproved', 'Rejected') THEN 1 ELSE 0 END [Approved],
CASE WHEN aai.PotentialHCAType = 'Y' THEN 'Yes' ELSE 'No' END HCA,
aai.Map + '/' + aai.Plat [Map/Plat],
ISNULL(aai.SAPNo, '') [SAPLeakNumber],
aai.AboveBelowGroundType [AboveBelowGround],
aai.FoundDateTime,
aa.HouseNo + ' ' + aa.Street1 [Address],
aa.City,
aai.SORLType [SORL],
aai.ReadingGrade [ReadingInPct],
aai.FoundBy [InstTypeFoundBy],
aai.GradeBy [InstTypeGradeBy],
aai.GradeType [Grade],
ISNULL(aai.Comments, '') [LocationRemarks],
aai.AssetAddressIndicationUID [UID],
aai.MasterLeakLogUID,
wc.Division,
aai.Map + '-' + aai.Plat [MapPlatNumber],
CASE WHEN aai.MapPlatLeakNumber is not null THEN CAST(aai.mapplatleaknumber as varchar(10)) + '/' ELSE '' END + ISNULL(aai.LeakNo, '') [LeakNo],
aai.LockedFlag [LockFlag]
from (Select * from tgAssetAddressIndication where ActiveFlag = 1) aai
--Join (select * from rDropDown where FilterName = 'ddLHLeakMgmtCurrentStatus' and ActiveFlag = 1) dd on aai.StatusType = dd.FieldValue
Join (Select * from tgAssetAddress where ActiveFlag = 1) aa on aai.AssetAddressUID = aa.AssetAddressUID
Join (Select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg on mg.MapGridUID = aai.MapGridUID
join (Select * from [dbo].[rWorkCenter] where ActiveFlag = 1) wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC

