<?php

use PHPUnit\Framework\TestCase;

class FilesystemFunctionsTest extends TestCase
{
	static $testFolder = '/test/working';

	protected function setUp()
	{
		define('__ROOT__', realpath(__DIR__ . '/../'));

		require_once __ROOT__ . '/src/FilesystemFunctionsTrait.php';
		require_once __ROOT__ . '/tests/support/App.php';
	}

	protected function tearDown()
	{ }

	public function testMkdir()
	{
		$this->assertTrue(App::mkdir(self::$testFolder . '/newfolder', 0777, true));

		/* should be still true and not throw an error even thou the folder is there */
		$this->assertTrue(App::mkdir(self::$testFolder . '/newfolder', 0777, true));
	}

	public function testFilePutContents()
	{
		$this->assertEquals(11, App::file_put_contents(self::$testFolder . '/newfolder/hello.txt', 'Hello World'));
		$this->assertEquals(12, App::file_put_contents(self::$testFolder . '/newfolder/multiplelines.txt', 'Hello' . chr(10) . 'World' . chr(10)));
	}

	public function testBasename()
	{
		$this->assertEquals('hello.txt', App::basename(self::$testFolder . '/newfolder/hello.txt'));
		$this->assertEquals('hello.txt', App::basename(__ROOT__ . self::$testFolder . '/newfolder/hello.txt'));
	}

	public function testDirname()
	{
		$this->assertEquals(self::$testFolder . '/newfolder', App::dirname(self::$testFolder . '/newfolder/hello.txt'));
		$this->assertEquals(self::$testFolder . '/newfolder', App::dirname(__ROOT__ . self::$testFolder . '/newfolder/hello.txt'));
	}

	public function testFile()
	{
		$this->assertEquals(['Hello' . chr(10), 'World' . chr(10)], App::file(self::$testFolder . '/newfolder/multiplelines.txt'));
	}

	public function testFileExists()
	{
		$this->assertFalse(App::file_exists(self::$testFolder . '/newfolder/fake.txt'));
		$this->assertTrue(App::file_exists(self::$testFolder . '/newfolder/multiplelines.txt'));
		$this->assertTrue(App::file_exists(__ROOT__ . self::$testFolder . '/newfolder/multiplelines.txt'));
	}

	public function testFileGetContents()
	{
		$this->assertEquals('Hello World', App::file_get_contents(self::$testFolder . '/newfolder/hello.txt'));
	}

	public function testFilesize()
	{
		$this->assertEquals(11, App::filesize(self::$testFolder . '/newfolder/hello.txt'));
	}

	public function testFopen()
	{
		$this->assertTrue(is_resource(App::fopen(self::$testFolder . '/newfolder/hello.txt', 'r')));
	}

	public function testGlob()
	{
		$files = App::glob(self::$testFolder . '/newfolder/*.txt');

		$this->assertTrue(is_array($files));
		$this->assertEquals(2, count($files));

		App::mkdir(self::$testFolder . '/newfolder/anotherfolder');
		App::file_put_contents(self::$testFolder . '/newfolder/anotherfolder/hello.txt', 'Hello World');

		$files = App::glob(self::$testFolder . '/newfolder/*.txt', 0, true);

		$this->assertTrue(is_array($files));
		$this->assertEquals(3, count($files));
	}

	public function testIs_file()
	{
		$this->assertTrue(App::is_file(self::$testFolder . '/newfolder/hello.txt'));
		$this->assertFalse(App::is_file(self::$testFolder . '/newfolder'));
	}

	public function testIs_dir()
	{
		$this->assertTrue(App::is_dir(self::$testFolder . '/newfolder'));
		$this->assertFalse(App::is_dir(self::$testFolder . '/newfolder/hello.txt'));
	}

	public function testParseIniFile()
	{
		App::file_put_contents(self::$testFolder . '/newfolder/hello.ini', 'foo = bar' . chr(10));

		$this->assertEquals(['foo' => 'bar'], App::parse_ini_file(self::$testFolder . '/newfolder/hello.ini'));
	}

	public function testPathinfo()
	{
		$array = App::pathinfo(self::$testFolder . '/newfolder/hello.ini');

		$this->assertTrue(is_array($array));

		$this->assertEquals('hello.ini', $array['basename']);
		$this->assertEquals('ini', $array['extension']);
		$this->assertEquals('hello', $array['filename']);
		$this->assertEquals(self::$testFolder . '/newfolder', $array['dirname']);
	}

	public function testCopy()
	{
		$this->assertTrue(App::copy(self::$testFolder . '/newfolder/hello.ini', self::$testFolder . '/newfolder/cat.ini'));
	}

	public function testChmod()
	{
		$this->assertTrue(App::chmod(self::$testFolder . '/newfolder/cat.ini', 0777));
	}

	public function testFilePerms()
	{
		$this->assertEquals(33279, App::fileperms(self::$testFolder . '/newfolder/cat.ini'));
	}

	public function testFileType()
	{
		$this->assertEquals('file', App::filetype(self::$testFolder . '/newfolder/cat.ini'));
	}

	public function testReadFile()
	{
		ob_start();
		App::readfile(self::$testFolder . '/newfolder/hello.ini');
		$result = ob_get_clean();

		$this->assertEquals('foo = bar' . chr(10), $result);
	}

	public function testRename()
	{
		$this->assertTrue(App::rename(self::$testFolder . '/newfolder/hello.ini', self::$testFolder . '/newfolder/world.ini'));
		$this->assertTrue(App::file_exists(self::$testFolder . '/newfolder/world.ini'));
	}

	public function testUnlink()
	{
		$this->assertTrue(App::unlink(self::$testFolder . '/newfolder/world.ini'));
		$this->assertFalse(App::file_exists(self::$testFolder . '/newfolder/world.ini'));

		$this->assertTrue(App::unlink(self::$testFolder . '/newfolder/cat.ini'));
		$this->assertFalse(App::file_exists(self::$testFolder . '/newfolder/cat.ini'));

		$this->assertTrue(App::unlink(self::$testFolder . '/newfolder/hello.txt'));
		$this->assertFalse(App::file_exists(self::$testFolder . '/newfolder/hello.txt'));

		$this->assertTrue(App::unlink(self::$testFolder . '/newfolder/anotherfolder/hello.txt'));
		$this->assertFalse(App::file_exists(self::$testFolder . '/newfolder/anotherfolder/hello.txt'));
	}

	public function testRmdir()
	{
		$this->assertTrue(App::rmdir(self::$testFolder . '/newfolder/anotherfolder'));
		$this->assertTrue(App::rmdir(self::$testFolder, true));
	}

	public function testVarExportPhpArray()
	{
		$exact = <<<EOF
<?php return array (
  'name' => 'Johnny Appleseed',
);
EOF;

		$this->assertEquals($exact, App::var_export_php(['name' => 'Johnny Appleseed']));
	}

	public function testVarExportPhpClass()
	{
		$exact = <<<EOF
<?php return (object)(array(
   'name' => 'Johnny Appleseed',
   'age' => 21,
));
EOF;

		$class = new \stdClass;
		$class->name = 'Johnny Appleseed';
		$class->age = 21;

		$this->assertEquals($exact, App::var_export_php($class));
	}
}
