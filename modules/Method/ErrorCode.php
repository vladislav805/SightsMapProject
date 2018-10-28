<?

	namespace Method;

	abstract class ErrorCode {

		const NO_PARAM = 0x01;
		const UNKNOWN_METHOD = 0x04;
		const UNKNOWN_ERROR = 0x05;

		const INCORRECT_LOGIN_PASSWORD = 0x10;
		const LOGIN_ALREADY_EXIST = 0x11;
		const INCORRECT_LENGTH_PASSWORD = 0x12;
		const INCORRECT_NAMES = 0x13;
		const EMAIL_ALREADY_EXIST = 0x14;

		const POINT_NOT_FOUND = 0x20;
		const INVALID_COORDINATES = 0x21;

		const MARK_NOT_FOUND = 0x30;
		const INVALID_COLOR = 0x31;

		const PHOTO_NOT_FOUND = 0x40;
		const UPLOAD_FAILURE = 0x41;
		const UPLOAD_INVALID_RESOLUTION = 0x42;

		const COMMENT_NOT_FOUND = 0x50;

		const RATING_INVALID = 0x60;

		const ACCESS_DENIED = 0x1e;
		const SESSION_NOT_FOUND = 0x1f;
		const FLOOD_CONTROL = 0x0f;
	}