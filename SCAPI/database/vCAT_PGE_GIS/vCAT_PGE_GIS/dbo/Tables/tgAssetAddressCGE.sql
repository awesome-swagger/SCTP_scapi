﻿CREATE TABLE [dbo].[tgAssetAddressCGE] (
    [AssetAddressCGEID]               INT                IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [AssetAddressCGEUID]              VARCHAR (100)      NOT NULL,
    [AssetAddressUID]                 VARCHAR (100)      NOT NULL,
    [AssetInspectionUID]              VARCHAR (100)      NULL,
    [MasterLeakLogUID]                VARCHAR (100)      NOT NULL,
    [MapGridUID]                      VARCHAR (100)      NOT NULL,
    [ProjectID]                       INT                NULL,
    [SourceID]                        VARCHAR (100)      NULL,
    [CreatedUserUID]                  VARCHAR (100)      NOT NULL,
    [ModifiedUserUID]                 VARCHAR (100)      CONSTRAINT [DF_tgAssetAddressCGE_ModifiedUserUID] DEFAULT ('') NOT NULL,
    [SrcDTLT]                         DATETIME           NULL,
    [SrvDTLT]                         DATETIME           CONSTRAINT [DF_g_AssetCGEs_SrvDTLT] DEFAULT (getdate()) NULL,
    [SrvDTLTOffset]                   DATETIMEOFFSET (7) CONSTRAINT [DF_g_AssetCGEs_SrvDTLTOffset] DEFAULT (sysdatetimeoffset()) NULL,
    [SrcOpenDTLT]                     DATETIME           NULL,
    [SrcClosedDTLT]                   DATETIME           NULL,
    [GPSType]                         VARCHAR (100)      NULL,
    [GPSSentence]                     VARCHAR (400)      NULL,
    [Latitude]                        FLOAT (53)         NULL,
    [Longitude]                       FLOAT (53)         NULL,
    [SHAPE]                           [sys].[geography]  NULL,
    [Comments]                        VARCHAR (2000)     NULL,
    [RevisionComments]                VARCHAR (500)      NULL,
    [Revision]                        INT                CONSTRAINT [DF_g_AssetCGEs_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]                      BIT                CONSTRAINT [DF_tgAssetAddressCGE_ActiveFlag] DEFAULT ((1)) NULL,
    [StatusType]                      VARCHAR (100)      CONSTRAINT [DF_g_AssetCGEs_StatusType] DEFAULT ('Active') NULL,
    [CGENIFType]                      VARCHAR (200)      NULL,
    [CGEReasonType]                   VARCHAR (200)      NULL,
    [NIFReasonType]                   VARCHAR (200)      NULL,
    [CGECardFlag]                     BIT                NULL,
    [CGECardNo]                       VARCHAR (100)      NULL,
    [Photo1]                          VARCHAR (250)      NULL,
    [Photo2]                          VARCHAR (250)      NULL,
    [Photo3]                          VARCHAR (250)      NULL,
    [ApprovedFlag]                    BIT                NULL,
    [ApprovedByUserUID]               VARCHAR (100)      NULL,
    [ApprovedDTLT]                    DATETIME           NULL,
    [SubmittedFlag]                   BIT                NULL,
    [SubmittedStatusType]             VARCHAR (200)      NULL,
    [SubmittedUserUID]                VARCHAR (100)      NULL,
    [SubmittedDTLT]                   DATETIME           NULL,
    [ResponseStatusType]              VARCHAR (200)      NULL,
    [Response]                        VARCHAR (500)      NULL,
    [ResponceErrorDescription]        VARCHAR (500)      NULL,
    [ResponseDTLT]                    DATETIME           NULL,
    [CompletedFlag]                   BIT                CONSTRAINT [DF_tgAssetAddressCGE_CompletedFlag] DEFAULT ((0)) NULL,
    [CompletedDTLT]                   DATETIME           NULL,
    [GPSSource]                       VARCHAR (20)       NULL,
    [GPSTime]                         VARCHAR (10)       NULL,
    [FixQuality]                      INT                NULL,
    [NumberOfSatellites]              INT                NULL,
    [HDOP]                            FLOAT (53)         NULL,
    [AltitudemetersAboveMeanSeaLevel] FLOAT (53)         NULL,
    [HeightOfGeoid]                   FLOAT (53)         NULL,
    [TimeSecondsSinceLastDGPS]        FLOAT (53)         NULL,
    [ChecksumData]                    VARCHAR (10)       NULL,
    [Bearing]                         FLOAT (53)         NULL,
    [Speed]                           FLOAT (53)         NULL,
    [GPSStatus]                       VARCHAR (20)       NULL,
    [NumberOfGPSAttempts]             INT                NULL,
    [InspectionRequestUID]            VARCHAR (100)      NULL,
    [ActivityUID]                     VARCHAR (100)      NULL,
    CONSTRAINT [PK_g_AssetCGEs] PRIMARY KEY CLUSTERED ([AssetAddressCGEID] ASC)
);
