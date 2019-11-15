<?php

namespace PogoApi\Construct;

class Move extends Construct
{

    public $name;
    public $type;
    public $energy;
    public $power;
    public $buffs;


    public function __construct($move)
    {

        if (!empty($move->buffs)) {
            $this->buffs = buffs();
        }
        $this->name = $this->name($move->uniqueId);
        $this->type = $this->type($move->type);
        $this->energy = $move->energyDelta;
        $this->power = $move->power;
        return $this;
    }
    private function buffs($buff)
    {
        $buffs = new stdClass();
        foreach ($buff as $k => $v) {
            $id = preg_split('/([[:upper:]][[:lower:]]+)/', $k, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            if ($id[0] === 'attacker') {
                $self = new stdClass();
                if ($id[1] === 'Attack') {
                    $self->attack = $v;
                } else if ($id[1] === 'Defense') {
                    $self->defence = $v;
                }
                $buffs->self = $self;
            } else if ($id[0] === 'target') {
                $target = new stdClass();
                if ($id[1] === 'Attack') {
                    $target->attack = $v;
                } else if ($id[1] === 'Defense') {
                    $target->defence = $v;
                }
                $buffs->target = $target;
            } else if ($id[1] === 'Activation') {
                $buffs->chance = $v;
            }
        }
        return $buffs;
    }

    private function name($name)
    {
        $name = str_replace('_', ' ', $name);
        $name = strtolower($name);
        $name = ucwords($name);
        return $name;
    }

    private function type($type)
    {
        global $PogoApi;
        $type = str_replace('POKEMON_TYPE_', '', $type);
        $type = str_to_lower($type);
        $type = ucfirst($type);
        $type = $PogoApi->types->$type;
        return $type;
    }
}
