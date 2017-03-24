<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 14.03.17
 * Time: 18:40
 */

namespace frontend\widgets;

use frontend\components\BoardComponent;
use yii\helpers\Html;
use yii\bootstrap\Widget;
use yii\widgets\Pjax;

class Board extends Widget
{
    public static function widget(BoardComponent $board, $figures)
    {
        Pjax::begin();

        echo Html::beginTag('table', [
            'class' => 'table-bordered'
        ]);
        echo Html::beginTag('tfoot');
        echo Html::beginTag('tr');
        foreach ($board->symbolLabel as $label) :

            echo Html::beginTag('th', [
                'style' => [
                    'text-align' => 'center',
                    'vertical-align' => 'middle'
                ]
            ]);
            echo Html::encode($label);
            echo Html::endTag('th');
        endforeach;
        echo Html::endTag('tr');
        echo Html::endTag('tfoot');

        for ($board->y = 8; $board->y >= 1; $board->y--) {

            echo Html::beginTag('tr');

            echo Html::beginTag('th', [
                'style' => [
                    'text-align' => 'center',
                    'vertical-align' => 'middle'
                ]
            ]);
            echo Html::encode($board->y);
            echo Html::endTag('th');

            for ($board->x = 1; $board->x <= 8; $board->x++) {

                $total = $board->y + $board->x;
                if ($total % 2 == 0) {

                    echo Html::beginTag('td', [
                        'height' => 50,
                        'width' => 50,
                        'bgcolor' => '#AF5200',
                        'align' => 'center',
                        'valign' => 'center'
                    ]);
                    foreach ($figures as $figure) {
                        if ($board->x == $figure->currentPositionX && $board->y == $figure->currentPositionY) {
                            if ($figure->status != 'killed') {
                                echo Html::img($figure->image, [
                                    'id' => 'figure'.$figure->id,
                                    'onclick' => "light(".$figure->name.', '.$figure->id.")"
                                ]);
                            }
                        }
                        Direction::widget($board, $figure);
                    }
                    echo Html::endTag('td');

                } else {

                    echo Html::beginTag('td', [
                        'height' => 50,
                        'width' => 50,
                        'bgcolor' => '#FFFFFF',
                        'align' => 'center',
                        'valign' => 'center'
                    ]);
                    foreach ($figures as $figure) {
                        if ($board->x == $figure->currentPositionX && $board->y == $figure->currentPositionY) {
                            if ($figure->status != 'killed') {
                                echo Html::img($figure->image, [
                                    'id' => 'figure' . $figure->id,
                                    'onclick' => "light(" . $figure->name . ', ' . $figure->id . ")"
                                ]);
                            }
                        }
                        Direction::widget($board, $figure);
                    }
                    echo Html::endTag('td');

                }
            }
        }
        echo Html::endTag('tr');

        echo Html::endTag('table');

        Pjax::end();
    }
}