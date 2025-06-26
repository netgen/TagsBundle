ALTER TABLE `eztags` ADD COLUMN `priority` BIGINT NOT NULL DEFAULT '0';

ALTER TABLE `eztags` ADD COLUMN `sort_by` varchar(100);
ALTER TABLE `eztags` ADD COLUMN `sort_order` varchar(100);
