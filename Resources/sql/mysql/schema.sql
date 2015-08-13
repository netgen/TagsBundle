CREATE TABLE `eztags` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL default '0',
  `main_tag_id` int(11) NOT NULL default '0',
  `keyword` varchar(255) NOT NULL default '',
  `depth` int(11) NOT NULL default '1',
  `path_string` varchar(255) NOT NULL default '',
  `modified` int(11) NOT NULL default '0',
  `remote_id` varchar(100) NOT NULL default '',
  `main_language_id` int(11) NOT NULL default '0',
  `language_mask` int(11) NOT NULL default '0',
  PRIMARY KEY ( `id` ),
  KEY `idx_eztags_keyword` ( `keyword` ),
  KEY `idx_eztags_keyword_id` ( `keyword`, `id` ),
  UNIQUE KEY `idx_eztags_remote_id` ( `remote_id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `eztags_attribute_link` (
  `id` int(11) NOT NULL auto_increment,
  `keyword_id` int(11) NOT NULL default '0',
  `objectattribute_id` int(11) NOT NULL default '0',
  `objectattribute_version` int(11) NOT NULL default '0',
  `object_id` int(11) NOT NULL default '0',
  `priority` int(11) NOT NULL default '0',
  PRIMARY KEY ( `id` ),
  KEY `idx_eztags_attr_link_keyword_id` ( `keyword_id` ),
  KEY `idx_eztags_attr_link_kid_oaid_oav` ( `keyword_id`, `objectattribute_id`, `objectattribute_version` ),
  KEY `idx_eztags_attr_link_kid_oid` ( `keyword_id`, `object_id` ),
  KEY `idx_eztags_attr_link_oaid_oav` ( `objectattribute_id`, `objectattribute_version` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `eztags_keyword` (
  `keyword_id` int(11) NOT NULL default '0',
  `language_id` int(11) NOT NULL default '0',
  `keyword` varchar(255) NOT NULL default '',
  `locale` varchar(255) NOT NULL default '',
  `status` int(11) NOT NULL default '0',
  PRIMARY KEY ( `keyword_id`, `locale` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
