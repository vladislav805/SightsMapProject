<?php

	namespace Method\Photo;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Sight;
	use Model\User;
	use ObjectController\PhotoController;
	use ObjectController\UserController;

	class Report extends APIPublicMethod {

		/** @var int */
		protected $sightId;

		/** @var int */
		protected $photoId;

		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {
			if (!$this->sightId || !$this->photoId) {
				throw new APIException(ErrorCode::NO_PARAM);
			}

			$currentUser = $main->getUser();

			$photo = (new PhotoController($main))->getById($this->photoId);

			/** @var Sight $sight */
			$sight = $main->perform(new \Method\Sight\GetById(["sightId" => $this->sightId]));

			/** @var User $author */
			$author = (new UserController($main))->getById($photo->getOwnerId());

			$str = <<<STR
<p>Пользователь <a href='//sights.velu.ga/user/%s'>%s %s</a> пожаловался на фотографию, которую пользователь <a href='//sights.velu.ga/user/%s'>%s %s</a> загрузил к достопримечательности <a href='//sights.velu.ga/sight/%d'>%s</a></p>
<div>
	<a href='%s'>
		<img src='%s' alt='Photo' width='500' align="center" />
	</a>
</div>
<div style="text-align: center"><a class="ButtonLink" href='//sights.velu.ga/sight/%d'>Открыть достопримечательность</a></div>
STR;


			$text = sprintf(
				$str,
				$currentUser->getLogin(),
				$currentUser->getFirstName(),
				$currentUser->getLastName(),
				$author->getLogin(),
				$author->getFirstName(),
				$author->getLastName(),
				$sight->getId(),
				$sight->getTitle(),
				$photo->getUrlOriginal(),
				$photo->getUrlOriginal(),
				$sight->getId()
			);

			send_mail_to_admin("Жалоба на фотографию", $text);

			return true;
		}
	}