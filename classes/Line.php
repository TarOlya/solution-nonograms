<?php


class Line
{
    const ROW = false;
    const COL = true;

    private $possibleVars;
    private $exactVar;
    private $groupBlock;
    private $currLineInMap;

    /**
     * Line constructor.
     * @param $groupBlock
     * @param $currLineInMap
     */
    public function __construct($groupBlock, $currLineInMap)
    {
        $this->groupBlock = $groupBlock;
        $this->currLineInMap = $currLineInMap;
    }

    /**
     * @return mixed
     */
    public function getCurrLineInMap()
    {
        return $this->currLineInMap;
    }

    /**
     * @param mixed $currLineInMap
     */
    public function setCurrLineInMap($currLineInMap)
    {
        $this->currLineInMap = $currLineInMap;
    }


    /**
     * @return mixed
     */
    public function getPossibleVars()
    {
        return $this->possibleVars;
    }

    /**
     * @return mixed
     */
    public function getExactVar()
    {
        return $this->exactVar;
    }

    /**
     * @return mixed
     */
    public function getGroupBlock()
    {
        return $this->groupBlock;
    }

    //создает возможоные варианты расположения
    function makeVariant($numberBlock, $endPreviousBlock = -1, &$currentVar = [], &$insertedBlock = [])
    {
        $start = $endPreviousBlock + 1;
        $tmp = array_slice($this->groupBlock, $numberBlock + 1);
        $finish = count($this->currLineInMap) - array_sum($tmp) - count($tmp);
        for ($i = $start; $i < $finish; $i++) {
            if ($numberBlock == 0) {//заполняем статусом "не закрашено" все предыдущие клетки
                $currentVar = array_fill(0, $i, CellState::NOTPAINTED);
            } else {//либо 1 предыдущую
                $currentVar[$i - 1] = CellState::NOTPAINTED;
            }
            if ($i + $this->groupBlock[$numberBlock] <= count($this->currLineInMap)) {//проверка на вместимость группы
                for ($j = 0; $j < $this->groupBlock[$numberBlock]; $j++) {
                    if ($i + $j < count($this->currLineInMap)) {
                        $currentVar[$i + $j] = CellState::PAINTED;
                    }
                }
                if ($numberBlock < count($this->groupBlock) - 1) {
                    $newInsertedBlocks = $insertedBlock;
                    $newInsertedBlocks[] = $i;
                    $currentVar[] = CellState::NOTPAINTED;
                    $this->makeVariant($numberBlock + 1, $i + $j, $currentVar, $newInsertedBlocks);
                    $currentVar = array_slice($currentVar, 0, $i);//убираем рассмотренную часть
                } else {
                    $fullZero = array_fill(0, count($this->currLineInMap), CellState::NOTPAINTED);
                    $currentVar += $fullZero;//все неинициализированные элементы заполняем статусом "не закрашено"
                    $this->possibleVars[] = $currentVar;
                    $currentVar = array_slice($currentVar, 0, $i - 1);
                }
            }
        }
    }

    //фильтрует варианты в зависимости от текущей линии в карте и находит точный вариант
    public function filter($mapLine)
    {
        $result = [];
        $this->exactVar = $mapLine;
        $arrExactCell = [];
        for ($i = 0; $i < count($mapLine); $i++) {
            if ($mapLine[$i] != CellState::UNKNOWN) {
                $arrExactCell[] = $i;
            }
        }
        for ($i = 0; $i < count($this->possibleVars); $i++) {
            $fullExact = false;
            for ($j = 0; $j < count($arrExactCell); $j++) {
                if ($this->exactVar[$arrExactCell[$j]] == $this->possibleVars[$i][$arrExactCell[$j]]) {
                    $fullExact = true;
                } else {
                    $fullExact = false;
                    break;
                }
            }
            if ($fullExact) {
                $result[] = $this->possibleVars[$i];
            }
        }
        if (count($result)) {
            $this->possibleVars = $result;
        }
        $this->generationExactVar();
        return $this->possibleVars;
    }

    //находит точный вариант
    function generationExactVar()
    {
        $this->exactVar = $this->possibleVars[0];
        for ($varNum = 1; $varNum < count($this->possibleVars); $varNum++) {
            $this->exactVar = array_intersect_assoc($this->exactVar, $this->possibleVars[$varNum]);
        }
        foreach ($this->exactVar as $key => $value) {
            $this->currLineInMap[$key] = $value;
        }
        return $this->exactVar;
    }


}