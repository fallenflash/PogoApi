<?php

namespace PogoApi\Traits;

trait Construct
{
    private function specialNames($name)
    {
        $specialNames = array("Nidoran Female" => "Nidoran♀", "Nidoran Male" => "Nidoran♂", "Mr Mime" => "Mr. Mime", "Ho Oh" => "Ho-Oh", "Mime Jr" => "Mime Jr.", "X Scissor" => "X-Scissor", "Lock On" => "Lock-On", "Futuresight" => "Future Sight");
        if (isset($specialNames[$name])) {
            $name = $specialNames[$name];
        }
        return $name;
    }
    private function name($name)
    {
        $name = str_replace('_', ' ', $name);
        $name = strtolower($name);
        $name = ucwords($name);
        return $this->specialNames($name);
    }
    private function type($type)
    {
        global $PogoApi;
        $type = str_replace('POKEMON_TYPE_', '', $type);
        $type = $this->name($type);
        return $PogoApi->types->$type;
    }
    private function move($move)
    {
        global $PogoApi;

        $move = str_replace('_FAST', '', $move);
        $move = $this->name($move);
        return $PogoApi->key->moves->$move;
    }
    private function item($item)
    {
        global $PogoApi;

        return $PogoApi->key->items->$item;
    }
    private function form($pokemon, $form)
    {
        global $PogoApi;
        $form = $this->name($form);
        $form = str_replace($pokemon . ' ', '', $form);
        if ($form === 'Normal') {
            return null;
        }
        return  $PogoApi->key->forms->$pokemon->$form->id;
    }
}
