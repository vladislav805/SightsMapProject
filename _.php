<?

	namespace PHPUnit\Framework {

		abstract class TestCase {
			public function setUp() {}
			public function tearDownAfterClass() {}
			public function assertEquals($dynamicValue, $needle) {}
			public function assertArrayHasKey($a, $b) {}
			public function assertClassHasAttribute() {}
			public function assertArraySubset() {}
			public function assertClassHasStaticAttribute() {}
			public function assertContains() {}
			public function assertContainsOnly() {}
			public function assertContainsOnlyInstancesOf() {}
			public function assertCount($a, $b) {}
			public function assertDirectoryExists() {}
			public function assertDirectoryIsReadable() {}
			public function assertDirectoryIsWritable() {}
			public function assertEmpty() {}
			public function assertEqualXMLStructure() {}
			public function assertFalse($v) {}
			public function assertFileEquals($v) {}
			public function assertFileExists() {}
			public function assertFileIsReadable() {}
			public function assertFileIsWritable() {}
			public function assertGreaterThan() {}
			public function assertGreaterThanOrEqual() {}
			public function assertInfinite() {}
			public function assertInstanceOf() {}
			public function assertInternalType() {}
			public function assertIsReadable() {}
			public function assertIsWritable() {}
			public function assertJsonFileEqualsJsonFile() {}
			public function assertJsonStringEqualsJsonFile() {}
			public function assertJsonStringEqualsJsonString() {}
			public function assertLessThan() {}
			public function assertLessThanOrEqual() {}
			public function assertNan() {}
			public function assertNull() {}
			public function assertObjectHasAttribute() {}
			public function assertRegExp() {}
			public function assertStringMatchesFormat() {}
			public function assertStringMatchesFormatFile() {}
			public function assertSame() {}
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