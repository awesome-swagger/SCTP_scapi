﻿CREATE TABLE [dbo].[UserTb] (
    [UserID]                 INT                IDENTITY (1, 1) NOT NULL,
    [UserUID]                VARCHAR (100)      NULL,
    [ProjectID]              INT                NULL,
    [UserCreatedUID]         VARCHAR (100)      NULL,
    [UserModifiedUID]        VARCHAR (100)      NULL,
    [UserCreatedDate]        DATETIME           NULL,
    [UserModifiedDate]       DATETIME           NULL,
    [UserInactiveDTLT]       DATETIME           NULL,
    [UserComments]           VARCHAR (2000)     NULL,
    [UserRevision]           INT                NULL,
    [UserActiveFlag]         BIT                CONSTRAINT [DF_UserTb_UserActiveFlag] DEFAULT ((1)) NULL,
    [UserInActiveFlag]       BIT                CONSTRAINT [DF_UserTb_UserInActiveFlag] DEFAULT ((0)) NULL,
    [UserLoginID]            VARCHAR (50)       NULL,
    [UserFirstName]          VARCHAR (50)       NULL,
    [UserLastName]           VARCHAR (50)       NULL,
    [UserLANID]              VARCHAR (20)       NULL,
    [UserPassword]           VARCHAR (75)       NULL,
    [UserEmployeeType]       VARCHAR (50)       NULL,
    [UserCompanyName]        VARCHAR (200)      NULL,
    [UserCompanyPhone]       VARCHAR (20)       NULL,
    [UserSupervisorUserUID]  VARCHAR (100)      NULL,
    [UserName]               VARCHAR (100)      NULL,
    [UserAppRoleType]        VARCHAR (50)       NULL,
    [UserPhone]              VARCHAR (20)       NULL,
    [UserCreatedDTLTOffset]  DATETIMEOFFSET (7) NULL,
    [UserModifiedDTLTOffset] DATETIMEOFFSET (7) NULL,
    [UserInactiveDTLTOffset] DATETIMEOFFSET (7) NULL,
    [UserArchiveFlag]        BIT                CONSTRAINT [DF_UserTb_UserArchiveFlag] DEFAULT ((0)) NULL,
    [HomeWorkCenterUID]      VARCHAR (100)      CONSTRAINT [DF_UserTb_HomeWorkCenterUID] DEFAULT ('') NULL,
    CONSTRAINT [PK_UserTb] PRIMARY KEY CLUSTERED ([UserID] ASC)
);



