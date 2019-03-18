<?

	namespace Model;

	use Error;

	/**
	 * Ниже описаны все возмоные уровни доступа к аккаунту при авторизации
	 * Уроверь BASIC_INFO выдается всегда - это минимальный уровень, позволяющий
	 * получить информацию о текущем пользователе (ФИ, логин, пол, город).
	 * Для получения другой информации, при авторизации требуется передать
	 * дополнительный параметр access, который будет содержать битовую маску
	 * уровней доступа, которые требуется получить.
	 * Следует учесть, что не все уровни доступа можно получить сторонним
	 * сайтам/приложениям.
	 */
	final class Access {

		/**
		 * users.get (w/o userIds)
		 */
		const BASIC_INFO = 0;

		/**
		 * sights.get (w/ ownerId)
		 */
		const SIGHTS_READONLY = 1;

		/**
		 * sights.add
		 * sights.edit
		 * sights.move
		 * sights.remove
		 * sights.setMarks
		 * sights.setPhotos (only with PHOTO permission)
		 * sights.report
		 */
		const SIGHTS_WRITE = 2;

		/**
		 * sights.getVisitState
		 * sights.setVisitState
		 * sights.getVisited
		 */
		const SIGHTS_PRIVATE_MODE = 4;

		/**
		 * sights.setVerify
		 * sights.setArchived
		 */
		const SIGHTS_MODERATOR = 8;

		/**
		 * photos.getUploadUri
		 * photos.save
		 */
		const PHOTOS = 16;

		/**
		 * marks.add
		 * marks.edit
		 * marks.remove
		 */
		const MARKS_MODERATOR = 32;

		/**
		 * comments.add
		 * comments.remove
		 */
		const COMMENTS_WRITE = 64;

		/**
		 * events.getCount
		 * events.get
		 * events.readAll
		 */
		const EVENTS = 128;

		/**
		 * rating.get
		 * #(Sight::rating::userValue)
		 */
		const RATING_READONLY = 256;

		/**
		 * rating.set
		 */
		const RATING_MODIFY = 512;

		/**
		 * cities.add
		 * cities.edit
		 * cities.remove
		 */
		const CITIES_MODERATOR = 1024;

		/**
		 * collections.get
		 */
		const COLLECTIONS_READONLY = 2048;

		/**
		 * collections.create
		 * collections.edit
		 * collections.remove
		 */
		const COLLECTIONS_WRITE = 4096;

		/**
		 * router.generate
		 */
		const NEURAL_NETWORK_RESULT = 8192;

		/**
		 * account.editInfo
		 * account.changePassword
		 * account.setProfilePhoto
		 * account.removeProfilePhoto
		 * account.setStatus
		 */
		const ACCOUNT_MODIFY = 16384;

		/**
		 * admin.getUserPosts
		 * admin.setUserPost
		 * admin.getBanned
		 * admin.setBan
		 */
		const PRIVATE_API = 32768;

		public function __construct() {
			throw new Error();
		}
	}