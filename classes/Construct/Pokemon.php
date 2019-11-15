<?php

namespace PogoApi\Construct;

class Pokemon
{

    public $gender;
    public $moves;

    public function __construct($data, $type, $form = null)
    {
        switch ($type) {
            case 'spawn':
                $this->gender($data);
                break;
            case 'smeargle':
                $this->smeargleMoves($data);
                break;
        }
        return $this;
    }
    private function gender($data)
    {
        $this->gender = new stdClass();
        if (isset($data->gender->femalPercent)) {
            $this->gender->female = $data->gender->femalePercent;
        } else {
            $this->gender->female = 0;
        }
        if (isset($data->gender->malePercent)) {
            $this->gender->male = $data->gender->malePercent;
        } else {
            $this->gender->male = 0;
        }
    }
    public function addGender($data, $form)
    {
        if (empty($this->gender)) {
            $this->gender($data);
        } else {
            if (($this->gender->female !== $data->gender->femalePercent && isset($data->gender->femalePercent)) && !empty($form)) {
                if (!isset($this->gender->$form)) {
                    $this->gender->$form = new stdClass();
                }
                $this->gender->$form->female = $data->gender->femalePercent;
            }
            if (($this->gender->male !== $data->gender->malePercent && isset($data->gender->malePercent)) && !empty($form)) {
                if (!isset($this->gender->$form)) {
                    $this->gender->$form = new stdClass();
                }
                $this->gender->$form->male = $data->gender->malePercent;
            }
        }
    }
    public function smeargleMoves($data)
    {
        global $PogoApi;
        $moves = new \stdClass();
        $moves->quick = [];
        $moves->charge = [];
        foreach ($data->quickMoves as $k => $v) {
            $move = ucwords(strtolower(str_replace('_', '', $v)));
            $move = $PogoApi->key->moves->$move;
            $moves->quick[] = $move;
        }
        foreach ($data->cinematicMoves as $k => $v) {
            $move = ucwords(strtolower(str_replace('_', '', $v)));
            $move = $PogoApi->key->moves->$move;
            $moves->charge[] = $move;
        }
        $this->moves = $moves;
    }
}
