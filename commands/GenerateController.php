<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\gii\generators\model\Generator;

/**
 * Command to generate ActiveRecord models from existing database tables.
 */
class GenerateController extends Controller
{
    public $ignoredTables = ['migration'];

    /**
     * Generate active record models from existing database tables.
     */
    public function actionIndex()
    {
        echo PHP_EOL . 'Generating active-models for following tables:' . PHP_EOL;
        
        $tables = array_diff(
            Yii::$app->db->createCommand('show tables')->queryColumn(),
            $this->ignoredTables
        );
        
        // Create the generated models directory if it doesn't exist
        $generatedDir = Yii::getAlias('@app/models/generated');
        if (!is_dir($generatedDir)) {
            mkdir($generatedDir, 0755, true);
            echo 'Created directory: ' . $generatedDir . PHP_EOL;
        }
        
        $counter = 0;
        foreach ($tables as $tableName) {
            $generator = new Generator([
                'ns' => 'app\models\generated',
                'tableName' => $tableName,
                'baseClass' => 'app\models\ActiveRecord'
            ]);
            $files = $generator->generate();
            foreach ($files as $file) {
                /* @var $file \yii\gii\CodeFile */
                $file->save();
            }
            echo '    > ' . $tableName . PHP_EOL;
            $counter++;
        }
        echo $counter . ' active-models were generated.' . PHP_EOL;
        
        return Controller::EXIT_CODE_NORMAL;
    }
}

