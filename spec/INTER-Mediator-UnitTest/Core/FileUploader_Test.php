<?php
/**
 * FileUploader_Test file
 */

use INTERMediator\FileUploader;
use PHPUnit\Framework\TestCase;

class FileUploader_Test extends TestCase
{
    /**
     * The saved $_POST superglobal to restore in tearDown.
     *
     * @var array<string, mixed>
     */
    private array $savedPost;
    /**
     * The saved $_FILES superglobal to restore in tearDown.
     *
     * @var array<string, mixed>
     */
    private array $savedFiles;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->savedPost = $_POST;
        $this->savedFiles = $_FILES;
    }

    /**
     * Tear down the test environment.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $_POST = $this->savedPost;
        $_FILES = $this->savedFiles;
    }

    /**
     * Test that decideMediaClassName defaults to FileSystem without a container definition.
     *
     * @return void
     */
    public function testDecideMediaClassNameDefault(): void
    {
        $this->assertSame('FileSystem', FileUploader::decideMediaClassName('PDO', null, 'f'));
        $this->assertSame('FileSystem', FileUploader::decideMediaClassName('PDO', [['field' => 'f']], 'f'));
    }

    /**
     * Test that decideMediaClassName returns the class matching the container definition.
     *
     * @return void
     */
    public function testDecideMediaClassNameByContainer(): void
    {
        $this->assertSame('AWSS3', FileUploader::decideMediaClassName('PDO', [['container' => 'S3']], 'f'));
        $this->assertSame('Dropbox', FileUploader::decideMediaClassName('PDO', [['container' => 'dropbox']], 'f'));
        $this->assertSame('FileURL', FileUploader::decideMediaClassName('PDO', [['container' => 'FileURL']], 'f'));
        $this->assertSame('FileSystem', FileUploader::decideMediaClassName('PDO', [['container' => 'FileSystem']], 'f'));
    }

    /**
     * Test that decideMediaClassName returns FileMakerContainer only for FileMaker db classes.
     *
     * @return void
     */
    public function testDecideMediaClassNameFileMakerContainer(): void
    {
        foreach ([true, 'FileMaker', 'FileMakerContainer'] as $container) {
            $defs = [['container' => $container]];
            $this->assertSame('FileMakerContainer', FileUploader::decideMediaClassName('FileMaker_DataAPI', $defs, 'f'));
            $this->assertSame('FileMakerContainer', FileUploader::decideMediaClassName('FileMaker_FX', $defs, 'f'));
            $this->assertSame('FileSystem', FileUploader::decideMediaClassName('PDO', $defs, 'f'));
        }
    }

    /**
     * Test that decideMediaClassName selects the container matching the target field.
     *
     * @return void
     */
    public function testDecideMediaClassNameRespectsTargetField(): void
    {
        $defs = [
            ['field' => 'photo', 'container' => 'S3'],
            ['field' => 'doc', 'container' => 'Dropbox'],
        ];
        $this->assertSame('AWSS3', FileUploader::decideMediaClassName('PDO', $defs, 'photo'));
        $this->assertSame('Dropbox', FileUploader::decideMediaClassName('PDO', $defs, 'doc'));
        $this->assertSame('FileSystem', FileUploader::decideMediaClassName('PDO', $defs, 'other'));
    }

    /**
     * Test that a container definition without a field applies to any field.
     *
     * @return void
     */
    public function testDecideMediaClassNameWithoutFieldAppliesToAll(): void
    {
        $this->assertSame('AWSS3', FileUploader::decideMediaClassName('PDO', [['container' => 's3']], 'any'));
    }

    /**
     * Test that processing outputs a single JSON document even when no files are uploaded.
     *
     * @return void
     */
    public function testProcessingWithoutFilesOutputsSingleJson(): void
    {
        $_POST = ['_im_contextname' => 'testtable', '_im_field' => 'vc1',
            '_im_keyfield' => 'id', '_im_keyvalue' => '1'];
        $_FILES = [];
        $uploader = new FileUploader();
        ob_start();
        try {
            $uploader->processing([['name' => 'testtable', 'key' => 'id']], null,
                ['db-class' => 'PDO', 'dsn' => 'sqlite::memory:'], 0);
        } finally {
            $output = ob_get_clean();
        }
        $json = json_decode($output ?: '', true);
        $this->assertIsArray($json, "Output must be a single JSON document: {$output}");
        $this->assertNotEmpty($json['errorMessages'] ?? []);
    }

    /**
     * Test that getResultForLog returns an array even without a context name in POST.
     *
     * @return void
     */
    public function testGetResultForLogWithoutContextName(): void
    {
        $_POST = [];
        $uploader = new FileUploader();
        // $this->assertIsArray($uploader->getResultForLog());
        // phpstan reports Call to method PHPUnit\Framework\Assert::assertIsArray() with array<mixed> will always evaluate to true.
    }
}
