
DROP SEQUENCE IF EXISTS eztags_s;
CREATE SEQUENCE eztags_s
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;

DROP SEQUENCE IF EXISTS eztags_attribute_link_s;
CREATE SEQUENCE eztags_attribute_link_s
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;

DROP TABLE IF EXISTS eztags;
CREATE TABLE eztags (
    id integer DEFAULT nextval( 'eztags_s'::text ) NOT NULL,
    parent_id integer DEFAULT 0 NOT NULL,
    main_tag_id integer DEFAULT 0 NOT NULL,
    keyword character varying(255) DEFAULT ''::character varying NOT NULL,
    depth integer DEFAULT 0 NOT NULL,
    path_string character varying(255) DEFAULT ''::character varying NOT NULL,
    modified integer DEFAULT 0 NOT NULL,
    remote_id character varying(100) DEFAULT ''::character varying NOT NULL,
    main_language_id integer DEFAULT 0 NOT NULL,
    language_mask integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS eztags_attribute_link;
CREATE TABLE eztags_attribute_link (
  id integer DEFAULT nextval( 'eztags_attribute_link_s'::text ) NOT NULL,
  keyword_id integer DEFAULT 0 NOT NULL,
  objectattribute_id integer DEFAULT 0 NOT NULL,
  objectattribute_version integer DEFAULT 0 NOT NULL,
  object_id integer DEFAULT 0 NOT NULL,
  priority integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS eztags_keyword;
CREATE TABLE eztags_keyword (
    keyword_id integer DEFAULT 0 NOT NULL,
    language_id integer DEFAULT 0 NOT NULL,
    locale character varying(255) DEFAULT ''::character varying NOT NULL,
    keyword character varying(255) DEFAULT ''::character varying NOT NULL,
    status integer DEFAULT 0 NOT NULL
);

CREATE UNIQUE INDEX idx_eztags_remote_id ON eztags USING btree ( remote_id );
CREATE INDEX idx_eztags_keyword ON eztags USING btree ( keyword );
CREATE INDEX idx_eztags_keyword_id ON eztags USING btree ( keyword, id );

CREATE INDEX idx_eztags_attr_link_keyword_id ON eztags_attribute_link USING btree ( keyword_id );
CREATE INDEX idx_eztags_attr_link_kid_oaid_oav ON eztags_attribute_link USING btree ( keyword_id, objectattribute_id, objectattribute_version );
CREATE INDEX idx_eztags_attr_link_kid_oid ON eztags_attribute_link USING btree ( keyword_id, object_id );
CREATE INDEX idx_eztags_attr_link_oaid_oav ON eztags_attribute_link USING btree ( objectattribute_id, objectattribute_version );
