<?php

/**
 * Opis: Kontroler obsugujcy dania HTTP i logik akcji.
 */


namespace app\controllers;

use app\models\Project;
use Yii;
use yii\web\Controller;

// Klasa ProjectController.
class ProjectController extends Controller
{
    // Metoda actionForm.
    public function actionForm()
    {
        $project = new Project();

        if ($project->load(Yii::$app->request->post())) {
            if ($project->validate() && $project->save()) {
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t('app', 'Project created.')
                );
            } else {
                Yii::$app->session->setFlash(
                    'danger',
                    Yii::t('app', 'An error occurred during saving.')
                );
            }
            return $this->refresh();
        }

        return $this->render('form', ['project' => $project]);
    }
}