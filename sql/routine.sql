CREATE PROCEDURE `rebuildRating` (IN `pId` INT) DETERMINISTIC BEGIN
	DECLARE `av` INT;
	SELECT AVG(`rate`) INTO `av` FROM `rating` WHERE `sightId` = `pId`;
	UPDATE `sight` SET `rating` = `av` WHERE `sightId` = `pId`;
END;

CREATE TRIGGER `onRatingInsert` AFTER INSERT ON `rating` FOR EACH ROW BEGIN
	CALL rebuildRating(`NEW`.`sightId`);
END;

CREATE TRIGGER `onRatingUpdate` AFTER UPDATE ON `rating` FOR EACH ROW BEGIN
	CALL rebuildRating(`NEW`.`sightId`);
END;

CREATE TRIGGER `onRatingDelete` AFTER DELETE ON `rating` FOR EACH ROW BEGIN
	CALL rebuildRating(`OLD`.`sightId`);
END;