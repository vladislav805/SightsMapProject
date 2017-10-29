<?php

	namespace Method\Authorize;

	final class Access {
		const READ_INFO = 0;
		const WRITE_INFO = 1;
		const READ_MAP = 2;
		const WRITE_MAP = 4;
		const WRITE_USER_STATUS = 32;
		const READ_COMMENTS = 64;
		const WRITE_COMMENTS = 128;
		const READ_PHOTOS = 256;
		const WRITE_PHOTOS = 512;
	}