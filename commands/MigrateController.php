<?php

/**
 * Opis: Komenda konsolowa aplikacji.
 */


namespace app\commands;

use yii\console\Controller;
use yii\gii\generators\model\Generator;

//  Klasa MigrateController odpowiada za logik tego moduu.
class MigrateController extends \yii\console\controllers\MigrateController
{
    public $migrationTable = 'migration';

    public $ignoredTablesInAutogeneratingActiveRecords = ['migration'];

    /**
     * Generate active record models from existing database tables.
     */
    //   actionGenerate realizuje fragment logiki aplikacji.
    public function actionGenerate()
    {
        echo PHP_EOL . 'Generating active-models for following tables:' . PHP_EOL;
        
        $tables = array_diff(
            $this->db->createCommand('show tables')->queryColumn(),
            $this->ignoredTablesInAutogeneratingActiveRecords
        );
        
        // Create the generated models directory if it doesn't exist
        $generatedDir = \Yii::getAlias('@app/models/generated');
        if (!is_dir($generatedDir)) {
            mkdir($generatedDir, 0755, true);
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

    // afterAction realizuje fragment logiki aplikacji.
    public function afterAction($action, $result): void
    {
        if (in_array($action->id, ['down', 'fresh', 'redo', 'to', 'up'])) {
            echo PHP_EOL . 'Generating active-models for following tables:' . PHP_EOL;
            $tables = array_diff(
                $this->db->createCommand('show tables')->queryColumn(),
                $this->ignoredTablesInAutogeneratingActiveRecords
            );
            
            // Create the generated models directory if it doesn't exist
            $generatedDir = \Yii::getAlias('@app/models/generated');
            if (!is_dir($generatedDir)) {
                mkdir($generatedDir, 0755, true);
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
        }
        parent::afterAction($action, $result);
    }
}
