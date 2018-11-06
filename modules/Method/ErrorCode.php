<?

	namespace Method;

	abstract class ErrorCode {

		// Base errors
		const NO_PARAM = 0x01;
		const UNKNOWN_METHOD = 0x04;
		const UNKNOWN_ERROR = 0x05;

		// Login, authorize
		const INCORRECT_LOGIN_PASSWORD = 0x10;
		const LOGIN_ALREADY_EXIST = 0x11;
		const INCORRECT_LENGTH_PASSWORD = 0x12;
		const INCORRECT_NAMES = 0x13;
		const EMAIL_ALREADY_EXIST = 0x14;
		const INVALID_EMAIL = 0x15;
		const SESSION_NOT_FOUND = 0x1f;

		// Sights
		const POINT_NOT_FOUND = 0x20;
		const INVALID_COORDINATES = 0x21;

		// Marks
		const MARK_NOT_FOUND = 0x30;
		const INVALID_COLOR = 0x31;

		// Photos
		const PHOTO_NOT_FOUND = 0x40;
		const UPLOAD_FAILURE = 0x41;
		const UPLOAD_INVALID_RESOLUTION = 0x42;

		// Comments
		const COMMENT_NOT_FOUND = 0x50;

		// Rating
		const RATING_INVALID = 0x60;

		// Execute
		const COMPILE_ERROR = 0x90;
		const RUNTIME_ERROR = 0x91;

		// For all
		const ACCESS_DENIED = 0x1e;
		const FLOOD_CONTROL = 0x0f;

		// Account state
		const ACCESS_FOR_METHOD_DENIED = 0xc0;
		const ACCOUNT_NOT_ACTIVE = 0xc1;

	}