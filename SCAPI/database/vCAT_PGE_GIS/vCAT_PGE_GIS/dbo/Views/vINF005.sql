﻿


CREATE VIEW [dbo].[vINF005] AS 

SELECT
 CAST(el.[PRNTFNO] AS VARCHAR(12)) AS [PRNTFNO]
,CAST(el.[SAPEQID] AS VARCHAR(18)) AS [SAPEQID]
,CAST(el.[EQOBJTYPE] AS VARCHAR(10)) AS [EQOBJTYPE]
,CAST(el.[EQSERNO] AS VARCHAR(25)) AS [EQSERNO]

,CASE WHEN CLB.MPRStatus IS NULL THEN FORMAT(CLB.CalbDate, 'MMddyyyy') ELSE NULL END AS [CALBDATE]
,CASE WHEN CLB.MPRStatus IS NULL THEN FORMAT(CLB.CalbDate, 'hhmmss') ELSE NULL END AS [CALBTIME]
,CASE WHEN CLB.EquipmentLogUID IS NOT NULL AND CLB.MPRStatus IS NULL THEN 'PASS' ELSE 'NU' END [CALBSTAT]
,CASE WHEN CLB.MPRStatus IS NULL THEN CAST(svor.UserLANID AS VARCHAR(4)) ELSE NULL END AS [SRVY_LANID]	
,CASE WHEN CLB.MPRStatus IS NULL THEN CAST(sup.UserLANID AS VARCHAR(4))	ELSE NULL END AS [SPVR_LANID]
,CASE WHEN CLB.EquipmentLogUID IS NOT NULL AND CLB.MPRStatus IS NULL THEN .25 ELSE NULL END AS [CALB_HRS]

,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGIDPIR' AND CLB.CalibrationVerificationFlag = 1 THEN 'YES' ELSE NULL END AS [DPIR_TEST_OK]
,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGIDPIR' AND CLB.CalibrationVerificationFlag = 1 THEN CAST(CAST(CLB.ReadPPM AS NUMERIC(6,2)) AS VARCHAR(6)) ELSE NULL END AS [DPIR_READ_PPM]
,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGIDPIR' AND CLB.AlarmPPM = 1 THEN 5.0 ELSE NULL END AS [DPIR_ALRM_PPM]

,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGIRMLD' AND CLB.CalibrationVerificationFlag = 1 THEN 'YES' ELSE NULL END AS [RMLD_TEST_OK]
,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGIRMLD' AND CLB.LaserCalb = 1 THEN 'YES' ELSE NULL END AS [RMLD_LASER_CAL]
,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGIRMLD' AND CLB.CalibrationVerificationFlag = 1 THEN CAST(CAST(CLB.ReadPPM AS NUMERIC(6,2)) AS VARCHAR(6)) ELSE NULL END AS [RMLD_READ_PPM]
,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGIRMLD' AND CLB.AlarmPPM = 1 THEN 5.0 ELSE NULL END AS [RMLD_ALRM_PPM]

,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGIFMPK' AND CLB.CalibrationVerificationFlag = 1 THEN CAST(CAST(CLB.ReadPPM AS NUMERIC(6,2)) AS VARCHAR(6)) ELSE NULL END AS [FPMK_READ_PPM]

,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGIOMD'  AND CLB.CalibrationVerificationFlag = 1 THEN CAST(CAST(CLB.OMDExmQty AS NUMERIC(6,2)) AS VARCHAR(6)) ELSE NULL END AS [OMD_EXMN_QTY]
,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGIOMD'  AND CLB.CalibrationVerificationFlag = 1 THEN CAST(CAST(CLB.ReadPPM AS NUMERIC(6,2)) AS VARCHAR(6)) ELSE NULL END AS [OMD_READ_PPM]

,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGISCOP' AND CLB.CalibrationVerificationFlag = 1 THEN 2.5 ELSE NULL END AS [SCOP_TEST_KIT]
,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGISCOP' AND CLB.CalibrationVerificationFlag = 1 THEN CAST(CAST(CLB.PLELRead AS NUMERIC(6,2)) AS VARCHAR(6)) ELSE NULL END AS [SCOP_PLEL_RDG]
,CASE WHEN CLB.MPRStatus IS NULL AND CLB.EquipmentType = 'G_COGISCOP' AND CLB.CalibrationVerificationFlag = 1 THEN CAST(CAST(CLB.PGASRead AS NUMERIC(6,2)) AS VARCHAR(6)) ELSE NULL END AS [SCOP_PGAS_RDG]

,NULL AS [MPR_RPT_BY]
,NULL AS [MPR_ASGN_TO]
,NULL AS [MPR_LOC]
,NULL AS [MPR_RPT_DATE]
,NULL AS [MPR_CAT]
,NULL AS [MPR_DFT_TYPE]
,NULL AS [MPR_DFT_OTHR]
,NULL AS [MPR_MNF]
,NULL AS [MPR_MAT_TYPE]
,NULL AS [MPR_MAT_AGE]
,NULL AS [MPR_SFTY_ISU]
,NULL AS [MPR_MAT_QTY] 
,NULL AS [MPR_CAUSE_PRBLM]
,NULL AS [MPR_DESC]

-- SELECT *
-- SELECT Count(*) 

FROM [dbo].[tEquipmentLog] el 
LEFT JOIN (
			SELECT CLB1.* 
			FROM [dbo].[tInspectionsEquipment] CLB1 -- WHERE CAST(SrcDTLT AS DATE) = '2016-11-07' ORDER BY SerialNumber, SrvDTLT  -- 36
				INNER JOIN (
								SELECT EquipmentLogUID, MIN(SrcDTLT) AS FirstDateTime 
								FROM [dbo].[tInspectionsEquipment] 
								WHERE Revision = 0
								GROUP BY EquipmentLogUID
							) CLB2 
								ON	CLB1.EquipmentLogUID = CLB2.EquipmentLogUID 
								AND CLB1.SrcDTLT = CLB2.FirstDateTime
			WHERE CLB1.ActiveFlag = 1
		) CLB ON el.EquipmentLogUID = CLB.EquipmentLogUID

LEFT  JOIN [dbo].[UserTb] svor 
	ON	svor.UserUID = clb.CreatedUserUID
	AND svor.UserActiveFlag = 1

LEFT  JOIN [dbo].[rWorkCenter] wc
	ON wc.WorkCenterAbbreviation = clb.MWC

LEFT  JOIN [dbo].[UserTb] sup 
	ON	sup.UserUID = wc.SupervisorUID
	AND sup.UserActiveFlag = 1
	
WHERE el.ActiveFlag=1