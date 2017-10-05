UPDATE `ezcontentclass_attribute`
SET `data_text1` = 'Default'
WHERE `data_type_string` = 'eztags'
AND `data_int2` = 0;

UPDATE `ezcontentclass_attribute`
SET `data_text1` = 'Select'
WHERE `data_type_string` = 'eztags'
AND `data_int2` = 1;
