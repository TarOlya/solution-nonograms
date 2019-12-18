<?php


class Nono
{
    private $map;
    private $queueToSolve;
    private $rows;//массив из Line как строки
    private $cols;//массив из Line как столбцы

    /**
     * Nono constructor.
     * @param $rows
     * @param $defifniteVars
     */
    public function __construct($rows, $cols)
    {
        $this->map = array();

        for ($i = 0; $i < count($rows); $i++) {
            for ($j = 0; $j < count($cols); $j++) {
                $this->map[$i][$j] = CellState::UNKNOWN;
            }
        }

        for ($i = 0; $i < count($rows); $i++) {
            $this->rows[$i] = new Line($rows[$i], $this->map[$i]);
            $this->rows[$i]->makeVariant(0);
        }

        for ($i = 0; $i < count($cols); $i++) {
            $this->cols[$i] = new Line($cols[$i], array_column($this->map, $i));
            $this->cols[$i]->makeVariant(0);
        }
        $this->queueToSolve = [
            Line::ROW => array_fill(0, count($this->rows), true),
            Line::COL => array_fill(0, count($this->cols), true)
        ];
    }

    public function solve()
    {
        for ($i = 0; $i < count($this->rows); $i++) {
            for ($j = 0; $j < count($this->cols); $j++) {
                if ($this->map[$i][$j] == CellState::UNKNOWN) {
                    $this->analysis(Line::COL);
                    $this->analysis(Line::ROW);
                }
            }
        }
    }

    //анализирует каждую линию в очереди
    public function analysis($rowOrCol)
    {
        if ($rowOrCol) {
            for ($i = 0; $i < count($this->cols); $i++) {
                if ($this->queueToSolve[Line::COL][$i]) {
                    $this->cols[$i]->filter(array_column($this->map, $i));
                    $this->writeInfInColumn($i, $this->cols[$i]->getExactVar());
                    if (count($this->cols[$i]->getPossibleVars()) == 1) {
                        $this->queueToSolve[Line::COL][$i] = false;
                    }
                }
            }
        } else {
            for ($i = 0; $i < count($this->rows); $i++) {
                if ($this->queueToSolve[Line::ROW][$i]) {
                    $this->rows[$i]->filter($this->map[$i]);
                    $this->writeInfInRow($i, $this->rows[$i]->getExactVar());
                    if (count($this->rows[$i]->getPossibleVars()) == 1) {
                        $this->queueToSolve[Line::ROW][$i] = false;
                    }
                }
            }
        }
    }

    public function writeInfInColumn($indexColumn, $writingArr)
    {
        for ($j = 0; $j < count($this->rows); $j++) {
            $this->map[$j][$indexColumn] |= $writingArr[$j];
        }
    }

    public function writeInfInRow($indexRow, $writingArr)
    {
        for ($i = 0; $i < count($this->cols); $i++) {
            $this->map[$indexRow][$i] |= $writingArr[$i];
        }
    }

    //метод для поиска максимального количества чисел в группах
    public function findValueMaxGroupDigs($rowOrCol)
    {
        if ($rowOrCol) {
            if (count($this->cols)) {
                $max = count($this->cols[0]->getGroupBlock());
                for ($i = 1; $i < count($this->cols); $i++) {
                    if (count($this->cols[$i]->getGroupBlock()) > $max) {
                        $max = count($this->cols[$i]->getGroupBlock());
                    }
                }
                return $max;
            }
        } else {
            if (count($this->rows)) {
                $max = count($this->rows[0]->getGroupBlock());
                for ($i = 1; $i < count($this->rows); $i++) {
                    if (count($this->rows[$i]->getGroupBlock()) > $max) {
                        $max = count($this->rows[$i]->getGroupBlock());
                    }
                }
                return $max;
            }
        }
    }

    //рисует весь кроссворд
    public function printMap()
    {
        echo "<table border='1'>";

        $tmpRow = "";
        $tmpCol = "";
        echo "<tr>";
        for ($i = 0; $i < $this->findValueMaxGroupDigs(Line::COL); $i++) {
            for ($j = 0; $j < $this->findValueMaxGroupDigs(Line::ROW); $j++) {
                echo "<td  height='20' width='20'></td>";
            }
            for ($j = 0; $j < count($this->cols); $j++) {
                $tmpCol = $this->cols[$j]->getGroupBlock();
                echo "<td height='20' width='20'>{$tmpCol[$i]}</td>";
            }
            echo "</tr><tr>";
        }
        echo "</tr>";

        echo "<tr>";
        for ($i = 0; $i < count($this->rows); $i++) {
            for ($gr = 0; $gr < $this->findValueMaxGroupDigs(Line::ROW); $gr++) {
                $tmpRow = $this->rows[$i]->getGroupBlock();
                echo "<td>{$tmpRow[$gr]}</td>";
            }
            for ($j = 0; $j < count($this->cols); $j++) {
                switch ($this->map[$i][$j]) {
                    case CellState::PAINTED:
                        echo "<td bgcolor='black' height='20' width='20'>&nbsp;</td>";
                        break;
                    case CellState::NOTPAINTED:
                        echo "<td bgcolor='#e6e6fa' height='20' width='20'>X</td>";
                        break;
                    case CellState::UNKNOWN:
                        echo "<td bgcolor='#a9a9a9' height='20' width='20'>?</td>";
                        break;
                }
            }
            echo "</tr>";
        }
        echo "</table>";
    }

}