
SELECT setval( 'eztags_id_seq', max( id ) ) FROM eztags;
SELECT setval( 'eztags_attribute_link_id_seq', max( id ) ) FROM eztags_attribute_link;
