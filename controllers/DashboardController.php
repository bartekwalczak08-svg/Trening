<?php

/**
 * Opis: Kontroler obsugujcy dania HTTP i logik akcji.
 */


namespace app\controllers;

use app\services\DashboardService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Kontroler dashboardu użytkownika.
 *
 * Zbiera metryki aktywności, podsumowania treningów i dane do wykresów progresu.
 */
class DashboardController extends Controller
{
    /**
     * @var DashboardService|null
     */
    private $dashboardService;

    /**
     * Ogranicza dostęp do dashboardu tylko dla zalogowanych użytkowników.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'progress'],
                'rules' => [
                    [
                        'actions' => ['index', 'progress'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Renderuje główny dashboard z KPI i ostatnimi treningami.
     */
    public function actionIndex()
    {
        $data = $this->getDashboardService()->buildIndexData((int) Yii::$app->user->id);

        return $this->render('index', [
            'todayWorkout' => $data['todayWorkout'],
            'recentWorkouts' => $data['recentWorkouts'],
            'kpi' => $data['kpi'],
        ]);
    }

    /**
     * Buduje dane analityczne progresu i rekomendacje treningowe.
     */
    public function actionProgress()
    {
        $data = $this->getDashboardService()->buildProgressData((int) Yii::$app->user->id);

        return $this->render('progress', $data);
    }

    /**
     * Lazy accessor for dashboard service.
     *
     * @return DashboardService
     */
    protected function getDashboardService()
    {
        if ($this->dashboardService === null) {
            $this->dashboardService = new DashboardService();
        }

        return $this->dashboardService;
    }
}
