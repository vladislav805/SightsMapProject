<?

	namespace PHPUnit\Framework {

		abstract class TestCase {
			public function setUp() {}
			public function tearDownAfterClass() {}
			public function assertEquals($expected, $actual) {}
			public function assertNotEquals($expected, $actual) {}
			public function assertArrayHasKey($a, $b) {}
			public function assertClassHasAttribute() {}
			public function assertArraySubset() {}
			public function assertClassHasStaticAttribute() {}
			public function assertContains($needle, $haystack) {}
			public function assertContainsOnly() {}
			public function assertContainsOnlyInstancesOf() {}
			public function assertCount($a, $b) {}
			public function assertDirectoryExists($v) {}
			public function assertDirectoryIsReadable($v) {}
			public function assertDirectoryIsWritable($v) {}
			public function assertEmpty($v) {}
			public function assertEqualXMLStructure() {}
			public function assertFalse($v) {}
			public function assertFileEquals($v) {}
			public function assertFileExists($v) {}
			public function assertFileIsReadable($v) {}
			public function assertFileIsWritable($v) {}
			public function assertGreaterThan($exp, $act) {}
			public function assertGreaterThanOrEqual($exp, $act) {}
			public function assertInfinite($v) {}
			public function assertInstanceOf($v) {}
			public function assertInternalType($v) {}
			public function assertIsReadable($v) {}
			public function assertIsWritable($v) {}
			public function assertJsonFileEqualsJsonFile() {}
			public function assertJsonStringEqualsJsonFile() {}
			public function assertJsonStringEqualsJsonString() {}
			public function assertLessThan($exp, $act) {}
			public function assertLessThanOrEqual($exp, $act) {}
			public function assertNan($v) {}
			public function assertNull($v) {}
			public function assertObjectHasAttribute($v) {}
			public function assertRegExp($pattern, $string) {}
			public function assertStringMatchesFormat() {}
			public function assertStringMatchesFormatFile() {}
			public function assertSame($actual, $needle) {}
			public function assertStringEndsWith() {}
			public function assertStringEqualsFile() {}
			public function assertStringStartsWith() {}
			public function assertThat() {}
			public function assertTrue($v) {}
			public function assertXmlFileEqualsXmlFile() {}
			public function assertXmlStringEqualsXmlFile() {}
			public function assertXmlStringEqualsXmlString() {}

		}

	}