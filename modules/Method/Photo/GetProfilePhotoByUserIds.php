<?php

	namespace Method\Photo;

	use APIPublicMethod;
	use IController;
	use Model\Photo;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class GetProfilePhotoByUserIds extends APIPublicMethod {

		/** @var int[]|string */
		protected $userIds;

		public function __construct($request) {
			parent::__construct($request);
			if (!is_array($this->userIds)) {
				$this->userIds = explode(",", $this->userIds);
			}
			$this->userIds = array_map("intval", $this->userIds);
		}

		/**
		 * Realization of some action
		 * @param IController		$main
		 * @param DatabaseConnection $db
		 * @return array[]
		 * @throws \APIException
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {
			if (!sizeOf($this->userIds)) {
				return [];
			}

			$sql = sprintf("select * from `photo` `p1` where `p1`.`photoId` in (select max(`p2`.`photoId`) as `photoId` from `photo` `p2` where `p2`.`ownerId` in (" . join(",", $this->userIds) . ") and `p2`.`type` = '%d' group by `p2`.`ownerId`)", Photo::TYPE_PROFILE);

			$items = $db->query($sql, DatabaseResultType::ITEMS);
			$data = [];

			foreach ($this->userIds as $userId) {
				$data[$userId] = null;
			}

			foreach ($items as $item) {
				$data[$item["ownerId"]] = $item;
			}

			unset($items);

			return $data;
		}
	}