ALTER SEQUENCE eztags_s RENAME TO eztags_id_seq;
ALTER SEQUENCE eztags_attribute_link_s RENAME TO eztags_attribute_link_id_seq;

ALTER TABLE eztags ALTER COLUMN id SET DEFAULT nextval('eztags_id_seq');
ALTER TABLE eztags_attribute_link ALTER COLUMN id SET DEFAULT nextval('eztags_attribute_link_id_seq');
