<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 18.02.17
 * Time: 22:53
 */

namespace frontend\controllers;

use app\models\Game;
use common\models\User;
use frontend\components\BoardComponent;
use app\models\PlayPositions;
use frontend\components\FigureBuilderComponent;
use app\models\Figure;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

class GameController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'play'],
                'rules' => [
                    [
                        'actions' => ['index', 'play'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Displays game play page.
     * @param integer $id
     * @return mixed
     */
    public function actionPlay($id)
    {
        $model = $this->findModel($id);

        $whiteUser = User::findOne(['id' => $model->white_user_id]);

        $blackUser = User::findOne(['id' => $model->black_user_id]);

        $board = new BoardComponent();

        $figures = FigureBuilderComponent::build();

        // fix this!
        foreach ($figures as $figure) {

            if ($figure->name == 'pawn') {
                if ($figure->color == 'white') {
                    $figureMoveX = $figure->currentPositionX + $figure->first_move[0];
                    $figureMoveY = $figure->currentPositionY + $figure->first_move[1];

                } else if ($figure->color == 'black') {
                    $figureMoveX = $figure->currentPositionX - $figure->first_move[0];
                    $figureMoveY = $figure->currentPositionY - $figure->first_move[1];
                }

                if (isset($_POST['move' . $figure->id . $figureMoveX . $figureMoveY])) {
                    $figure->move($figureMoveX, $figureMoveY);
                }
            }

            if ($figure->name == 'king') {
                foreach ($figure->castlingMove as $castling) {
                    $figureMoveX = $figure->currentPositionX + $castling[0];
                    $figureMoveY = $figure->currentPositionY + $castling[1];

                    if (isset($_POST['cast' . $figure->id . $figureMoveX . $figureMoveY])) {
                        $figure->castling($figureMoveX, $figureMoveY);
                    }
                }
            }

            foreach ($figure->moves as $moves) {
                if ($figure->color == 'white') {
                    $figureMoveX = $figure->currentPositionX + $moves[0];
                    $figureMoveY = $figure->currentPositionY + $moves[1];

                } else if ($figure->color == 'black') {
                    $figureMoveX = $figure->currentPositionX - $moves[0];
                    $figureMoveY = $figure->currentPositionY - $moves[1];
                }

                if (isset($_POST['move' . $figure->id . $figureMoveX . $figureMoveY])) {
                    $figure->move($figureMoveX, $figureMoveY);
                }
            }

            foreach ($figure->attacks as $attack) {
                if ($figure->color == 'white') {
                    $desiredPosition = PlayPositions::findOne([
                        'current_x' => $figure->currentPositionX + $attack[0],
                        'current_y' => $figure->currentPositionY + $attack[1]
                    ]);
                } else if ($figure->color == 'black') {
                    $desiredPosition = PlayPositions::findOne([
                        'current_x' => $figure->currentPositionX - $attack[0],
                        'current_y' => $figure->currentPositionY - $attack[1]
                    ]);
                }

                if (empty($desiredPosition->figure_id) == false) {
                    $desiredFigure = Figure::findOne(['id' => $desiredPosition->id]);

                    if (isset($_POST['attack' . $desiredFigure->id])) {
                        $figure->attack($desiredFigure->id);
                        $this->refresh();
                    }
                }
            }
        }

        // fix this!
        if (isset($_POST['back'])) {
            FigureBuilderComponent::back($figures);
            FigureBuilderComponent::resetStatuses();
            $this->refresh();
        }

        return $this->render('play', [
            'model' => $model,
            'whiteUser' => $whiteUser,
            'blackUser' => $blackUser,
            'board' => $board,
            'figures' => $figures
        ]);
    }

    /**
     * Displays game preview page.
     *
     * @return mixed
     */
    public function actionPreview()
    {
        return $this->render('preview');
    }

    /**
     * Finds the Game model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Game the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Game::findOne(['game_id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}