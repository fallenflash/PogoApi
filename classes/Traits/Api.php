<?php

namespace PogoApi\Traits;

trait Api
{

    public function __invoke($prop, $set = null, $output = 'object')
    {
        if ($set === null) {
            if (isset($this->$prop)) {
                $prop = $this->$prop;
            } else {
                $prop = null;
            }
            switch ($output) {
                case 'object':
                    return $prop;
                    break;
                case 'string':
                    if (is_array($prop)) {
                        $prop = implode(', ', $prop);
                    } else if (is_object($prop)) {
                        foreach ($prop as $k => $v) {
                            $prop .= $k . '=' . $v . ', ';
                        }
                    } else {
                        $prop = strval($prop);
                    }
                    return $prop;
                    break;
                case 'json':
                    return json_encode($prop, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    break;
                case 'echo':
                    if (is_array($prop)) {
                        $prop = implode(', ', $prop);
                    } else if (is_object($prop)) {
                        foreach ($prop as $k => $v) {
                            $prop .= $k . '=' . $v . ', ';
                        }
                    } else {
                        $prop = strval($prop);
                    }
                    echo $prop;
                    break;
                default:
                    return prop;
                    break;
            }
        } else {
            $this->$prop = $set;
            return $this;
        }
    }
}
