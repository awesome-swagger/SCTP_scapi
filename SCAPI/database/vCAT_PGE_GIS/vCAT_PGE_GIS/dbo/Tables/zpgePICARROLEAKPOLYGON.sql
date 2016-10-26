﻿CREATE TABLE [dbo].[zpgePICARROLEAKPOLYGON] (
    [OBJECTID]              INT              IDENTITY (1, 1) NOT NULL,
    [GLOBALID]              UNIQUEIDENTIFIER NOT NULL,
    [SOURCEACCURACY]        NVARCHAR (14)    NULL,
    [CONSTRUCTIONSTATUS]    NVARCHAR (10)    NULL,
    [LASTINSPECTIONDATE]    DATETIME2 (7)    NULL,
    [CONVERSIONID]          INT              NULL,
    [CONVERSIONWORKPACKAGE] NVARCHAR (40)    NULL,
    [LOCATIONDESCRIPTION]   NVARCHAR (255)   NULL,
    [MAPSCALE]              INT              NULL,
    [CREATEUSER]            NVARCHAR (30)    NULL,
    [CREATEDATE]            DATETIME2 (7)    NULL,
    [UPDATEUSER]            NVARCHAR (30)    NULL,
    [UPDATEDATE]            DATETIME2 (7)    NULL,
    [SYMBOLSCALE]           INT              NULL,
    [CAPNUMBER]             NVARCHAR (30)    NULL,
    [SHAPE]                 [sys].[geometry] NULL,
    PRIMARY KEY CLUSTERED ([OBJECTID] ASC)
);


GO
CREATE SPATIAL INDEX [FDO_SHAPE]
    ON [dbo].[zpgePICARROLEAKPOLYGON] ([SHAPE])
    USING GEOMETRY_GRID
    WITH  (
            BOUNDING_BOX = (XMAX = 20081600, XMIN = -16800800, YMAX = 32802000, YMIN = -32802000),
            GRIDS = (LEVEL_1 = MEDIUM, LEVEL_2 = MEDIUM, LEVEL_3 = MEDIUM, LEVEL_4 = MEDIUM)
          );
