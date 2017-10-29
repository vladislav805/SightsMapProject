<?php

	namespace Method\User;

	use Method\Photo\GetProfilePhotoByUserIds;
	use IController;
	use APIPublicMethod;
	use Params;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class GetByIds extends APIPublicMethod {

		/** @var array */
		protected $userIds;

		public function __construct($request) {
			parent::__construct($request);
			$this->userIds = array_values(array_filter(explode(",", (string) $this->userIds)));
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws \APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!sizeOf($this->userIds) && $main->getSession()) {
				$this->userIds = [$main->getSession()->getUserId()];
			}

			$userIds = array_unique(array_map(function($item) {
				return is_numeric($item) ? intval($item) : safeString($item);
			}, $this->userIds));

			//$sql = sprintf("SELECT `user`.`userId`,`user`.`login`,`user`.`firstName`,`user`.`lastName`,`user`.`sex`,`user`.`lastSeen`,`photo`.`ownerId`,`photo`.`photoId`,`photo`.`date`,`photo`.`photo200`,`photo`.`photoMax`,`photo`.`type`,`photo`.`path` FROM `user` LEFT JOIN `photo` ON `photo`.`ownerId` = `user`.`userId` AND `photo`.`type` = '%2\$d' WHERE `user`.`userId` IN ('%s') OR `user`.`login` IN ('%1\$s') GROUP BY `user`.`userId`", join("','", $userIds), Photo::TYPE_PROFILE);
			$sql = sprintf("SELECT * FROM `user` WHERE `user`.`userId` IN ('%s') OR `user`.`login` IN ('%1\$s')", join("','", $userIds));

			$data = $db->query($sql, DatabaseResultType::ITEMS);

			$userIds = array_column($data, "userId");
			$p = new Params();
			$p->set("userIds", $userIds);
			$photos = $main->perform(new GetProfilePhotoByUserIds($p));

			foreach ($data as &$user) {
				if ($photos[$user["userId"]]) {
					$user = array_merge($user, $photos[$user["userId"]]);
				}
			}

			return parseItems($data, "\\Model\\User");
		}
	}