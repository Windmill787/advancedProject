<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 18.02.17
 * Time: 22:53
 */

namespace frontend\controllers;

use app\models\Game;
use app\models\History;
use app\models\HistorySearch;
use common\models\User;
use frontend\components\BoardComponent;
use app\models\PlayPositions;
use frontend\components\FigureBuilderComponent;
use app\models\Figure;
use frontend\components\FigureComponent;
use frontend\components\KingComponent;
use frontend\components\PawnComponent;
use yii\base\Model;
use yii\data\Pagination;
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
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
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
     * Displays game index page.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $query = Game::find()
            ->where(['status' => 'in progress'])
            ->andWhere(['white_user_id' => \Yii::$app->user->id])
            ->orWhere(['black_user_id' => \Yii::$app->user->id]);

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => 10]);
        $games = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('index', [
            'games' => $games,
            'pages' => $pages,
        ]);
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

        $figures = \Yii::$container->get('figures', [], [$model->id]);

        $history = History::find()
            ->where(['game_id' => $model->id])
            ->all();

        $playPositions = PlayPositions::find()
            ->where(['game_id' => $model->id])
            ->all();

        $request = \Yii::$app->request;
        $post = \Yii::$app->request->post('PlayPositions');

        if ($request->post('PlayPositions')) {
            $invitation = PlayPositions::findOne($post['id']);

            $invitation->current_x = $post['current_x'];
            $invitation->current_y = $post['current_y'];
            $invitation->save(false);
            return $this->refresh();
        }

        foreach ($figures as $figure) {

            /**
             * @var $figure PawnComponent
             */
            /*if ($figure->name == 'pawn') {
                if ($figure->color == 'white') {
                    $figureMoveX = $figure->currentPositionX + $figure->first_move[0];
                    $figureMoveY = $figure->currentPositionY + $figure->first_move[1];

                } else if ($figure->color == 'black') {
                    $figureMoveX = $figure->currentPositionX - $figure->first_move[0];
                    $figureMoveY = $figure->currentPositionY - $figure->first_move[1];
                }

                if (isset($_POST['firstMove' . $figure->id . $figureMoveX . $figureMoveY . $model->id])) {
                    $model->move = $model->move + 1;
                    $model->save();
                    $figure->move($figureMoveX, $figureMoveY, $model->id);
                    $this->refresh();
                }
            }*/

            /**
             * @var $figure KingComponent
             */
            if ($figure->name == 'king') {
                foreach ($figure->castlingMove as $castling) {
                    $figureMoveX = $figure->currentPositionX + $castling[0];
                    $figureMoveY = $figure->currentPositionY + $castling[1];

                    if ($figure->color == 'white') {
                        if ($castling[0] == 2) {
                            $rook = PlayPositions::findOne(['game_id' => $model->id, 'figure_id' => 14]);
                        } else if ($castling[0] == -2) {
                            $rook = PlayPositions::findOne(['game_id' => $model->id, 'figure_id' => 13]);
                        }
                    } else if ($figure->color == 'black') {
                        if ($castling[0] == 2) {
                            $rook = PlayPositions::findOne(['game_id' => $model->id, 'figure_id' => 30]);
                        } else if ($castling[0] == -2) {
                            $rook = PlayPositions::findOne(['game_id' => $model->id, 'figure_id' => 29]);
                        }
                    }

                    if (isset($_POST['cast' . $figure->id . $figureMoveX . $figureMoveY . $rook->id . $model->id])) {
                        //$model->move = $model->move + 1;
                        //$model->save();
                        $figure->castling($figureMoveX, $figureMoveY, $rook->id, $castling[0], $model->id);
                        $this->refresh();
                    }
                }
            }

            /**
             * @var $figure FigureComponent
             */
            foreach ($figure->moves as $moves) {
                if ($figure->color == 'white') {
                    if ($figure->name == 'bishop' ||
                        $figure->name == 'queen' ||
                        $figure->name == 'rook') {
                        for ($i = 1; $i <= 8; $i++) {
                            $figureMoveX = $figure->currentPositionX + $moves[0] * $i;
                            $figureMoveY = $figure->currentPositionY + $moves[1] * $i;
                            if (isset($_POST['move' . $figure->id . $figureMoveX . $figureMoveY . $model->id])) {
                                $model->move = $model->move + 1;
                                $model->save();
                                $figure->move($figureMoveX, $figureMoveY, $model->id);
                                $this->refresh();
                            }
                        }
                    } else {
                        $figureMoveX = $figure->currentPositionX + $moves[0];
                        $figureMoveY = $figure->currentPositionY + $moves[1];
                    }
                } else if ($figure->color == 'black') {
                    if ($figure->name == 'bishop' ||
                        $figure->name == 'queen' ||
                        $figure->name == 'rook') {
                        for ($i = 1; $i <= 8; $i++) {
                            $figureMoveX = $figure->currentPositionX - $moves[0] * $i;
                            $figureMoveY = $figure->currentPositionY - $moves[1] * $i;
                            if (isset($_POST['move' . $figure->id . $figureMoveX . $figureMoveY . $model->id])) {
                                $model->move = $model->move + 1;
                                $model->save();
                                $figure->move($figureMoveX, $figureMoveY, $model->id);
                                $this->refresh();
                            }
                        }
                    } else {
                        $figureMoveX = $figure->currentPositionX - $moves[0];
                        $figureMoveY = $figure->currentPositionY - $moves[1];
                    }
                }

                if (isset($_POST['move' . $figure->id . $figureMoveX . $figureMoveY . $model->id])) {
                    $model->move = $model->move + 1;
                    $model->save();
                    $figure->move($figureMoveX, $figureMoveY, $model->id);
                    $this->refresh();
                }
            }

            foreach ($figure->attacks as $attack) {
                if ($figure->color == 'white') {
                    $figureAttackX = $figure->currentPositionX + $attack[0];
                    $figureAttackY = $figure->currentPositionY + $attack[1];
                } else if ($figure->color == 'black') {
                    if ($figure->name == 'bishop' ||
                        $figure->name == 'queen' ||
                        $figure->name == 'rook') {
                        for ($i = 1; $i <= 8; $i++) {
                            $figureAttackX = $figure->currentPositionX - $attack[0] * $i;
                            $figureAttackY = $figure->currentPositionY - $attack[1] * $i;
                            if (empty($figureAttackX) == false && empty($figureAttackY) == false) {
                                $desiredFigure = PlayPositions::findOne([
                                    'game_id' => $model->id,
                                    'current_x' => $figureAttackX,
                                    'current_y' => $figureAttackY
                                ]);
                                if (empty($desiredFigure->id) == false) {
                                    if (isset($_POST['attack' . $desiredFigure->figure_id . $figure->id . $model->id])) {
                                        $figure->attack($desiredFigure, $model->id);
                                        $this->refresh();
                                    }
                                }
                            }
                        }
                    } else {
                        $figureAttackX = $figure->currentPositionX - $attack[0];
                        $figureAttackY = $figure->currentPositionY - $attack[1];
                    }
                }

                if (empty($figureAttackX) == false && empty($figureAttackY) == false) {
                    $desiredFigure = PlayPositions::findOne([
                        'game_id' => $model->id,
                        'current_x' => $figureAttackX,
                        'current_y' => $figureAttackY
                    ]);
                    if (empty($desiredFigure->id) == false) {
                        if (isset($_POST['attack' . $desiredFigure->figure_id . $figure->id . $model->id])) {
                            $figure->attack($desiredFigure, $model->id);
                            $this->refresh();
                        }
                    }
                }
            }
        }

        if (isset($_POST['back'])) {
            FigureBuilderComponent::back($figures, $model->id);
            FigureBuilderComponent::resetStatuses($model->id);
            $this->refresh();
        }

        return $this->render('play', [
            'model' => $model,
            'whiteUser' => $whiteUser,
            'blackUser' => $blackUser,
            'board' => $board,
            'figures' => $figures,
            'history' => $history,
            'playPositions' => $playPositions
        ]);
    }

    /**
     * Displays watch game page.
     *
     * @return mixed
     */
    public function actionWatch()
    {
        $query = Game::find()->where(['status' => 'in progress']);
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => 10]);
        $games = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('watch', [
            'games' => $games,
            'pages' => $pages,
        ]);
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
        if (($model = Game::findOne(['id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}