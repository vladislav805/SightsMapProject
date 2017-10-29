<?php

	namespace tools;

	abstract class DatabaseResultType {
		const RAW = 0;
		const ITEM = 1;
		const ITEMS = 2;
		const COUNT = 3;
		const INSERTED_ID = 4;
		const AFFECTED_ROWS = 5;
	}