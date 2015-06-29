-- Adds title to H5P table.
ALTER TABLE `#__h5p` ADD COLUMN `title` VARCHAR(100) NOT NULL DEFAULT ''  AFTER `h5p_id`;
