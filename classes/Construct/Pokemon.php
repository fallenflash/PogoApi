<?php

namespace PogoApi\Construct;

class Pokemon
{
    use \PogoApi\Traits\Construct;

    public $gender;
    public $moves;
    public $name;
    public $dex;
    public $encounter;
    public $size;
    public $family;
    public $model;
    public $buddy;
    private $data;
    public function __construct($data, $type, $form = null)
    {
        switch ($type) {
            case 'spawn':
                $this->gender($data);
                break;
            case 'smeargle':
                $this->smeargleMoves($data);
                break;
            case 'pokemon':
                if (!empty($form)) {
                    switch ($form) {
                        case 'NORMAL':
                        case null:
                            $this->pokemon($data);
                            break;
                        case 'PURIFIED':
                            $this->forms($form);
                            break;
                        case 'SHADOW':
                            $this->addShadow($data);
                            break;
                        default:
                            $this->addForm($data, $form);
                    }
                }
                $this->pokemon($data);

                break;
        }

        return $this;
    }
    public function editPokemon($data, $form)
    {
        $skipForms = array('NORMAL', 'PURIFIED');
        if (empty($this->name) && empty($form)) {
            $this->pokemon($data);
        } else if (empty($this->name) && (!empty($form) && in_array($form, $skipForms))) {
            $this->pokemon($data);
        } else if (!empty($form) && $form === 'SHADOW') {
            $this->addShadow($data);
        } else if (!empty($form) && !in_array($form, $skipForms) && $form !== 'SHADOW') {
            $this->addForm($data, $form);
        }
    }
    private function gender($data)
    {
        $this->gender = new \stdClass();
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
            if (isset($data->gender->femalePercent)) {
                if ($this->gender->female !== $data->gender->femalePercent && !empty($form)) {
                    if (!isset($this->gender->$form)) {
                        $this->gender->$form = new \stdClass();
                    }
                    $this->gender->$form->female = $data->gender->femalePercent;
                }
            }
            if (isset($data->gender->malePercent)) {
                if ($this->gender->male !== $data->gender->malePercent && !empty($form)) {
                    if (!isset($this->gender->$form)) {
                        $this->gender->$form = new \stdClass();
                    }
                    $this->gender->$form->male = $data->gender->malePercent;
                }
            }
        }
    }
    public function smeargleMoves($data)
    {
        $moves = new class ($data)
        {
            use \PogoApi\Traits\Construct;

            public function __construct($data)
            {


                $this->quick = [];
                $this->charge = [];
                foreach ($data->quickMoves as $k => $v) {
                    $move = $this->move($v);
                    $this->quick[] = $move;
                }
                foreach ($data->cinematicMoves as $k => $v) {
                    $move = $this->move($v);
                    $this->charge[] = $move;
                }
            }
        };
        $this->moves = $moves;
    }
    private function pokemon($data)
    {
        global $PogoApi;
        $this->data = $data;
        $name = $this->name($data->pokemonId);

        $this->dex = $PogoApi->key->mon->$name;
        $this->name = $name;
        $this->family = $this->name(str_replace('FAMILY_', '', $data->familyId));

        if (isset($data->form)) {
            $this->forms($data->form);
        }

        if (isset($data->quickMoves) && isset($data->cinematicMoves)) {
            $this->moves($data->quickMoves, $data->cinematicMoves, $data->thirdMove);
        }
        if (isset($data->evolutionBranch)) {
            $this->evo($data->evolutionBranch);
        }
        if (isset($data->parentPokemonId)) {
            $parent = $this->name($data->parentPokemonId);
            $parent = $PogoApi->key->pokemon->$parent->id;
        }
        if (isset($data->rarity)) {
            $rarity = explode('_', $data->rarity);
            $this->rarity = $rarity;
        }
        $this->stats = $this->stats($data->stats);
        $this->encounter = $this->encounter($data->encounter);
        $this->size = $this->size($data);
        $this->model = $this->model($data);
        $this->buddy = $this->buddy($data);
    }
    private function types($data)
    {
        if (!isset($this->type)) {
            $this->type = [];
        }
        $type1 = $this->type($data->type);
        if (!in_array($type1, $this->type)) {
            $this->type[] = $type1;
        }
        if (isset($data->type2)) {
            $type2 = $this->type($data->type2);
            if (!in_array($type2, $this->type)) {
                $this->type[] = $type2;
            }
        }
    }
    private function stats($stat)
    {
        $stats = new \stdClass();
        $stats->atk = $stat->baseStamina;
        $stats->def = $stat->baseAttack;
        $stats->sta = $stat->baseDefense;
        return $stats;
    }
    private function forms($data)
    {
        if (!isset($this->forms)) {
            $this->forms = [];
        }
        $form = $this->form($this->name, $data);
        if (!in_array($form, $this->forms) && $form !== null) {
            $this->forms[] = $form;
        }
    }
    private function encounter($data)
    {
        $result = new \stdClass();
        $result->capture = $data->baseCaptureRate;
        $result->flee = $data->baseFleeRate;
        $result->movement = $this->name(str_replace('MOVEMENT_', '', $data->movementType));
        $result->timer = $data->movementTimerS;
        $result->jumpTime = $data->jumpTimeS;
        $result->attackTimerS = $data->attackTimerS;
        $result->atkChance = $data->attackProbability;
        $result->dodgeChance = $data->dodgeProbability;
        $result->dodgeDuration = $data->dodgeDurationS;
        $result->dodgeDistance = $data->dodgeDistance;
        $result->minAction = $data->minPokemonActionFrequencyS;
        $result->maxAction = $data->maxPokemonActionFrequencyS;
        return $result;
    }
    private function size($data)
    {
        $size = new \stdClass();
        $size->height = $data->pokedexHeightM;
        $size->weight = $data->pokedexWeightKg;
        $size->heightDev = $data->heightStdDev;
        $size->weightDev = $data->weightStdDev;
        return $size;
    }
    private function buddy($data)
    {
        $buddy = new \stdClass();
        $buddy->distance = $data->kmBuddyDistance;
        $buddy->offsetMale = $data->buddyOffsetMale;
        $buddy->offsetFemale = $data->buddyOffsetFemale;
        $buddy->scale = $data->buddyScale;
        if (isset($data->buddySize)) {
            $size = explode('_', $data->buddySize);
            $size = $this->name($size[1]);
            $buddy->size = $size;
        }
        return $buddy;
    }
    private function model($data)
    {
        $model = new \stdClass();
        $model->scale = $data->modelScale;
        $model->scaleV2 = $data->modelScaleV2;
        $model->height = $data->modelHeight;
        return $model;
    }
    private function moves($quick, $charge, $third)
    {
        if (!isset($this->moves)) {
            $this->moves = new \stdClass();
            $this->moves->quick = [];
            $this->moves->charge = [];
            $this->moves->third = new \stdClass();
        }
        foreach ($quick as $k => $v) {
            $this->moves->quick[] = $this->move($v);
        }
        foreach ($charge as $k => $v) {
            $this->moves->charge[] = $this->move($v);
        }

        $this->moves->third->stardust = $third->stardustToUnlock;
        $this->moves->third->target = $third->stardustToUnlock;
    }
    private function evo($branch)
    {
        if (!isset($this->evo)) {
            $this->evo = [];
        }
        foreach ($branch as $k => $v) {
            $evolution = new class ($v)
            {
                use \PogoApi\Traits\Construct;
                public function __construct($v)
                {
                    $this->name = $v->evolution;
                    $this->cost = $v->candyCost;
                    if (isset($v->form)) {
                        $form = explode('_', $v->form);
                        $form = array_splice($form, 1);
                        if ($form[0] !== 'NORMAL') {
                            $this->form = $this->name(implode(' ', $form));
                        }
                    }
                    if (isset($v->evolutionItemRequirement)) {
                        $this->item = $this->item($v->evolutionItemRequirement);
                    }
                    if (isset($v->kmBuddyDistanceRequirement)) {
                        $this->walkDistance = $v->kmBuddyDistanceRequirement;
                    }
                    if (isset($v->mustBeBuddy)) {
                        $this->buddy = $v->mustBeBuddy;
                    }
                    if (isset($v->onlyNighttime) && $v->onlyNighttime === true) {
                        $this->time = 'Night';
                    } else if (isset($v->onlyDaytime) && $v->onlyDaytime === true) {
                        $this->time = 'Day';
                    }
                    if (isset($v->priority)) {
                        $this->priority = $v->priority;
                    }
                    if (isset($v->lureItemRequirement)) {
                        $module = str_split('_', $v->lureItemRequirement);
                        $this->lureModule = $this->name($module[3]);
                    }
                }
            };
            $this->evo[] = $evolution;
        }
    }
    public function addShadow($data)
    {
        $this->shadow = new class ($data)
        {
            public function __construct($data)
            {
                $capture = $data->encounter;
                $data = $data->shadow;
                $this->dust = $data->purificationStardustNeeded;
                $this->candy = $data->purificationCandyNeeded;
                $this->purifiedMove = $data->purifiedChargeMove;
                $this->shadowMove = $data->shadowChargeMove;
                $this->capture = $capture->baseCaptureRate;
                $this->flee = $capture->baseFleeRate;
            }
        };
        if (!isset($this->forms)) {
            $this->forms = [];
        }
        $this->forms[] = 'Shadow';
    }
    public function addForm($data, $form)
    {
        if (isset($this->name)) {
            $name = $this->name;
        } else {
            $name = $this->name($data->pokemonId);
        }
        $form = $this->form($name, $form);
        if (!isset($this->forms)) {
            $this->forms = [];
        }
        $this->forms[] = $form;
        $this->$form = new Pokemon($data, $form);
    }
}
