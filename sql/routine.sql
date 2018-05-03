CREATE PROCEDURE `rebuildRating` (IN `pId` INT) DETERMINISTIC BEGIN
	DECLARE `av` INT;
	SELECT AVG(`rate`) INTO `av` FROM `rating` WHERE `pointId` = `pId`;
	UPDATE `point` SET `rating` = `av` WHERE `pointId` = `pId`;
END;

CREATE TRIGGER `onRatingInsert` AFTER INSERT ON `rating` FOR EACH ROW BEGIN
	CALL rebuildRating(`NEW`.`pointId`);
END;

CREATE TRIGGER `onRatingUpdate` AFTER UPDATE ON `rating` FOR EACH ROW BEGIN
	CALL rebuildRating(`NEW`.`pointId`);
END;

CREATE TRIGGER `onRatingDelete` AFTER DELETE ON `rating` FOR EACH ROW BEGIN
	CALL rebuildRating(`OLD`.`pointId`);
END;