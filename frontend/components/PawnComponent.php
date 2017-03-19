<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 22.02.17
 * Time: 12:42
 */

namespace frontend\components;

class PawnComponent extends FigureComponent
{
    public $name = 'pawn';

    public function __construct($color, $number = null, $config = [])
    {
        parent::__construct($color, $this->name, $number, $config);
    }

    public function move()
    {
        $this->currentPositionX = $this->currentPositionX + $this->moveX;
        $this->currentPositionY = $this->currentPositionY + $this->moveY;
    }

    public function firstMove() {
        $this->currentPositionX = $this->currentPositionX + $this->moveX;
        $this->currentPositionY = $this->currentPositionY + $this->moveY + 1;
    }
}