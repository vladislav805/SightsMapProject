<?php

	namespace Method\Authorize;

	final class Access {
		// User info
		const READ_INFO = 0;
		const WRITE_INFO = 1;

		// Place info
		const READ_MAP = 2;
		const WRITE_MAP = 4;

		// Online
		const WRITE_USER_STATUS = 32;

		// Comments
		const READ_COMMENTS = 64;
		const WRITE_COMMENTS = 128;

		// Photo
		const READ_PHOTOS = 256;
		const WRITE_PHOTOS = 512;

		// Rating
		const READ_RATING = 1024;
		const WRITE_RATING = 2048;

		// Visited
		const READ_VISIT = 4096;
		const WRITE_VISIT = 8192;
	}