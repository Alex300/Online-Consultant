
CREATE TABLE IF NOT EXISTS `cot_oc_message` (
    `messageid` int(11) NOT NULL AUTO_INCREMENT,
    `threadid` int(11) NOT NULL,
    `ikind` int(11) NOT NULL,
    `agentId` int(11) NOT NULL DEFAULT '0',
    `tmessage` text NOT NULL,
    `dtmcreated` datetime DEFAULT '0000-00-00 00:00:00',
    `tname` varchar(64) DEFAULT NULL,
    PRIMARY KEY (`messageid`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cot_oc_thread` (
  `threadid` int(11) NOT NULL AUTO_INCREMENT,
  `userName` varchar(64) NOT NULL,
  `userid` varchar(255) DEFAULT NULL,
  `agentName` varchar(64) DEFAULT NULL,
  `agentId` int(11) NOT NULL DEFAULT '0',
  `dtmcreated` datetime DEFAULT '0000-00-00 00:00:00',
  `dtmmodified` datetime DEFAULT '0000-00-00 00:00:00',
  `lrevision` int(11) NOT NULL DEFAULT '0',
  `istate` int(11) NOT NULL DEFAULT '0',
  `ltoken` int(11) NOT NULL,
  `remote` varchar(255) DEFAULT NULL,
  `referer` text,
  `nextagent` int(11) NOT NULL DEFAULT '0',
  `locale` varchar(8) DEFAULT NULL,
  `lastpinguser` datetime DEFAULT '0000-00-00 00:00:00',
  `lastpingagent` datetime DEFAULT '0000-00-00 00:00:00',
  `userTyping` int(11) DEFAULT '0',
  `agentTyping` int(11) DEFAULT '0',
  `shownmessageid` int(11) NOT NULL DEFAULT '0',
  `userAgent` varchar(255) DEFAULT NULL,
  `messageCount` varchar(16) DEFAULT NULL,
  `groupid` int(11) DEFAULT NULL,
  PRIMARY KEY (`threadid`),
  KEY `idx_userid` (`userid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cot_oc_revision` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cot_oc_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `locale` varchar(8) DEFAULT NULL,
  `groupid` int(11) DEFAULT NULL,
  `vcvalue` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cot_oc_invite` (
  `inv_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL DEFAULT '0',
  `online_id` INT NOT NULL ,
  `inv_text` varchar(255) NOT NULL DEFAULT '',
  `inv_status` INT( 1 ) NULL DEFAULT '0',
  `inv_dtsended` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  `inv_answed` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  `agentId` INT NOT NULL DEFAULT '0',
  `threadid` INT DEFAULT '0',
  PRIMARY KEY (`inv_id`)
) ENGINE = InnoDB   DEFAULT CHARSET=utf8;
