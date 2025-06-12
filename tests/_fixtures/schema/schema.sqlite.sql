
DROP TABLE IF EXISTS 'eztags';
CREATE TABLE 'eztags' (
  'id' integer PRIMARY KEY AUTOINCREMENT,
  'parent_id' integer NOT NULL DEFAULT 0,
  'main_tag_id' integer NOT NULL DEFAULT 0,
  'keyword' text(255) NOT NULL DEFAULT '',
  'depth' integer NOT NULL DEFAULT '1',
  'path_string' text(255) NOT NULL DEFAULT '',
  'modified' integer NOT NULL DEFAULT 0,
  'remote_id' text(100) NOT NULL DEFAULT '',
  'main_language_id' integer NOT NULL DEFAULT 0,
  'language_mask' integer NOT NULL DEFAULT 0,
  'is_hidden' integer NOT NULL DEFAULT 0,
  'is_invisible' integer NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS 'eztags_attribute_link';
CREATE TABLE 'eztags_attribute_link' (
  'id' integer PRIMARY KEY AUTOINCREMENT,
  'keyword_id' integer NOT NULL DEFAULT 0,
  'objectattribute_id' integer NOT NULL DEFAULT 0,
  'objectattribute_version' integer NOT NULL DEFAULT 0,
  'object_id' integer NOT NULL DEFAULT 0,
  'priority' integer NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS 'eztags_keyword';
CREATE TABLE 'eztags_keyword' (
  'keyword_id' integer NOT NULL DEFAULT 0,
  'language_id' integer NOT NULL DEFAULT 0,
  'keyword' text(255) NOT NULL DEFAULT '',
  'locale' text(255) NOT NULL DEFAULT '',
  'status' integer NOT NULL DEFAULT 0
);

CREATE UNIQUE INDEX 'idx_eztags_remote_id' ON 'eztags' ( 'remote_id' );
CREATE INDEX 'idx_eztags_keyword' ON 'eztags' ( 'keyword' );
CREATE INDEX 'idx_eztags_keyword_id' ON 'eztags' ( 'keyword', 'id' );

CREATE INDEX 'idx_eztags_attr_link_keyword_id' ON 'eztags_attribute_link' ( 'keyword_id' );
CREATE INDEX 'idx_eztags_attr_link_kid_oaid_oav' ON 'eztags_attribute_link' ( 'keyword_id', 'objectattribute_id', 'objectattribute_version' );
CREATE INDEX 'idx_eztags_attr_link_kid_oid' ON 'eztags_attribute_link' ( 'keyword_id', 'object_id' );
CREATE INDEX 'idx_eztags_attr_link_oaid_oav' ON 'eztags_attribute_link' ( 'objectattribute_id', 'objectattribute_version' );
