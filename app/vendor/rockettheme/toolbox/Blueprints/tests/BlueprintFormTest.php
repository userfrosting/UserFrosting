<?php
use RocketTheme\Toolbox\Blueprints\BlueprintForm;
use RocketTheme\Toolbox\File\YamlFile;

require_once 'helper.php';

class BlueprintsBlueprintFormTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testLoad($test)
    {
        $blueprint = new Blueprint($test);
        $blueprint->setOverrides(
            ['extends' => ['extends', 'basic']]
        );
        $blueprint->load();

        // Save test results if they do not exist (data needs to be verified by human!)
        /*
        $resultFile = YamlFile::instance(__DIR__ . '/data/form/items/' . $test . '.yaml');
        if (!$resultFile->exists()) {
            $resultFile->content($blueprint->toArray());
            $resultFile->save();
        }
        */

        // Test 1: Loaded form.
        $this->assertEquals($this->loadYaml($test, 'form/items'), $blueprint->toArray());

    }

    public function dataProvider()
    {
        return [
            ['empty'],
            ['basic'],
            ['import'],
            ['extends']
        ];
    }

    protected function loadYaml($test, $type = 'blueprint')
    {
        $file = YamlFile::instance(__DIR__ . "/data/{$type}/{$test}.yaml");
        $content = $file->content();
        $file->free();

        return $content;
    }
}

class Blueprint extends BlueprintForm
{
    /**
     * @param string $filename
     * @return string
     */
    protected function loadFile($filename)
    {
        $file = YamlFile::instance(__DIR__ . "/data/blueprint/{$filename}.yaml");
        $content = $file->content();
        $file->free();

        return $content;
    }

    /**
     * @param string|array $path
     * @param string $context
     * @return array
     */
    protected function getFiles($path, $context = null)
    {
        if (is_string($path)) {
            // Resolve filename.
            if (isset($this->overrides[$path])) {
                $path = $this->overrides[$path];
            } else {
                if ($context === null) {
                    $context = $this->context;
                }
                if ($context && $context[strlen($context)-1] !== '/') {
                    $context .= '/';
                }
                $path = $context . $path;
            }
        }

        $files = (array) $path;

        return $files;
    }

}