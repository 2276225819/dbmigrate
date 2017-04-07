create table dd(
    id int(11) NOT NULL,
    b varchar(10) NOT NULL DEFAULT '',
    a varchar(10) DEFAULT NULL,PRIMARY KEY id UNIQUE idab a b ab DEFAULT NULL PRIMARY,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idab` (`id`,`a`,`b`),
  KEY `b` (`b`),
  KEY `a` (`a`),
  KEY `ab` (`b`,`a`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

create table d_device(
    ID int(11) NOT NULL AUTO_INCREMENT,
    User_ID int(11) DEFAULT NULL,
    Baby_ID int(11) DEFAULT NULL,
    Model_ID int(11) NOT NULL DEFAULT '0' COMMENT '1，2，3',
    Identity varchar(100) NOT NULL,
    Name varchar(50) DEFAULT NULL,
    Token varchar(200) DEFAULT NULL,
    RegisterTime timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY ID DEFAULT CURRENT_TIMESTAMP PRIMARY,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='设备表';

create table d_model(
    ID int(11) NOT NULL AUTO_INCREMENT,
    Type smallint(6) NOT NULL,
    Name varchar(100) NOT NULL,
    Description varchar(300) DEFAULT NULL,
    isBindBaby tinyint(3) DEFAULT NULL,PRIMARY KEY ID DEFAULT NULL PRIMARY,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='设备型号表';

create table d_realtime_gateway_01(
    Devices_ID int(11) NOT NULL,
    Temperature smallint(6) DEFAULT NULL,
    RelativeHumidity tinyint(4) DEFAULT NULL,
    TVOC float DEFAULT NULL,
    UpdateTime timestamp NULL DEFAULT NULL,
    LastRange int(11) DEFAULT NULL COMMENT '旧记录距离（和新纪录比较判断',UNIQUE KEY Devices_ID COMMENT '旧记录距离（和新纪录比较判断' UNIQUE,
  UNIQUE KEY `Devices_ID` (`Devices_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='网关实时数据表';

create table d_realtime_wear_01(
    Devices_ID int(11) NOT NULL,
    Gateway_ID int(11) DEFAULT NULL,
    Temperature float DEFAULT NULL,
    HeartRate smallint(6) DEFAULT NULL,
    Battery tinyint(4) NOT NULL,
    UpdateTime timestamp NULL DEFAULT NULL,
    LastRange int(11) DEFAULT NULL COMMENT '旧记录距离（和新纪录比较判断',UNIQUE KEY Devices_ID COMMENT '旧记录距离（和新纪录比较判断' UNIQUE,
  UNIQUE KEY `Devices_ID` (`Devices_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='脚环实时数据表';

create table h_heartrate(
    Device_ID int(11) NOT NULL DEFAULT '0',
    UpdateDate date NOT NULL DEFAULT '0000-00-00',
    Value blob PRIMARY KEY Device_ID UpdateDate,
  PRIMARY KEY (`Device_ID`,`UpdateDate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

create table h_temperature(
    Device_ID int(11) NOT NULL DEFAULT '0',
    UpdateDate date NOT NULL DEFAULT '0000-00-00',
    Value blob PRIMARY KEY Device_ID UpdateDate,
  PRIMARY KEY (`Device_ID`,`UpdateDate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

create table u_baby(
    ID int(10) unsigned NOT NULL AUTO_INCREMENT,
    User_ID int(11) NOT NULL,
    Name varchar(50) NOT NULL,
    Sex tinyint(4) NOT NULL DEFAULT '0',
    Birthday date NOT NULL,
    ImgUrl varchar(255) DEFAULT NULL,PRIMARY KEY ID DEFAULT NULL PRIMARY,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='宝宝';

create table u_note(
    ID int(11) NOT NULL AUTO_INCREMENT,
    Time timestamp NULL DEFAULT NULL,
    Baby_ID int(11) DEFAULT NULL,
    Content varchar(1024) DEFAULT NULL,
    Temperature decimal(10,2) DEFAULT NULL,PRIMARY KEY ID DEFAULT NULL PRIMARY,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

create table u_user(
    ID int(10) unsigned NOT NULL AUTO_INCREMENT,
    PWD varchar(100) NOT NULL,
    TEL char(15) NOT NULL,
    Nickname varchar(50) DEFAULT NULL,
    Sex tinyint(4) DEFAULT '0',
    Token varchar(200) DEFAULT NULL,
    TokenCreateTime timestamp NULL DEFAULT NULL,
    RegisterIP varchar(20) DEFAULT NULL,
    RegisterTime timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ImgUrl varchar(255) DEFAULT NULL,PRIMARY KEY ID UNIQUE TEL DEFAULT NULL PRIMARY,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `TEL` (`TEL`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户表';

