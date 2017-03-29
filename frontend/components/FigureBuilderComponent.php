<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 22.02.17
 * Time: 12:44
 */

namespace frontend\components;

use app\models\Figure;
use app\models\PlayPositions;
use yii\base\Component;
use Yii;

class FigureBuilderComponent extends Component
{
    public static function build()
    {
        // need fix!
        return $figures = [
            $whitePawn1 = new PawnComponent('white', 1),
            $whitePawn2 = new PawnComponent('white', 2),
            $whitePawn3 = new PawnComponent('white', 3),
            $whitePawn4 = new PawnComponent('white', 4),
            $whitePawn5 = new PawnComponent('white', 5),
            $whitePawn6 = new PawnComponent('white', 6),
            $whitePawn7 = new PawnComponent('white', 7),
            $whitePawn8 = new PawnComponent('white', 8),
            $whiteKnight1 = new KnightComponent('white', 1),
            $whiteKnight2 = new KnightComponent('white', 2),
            $whiteBishop1 = new BishopComponent('white', 1),
            $whiteBishop2 = new BishopComponent('white', 2),
            $whiteRook1 = new RookComponent('white', 1),
            $whiteRook2 = new RookComponent('white', 2),
            $whiteQueen = new QueenComponent('white'),
            $whiteKing = new KingComponent('white'),

            $blackPawn1 = new PawnComponent('black', 1),
            $blackPawn2 = new PawnComponent('black', 2),
            $blackPawn3 = new PawnComponent('black', 3),
            $blackPawn4 = new PawnComponent('black', 4),
            $blackPawn5 = new PawnComponent('black', 5),
            $blackPawn6 = new PawnComponent('black', 6),
            $blackPawn7 = new PawnComponent('black', 7),
            $blackPawn8 = new PawnComponent('black', 8),
            $blackKnight1 = new KnightComponent('black', 1),
            $blackKnight2 = new KnightComponent('black', 2),
            $blackBishop1 = new BishopComponent('black', 1),
            $blackBishop2 = new BishopComponent('black', 2),
            $blackRook1 = new RookComponent('black', 1),
            $blackRook2 = new RookComponent('black', 2),
            $blackQueen = new QueenComponent('black'),
            $blackKing = new KingComponent('black'),
        ];
    }

    public static function back($figures) {
        foreach ($figures as $figure) {
            $position = PlayPositions::findOne(['figure_id' => $figure->id]);
            $position->figure_id = $figure->id;
            $position->current_x = $figure->startPositionX;
            $position->current_y = $figure->startPositionY;
            $position->save();
        }
    }

    public static function resetStatuses() {
        $figures = Figure::find()->all();
        foreach ($figures as $figure) {
            $figure->status = 'active';
            $figure->save();
        }
    }
}