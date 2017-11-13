--
-- Database: `sights`
--

-- --------------------------------------------------------

--
-- Table structure for table `authorize`
--

CREATE TABLE `authorize` (
  `authId` int(11) NOT NULL COMMENT 'ID',
  `authKey` varchar(128) NOT NULL COMMENT 'Authorize user key',
  `userId` int(11) NOT NULL COMMENT 'User ID',
  `accessMask` int(11) NOT NULL COMMENT 'Bitmask access for...',
  `date` int(11) NOT NULL COMMENT 'Date of authorize'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `commentId` int(11) NOT NULL,
  `pointId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `text` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `eventId` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `type` smallint(6) DEFAULT NULL,
  `ownerUserId` int(11) NOT NULL,
  `actionUserId` int(11) NOT NULL,
  `subjectId` int(11) DEFAULT NULL,
  `isNew` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mark`
--

CREATE TABLE `mark` (
  `markId` int(11) NOT NULL,
  `title` varchar(64) NOT NULL,
  `type` int(11) DEFAULT NULL,
  `color` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `photo`
--

CREATE TABLE `photo` (
  `photoId` int(11) NOT NULL,
  `ownerId` int(11) NOT NULL,
  `type` smallint(6) DEFAULT NULL,
  `date` int(11) NOT NULL,
  `path` varchar(40) DEFAULT NULL,
  `photo200` varchar(40) DEFAULT NULL,
  `photoMax` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `point`
--

CREATE TABLE `point` (
  `ownerId` int(11) NOT NULL,
  `pointId` int(11) NOT NULL,
  `lat` double DEFAULT NULL,
  `lng` double DEFAULT NULL,
  `dateCreated` int(11) NOT NULL,
  `dateUpdated` int(11) DEFAULT '0',
  `title` varchar(128) DEFAULT NULL,
  `description` mediumtext,
  `isVerified` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pointMark`
--

CREATE TABLE `pointMark` (
  `id` int(11) NOT NULL,
  `pointId` int(11) NOT NULL,
  `markId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pointPhoto`
--

CREATE TABLE `pointPhoto` (
  `id` int(11) NOT NULL,
  `pointId` int(11) NOT NULL,
  `photoId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pointVisit`
--

CREATE TABLE `pointVisit` (
  `id` int(11) NOT NULL,
  `pointId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `state` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userId` int(11) NOT NULL,
  `login` varchar(32) NOT NULL,
  `password` varchar(128) NOT NULL,
  `firstName` varchar(64) NOT NULL,
  `lastName` varchar(64) NOT NULL,
  `sex` tinyint(4) NOT NULL DEFAULT '0',
  `lastSeen` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authorize`
--
ALTER TABLE `authorize`
  ADD PRIMARY KEY (`authId`),
  ADD UNIQUE KEY `id` (`authId`),
  ADD UNIQUE KEY `userAccessToken` (`authKey`),
  ADD KEY `id_2` (`authId`),
  ADD KEY `id_3` (`authId`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`commentId`),
  ADD UNIQUE KEY `comment_commentId_uindex` (`commentId`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`eventId`),
  ADD UNIQUE KEY `event_eventId_uindex` (`eventId`);

--
-- Indexes for table `mark`
--
ALTER TABLE `mark`
  ADD PRIMARY KEY (`markId`),
  ADD UNIQUE KEY `mark_markId_uindex` (`markId`);

--
-- Indexes for table `photo`
--
ALTER TABLE `photo`
  ADD PRIMARY KEY (`photoId`),
  ADD UNIQUE KEY `photo_photoId_uindex` (`photoId`);

--
-- Indexes for table `point`
--
ALTER TABLE `point`
  ADD PRIMARY KEY (`pointId`),
  ADD UNIQUE KEY `point_pointId_uindex` (`pointId`);

--
-- Indexes for table `pointMark`
--
ALTER TABLE `pointMark`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pointMark_id_uindex` (`id`);

--
-- Indexes for table `pointPhoto`
--
ALTER TABLE `pointPhoto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pointPhoto_id_uindex` (`id`);

--
-- Indexes for table `pointVisit`
--
ALTER TABLE `pointVisit`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pointVisit_id_uindex` (`id`),
  ADD UNIQUE KEY `pointVisit_2` (`pointId`,`userId`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `user_userId_uindex` (`userId`),
  ADD UNIQUE KEY `user_login_uindex` (`login`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authorize`
--
ALTER TABLE `authorize`
  MODIFY `authId` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `commentId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `eventId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;
--
-- AUTO_INCREMENT for table `mark`
--
ALTER TABLE `mark`
  MODIFY `markId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1011;
--
-- AUTO_INCREMENT for table `photo`
--
ALTER TABLE `photo`
  MODIFY `photoId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `point`
--
ALTER TABLE `point`
  MODIFY `pointId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=446;
--
-- AUTO_INCREMENT for table `pointMark`
--
ALTER TABLE `pointMark`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;
--
-- AUTO_INCREMENT for table `pointPhoto`
--
ALTER TABLE `pointPhoto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `pointVisit`
--
ALTER TABLE `pointVisit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;COMMIT;
