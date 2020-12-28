<?php $GLOBALS['__jpv_dotWithArrayPrototype'] = function ($base) {
    $arrayPrototype = function ($base, $key) {
        if ($key === 'length') {
            return count($base);
        }
        if ($key === 'forEach') {
            return function ($callback, $userData = null) use (&$base) {
                return array_walk($base, $callback, $userData);
            };
        }
        if ($key === 'map') {
            return function ($callback) use (&$base) {
                return array_map($callback, $base);
            };
        }
        if ($key === 'filter') {
            return function ($callback, $flag = 0) use ($base) {
                return func_num_args() === 1 ? array_filter($base, $callback) : array_filter($base, $callback, $flag);
            };
        }
        if ($key === 'pop') {
            return function () use (&$base) {
                return array_pop($base);
            };
        }
        if ($key === 'shift') {
            return function () use (&$base) {
                return array_shift($base);
            };
        }
        if ($key === 'push') {
            return function ($item) use (&$base) {
                return array_push($base, $item);
            };
        }
        if ($key === 'unshift') {
            return function ($item) use (&$base) {
                return array_unshift($base, $item);
            };
        }
        if ($key === 'indexOf') {
            return function ($item) use (&$base) {
                $search = array_search($item, $base);

                return $search === false ? -1 : $search;
            };
        }
        if ($key === 'slice') {
            return function ($offset, $length = null, $preserveKeys = false) use (&$base) {
                return array_slice($base, $offset, $length, $preserveKeys);
            };
        }
        if ($key === 'splice') {
            return function ($offset, $length = null, $replacements = array()) use (&$base) {
                return array_splice($base, $offset, $length, $replacements);
            };
        }
        if ($key === 'reverse') {
            return function () use (&$base) {
                return array_reverse($base);
            };
        }
        if ($key === 'reduce') {
            return function ($callback, $initial = null) use (&$base) {
                return array_reduce($base, $callback, $initial);
            };
        }
        if ($key === 'join') {
            return function ($glue) use (&$base) {
                return implode($glue, $base);
            };
        }
        if ($key === 'sort') {
            return function ($callback = null) use (&$base) {
                return $callback ? usort($base, $callback) : sort($base);
            };
        }

        return null;
    };

    $getFromArray = function ($base, $key) use ($arrayPrototype) {
        return isset($base[$key])
            ? $base[$key]
            : $arrayPrototype($base, $key);
    };

    $getCallable = function ($base, $key) use ($getFromArray) {
        if (is_callable(array($base, $key))) {
            return new class(array($base, $key)) extends \ArrayObject
            {
                public function getValue()
                {
                    if ($this->isArrayAccessible()) {
                        return $this[0][$this[1]];
                    }

                    return $this[0]->{$this[1]} ?? null;
                }

                public function setValue($value)
                {
                    if ($this->isArrayAccessible()) {
                        $this[0][$this[1]] = $value;

                        return;
                    }

                    $this[0]->{$this[1]} = $value;
                }

                public function getCallable()
                {
                    return $this->getArrayCopy();
                }

                public function __isset($name)
                {
                    $value = $this->getValue();

                    if ((is_array($value) || $value instanceof ArrayAccess) && isset($value[$name])) {
                        return true;
                    }

                    return is_object($value) && isset($value->$name);
                }

                public function __get($name)
                {
                    return new self(array($this->getValue(), $name));
                }

                public function __set($name, $value)
                {
                    $value = $this->getValue();

                    if (is_array($value)) {
                        $value[$name] = $value;
                        $this->setValue($value);

                        return;
                    }

                    $value->$name = $value;
                }

                public function __toString()
                {
                    return (string) $this->getValue();
                }

                public function __toBoolean()
                {
                    $value = $this->getValue();

                    if (method_exists($value, '__toBoolean')) {
                        return $value->__toBoolean();
                    }

                    return !!$value;
                }

                public function __invoke(...$arguments)
                {
                    return call_user_func_array($this->getCallable(), $arguments);
                }

                private function isArrayAccessible()
                {
                    return is_array($this[0]) || $this[0] instanceof ArrayAccess && !isset($this[0]->{$this[1]});
                }
            };
        }
        if ($base instanceof \ArrayAccess) {
            return $getFromArray($base, $key);
        }
    };

    $getRegExp = function ($value) {
        return is_object($value) && isset($value->isRegularExpression) && $value->isRegularExpression ? $value->regExp . $value->flags : null;
    };

    $fallbackDot = function ($base, $key) use ($getCallable, $getRegExp) {
        if (is_string($base)) {
            if (preg_match('/^[-+]?\d+$/', strval($key))) {
                return substr($base, intval($key), 1);
            }
            if ($key === 'length') {
                return strlen($base);
            }
            if ($key === 'substr' || $key === 'slice') {
                return function ($start, $length = null) use ($base) {
                    return func_num_args() === 1 ? substr($base, $start) : substr($base, $start, $length);
                };
            }
            if ($key === 'charAt') {
                return function ($pos) use ($base) {
                    return substr($base, $pos, 1);
                };
            }
            if ($key === 'indexOf') {
                return function ($needle) use ($base) {
                    $pos = strpos($base, $needle);

                    return $pos === false ? -1 : $pos;
                };
            }
            if ($key === 'toUpperCase') {
                return function () use ($base) {
                    return strtoupper($base);
                };
            }
            if ($key === 'toLowerCase') {
                return function () use ($base) {
                    return strtolower($base);
                };
            }
            if ($key === 'match') {
                return function ($search) use ($base, $getRegExp) {
                    $regExp = $getRegExp($search);
                    $search = $regExp ? $regExp : (is_string($search) ? '/' . preg_quote($search, '/') . '/' : strval($search));

                    return preg_match($search, $base);
                };
            }
            if ($key === 'split') {
                return function ($delimiter) use ($base, $getRegExp) {
                    if ($regExp = $getRegExp($delimiter)) {
                        return preg_split($regExp, $base);
                    }

                    return explode($delimiter, $base);
                };
            }
            if ($key === 'replace') {
                return function ($from, $to) use ($base, $getRegExp) {
                    if ($regExp = $getRegExp($from)) {
                        return preg_replace($regExp, $to, $base);
                    }

                    return str_replace($from, $to, $base);
                };
            }
        }

        return $getCallable($base, $key);
    };

    foreach (array_slice(func_get_args(), 1) as $key) {
        $base = is_array($base)
            ? $getFromArray($base, $key)
            : (is_object($base)
                ? (isset($base->$key)
                    ? $base->$key
                    : (method_exists($base, $method = "get" . ucfirst($key))
                        ? $base->$method()
                        : (method_exists($base, $key)
                            ? array($base, $key)
                            : $getCallable($base, $key)
                        )
                    )
                )
                : $fallbackDot($base, $key)
            );
    }

    return $base;
};;
$GLOBALS['__jpv_dotWithArrayPrototype_with_ref'] = function (&$base) {
    $arrayPrototype = function (&$base, $key) {
        if ($key === 'length') {
            return count($base);
        }
        if ($key === 'forEach') {
            return function ($callback, $userData = null) use (&$base) {
                return array_walk($base, $callback, $userData);
            };
        }
        if ($key === 'map') {
            return function ($callback) use (&$base) {
                return array_map($callback, $base);
            };
        }
        if ($key === 'filter') {
            return function ($callback, $flag = 0) use ($base) {
                return func_num_args() === 1 ? array_filter($base, $callback) : array_filter($base, $callback, $flag);
            };
        }
        if ($key === 'pop') {
            return function () use (&$base) {
                return array_pop($base);
            };
        }
        if ($key === 'shift') {
            return function () use (&$base) {
                return array_shift($base);
            };
        }
        if ($key === 'push') {
            return function ($item) use (&$base) {
                return array_push($base, $item);
            };
        }
        if ($key === 'unshift') {
            return function ($item) use (&$base) {
                return array_unshift($base, $item);
            };
        }
        if ($key === 'indexOf') {
            return function ($item) use (&$base) {
                $search = array_search($item, $base);

                return $search === false ? -1 : $search;
            };
        }
        if ($key === 'slice') {
            return function ($offset, $length = null, $preserveKeys = false) use (&$base) {
                return array_slice($base, $offset, $length, $preserveKeys);
            };
        }
        if ($key === 'splice') {
            return function ($offset, $length = null, $replacements = array()) use (&$base) {
                return array_splice($base, $offset, $length, $replacements);
            };
        }
        if ($key === 'reverse') {
            return function () use (&$base) {
                return array_reverse($base);
            };
        }
        if ($key === 'reduce') {
            return function ($callback, $initial = null) use (&$base) {
                return array_reduce($base, $callback, $initial);
            };
        }
        if ($key === 'join') {
            return function ($glue) use (&$base) {
                return implode($glue, $base);
            };
        }
        if ($key === 'sort') {
            return function ($callback = null) use (&$base) {
                return $callback ? usort($base, $callback) : sort($base);
            };
        }

        return null;
    };

    $getFromArray = function (&$base, $key) use ($arrayPrototype) {
        return isset($base[$key])
            ? $base[$key]
            : $arrayPrototype($base, $key);
    };

    $getCallable = function (&$base, $key) use ($getFromArray) {
        if (is_callable(array($base, $key))) {
            return new class(array($base, $key)) extends \ArrayObject
            {
                public function getValue()
                {
                    if ($this->isArrayAccessible()) {
                        return $this[0][$this[1]];
                    }

                    return $this[0]->{$this[1]} ?? null;
                }

                public function setValue($value)
                {
                    if ($this->isArrayAccessible()) {
                        $this[0][$this[1]] = $value;

                        return;
                    }

                    $this[0]->{$this[1]} = $value;
                }

                public function getCallable()
                {
                    return $this->getArrayCopy();
                }

                public function __isset($name)
                {
                    $value = $this->getValue();

                    if ((is_array($value) || $value instanceof ArrayAccess) && isset($value[$name])) {
                        return true;
                    }

                    return is_object($value) && isset($value->$name);
                }

                public function __get($name)
                {
                    return new self(array($this->getValue(), $name));
                }

                public function __set($name, $value)
                {
                    $value = $this->getValue();

                    if (is_array($value)) {
                        $value[$name] = $value;
                        $this->setValue($value);

                        return;
                    }

                    $value->$name = $value;
                }

                public function __toString()
                {
                    return (string) $this->getValue();
                }

                public function __toBoolean()
                {
                    $value = $this->getValue();

                    if (method_exists($value, '__toBoolean')) {
                        return $value->__toBoolean();
                    }

                    return !!$value;
                }

                public function __invoke(...$arguments)
                {
                    return call_user_func_array($this->getCallable(), $arguments);
                }

                private function isArrayAccessible()
                {
                    return is_array($this[0]) || $this[0] instanceof ArrayAccess && !isset($this[0]->{$this[1]});
                }
            };
        }
        if ($base instanceof \ArrayAccess) {
            return $getFromArray($base, $key);
        }
    };

    $getRegExp = function ($value) {
        return is_object($value) && isset($value->isRegularExpression) && $value->isRegularExpression ? $value->regExp . $value->flags : null;
    };

    $fallbackDot = function (&$base, $key) use ($getCallable, $getRegExp) {
        if (is_string($base)) {
            if (preg_match('/^[-+]?\d+$/', strval($key))) {
                return substr($base, intval($key), 1);
            }
            if ($key === 'length') {
                return strlen($base);
            }
            if ($key === 'substr' || $key === 'slice') {
                return function ($start, $length = null) use ($base) {
                    return func_num_args() === 1 ? substr($base, $start) : substr($base, $start, $length);
                };
            }
            if ($key === 'charAt') {
                return function ($pos) use ($base) {
                    return substr($base, $pos, 1);
                };
            }
            if ($key === 'indexOf') {
                return function ($needle) use ($base) {
                    $pos = strpos($base, $needle);

                    return $pos === false ? -1 : $pos;
                };
            }
            if ($key === 'toUpperCase') {
                return function () use ($base) {
                    return strtoupper($base);
                };
            }
            if ($key === 'toLowerCase') {
                return function () use ($base) {
                    return strtolower($base);
                };
            }
            if ($key === 'match') {
                return function ($search) use ($base, $getRegExp) {
                    $regExp = $getRegExp($search);
                    $search = $regExp ? $regExp : (is_string($search) ? '/' . preg_quote($search, '/') . '/' : strval($search));

                    return preg_match($search, $base);
                };
            }
            if ($key === 'split') {
                return function ($delimiter) use ($base, $getRegExp) {
                    if ($regExp = $getRegExp($delimiter)) {
                        return preg_split($regExp, $base);
                    }

                    return explode($delimiter, $base);
                };
            }
            if ($key === 'replace') {
                return function ($from, $to) use ($base, $getRegExp) {
                    if ($regExp = $getRegExp($from)) {
                        return preg_replace($regExp, $to, $base);
                    }

                    return str_replace($from, $to, $base);
                };
            }
        }

        return $getCallable($base, $key);
    };

    $crawler = &$base;
    $result = $base;
    foreach (array_slice(func_get_args(), 1) as $key) {
        $result = is_array($crawler)
            ? $getFromArray($crawler, $key)
            : (is_object($crawler)
                ? (isset($crawler->$key)
                    ? $crawler->$key
                    : (method_exists($crawler, $method = "get" . ucfirst($key))
                        ? $crawler->$method()
                        : (method_exists($crawler, $key)
                            ? array($crawler, $key)
                            : $getCallable($crawler, $key)
                        )
                    )
                )
                : $fallbackDot($crawler, $key)
            );
        $crawler = &$result;
    }

    return $result;
};;
$GLOBALS['__jpv_and'] = function ($base) {
    foreach (array_slice(func_get_args(), 1) as $value) {
        if ($base) {
            $base = $value();
        }
    }

    return $base;
};
$GLOBALS['__jpv_and_with_ref'] = $GLOBALS['__jpv_and'];
$GLOBALS['__jpv_regExp'] = function ($value) {
    $chunks = strlen($value) ? explode(substr($value, 0, 1), $value) : [''];
    $flags = preg_replace('/[^imsxeADSUXJu]/', '', end($chunks));

    return (object) array(
        'isRegularExpression' => true,
        'flags' => $flags,
        'regExp' => rtrim($value, 'gimuy'),
    );
};
$GLOBALS['__jpv_regExp_with_ref'] = $GLOBALS['__jpv_regExp'];
$GLOBALS['__jpv_or'] = function ($base) {
    foreach (array_slice(func_get_args(), 1) as $value) {
        if (!$base) {
            $base = $value();
        }
    }

    return $base;
};
$GLOBALS['__jpv_or_with_ref'] = function (&$base) {
    foreach (array_slice(func_get_args(), 1) as $value) {
        if (!$base) {
            $base = $value();
        }
    }

    return $base;
};
$GLOBALS['__jpv_set'] = function ($base, $key, $operator, $value) {
    switch ($operator) {
        case '=':
            if (is_array($base)) {
                $base[$key] = $value;
                break;
            }
            if (method_exists($base, $method = "set" . ucfirst($key))) {
                $base->$method($value);
                break;
            }
            $base->$key = $value;
            break;
        case '+=':
            if (is_array($base)) {
                if ((isset($base[$key]) && is_string($base[$key])) || is_string($value)) {
                    $base[$key] .= $value;
                    break;
                }
                $base[$key] += $value;
                break;
            }
            if ((isset($base->$key) && is_string($base->$key)) || is_string($value)) {
                $base->$key .= $value;
                break;
            }
            $base->$key += $value;
            break;
        case '-=':
            if (is_array($base)) {
                $base[$key] -= $value;
                break;
            }
            $base->$key -= $value;
            break;
        case '*=':
            if (is_array($base)) {
                $base[$key] *= $value;
                break;
            }
            $base->$key *= $value;
            break;
        case '/=':
            if (is_array($base)) {
                $base[$key] /= $value;
                break;
            }
            $base->$key /= $value;
            break;
        case '%=':
            if (is_array($base)) {
                $base[$key] %= $value;
                break;
            }
            $base->$key %= $value;
            break;
        case '|=':
            if (is_array($base)) {
                $base[$key] |= $value;
                break;
            }
            $base->$key |= $value;
            break;
        case '&=':
            if (is_array($base)) {
                $base[$key] &= $value;
                break;
            }
            $base->$key &= $value;
            break;
        case '&&=':
            if (is_array($base)) {
                $base[$key] = $base[$key] ? $value : $base[$key];
                break;
            }
            $base->$key = $base->$key ? $value : $base->$key;
            break;
        case '||=':
            if (is_array($base)) {
                $base[$key] = $base[$key] ? $base[$key] : $value;
                break;
            }
            $base->$key = $base->$key ? $base->$key : $value;
            break;
    }

    return $base;
};
$GLOBALS['__jpv_set_with_ref'] = $GLOBALS['__jpv_set'];
$GLOBALS['__jpv_plus'] = function ($base) {
    foreach (array_slice(func_get_args(), 1) as $value) {
        $base = is_string($base) || is_string($value) ? $base . $value : $base + $value;
    }

    return $base;
};
$GLOBALS['__jpv_plus_with_ref'] = $GLOBALS['__jpv_plus'];
$GLOBALS['__jpv_regExpClass'] = RegExp::class; class RegExp {
    private $regExp = '';

    public function __construct($regExp) {
        $this->regExp = $regExp;
    }

    public function __toString() {
        return '/' . str_replace('/', '\\/', $this->regExp) . '/';
    }
};
$GLOBALS['__jpv_regExpClass_with_ref'] = $GLOBALS['__jpv_regExpClass'];
 ?><?php
$pug_vars = [];
foreach (array_keys(get_defined_vars()) as $__pug_key) {
    $pug_vars[$__pug_key] = &$$__pug_key;
}
?><?php $pugModule = [
  'Phug\\Formatter\\Format\\BasicFormat::dependencies_storage' => 'pugModule',
  'Phug\\Formatter\\Format\\BasicFormat::helper_prefix' => 'Phug\\Formatter\\Format\\BasicFormat::',
  'Phug\\Formatter\\Format\\BasicFormat::get_helper' => function ($name) use (&$pugModule) {
    $dependenciesStorage = $pugModule['Phug\\Formatter\\Format\\BasicFormat::dependencies_storage'];
    $prefix = $pugModule['Phug\\Formatter\\Format\\BasicFormat::helper_prefix'];
    $format = $pugModule['Phug\\Formatter\\Format\\BasicFormat::dependencies_storage'];

                            if (!isset($$dependenciesStorage)) {
                                return $format->getHelper($name);
                            }

                            $storage = $$dependenciesStorage;

                            if (!isset($storage[$prefix.$name]) &&
                                !(is_array($storage) && array_key_exists($prefix.$name, $storage))
                            ) {
                                throw new \Exception(
                                    var_export($name, true).
                                    ' dependency not found in the namespace: '.
                                    var_export($prefix, true)
                                );
                            }

                            return $storage[$prefix.$name];
                        },
  'Phug\\Formatter\\Format\\BasicFormat::pattern' => function ($pattern) use (&$pugModule) {

                    $args = func_get_args();
                    $function = 'sprintf';
                    if (is_callable($pattern)) {
                        $function = $pattern;
                        $args = array_slice($args, 1);
                    }

                    return call_user_func_array($function, $args);
                },
  'Phug\\Formatter\\Format\\BasicFormat::patterns.html_text_escape' => 'htmlspecialchars',
  'Phug\\Formatter\\Format\\BasicFormat::pattern.html_text_escape' => function () use (&$pugModule) {
    $proceed = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\BasicFormat::patterns.html_text_escape'];

                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                },
  'Phug\\Formatter\\Format\\BasicFormat::available_attribute_assignments' => array (
  0 => 'class',
  1 => 'style',
),
  'Phug\\Formatter\\Format\\BasicFormat::patterns.attribute_pattern' => ' %s="%s"',
  'Phug\\Formatter\\Format\\BasicFormat::pattern.attribute_pattern' => function () use (&$pugModule) {
    $proceed = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\BasicFormat::patterns.attribute_pattern'];

                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                },
  'Phug\\Formatter\\Format\\BasicFormat::patterns.boolean_attribute_pattern' => ' %s="%s"',
  'Phug\\Formatter\\Format\\BasicFormat::pattern.boolean_attribute_pattern' => function () use (&$pugModule) {
    $proceed = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\BasicFormat::patterns.boolean_attribute_pattern'];

                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                },
  'Phug\\Formatter\\Format\\BasicFormat::attribute_assignments' => function (&$attributes, $name, $value) use (&$pugModule) {
    $availableAssignments = $pugModule['Phug\\Formatter\\Format\\BasicFormat::available_attribute_assignments'];
    $getHelper = $pugModule['Phug\\Formatter\\Format\\BasicFormat::get_helper'];

                    if (!in_array($name, $availableAssignments)) {
                        return $value;
                    }

                    $helper = $getHelper($name.'_attribute_assignment');

                    return $helper($attributes, $value);
                },
  'Phug\\Formatter\\Format\\BasicFormat::attribute_assignment' => function (&$attributes, $name, $value) use (&$pugModule) {
    $attributeAssignments = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attribute_assignments'];

                    if (isset($name) && $name !== '') {
                        $result = $attributeAssignments($attributes, $name, $value);
                        if (($result !== null && $result !== false && ($result !== '' || $name !== 'class'))) {
                            $attributes[$name] = $result;
                        }
                    }
                },
  'Phug\\Formatter\\Format\\BasicFormat::merge_attributes' => function () use (&$pugModule) {
    $attributeAssignment = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attribute_assignment'];

                    $attributes = [];
                    foreach (array_filter(func_get_args(), 'is_array') as $input) {
                        foreach ($input as $name => $value) {
                            $attributeAssignment($attributes, $name, $value);
                        }
                    }

                    return $attributes;
                },
  'Phug\\Formatter\\Format\\BasicFormat::array_escape' => function ($name, $input) use (&$pugModule) {
    $arrayEscape = $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape'];
    $escape = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern.html_text_escape'];

                        if (is_array($input) && in_array(strtolower($name), ['class', 'style'])) {
                            $result = [];
                            foreach ($input as $key => $value) {
                                $result[$escape($key)] = $arrayEscape($name, $value);
                            }

                            return $result;
                        }
                        if (is_array($input) || is_object($input) && !method_exists($input, '__toString')) {
                            return $escape(json_encode($input));
                        }
                        if (is_string($input)) {
                            return $escape($input);
                        }

                        return $input;
                    },
  'Phug\\Formatter\\Format\\BasicFormat::attributes_mapping' => array (
),
  'Phug\\Formatter\\Format\\BasicFormat::attributes_assignment' => function () use (&$pugModule) {
    $attrMapping = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_mapping'];
    $mergeAttr = $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern'];
    $escape = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern.html_text_escape'];
    $attr = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern.attribute_pattern'];
    $bool = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern.boolean_attribute_pattern'];

                        $attributes = call_user_func_array($mergeAttr, func_get_args());
                        $code = '';
                        foreach ($attributes as $originalName => $value) {
                            if ($value !== null && $value !== false && ($value !== '' || $originalName !== 'class')) {
                                $name = isset($attrMapping[$originalName])
                                    ? $attrMapping[$originalName]
                                    : $originalName;
                                if ($value === true) {
                                    $code .= $pattern($bool, $name, $name);

                                    continue;
                                }
                                if (is_array($value) || is_object($value) &&
                                    !method_exists($value, '__toString')) {
                                    $value = json_encode($value);
                                }

                                $code .= $pattern($attr, $name, $value);
                            }
                        }

                        return $code;
                    },
  'Phug\\Formatter\\Format\\BasicFormat::class_attribute_assignment' => function (&$attributes, $value) use (&$pugModule) {

            $split = function ($input) {
                return preg_split('/(?<![\[\{\<\=\%])\s+(?![\]\}\>\=\%])/', strval($input));
            };
            $classes = isset($attributes['class']) ? array_filter($split($attributes['class'])) : [];
            foreach ((array) $value as $key => $input) {
                if (!is_string($input) && is_string($key)) {
                    if (!$input) {
                        continue;
                    }

                    $input = $key;
                }
                foreach ($split($input) as $class) {
                    if (!in_array($class, $classes)) {
                        $classes[] = $class;
                    }
                }
            }

            return implode(' ', $classes);
        },
  'Phug\\Formatter\\Format\\BasicFormat::style_attribute_assignment' => function (&$attributes, $value) use (&$pugModule) {

            if (is_string($value) && mb_substr($value, 0, 7) === '{&quot;') {
                $value = json_decode(htmlspecialchars_decode($value));
            }
            $styles = isset($attributes['style']) ? array_filter(explode(';', $attributes['style'])) : [];
            foreach ((array) $value as $propertyName => $propertyValue) {
                if (!is_int($propertyName)) {
                    $propertyValue = $propertyName.':'.$propertyValue;
                }
                $styles[] = $propertyValue;
            }

            return implode(';', $styles);
        },
  'Phug\\Formatter\\Format\\HtmlFormat::dependencies_storage' => 'pugModule',
  'Phug\\Formatter\\Format\\HtmlFormat::helper_prefix' => 'Phug\\Formatter\\Format\\HtmlFormat::',
  'Phug\\Formatter\\Format\\HtmlFormat::get_helper' => function ($name) use (&$pugModule) {
    $dependenciesStorage = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::dependencies_storage'];
    $prefix = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::helper_prefix'];
    $format = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::dependencies_storage'];

                            if (!isset($$dependenciesStorage)) {
                                return $format->getHelper($name);
                            }

                            $storage = $$dependenciesStorage;

                            if (!isset($storage[$prefix.$name]) &&
                                !(is_array($storage) && array_key_exists($prefix.$name, $storage))
                            ) {
                                throw new \Exception(
                                    var_export($name, true).
                                    ' dependency not found in the namespace: '.
                                    var_export($prefix, true)
                                );
                            }

                            return $storage[$prefix.$name];
                        },
  'Phug\\Formatter\\Format\\HtmlFormat::pattern' => function ($pattern) use (&$pugModule) {

                    $args = func_get_args();
                    $function = 'sprintf';
                    if (is_callable($pattern)) {
                        $function = $pattern;
                        $args = array_slice($args, 1);
                    }

                    return call_user_func_array($function, $args);
                },
  'Phug\\Formatter\\Format\\HtmlFormat::patterns.html_text_escape' => 'htmlspecialchars',
  'Phug\\Formatter\\Format\\HtmlFormat::pattern.html_text_escape' => function () use (&$pugModule) {
    $proceed = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::pattern'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::patterns.html_text_escape'];

                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                },
  'Phug\\Formatter\\Format\\HtmlFormat::available_attribute_assignments' => array (
  0 => 'class',
  1 => 'style',
),
  'Phug\\Formatter\\Format\\HtmlFormat::patterns.attribute_pattern' => ' %s="%s"',
  'Phug\\Formatter\\Format\\HtmlFormat::pattern.attribute_pattern' => function () use (&$pugModule) {
    $proceed = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::pattern'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::patterns.attribute_pattern'];

                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                },
  'Phug\\Formatter\\Format\\HtmlFormat::patterns.boolean_attribute_pattern' => ' %s',
  'Phug\\Formatter\\Format\\HtmlFormat::pattern.boolean_attribute_pattern' => function () use (&$pugModule) {
    $proceed = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::pattern'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::patterns.boolean_attribute_pattern'];

                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                },
  'Phug\\Formatter\\Format\\HtmlFormat::attribute_assignments' => function (&$attributes, $name, $value) use (&$pugModule) {
    $availableAssignments = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::available_attribute_assignments'];
    $getHelper = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::get_helper'];

                    if (!in_array($name, $availableAssignments)) {
                        return $value;
                    }

                    $helper = $getHelper($name.'_attribute_assignment');

                    return $helper($attributes, $value);
                },
  'Phug\\Formatter\\Format\\HtmlFormat::attribute_assignment' => function (&$attributes, $name, $value) use (&$pugModule) {
    $attributeAssignments = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attribute_assignments'];

                    if (isset($name) && $name !== '') {
                        $result = $attributeAssignments($attributes, $name, $value);
                        if (($result !== null && $result !== false && ($result !== '' || $name !== 'class'))) {
                            $attributes[$name] = $result;
                        }
                    }
                },
  'Phug\\Formatter\\Format\\HtmlFormat::merge_attributes' => function () use (&$pugModule) {
    $attributeAssignment = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attribute_assignment'];

                    $attributes = [];
                    foreach (array_filter(func_get_args(), 'is_array') as $input) {
                        foreach ($input as $name => $value) {
                            $attributeAssignment($attributes, $name, $value);
                        }
                    }

                    return $attributes;
                },
  'Phug\\Formatter\\Format\\HtmlFormat::array_escape' => function ($name, $input) use (&$pugModule) {
    $arrayEscape = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape'];
    $escape = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::pattern.html_text_escape'];

                        if (is_array($input) && in_array(strtolower($name), ['class', 'style'])) {
                            $result = [];
                            foreach ($input as $key => $value) {
                                $result[$escape($key)] = $arrayEscape($name, $value);
                            }

                            return $result;
                        }
                        if (is_array($input) || is_object($input) && !method_exists($input, '__toString')) {
                            return $escape(json_encode($input));
                        }
                        if (is_string($input)) {
                            return $escape($input);
                        }

                        return $input;
                    },
  'Phug\\Formatter\\Format\\HtmlFormat::attributes_mapping' => array (
),
  'Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment' => function () use (&$pugModule) {
    $attrMapping = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_mapping'];
    $mergeAttr = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::pattern'];
    $escape = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::pattern.html_text_escape'];
    $attr = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::pattern.attribute_pattern'];
    $bool = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::pattern.boolean_attribute_pattern'];

                        $attributes = call_user_func_array($mergeAttr, func_get_args());
                        $code = '';
                        foreach ($attributes as $originalName => $value) {
                            if ($value !== null && $value !== false && ($value !== '' || $originalName !== 'class')) {
                                $name = isset($attrMapping[$originalName])
                                    ? $attrMapping[$originalName]
                                    : $originalName;
                                if ($value === true) {
                                    $code .= $pattern($bool, $name, $name);

                                    continue;
                                }
                                if (is_array($value) || is_object($value) &&
                                    !method_exists($value, '__toString')) {
                                    $value = json_encode($value);
                                }

                                $code .= $pattern($attr, $name, $value);
                            }
                        }

                        return $code;
                    },
  'Phug\\Formatter\\Format\\HtmlFormat::class_attribute_assignment' => function (&$attributes, $value) use (&$pugModule) {

            $split = function ($input) {
                return preg_split('/(?<![\[\{\<\=\%])\s+(?![\]\}\>\=\%])/', strval($input));
            };
            $classes = isset($attributes['class']) ? array_filter($split($attributes['class'])) : [];
            foreach ((array) $value as $key => $input) {
                if (!is_string($input) && is_string($key)) {
                    if (!$input) {
                        continue;
                    }

                    $input = $key;
                }
                foreach ($split($input) as $class) {
                    if (!in_array($class, $classes)) {
                        $classes[] = $class;
                    }
                }
            }

            return implode(' ', $classes);
        },
  'Phug\\Formatter\\Format\\HtmlFormat::style_attribute_assignment' => function (&$attributes, $value) use (&$pugModule) {

            if (is_string($value) && mb_substr($value, 0, 7) === '{&quot;') {
                $value = json_decode(htmlspecialchars_decode($value));
            }
            $styles = isset($attributes['style']) ? array_filter(explode(';', $attributes['style'])) : [];
            foreach ((array) $value as $propertyName => $propertyValue) {
                if (!is_int($propertyName)) {
                    $propertyValue = $propertyName.':'.$propertyValue;
                }
                $styles[] = $propertyValue;
            }

            return implode(';', $styles);
        },
]; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['bemto_custom_inline_tag'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'customTag', null], [false, 'self_closing', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(11);
// PUG_DEBUG:11
 ?><?php $self_closing = $GLOBALS['__jpv_or_with_ref']($self_closing, function () { return false; }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(12);
// PUG_DEBUG:12
 ?><<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(13);
// PUG_DEBUG:13
 ?><?= htmlspecialchars((is_bool($_pug_temp = (isset($customTag) ? $customTag : null)) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(21);
// PUG_DEBUG:21
 ?><?php if (method_exists($_pug_temp = (isset($attributes) ? $attributes : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(20);
// PUG_DEBUG:20
 ?><?php foreach ($attributes as $attribute => $__current_value) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(19);
// PUG_DEBUG:19
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'hasOwnProperty')((isset($attribute) ? $attribute : null)), function () use (&$attributes, &$attribute) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, (isset($attribute) ? $attribute : null)) !== $GLOBALS['__jpv_and'](false, function () use (&$attributes, &$attribute) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, (isset($attribute) ? $attribute : null)) !== null; }); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(14);
// PUG_DEBUG:14
 ?> <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(15);
// PUG_DEBUG:15
 ?><?= htmlspecialchars((is_bool($_pug_temp = (isset($attribute) ? $attribute : null)) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(16);
// PUG_DEBUG:16
 ?>="<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(17);
// PUG_DEBUG:17
 ?><?= (is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, (isset($attribute) ? $attribute : null)) === true ? (isset($attribute) ? $attribute : null) : $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, (isset($attribute) ? $attribute : null))) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(18);
// PUG_DEBUG:18
 ?>"<?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(23);
// PUG_DEBUG:23
 }  if (method_exists($_pug_temp = (isset($self_closing) ? $self_closing : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(22);
// PUG_DEBUG:22
 ?>/><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(24);
// PUG_DEBUG:24
 ?>><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(25);
// PUG_DEBUG:25
 ?></<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(26);
// PUG_DEBUG:26
 ?><?= htmlspecialchars((is_bool($_pug_temp = (isset($customTag) ? $customTag : null)) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(27);
// PUG_DEBUG:27
 ?>><?php } ?><?php
}; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['bemto_custom_tag'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'customTag', null], [false, 'tagMetadata', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(28);
// PUG_DEBUG:28
 ?><?php $customTag = $GLOBALS['__jpv_or_with_ref']($customTag, function () { return 'div'; }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(29);
// PUG_DEBUG:29
 ?><?php $tagMetadata = $GLOBALS['__jpv_or_with_ref']($tagMetadata, function () { return array(  ); }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(30);
// PUG_DEBUG:30
 ?><?php $selfClosing = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(33);
// PUG_DEBUG:33
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($customTag, 'substr')(-1) === '/', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(31);
// PUG_DEBUG:31
 ?><?php $selfClosing = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(32);
// PUG_DEBUG:32
 ?><?php $customTag = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($customTag, 'slice')(0, -1) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(34);
// PUG_DEBUG:34
 }  $tag_type = $GLOBALS['__jpv_or']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($tagMetadata, 'type'), function () use (&$customTag, &$get_bemto_tag_type) { return (function_exists('get_bemto_tag_type') ? get_bemto_tag_type($customTag) : $get_bemto_tag_type($customTag)); }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(45);
// PUG_DEBUG:45
 ?><?php switch ($tag_type) { ?><?php case 'inline' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(35);
// PUG_DEBUG:35
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'bemto_custom_inline_tag';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($pugModule, 'Phug\\Formatter\\Format\\BasicFormat::merge_attributes')(array(  ), (isset($attributes) ? $attributes : null)), [[false, (isset($customTag) ? $customTag : null)]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    ?><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php
}); ?><?php break ?><?php case 'self_closing' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(36);
// PUG_DEBUG:36
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'bemto_custom_inline_tag';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($pugModule, 'Phug\\Formatter\\Format\\BasicFormat::merge_attributes')(array(  ), (isset($attributes) ? $attributes : null)), [[false, (isset($customTag) ? $customTag : null)], [false, true]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    ?><?php
}); ?><?php break ?><?php default ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(40);
// PUG_DEBUG:40
 ?><?php if (method_exists($_pug_temp = (isset($selfClosing) ? $selfClosing : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(39);
// PUG_DEBUG:39
 ?><<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(37);
// PUG_DEBUG:37
 ?><?= (is_bool($_pug_temp = (isset($customTag) ? $customTag : null)) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(38);
// PUG_DEBUG:38
 ?><?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment']($attributes)) ? var_export($_pug_temp, true) : $_pug_temp) ?> /><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(44);
// PUG_DEBUG:44
 ?><<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(41);
// PUG_DEBUG:41
 ?><?= (is_bool($_pug_temp = (isset($customTag) ? $customTag : null)) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(42);
// PUG_DEBUG:42
 ?><?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment']($attributes)) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(43);
// PUG_DEBUG:43
 ?></<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(41);
// PUG_DEBUG:41
 ?><?= (is_bool($_pug_temp = (isset($customTag) ? $customTag : null)) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php } ?><?php } ?><?php
}; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['bemto_tag'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'tag', null], [false, 'tagMetadata', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(47);
// PUG_DEBUG:47
 ?><?php $settings = (function_exists('get_bemto_settings') ? get_bemto_settings() : $get_bemto_settings()) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(48);
// PUG_DEBUG:48
 ?><?php $tagMetadata = $GLOBALS['__jpv_or_with_ref']($tagMetadata, function () { return array(  ); }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(49);
// PUG_DEBUG:49
 ?><?php $newTag = $GLOBALS['__jpv_or_with_ref']($tag, function () use (&$settings) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'default_tag'); }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(50);
// PUG_DEBUG:50
 ?><?php $contextIndex = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, 'length') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(57);
// PUG_DEBUG:57
 ?><?php if (!(isset($tag) ? $tag : null)) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(52);
// PUG_DEBUG:52
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, (isset($contextIndex) ? $contextIndex : null) - 1) === 'inline', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(51);
// PUG_DEBUG:51
 ?><?php $newTag = 'span' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(54);
// PUG_DEBUG:54
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, (isset($contextIndex) ? $contextIndex : null) - 1) === 'list', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(53);
// PUG_DEBUG:53
 ?><?php $newTag = 'li' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(56);
// PUG_DEBUG:56
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, (isset($contextIndex) ? $contextIndex : null) - 1) === 'optionlist', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(55);
// PUG_DEBUG:55
 ?><?php $newTag = 'option' ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(66);
// PUG_DEBUG:66
 }  if (!$GLOBALS['__jpv_or_with_ref']($tag, function () use (&$tag) { return (isset($tag) ? $tag : null) == $GLOBALS['__jpv_or']('span', function () use (&$tag) { return (isset($tag) ? $tag : null) == 'div'; }); })) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(59);
// PUG_DEBUG:59
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'href'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(58);
// PUG_DEBUG:58
 ?><?php $newTag = 'a' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(61);
// PUG_DEBUG:61
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'for'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(60);
// PUG_DEBUG:60
 ?><?php $newTag = 'label' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(63);
// PUG_DEBUG:63
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'type'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(62);
// PUG_DEBUG:62
 ?><?php $newTag = $block ? 'button' : 'input' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(65);
// PUG_DEBUG:65
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'src'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(64);
// PUG_DEBUG:64
 ?><?php $newTag = 'img' ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(68);
// PUG_DEBUG:68
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, (isset($contextIndex) ? $contextIndex : null) - 1) === $GLOBALS['__jpv_and']('list', function () use (&$newTag) { return (isset($newTag) ? $newTag : null) !== 'li'; }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(67);
// PUG_DEBUG:67
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(71);
// PUG_DEBUG:71
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, (isset($contextIndex) ? $contextIndex : null) - 1) !== $GLOBALS['__jpv_and']('list', function () use (&$bemto_chain_contexts, &$contextIndex, &$newTag) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, (isset($contextIndex) ? $contextIndex : null) - 1) !== $GLOBALS['__jpv_and']('pseudo-list', function () use (&$newTag) { return (isset($newTag) ? $newTag : null) === 'li'; }); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(69);
// PUG_DEBUG:69
 ?><ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(70);
// PUG_DEBUG:70
 ?><?php $bemto_chain_contexts = $GLOBALS['__jpv_set_with_ref']($bemto_chain_contexts, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, 'length'), '=', 'pseudo-list') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(74);
// PUG_DEBUG:74
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, (isset($contextIndex) ? $contextIndex : null) - 1) === $GLOBALS['__jpv_and']('pseudo-list', function () use (&$newTag) { return (isset($newTag) ? $newTag : null) !== 'li'; }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(72);
// PUG_DEBUG:72
 ?></ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(73);
// PUG_DEBUG:73
 ?><?php $bemto_chain_contexts = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, 'splice')(0, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, 'length') - 1) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(75);
// PUG_DEBUG:75
 }  $content_type = $GLOBALS['__jpv_or']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($tagMetadata, 'content_type'), function () use (&$newTag, &$get_bemto_tag_content_type) { return (function_exists('get_bemto_tag_content_type') ? get_bemto_tag_content_type($newTag) : $get_bemto_tag_content_type($newTag)); }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(76);
// PUG_DEBUG:76
 ?><?php $bemto_chain_contexts = $GLOBALS['__jpv_set_with_ref']($bemto_chain_contexts, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, 'length'), '=', $content_type) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(90);
// PUG_DEBUG:90
 ?><?php if (method_exists($_pug_temp = (isset($newTag) ? $newTag : null) == 'img', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(78);
// PUG_DEBUG:78
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'alt'), function () use (&$attributes) { return !$GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'title'); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(77);
// PUG_DEBUG:77
 ?><?php $attributes = $GLOBALS['__jpv_set_with_ref']($attributes, 'title', '=', '') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(80);
// PUG_DEBUG:80
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'title'), function () use (&$attributes) { return !$GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'alt'); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(79);
// PUG_DEBUG:79
 ?><?php $attributes = $GLOBALS['__jpv_set_with_ref']($attributes, 'alt', '=', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'title')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(82);
// PUG_DEBUG:82
 }  if (!$GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'alt')) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(81);
// PUG_DEBUG:81
 ?><?php $attributes = $GLOBALS['__jpv_set_with_ref']($attributes, 'alt', '=', '') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(84);
// PUG_DEBUG:84
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'alt') === '', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(83);
// PUG_DEBUG:83
 ?><?php $attributes = $GLOBALS['__jpv_set_with_ref']($attributes, 'role', '=', 'presentation') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(89);
// PUG_DEBUG:89
 }  if (!$GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'src')) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(86);
// PUG_DEBUG:86
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'nosrc_substitute') === true, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(85);
// PUG_DEBUG:85
 ?><?php $attributes = $GLOBALS['__jpv_set_with_ref']($attributes, 'src', '=', 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(88);
// PUG_DEBUG:88
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'nosrc_substitute'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(87);
// PUG_DEBUG:87
 ?><?php $attributes = $GLOBALS['__jpv_set_with_ref']($attributes, 'src', '=', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'nosrc_substitute')) ?><?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(93);
// PUG_DEBUG:93
 }  if (method_exists($_pug_temp = (isset($newTag) ? $newTag : null) == 'input', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(92);
// PUG_DEBUG:92
 ?><?php if (!$GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'type')) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(91);
// PUG_DEBUG:91
 ?><?php $attributes = $GLOBALS['__jpv_set_with_ref']($attributes, 'type', '=', "text") ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(96);
// PUG_DEBUG:96
 }  if (method_exists($_pug_temp = (isset($newTag) ? $newTag : null) == 'main', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(95);
// PUG_DEBUG:95
 ?><?php if (!$GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'role')) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(94);
// PUG_DEBUG:94
 ?><?php $attributes = $GLOBALS['__jpv_set_with_ref']($attributes, 'role', '=', 'main') ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(98);
// PUG_DEBUG:98
 }  if (method_exists($_pug_temp = (isset($newTag) ? $newTag : null) == 'html', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(97);
// PUG_DEBUG:97
 ?><!DOCTYPE html>
<?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(99);
// PUG_DEBUG:99
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'bemto_custom_tag';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($pugModule, 'Phug\\Formatter\\Format\\BasicFormat::merge_attributes')(array(  ), (isset($attributes) ? $attributes : null)), [[false, (isset($newTag) ? $newTag : null)], [false, (isset($tagMetadata) ? $tagMetadata : null)]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    ?><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(100);
// PUG_DEBUG:100
 ?><?php if (method_exists($_pug_temp = (isset($block) ? $block : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php } ?><?php
}; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['b'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'options', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(106);
// PUG_DEBUG:106
 ?><?php $settings = (function_exists('get_bemto_settings') ? get_bemto_settings() : $get_bemto_settings()) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(108);
// PUG_DEBUG:108
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_and_with_ref']($options, function () use (&$options) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($options, 'prefix') !== null; }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(107);
// PUG_DEBUG:107
 ?><?php $settings = $GLOBALS['__jpv_set_with_ref']($settings, 'prefix', '=', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($options, 'prefix')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(109);
// PUG_DEBUG:109
 }  $tag = $GLOBALS['__jpv_and_with_ref']($options, function () use (&$options, &$gettype) { return $GLOBALS['__jpv_or']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($options, 'tag'), function () use (&$options, &$gettype) { return ((function_exists('gettype') ? gettype($options) : $gettype($options)) == 'string' ? $options : ''); }); }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(110);
// PUG_DEBUG:110
 ?><?php $isElement = $GLOBALS['__jpv_and_with_ref']($options, function () use (&$options) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($options, 'isElement'); }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(111);
// PUG_DEBUG:111
 ?><?php $tagMetadata = $GLOBALS['__jpv_and_with_ref']($options, function () use (&$options) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($options, 'metadata'); }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(112);
// PUG_DEBUG:112
 ?><?php $block_sets_context = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(210);
// PUG_DEBUG:210
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'class'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(113);
// PUG_DEBUG:113
 ?><?php $bemto_classes = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($attributes, 'class') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(115);
// PUG_DEBUG:115
 ?><?php if (method_exists($_pug_temp = (function_exists('is_array') ? is_array((isset($bemto_classes) ? $bemto_classes : null)) : (isset($is_array) ? $is_array : null)((isset($bemto_classes) ? $bemto_classes : null))), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(114);
// PUG_DEBUG:114
 ?><?php $bemto_classes = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_classes, 'join')(' ') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(116);
// PUG_DEBUG:116
 }  $bemto_classes = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_classes, 'split')(' ') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(117);
// PUG_DEBUG:117
 ?><?php $bemto_objects = array(  ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(118);
// PUG_DEBUG:118
 ?><?php $is_first_object = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(119);
// PUG_DEBUG:119
 ?><?php $new_context = array(  ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(183);
// PUG_DEBUG:183
 ?><?php $__eachScopeVariables = ['klass' => isset($klass) ? $klass : null, 'i' => isset($i) ? $i : null];foreach ($bemto_classes as $i => $klass) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(120);
// PUG_DEBUG:120
 ?><?php $bemto_object = array(  ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(121);
// PUG_DEBUG:121
 ?><?php $prev_object = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 'length') - 1) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(122);
// PUG_DEBUG:122
 ?><?php $sets_context = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(125);
// PUG_DEBUG:125
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($klass, 'match')($GLOBALS['__jpv_regExp']('/^[A-Z-]+[A-Z0-9-]?$/')), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(123);
// PUG_DEBUG:123
 ?><?php $tag = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($klass, 'toLowerCase')() ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(124);
// PUG_DEBUG:124
 ?><?php continue ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(127);
// PUG_DEBUG:127
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_and_with_ref']($is_first_object, function () use (&$isElement) { return (isset($isElement) ? $isElement : null); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(126);
// PUG_DEBUG:126
 ?><?php $bemto_object = $GLOBALS['__jpv_set_with_ref']($bemto_object, 'context', '=', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain, 'length') - 1)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(128);
// PUG_DEBUG:128
 }  $modifier_class = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($klass, 'match')(new RegExp($GLOBALS['__jpv_plus']('^(?!', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'element'), '[A-Za-z0-9])', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'modifier'), "(.+)$"))) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(133);
// PUG_DEBUG:133
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_and_with_ref']($modifier_class, function () use (&$prev_object) { return $GLOBALS['__jpv_and_with_ref']($prev_object, function () use (&$prev_object) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($prev_object, 'name'); }); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(130);
// PUG_DEBUG:130
 ?><?php if (!$GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($prev_object, 'modifiers')) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(129);
// PUG_DEBUG:129
 ?><?php $prev_object = $GLOBALS['__jpv_set_with_ref']($prev_object, 'modifiers', '=', array(  )) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(131);
// PUG_DEBUG:131
 }  $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($prev_object, 'modifiers', 'push')($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($modifier_class, 1)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(132);
// PUG_DEBUG:132
 ?><?php continue ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(134);
// PUG_DEBUG:134
 }  $element_class = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($klass, 'match')(new RegExp($GLOBALS['__jpv_plus']('^(?!', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'modifier'), '[A-Za-z0-9])', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'element'), "(.+)$"))) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(137);
// PUG_DEBUG:137
 ?><?php if (method_exists($_pug_temp = (isset($element_class) ? $element_class : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(135);
// PUG_DEBUG:135
 ?><?php $bemto_object = $GLOBALS['__jpv_set_with_ref']($bemto_object, 'context', '=', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain, 'length') - 1)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(136);
// PUG_DEBUG:136
 ?><?php $klass = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($element_class, 1) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(138);
// PUG_DEBUG:138
 }  $name_with_context = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($klass, 'match')(new RegExp($GLOBALS['__jpv_plus']('^(.*[A-Za-z0-9])(?!', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'modifier'), "$)", $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'element'), "$"))) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(144);
// PUG_DEBUG:144
 ?><?php if (method_exists($_pug_temp = (isset($name_with_context) ? $name_with_context : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(139);
// PUG_DEBUG:139
 ?><?php $klass = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($name_with_context, 1) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(140);
// PUG_DEBUG:140
 ?><?php $bemto_object = $GLOBALS['__jpv_set_with_ref']($bemto_object, 'is_context', '=', true) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(141);
// PUG_DEBUG:141
 ?><?php $sets_context = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(142);
// PUG_DEBUG:142
 ?><?php $block_sets_context = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(143);
// PUG_DEBUG:143
 ?><?php $isElement = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(145);
// PUG_DEBUG:145
 }  $name_with_modifier = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($klass, 'match')(new RegExp($GLOBALS['__jpv_plus']('^(.*?[A-Za-z0-9])(?!', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'element'), '[A-Za-z0-9])', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'modifier'), "(.+)$"))) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(150);
// PUG_DEBUG:150
 ?><?php if (method_exists($_pug_temp = (isset($name_with_modifier) ? $name_with_modifier : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(146);
// PUG_DEBUG:146
 ?><?php $klass = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($name_with_modifier, 1) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(148);
// PUG_DEBUG:148
 ?><?php if (!$GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'modifiers')) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(147);
// PUG_DEBUG:147
 ?><?php $bemto_object = $GLOBALS['__jpv_set_with_ref']($bemto_object, 'modifiers', '=', array(  )) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(149);
// PUG_DEBUG:149
 }  $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'modifiers', 'push')($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($name_with_modifier, 2)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(151);
// PUG_DEBUG:151
 }  $found_prefix = '' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(152);
// PUG_DEBUG:152
 ?><?php $prefix_regex_string = '()?' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(171);
// PUG_DEBUG:171
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'prefix'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(153);
// PUG_DEBUG:153
 ?><?php $prefix = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'prefix') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(155);
// PUG_DEBUG:155
 ?><?php if (method_exists($_pug_temp = (function_exists('gettype') ? gettype((isset($prefix) ? $prefix : null)) : (isset($gettype) ? $gettype : null)((isset($prefix) ? $prefix : null))) === 'string', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(154);
// PUG_DEBUG:154
 ?><?php $prefix = array( '' => $prefix ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(156);
// PUG_DEBUG:156
 }  $prefix_regex_test = array(  ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(163);
// PUG_DEBUG:163
 ?><?php if (method_exists($_pug_temp = (function_exists('is_object') ? is_object((isset($prefix) ? $prefix : null)) : (isset($is_object) ? $is_object : null)((isset($prefix) ? $prefix : null))), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(161);
// PUG_DEBUG:161
 ?><?php $__eachScopeVariables = ['value' => isset($value) ? $value : null, 'key' => isset($key) ? $key : null];foreach ($prefix as $key => $value) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(158);
// PUG_DEBUG:158
 ?><?php if (method_exists($_pug_temp = (function_exists('gettype') ? gettype((isset($key) ? $key : null)) : (isset($gettype) ? $gettype : null)((isset($key) ? $key : null))) === $GLOBALS['__jpv_and']('string', function () use (&$key, &$prefix_regex_test) { return (isset($key) ? $key : null) != $GLOBALS['__jpv_and']('', function () use (&$prefix_regex_test, &$key) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($prefix_regex_test, 'indexOf')((isset($key) ? $key : null)) == -1; }); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(157);
// PUG_DEBUG:157
 ?><?php $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($prefix_regex_test, 'push')($key) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(160);
// PUG_DEBUG:160
 }  if (method_exists($_pug_temp = (function_exists('gettype') ? gettype((isset($value) ? $value : null)) : (isset($gettype) ? $gettype : null)((isset($value) ? $value : null))) === $GLOBALS['__jpv_and']('string', function () use (&$value, &$prefix_regex_test) { return (isset($value) ? $value : null) != $GLOBALS['__jpv_and']('', function () use (&$prefix_regex_test, &$value) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($prefix_regex_test, 'indexOf')((isset($value) ? $value : null)) == -1; }); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(159);
// PUG_DEBUG:159
 ?><?php $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($prefix_regex_test, 'push')($value) ?><?php } ?><?php }extract($__eachScopeVariables); 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(162);
// PUG_DEBUG:162
 ?><?php $prefix_regex_string = $GLOBALS['__jpv_plus']('(', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($prefix_regex_test, 'join')('|'), ')?') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(164);
// PUG_DEBUG:164
 }  $name_with_prefix = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($klass, 'match')(new RegExp($GLOBALS['__jpv_plus']('^', $prefix_regex_string, "([A-Za-z0-9]+.*)$"))) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(170);
// PUG_DEBUG:170
 ?><?php if (method_exists($_pug_temp = (isset($name_with_prefix) ? $name_with_prefix : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(165);
// PUG_DEBUG:165
 ?><?php $klass = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($name_with_prefix, 2) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(166);
// PUG_DEBUG:166
 ?><?php $found_prefix = $GLOBALS['__jpv_or']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($name_with_prefix, 1), function () { return ''; }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(167);
// PUG_DEBUG:167
 ?><?php $found_prefix = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($prefix, $found_prefix) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(169);
// PUG_DEBUG:169
 ?><?php if (method_exists($_pug_temp = (isset($found_prefix) ? $found_prefix : null) === $GLOBALS['__jpv_or'](null, function () use (&$found_prefix) { return (isset($found_prefix) ? $found_prefix : null) === true; }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(168);
// PUG_DEBUG:168
 ?><?php $found_prefix = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($name_with_prefix, 1) ?><?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(172);
// PUG_DEBUG:172
 }  $bemto_object = $GLOBALS['__jpv_set_with_ref']($bemto_object, 'prefix', '=', ($GLOBALS['__jpv_or_with_ref']($found_prefix, function () { return ''; })));
$GLOBALS['__jpv_dotWithArrayPrototype']((function_exists('replace') ? replace($GLOBALS['__jpv_regExp']('/\-/g'), '%DASH%') : $replace($GLOBALS['__jpv_regExp']('/\-/g'), '%DASH%')), 'replace')($GLOBALS['__jpv_regExp']('/\_/g'), '%UNDERSCORE%') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(174);
// PUG_DEBUG:174
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_and_with_ref']($sets_context, function () use (&$klass) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($klass, 'match')($GLOBALS['__jpv_regExp']('/^[a-zA-Z0-9]+.*/')); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(173);
// PUG_DEBUG:173
 ?><?php $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($new_context, 'push')($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'context') ? ($GLOBALS['__jpv_plus']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'context'), $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'element'), $klass)) : ($GLOBALS['__jpv_plus']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'prefix'), $klass))) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(175);
// PUG_DEBUG:175
 }  $bemto_object = $GLOBALS['__jpv_set_with_ref']($bemto_object, 'name', '=', $klass) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(176);
// PUG_DEBUG:176
 ?><?php $is_first_object = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(181);
// PUG_DEBUG:181
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'context'), function () use (&$bemto_object) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'context', 'length') > 1; }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(180);
// PUG_DEBUG:180
 ?><?php $__eachScopeVariables = ['subcontext' => isset($subcontext) ? $subcontext : null, 'i' => isset($i) ? $i : null];foreach ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'context') as $i => $subcontext) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(177);
// PUG_DEBUG:177
 ?><?php $sub_object = clone ($bemto_object) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(178);
// PUG_DEBUG:178
 ?><?php $sub_object = $GLOBALS['__jpv_set_with_ref']($sub_object, 'context', '=', array( $subcontext )) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(179);
// PUG_DEBUG:179
 ?><?php $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 'push')($sub_object) ?><?php }extract($__eachScopeVariables); ?><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(182);
// PUG_DEBUG:182
 ?><?php $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 'push')($bemto_object) ?><?php } ?><?php }extract($__eachScopeVariables); 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(187);
// PUG_DEBUG:187
 ?><?php if (!$GLOBALS['__jpv_and_with_ref']($isElement, function () use (&$new_context, &$bemto_objects) { return !$GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($new_context, 'length'), function () use (&$bemto_objects) { return $GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 0), function () use (&$bemto_objects) { return $GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 0, 'name'), function () use (&$bemto_objects) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 0, 'name', 'match')($GLOBALS['__jpv_regExp']('/^[a-zA-Z0-9]+.*/')); }); }); }); })) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(184);
// PUG_DEBUG:184
 ?><?php $bemto_objects = $GLOBALS['__jpv_set_with_ref']($bemto_objects, 0, '=', $GLOBALS['__jpv_set']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 0), 'is_context', '=', true)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(185);
// PUG_DEBUG:185
 ?><?php $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($new_context, 'push')($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 0, 'context') ? ($GLOBALS['__jpv_plus']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 0, 'context'), $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'element'), $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 0, 'name'))) : ($GLOBALS['__jpv_plus']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 0, 'prefix'), $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 0, 'name')))) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(186);
// PUG_DEBUG:186
 ?><?php $block_sets_context = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(194);
// PUG_DEBUG:194
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($new_context, 'length'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(192);
// PUG_DEBUG:192
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'flat_elements'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(191);
// PUG_DEBUG:191
 ?><?php $__eachScopeVariables = ['subcontext' => isset($subcontext) ? $subcontext : null, 'i' => isset($i) ? $i : null];foreach ($new_context as $i => $subcontext) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(188);
// PUG_DEBUG:188
 ?><?php $context_with_element = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($subcontext, 'match')(new RegExp($GLOBALS['__jpv_plus']('^(.*?[A-Za-z0-9])(?!', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'modifier'), '[A-Za-z0-9])', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'element'), ".+$"))) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(190);
// PUG_DEBUG:190
 ?><?php if (method_exists($_pug_temp = (isset($context_with_element) ? $context_with_element : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(189);
// PUG_DEBUG:189
 ?><?php $new_context = $GLOBALS['__jpv_set_with_ref']($new_context, $i, '=', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($context_with_element, 1)) ?><?php } ?><?php }extract($__eachScopeVariables); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(193);
// PUG_DEBUG:193
 }  $bemto_chain = $GLOBALS['__jpv_set_with_ref']($bemto_chain, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain, 'length'), '=', $new_context) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(208);
// PUG_DEBUG:208
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_objects, 'length'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(195);
// PUG_DEBUG:195
 ?><?php $new_classes = array(  ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(204);
// PUG_DEBUG:204
 ?><?php $__eachScopeVariables = ['bemto_object' => isset($bemto_object) ? $bemto_object : null];foreach ($bemto_objects as $bemto_object) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(203);
// PUG_DEBUG:203
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'name'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(196);
// PUG_DEBUG:196
 ?><?php $start = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'prefix') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(198);
// PUG_DEBUG:198
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'context'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(197);
// PUG_DEBUG:197
 ?><?php $start = $GLOBALS['__jpv_plus']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'context'), $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'output_element')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(199);
// PUG_DEBUG:199
 }  $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($new_classes, 'push')($GLOBALS['__jpv_plus_with_ref']($start, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'name'))) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(202);
// PUG_DEBUG:202
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'modifiers'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(201);
// PUG_DEBUG:201
 ?><?php $__eachScopeVariables = ['modifier' => isset($modifier) ? $modifier : null];foreach ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'modifiers') as $modifier) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(200);
// PUG_DEBUG:200
 ?><?php $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($new_classes, 'push')($GLOBALS['__jpv_plus_with_ref']($start, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_object, 'name'), $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'output_modifier'), $modifier)) ?><?php }extract($__eachScopeVariables); ?><?php } ?><?php } ?><?php }extract($__eachScopeVariables); 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(205);
// PUG_DEBUG:205
 ?><?php $delimiter = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'class_delimiter') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(206);
// PUG_DEBUG:206
 ?><?php $delimiter = $delimiter ? ($GLOBALS['__jpv_plus'](' ', $delimiter, ' ')) : ' ' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(207);
// PUG_DEBUG:207
 ?><?php $attributes = $GLOBALS['__jpv_set_with_ref']($attributes, 'class', '=', $GLOBALS['__jpv_dotWithArrayPrototype']($GLOBALS['__jpv_dotWithArrayPrototype']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($new_classes, 'join')($delimiter), 'replace')($GLOBALS['__jpv_regExp']('/%DASH%/g'), '-'), 'replace')($GLOBALS['__jpv_regExp']('/%UNDERSCORE%/g'), '_')) ?><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(209);
// PUG_DEBUG:209
 ?><?php $attributes = $GLOBALS['__jpv_set_with_ref']($attributes, 'class', '=', null) ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(212);
// PUG_DEBUG:212
 }  if (method_exists($_pug_temp = (isset($block) ? $block : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(211);
// PUG_DEBUG:211
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'bemto_tag';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($pugModule, 'Phug\Formatter\Format\BasicFormat::merge_attributes')(array(  ), (isset($attributes) ? $attributes : null)), [[false, (isset($tag) ? $tag : null)], [false, (isset($tagMetadata) ? $tagMetadata : null)]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    ?><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php
}); ?><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(213);
// PUG_DEBUG:213
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'bemto_tag';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($pugModule, 'Phug\\Formatter\\Format\\BasicFormat::merge_attributes')(array(  ), (isset($attributes) ? $attributes : null)), [[false, (isset($tag) ? $tag : null)], [false, (isset($tagMetadata) ? $tagMetadata : null)]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    ?><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(215);
// PUG_DEBUG:215
 }  if (!$GLOBALS['__jpv_and_with_ref']($isElement, function () use (&$block_sets_context) { return (isset($block_sets_context) ? $block_sets_context : null); })) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(214);
// PUG_DEBUG:214
 ?><?php $bemto_chain = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain, 'splice')(0, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain, 'length') - 1) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(216);
// PUG_DEBUG:216
 }  $bemto_chain_contexts = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, 'splice')(0, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, 'length') - 1) ?><?php
}; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['e'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'options', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(218);
// PUG_DEBUG:218
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_and_with_ref']($options, function () use (&$options, &$gettype) { return (function_exists('gettype') ? gettype((isset($options) ? $options : null)) : (isset($gettype) ? $gettype : null)((isset($options) ? $options : null))) == 'string'; }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(217);
// PUG_DEBUG:217
 ?><?php $options = array( 'tag' => $options ) ?><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(219);
// PUG_DEBUG:219
 ?><?php $options = $GLOBALS['__jpv_or_with_ref']($options, function () { return array(  ); }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(220);
// PUG_DEBUG:220
 }  $options = $GLOBALS['__jpv_set_with_ref']($options, 'isElement', '=', true) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(222);
// PUG_DEBUG:222
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($pugModule, 'Phug\\Formatter\\Format\\BasicFormat::merge_attributes')(array(  ), (isset($attributes) ? $attributes : null)), [[false, (isset($options) ? $options : null)]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    ?><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(221);
// PUG_DEBUG:221
 ?><?php
}); ?><?php
}; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['icon'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'name', null], [false, 'view', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(226);
// PUG_DEBUG:226
 ?><?php $mod = $GLOBALS['__jpv_or_with_ref']($mod, function () { return ''; }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(227);
// PUG_DEBUG:227
 ?><?php $view = $GLOBALS['__jpv_or_with_ref']($view, function () { return ''; }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(230);
// PUG_DEBUG:230
 ?><svg<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('class', $GLOBALS['__jpv_plus']("icon icon-", (isset($name) ? $name : null)))], ['viewBox' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('viewBox', (isset($view) ? $view : null))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(229);
// PUG_DEBUG:229
 ?><use<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['xlink:href' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('xlink:href', $GLOBALS['__jpv_plus']("/app/icons/sprite.svg#", (isset($name) ? $name : null)))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(228);
// PUG_DEBUG:228
 ?></use></svg><?php
}; ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(469);
// PUG_DEBUG:469
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(232);
// PUG_DEBUG:232
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(225);
// PUG_DEBUG:225
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(224);
// PUG_DEBUG:224
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(2);
// PUG_DEBUG:2
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(0);
// PUG_DEBUG:0
 ?><?php // Cloning via http://stackoverflow.com/a/728694/885556
function clone(obj) {
    var copy;

    // Handle the 3 simple types, and null or undefined
    if (null == obj || "object" != typeof obj) return obj;

    // Handle Date
    if (obj instanceof Date) {
        copy = new Date();
        copy.setTime(obj.getTime());
        return copy;
    }

    // Handle Array
    if (obj instanceof Array) {
        copy = [];
        for (var i = 0, len = obj.length; i < len; i++) {
            copy[i] = clone(obj[i]);
        }
        return copy;
    }

    // Handle Object
    if (obj instanceof Object) {
        copy = {};
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = clone(obj[attr]);
        }
        return copy;
    }

    throw new Error("Unable to copy obj! Its type isn't supported.");
}
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1);
// PUG_DEBUG:1
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(10);
// PUG_DEBUG:10
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(5);
// PUG_DEBUG:5
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(3);
// PUG_DEBUG:3
 ?><?php $get_bemto_tag_type = function ($tagName) use (&$result, &$bemto_tag_metadata) {
  $result = 'block';
  if ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_tag_metadata, $tagName)) {
    $result = $GLOBALS['__jpv_or']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_tag_metadata, $tagName, 'type'), function () use (&$result) { return $result; });
  }
  return $result;
};
$get_bemto_tag_content_type = function ($tagName) use (&$result, &$bemto_tag_metadata) {
  $result = 'block';
  if ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_tag_metadata, $tagName)) {
    $result = $GLOBALS['__jpv_or']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_tag_metadata, $tagName, 'content_type'), function () use (&$bemto_tag_metadata, &$tagName, &$result) { return $GLOBALS['__jpv_or']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_tag_metadata, $tagName, 'type'), function () use (&$result) { return $result; }); });
  }
  return $result;
};
$bemto_tag_metadata = array( 'hr' => array( 'type' => 'self_closing' ), 'br' => array( 'type' => 'self_closing' ), 'wbr' => array( 'type' => 'self_closing' ), 'source' => array( 'type' => 'self_closing' ), 'img' => array( 'type' => 'self_closing' ), 'input' => array( 'type' => 'self_closing' ), 'a' => array( 'type' => 'inline' ), 'abbr' => array( 'type' => 'inline' ), 'acronym' => array( 'type' => 'inline' ), 'b' => array( 'type' => 'inline' ), 'code' => array( 'type' => 'inline' ), 'em' => array( 'type' => 'inline' ), 'font' => array( 'type' => 'inline' ), 'i' => array( 'type' => 'inline' ), 'ins' => array( 'type' => 'inline' ), 'kbd' => array( 'type' => 'inline' ), 'map' => array( 'type' => 'inline' ), 'pre' => array( 'type' => 'inline' ), 'samp' => array( 'type' => 'inline' ), 'small' => array( 'type' => 'inline' ), 'span' => array( 'type' => 'inline' ), 'strong' => array( 'type' => 'inline' ), 'sub' => array( 'type' => 'inline' ), 'sup' => array( 'type' => 'inline' ), 'textarea' => array( 'type' => 'inline' ), 'time' => array( 'type' => 'inline' ), 'label' => array( 'content_type' => 'inline' ), 'p' => array( 'content_type' => 'inline' ), 'h1' => array( 'content_type' => 'inline' ), 'h2' => array( 'content_type' => 'inline' ), 'h3' => array( 'content_type' => 'inline' ), 'h4' => array( 'content_type' => 'inline' ), 'h5' => array( 'content_type' => 'inline' ), 'h6' => array( 'content_type' => 'inline' ), 'ul' => array( 'content_type' => 'list' ), 'ol' => array( 'content_type' => 'list' ), 'select' => array( 'content_type' => 'optionlist' ), 'datalist' => array( 'content_type' => 'optionlist' ) ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(4);
// PUG_DEBUG:4
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(6);
// PUG_DEBUG:6
 ?><?php $default_bemto_settings = array( 'prefix' => '', 'element' => '__', 'modifier' => '_', 'default_tag' => 'div', 'nosrc_substitute' => true, 'flat_elements' => true, 'class_delimiter' => '' );
$bemto_output_settings = array( 'element', 'modifier' );
$bemto_settings = $default_bemto_settings;
$get_bemto_settings = function () use (&$settings, &$bemto_settings, &$bemto_settings_prefix, &$bemto_settings_element, &$bemto_settings_modifier, &$bemto_settings_default_tag, &$i, &$bemto_output_settings, &$setting) {
  $settings = clone ($bemto_settings);
  if ($bemto_settings_prefix !== null) {
    $settings = $GLOBALS['__jpv_set_with_ref']($settings, 'prefix', '=', $bemto_settings_prefix);
  }
  if ($bemto_settings_element !== null) {
    $settings = $GLOBALS['__jpv_set_with_ref']($settings, 'element', '=', $bemto_settings_element);
  }
  if ($bemto_settings_modifier !== null) {
    $settings = $GLOBALS['__jpv_set_with_ref']($settings, 'modifier', '=', $bemto_settings_modifier);
  }
  if ($bemto_settings_default_tag !== null) {
    $settings = $GLOBALS['__jpv_set_with_ref']($settings, 'default_tag', '=', $bemto_settings_default_tag);
  }
  for ($i = 0; $i < $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_output_settings, 'length'); $i++) {
    $setting = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_output_settings, $i);
    if ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, $GLOBALS['__jpv_plus']('output_', $setting)) === null) {
      $settings = $GLOBALS['__jpv_set_with_ref']($settings, $GLOBALS['__jpv_plus']('output_', $setting), '=', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, $setting));
    }
  }
  return $settings;
};
$set_bemto_setting = function ($name, $value) use (&$bemto_settings, &$bemto_settings_prefix, &$bemto_settings_element, &$bemto_settings_modifier, &$bemto_settings_default_tag) {
  $bemto_settings = $GLOBALS['__jpv_set_with_ref']($bemto_settings, $name, '=', $value);
  if ($name == $GLOBALS['__jpv_and']('prefix', function () use (&$bemto_settings_prefix) { return $bemto_settings_prefix !== null; })) {
    $bemto_settings_prefix = null;
  }
  if ($name == $GLOBALS['__jpv_and']('element', function () use (&$bemto_settings_element) { return $bemto_settings_element !== null; })) {
    $bemto_settings_element = null;
  }
  if ($name == $GLOBALS['__jpv_and']('modifier', function () use (&$bemto_settings_modifier) { return $bemto_settings_modifier !== null; })) {
    $bemto_settings_modifier = null;
  }
  if ($name == $GLOBALS['__jpv_and']('default_tag', function () use (&$bemto_settings_default_tag) { return $bemto_settings_default_tag !== null; })) {
    $bemto_settings_default_tag = null;
  }
};
$set_bemto_settings = function ($settings) use (&$name, &$set_bemto_setting) {
  foreach ($settings as $name => $__current_value) {
    if ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, 'hasOwnProperty')($name)) {
      (function_exists('set_bemto_setting') ? set_bemto_setting($name, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, $name)) : $set_bemto_setting($name, $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($settings, $name)));
    }
  }
} ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(103);
// PUG_DEBUG:103
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(46);
// PUG_DEBUG:46
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(102);
// PUG_DEBUG:102
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($bemto_chain_contexts, (isset($contextIndex) ? $contextIndex : null) - 1) === $GLOBALS['__jpv_and']('list', function () use (&$newTag) { return (isset($newTag) ? $newTag : null) != 'li'; }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(101);
// PUG_DEBUG:101
 ?></li><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(223);
// PUG_DEBUG:223
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(104);
// PUG_DEBUG:104
 ?><?php $bemto_chain = array(  ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(105);
// PUG_DEBUG:105
 ?><?php $bemto_chain_contexts = array( 'block' ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(231);
// PUG_DEBUG:231
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(233);
// PUG_DEBUG:233
 ?><?php $title = $GLOBALS['__jpv_or_with_ref']($title, function () { return ''; });
$description = $GLOBALS['__jpv_or_with_ref']($description, function () { return ''; });
$image = $GLOBALS['__jpv_or_with_ref']($image, function () { return ''; }) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(234);
// PUG_DEBUG:234
 ?><?php $html = array( 'attrs' => array( 'lang' => 'ru' ), 'classList' => array(  ) ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(235);
// PUG_DEBUG:235
 ?><?php $body = array( 'attrs' => array(  ), 'classList' => array(  ) ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(236);
// PUG_DEBUG:236
 ?><?php $meta = array( 'charset' => 'utf-8', 'description' => $description, 'keywords' => array(  ), 'ogTitle' => $title, 'viewport' => 'width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1', 'XUACompatible' => 'IE=edge' ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(237);
// PUG_DEBUG:237
 ?><?php $link = array( 'icon' => '', 'icon16x16' => '', 'icon32x32' => '' ) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(238);
// PUG_DEBUG:238
 ?><?php $title = 'История показаний' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(239);
// PUG_DEBUG:239
 ?><?php $type = 'lk' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(240);
// PUG_DEBUG:240
 ?><!DOCTYPE html><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(468);
// PUG_DEBUG:468
 ?><html<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(241);
// PUG_DEBUG:241
 ?><?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($html, 'attrs'), ['class' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('class', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($html, 'classList'))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(259);
// PUG_DEBUG:259
 ?><head><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(243);
// PUG_DEBUG:243
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($meta, 'charset'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(242);
// PUG_DEBUG:242
 ?><meta<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['charset' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('charset', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($meta, 'charset'))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(245);
// PUG_DEBUG:245
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($meta, 'XUACompatible'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(244);
// PUG_DEBUG:244
 ?><meta<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['http-equiv' => 'X-UA-Compatible'], ['content' => $pugModule['Phug\Formatter\Format\HtmlFormat::array_escape']('content', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($meta, 'XUACompatible'))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(247);
// PUG_DEBUG:247
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($meta, 'viewport'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(246);
// PUG_DEBUG:246
 ?><meta<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['name' => 'viewport'], ['content' => $pugModule['Phug\Formatter\Format\HtmlFormat::array_escape']('content', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($meta, 'viewport'))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(249);
// PUG_DEBUG:249
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($meta, 'description'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(248);
// PUG_DEBUG:248
 ?><meta<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['name' => 'description'], ['content' => $pugModule['Phug\Formatter\Format\HtmlFormat::array_escape']('content', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($meta, 'description'))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(251);
// PUG_DEBUG:251
 ?><title><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(250);
// PUG_DEBUG:250
 ?><?= htmlspecialchars((is_bool($_pug_temp = (isset($title) ? $title : null)) ? var_export($_pug_temp, true) : $_pug_temp)) ?></title><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(253);
// PUG_DEBUG:253
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($link, 'icon'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(252);
// PUG_DEBUG:252
 ?><link<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['rel' => 'icon'], ['href' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('href', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($link, 'icon'))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(255);
// PUG_DEBUG:255
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($link, 'icon16x16'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(254);
// PUG_DEBUG:254
 ?><link<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['rel' => 'icon'], ['href' => $pugModule['Phug\Formatter\Format\HtmlFormat::array_escape']('href', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($link, 'icon16x16'))], ['sizes' => '16x16'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(256);
// PUG_DEBUG:256
 ?><link<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['rel' => 'stylesheet'], ['href' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('href', $GLOBALS['__jpv_plus']("/css/app.css?v=", $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($Date, 'now')()))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(258);
// PUG_DEBUG:258
 ?><script><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(257);
// PUG_DEBUG:257
 ?>function canUseWebP(){var e=document.createElement("canvas");return!(!e.getContext||!e.getContext("2d"))&&0==e.toDataURL("image/webp").indexOf("data:image/webp")}var root=document.getElementsByTagName("html")[0];canUseWebP()?root.classList.add("ws"):root.classList.add("wn");</script></head><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(467);
// PUG_DEBUG:467
 ?><body<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(260);
// PUG_DEBUG:260
 ?><?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($body, 'attrs'), ['class' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('class', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($body, 'classList'))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(460);
// PUG_DEBUG:460
 ?><main><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(285);
// PUG_DEBUG:285
 ?><?php if (method_exists($_pug_temp = (isset($type) ? $type : null) == 'lk', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(284);
// PUG_DEBUG:284
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(283);
// PUG_DEBUG:283
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'HEADER'], ['class' => 'header']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(270);
// PUG_DEBUG:270
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'wrap'], ['class' => 'wrap']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(269);
// PUG_DEBUG:269
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'top'], ['class' => 'flex'], ['class' => '_vertical'], ['class' => '_justify']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(262);
// PUG_DEBUG:262
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'logo'], ['class' => 'logo'], ['href' => '/']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(261);
// PUG_DEBUG:261
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/app/img/logo.svg'], ['alt' => 'Логотип'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(268);
// PUG_DEBUG:268
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'right'], ['class' => 'flex'], ['class' => '_vertical']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(265);
// PUG_DEBUG:265
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'connection'], ['class' => 'flex'], ['class' => '_column']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(264);
// PUG_DEBUG:264
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'name'], ['class' => 'h3']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(263);
// PUG_DEBUG:263
 ?>Иван Петров<?php
});;
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(267);
// PUG_DEBUG:267
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'log-out'], ['class' => 'h3'], ['class' => 'btn'], ['class' => '_main'], ['class' => 'flex'], ['class' => '_vertical']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(266);
// PUG_DEBUG:266
 ?>Выйти<?php
});;
}); ?><?php
}); ?><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(282);
// PUG_DEBUG:282
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'bottom'], ['class' => 'flex'], ['class' => '_vertical'], ['class' => '_justify']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(281);
// PUG_DEBUG:281
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'UL'], ['class' => 'ul'], ['class' => 'flex']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(273);
// PUG_DEBUG:273
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'LI'], ['class' => 'li']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(272);
// PUG_DEBUG:272
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'li-link'], ['href' => '/transmit-data']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(271);
// PUG_DEBUG:271
 ?>ПЕРЕДАТЬ ПОКАЗАНИЯ<?php
});;
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(276);
// PUG_DEBUG:276
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'LI'], ['class' => 'li']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(275);
// PUG_DEBUG:275
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'li-link'], ['href' => '/history-data']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(274);
// PUG_DEBUG:274
 ?>ИСТОРИЯ ПОКАЗАНИЙ<?php
});;
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(280);
// PUG_DEBUG:280
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'LI'], ['class' => 'li']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(279);
// PUG_DEBUG:279
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'li-link'], ['href' => '/settings']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(277);
// PUG_DEBUG:277
 ?>НАСТРОЙКИ<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(278);
// PUG_DEBUG:278
;
});;
});;
});;
}); ?><?php
}); ?><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(394);
// PUG_DEBUG:394
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(309);
// PUG_DEBUG:309
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'HEADER'], ['class' => 'header']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(297);
// PUG_DEBUG:297
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'wrap'], ['class' => 'wrap']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(296);
// PUG_DEBUG:296
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'top'], ['class' => 'flex'], ['class' => '_vertical'], ['class' => '_justify']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(287);
// PUG_DEBUG:287
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'logo'], ['class' => 'logo'], ['href' => '/']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(286);
// PUG_DEBUG:286
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/app/img/logo.svg'], ['alt' => 'Логотип'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(295);
// PUG_DEBUG:295
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'right'], ['class' => 'flex'], ['class' => '_vertical']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(292);
// PUG_DEBUG:292
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'connection'], ['class' => 'flex'], ['class' => '_column']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(289);
// PUG_DEBUG:289
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'phone'], ['class' => 'h3'], ['href' => 'tel:8 (996) 925-98-97']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(288);
// PUG_DEBUG:288
 ?>8 (996) 925-98-97<?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(291);
// PUG_DEBUG:291
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'callback'], ['class' => 'h5']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(290);
// PUG_DEBUG:290
 ?>Заказать обратный звонок<?php
});;
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(294);
// PUG_DEBUG:294
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'log-in'], ['class' => 'h3'], ['class' => 'btn'], ['class' => '_main'], ['class' => 'flex'], ['class' => '_vertical']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(293);
// PUG_DEBUG:293
 ?>Войти<?php
});;
}); ?><?php
}); ?><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(308);
// PUG_DEBUG:308
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'bottom'], ['class' => 'flex'], ['class' => '_vertical'], ['class' => '_justify']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(307);
// PUG_DEBUG:307
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'UL'], ['class' => 'ul'], ['class' => 'flex']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(300);
// PUG_DEBUG:300
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'LI'], ['class' => 'li']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(299);
// PUG_DEBUG:299
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'li-link'], ['href' => '#']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(298);
// PUG_DEBUG:298
 ?>Сведения об организации<?php
});;
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(303);
// PUG_DEBUG:303
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'LI'], ['class' => 'li']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(302);
// PUG_DEBUG:302
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'li-link'], ['href' => '#']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(301);
// PUG_DEBUG:301
 ?>Типы датчиков<?php
});;
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(306);
// PUG_DEBUG:306
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'LI'], ['class' => 'li']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(305);
// PUG_DEBUG:305
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'li-link'], ['href' => '#']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(304);
// PUG_DEBUG:304
 ?>Контакты<?php
});;
});;
});;
}); ?><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(393);
// PUG_DEBUG:393
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(357);
// PUG_DEBUG:357
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modal-log-in'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(356);
// PUG_DEBUG:356
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(317);
// PUG_DEBUG:317
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(314);
// PUG_DEBUG:314
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-panel'], ['class' => 'tab-panel--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(311);
// PUG_DEBUG:311
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-panel_title'], ['data-tab-panel' => 'log-in'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(310);
// PUG_DEBUG:310
 ?>Вход</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(313);
// PUG_DEBUG:313
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-panel_title'], ['class' => 'tab-panel_title-last'], ['data-tab-panel' => 'register'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(312);
// PUG_DEBUG:312
 ?>Регистрация</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(316);
// PUG_DEBUG:316
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(315);
// PUG_DEBUG:315
 ?>&#215;</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(355);
// PUG_DEBUG:355
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(354);
// PUG_DEBUG:354
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-all-content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(331);
// PUG_DEBUG:331
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-content'], ['class' => 'tab-content--log-in'], ['data-tab-content' => 'log-in'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(330);
// PUG_DEBUG:330
 ?><form<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form'], ['class' => 'log-in-form'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(325);
// PUG_DEBUG:325
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(320);
// PUG_DEBUG:320
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--col'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(318);
// PUG_DEBUG:318
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'tel'], ['name' => 'phone'], ['placeholder' => 'Введите телефон'], ['class' => 'input-tel'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(319);
// PUG_DEBUG:319
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'password'], ['name' => 'password'], ['placeholder' => 'Введите пароль'], ['class' => 'input-pass'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(324);
// PUG_DEBUG:324
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(321);
// PUG_DEBUG:321
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(323);
// PUG_DEBUG:323
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '#'], ['class' => 'remind-password'], ['class' => 'link--main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(322);
// PUG_DEBUG:322
 ?>Забыли пароль?</a></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(329);
// PUG_DEBUG:329
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(328);
// PUG_DEBUG:328
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(327);
// PUG_DEBUG:327
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn_main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(326);
// PUG_DEBUG:326
 ?>Войти</button></div></div></form></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(353);
// PUG_DEBUG:353
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-content'], ['class' => 'tab-content--register'], ['data-tab-content' => 'register'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(352);
// PUG_DEBUG:352
 ?><form<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form'], ['class' => 'register-form'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(336);
// PUG_DEBUG:336
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(335);
// PUG_DEBUG:335
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--col'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(332);
// PUG_DEBUG:332
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'tel'], ['name' => 'phone'], ['placeholder' => 'Введите телефон'], ['class' => 'input-tel'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(333);
// PUG_DEBUG:333
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'password'], ['name' => 'password'], ['placeholder' => 'Введите пароль'], ['class' => 'input-pass'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(334);
// PUG_DEBUG:334
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'password'], ['name' => 'password2'], ['placeholder' => 'Повторите пароль'], ['class' => 'input-repeat-pass'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(347);
// PUG_DEBUG:347
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(346);
// PUG_DEBUG:346
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'checkbox-container'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(345);
// PUG_DEBUG:345
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(337);
// PUG_DEBUG:337
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'agree'], ['name' => 'agreement'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(344);
// PUG_DEBUG:344
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'agree'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(338);
// PUG_DEBUG:338
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(339);
// PUG_DEBUG:339
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(343);
// PUG_DEBUG:343
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(340);
// PUG_DEBUG:340
 ?>Я согласен с <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(342);
// PUG_DEBUG:342
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/terms-of-service'], ['class' => 'link--main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(341);
// PUG_DEBUG:341
 ?>пользовательским соглашением</a></div></label></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(351);
// PUG_DEBUG:351
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(350);
// PUG_DEBUG:350
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(349);
// PUG_DEBUG:349
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn_main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(348);
// PUG_DEBUG:348
 ?>Далее</button></div></div></form></div></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(392);
// PUG_DEBUG:392
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(391);
// PUG_DEBUG:391
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modal-password-recovery'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(390);
// PUG_DEBUG:390
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(363);
// PUG_DEBUG:363
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(360);
// PUG_DEBUG:360
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-panel'], ['class' => 'tab-panel--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(359);
// PUG_DEBUG:359
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-panel_title'], ['data-tab-panel' => 'recovery'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(358);
// PUG_DEBUG:358
 ?>Восстановление пароля</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(362);
// PUG_DEBUG:362
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(361);
// PUG_DEBUG:361
 ?>&#215;</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(389);
// PUG_DEBUG:389
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(388);
// PUG_DEBUG:388
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-all-content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(387);
// PUG_DEBUG:387
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-content'], ['class' => 'tab-content--recovery'], ['data-tab-content' => 'recovery'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(386);
// PUG_DEBUG:386
 ?><form<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form'], ['class' => 'password-recovery-form'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(370);
// PUG_DEBUG:370
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'], ['class' => 'password-recovery_step1'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(365);
// PUG_DEBUG:365
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(364);
// PUG_DEBUG:364
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'tel'], ['name' => 'phone'], ['placeholder' => 'Введите телефон'], ['class' => 'input-tel'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(367);
// PUG_DEBUG:367
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(366);
// PUG_DEBUG:366
 ?>На ваш телефон будет отправлен код</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(369);
// PUG_DEBUG:369
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn_main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(368);
// PUG_DEBUG:368
 ?>Отправить код</button></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(377);
// PUG_DEBUG:377
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'], ['class' => 'password-recovery_step2'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(372);
// PUG_DEBUG:372
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(371);
// PUG_DEBUG:371
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'text'], ['name' => 'code'], ['placeholder' => 'Введите код'], ['class' => 'input-code'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(374);
// PUG_DEBUG:374
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(373);
// PUG_DEBUG:373
 ?>На ваш телефон отправлен код</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(376);
// PUG_DEBUG:376
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn_main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(375);
// PUG_DEBUG:375
 ?>Проверить код</button></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(383);
// PUG_DEBUG:383
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'], ['class' => 'password-recovery_step3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(380);
// PUG_DEBUG:380
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--col'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(378);
// PUG_DEBUG:378
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'password'], ['name' => 'password'], ['placeholder' => 'Введите новый пароль'], ['class' => 'input-pass'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(379);
// PUG_DEBUG:379
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'password'], ['name' => 'password2'], ['placeholder' => 'Повторите пароль'], ['class' => 'input-pass'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(382);
// PUG_DEBUG:382
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn_main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(381);
// PUG_DEBUG:381
 ?>Изменить</button></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(384);
// PUG_DEBUG:384
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'hidden'], ['name' => 'step'], ['value' => '1'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(385);
// PUG_DEBUG:385
 ?></form></div></div></div></div></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(421);
// PUG_DEBUG:421
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'history-data'], ['class' => 'section']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(420);
// PUG_DEBUG:420
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'wrap'], ['class' => 'wrap']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(396);
// PUG_DEBUG:396
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'title'], ['class' => 'text-l']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(395);
// PUG_DEBUG:395
 ?>История показаний<?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(419);
// PUG_DEBUG:419
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'content']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(418);
// PUG_DEBUG:418
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'TABLE'], ['class' => 'table'], ['class' => 'text-m']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(403);
// PUG_DEBUG:403
 ?><tr><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(398);
// PUG_DEBUG:398
 ?><th><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(397);
// PUG_DEBUG:397
 ?>Дата</th><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(400);
// PUG_DEBUG:400
 ?><th><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(399);
// PUG_DEBUG:399
 ?>Показания</th><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(402);
// PUG_DEBUG:402
 ?><th><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(401);
// PUG_DEBUG:401
 ?>Расход</th></tr><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(410);
// PUG_DEBUG:410
 ?><tr><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(405);
// PUG_DEBUG:405
 ?><td><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(404);
// PUG_DEBUG:404
 ?>01.12.2020</td><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(407);
// PUG_DEBUG:407
 ?><td><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(406);
// PUG_DEBUG:406
 ?>300</td><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(409);
// PUG_DEBUG:409
 ?><td><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(408);
// PUG_DEBUG:408
 ?>120</td></tr><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(417);
// PUG_DEBUG:417
 ?><tr><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(412);
// PUG_DEBUG:412
 ?><td><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(411);
// PUG_DEBUG:411
 ?>01.11.2020</td><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(414);
// PUG_DEBUG:414
 ?><td><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(413);
// PUG_DEBUG:413
 ?>180</td><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(416);
// PUG_DEBUG:416
 ?><td><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(415);
// PUG_DEBUG:415
 ?>180</td></tr><?php
});;
});;
});;
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(422);
// PUG_DEBUG:422
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(459);
// PUG_DEBUG:459
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(458);
// PUG_DEBUG:458
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'FOOTER'], ['class' => 'footer']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(452);
// PUG_DEBUG:452
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'wrap'], ['class' => 'wrap']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(423);
// PUG_DEBUG:423
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(434);
// PUG_DEBUG:434
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'top'], ['class' => 'flex'], ['class' => '_vertical']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(425);
// PUG_DEBUG:425
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'logo'], ['class' => 'logo'], ['href' => '/']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(424);
// PUG_DEBUG:424
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/app/img/logo-w.svg'], ['alt' => 'Логотип'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(433);
// PUG_DEBUG:433
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'top-right']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(427);
// PUG_DEBUG:427
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'phone'], ['class' => 'h3'], ['href' => 'tel:8 (996) 925-98-97']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(426);
// PUG_DEBUG:426
 ?>8 (996) 925-98-97<?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(432);
// PUG_DEBUG:432
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'email'], ['href' => 'mailto:help@cdtmail.ru']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(428);
// PUG_DEBUG:428
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(429);
// PUG_DEBUG:429
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'icon';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, array(  ), [[false, 'mail']], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    ?><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(431);
// PUG_DEBUG:431
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'text'], ['class' => 'link'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(430);
// PUG_DEBUG:430
 ?>info@сенсет.рф</div><?php
}); ?><?php
}); ?><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(451);
// PUG_DEBUG:451
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'middle'], ['class' => 'flex'], ['class' => '_justify'], ['class' => '_vertical']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(439);
// PUG_DEBUG:439
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'left']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(438);
// PUG_DEBUG:438
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'address'], ['class' => 'h3']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(435);
// PUG_DEBUG:435
 ?>г. Самара, <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(436);
// PUG_DEBUG:436
 ?><br><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(437);
// PUG_DEBUG:437
 ?>ул. Стара-загора, д. 79<?php
});;
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(450);
// PUG_DEBUG:450
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'right']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(449);
// PUG_DEBUG:449
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'UL'], ['class' => 'ul'], ['class' => 'flex'], ['class' => 'h4']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(442);
// PUG_DEBUG:442
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'LI'], ['class' => 'li']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(441);
// PUG_DEBUG:441
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'li-link'], ['href' => '#']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(440);
// PUG_DEBUG:440
 ?>Сведения об организации<?php
});;
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(445);
// PUG_DEBUG:445
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'LI'], ['class' => 'li']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(444);
// PUG_DEBUG:444
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'li-link'], ['href' => '#']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(443);
// PUG_DEBUG:443
 ?>Типы датчиков<?php
});;
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(448);
// PUG_DEBUG:448
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'LI'], ['class' => 'li']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(447);
// PUG_DEBUG:447
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'A'], ['class' => 'li-link'], ['href' => '#']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(446);
// PUG_DEBUG:446
 ?>Контакты<?php
});;
});;
});;
});;
}); ?><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(457);
// PUG_DEBUG:457
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $__local_pug_key) {
    if (mb_substr($__local_pug_key, 0, 6) === '__pug_' || in_array($__local_pug_key, ['attributes', 'block', 'pug_vars'])) {
        continue;
    }
    $pug_vars[$__local_pug_key] = &$$__local_pug_key;
    $__local_pug_ref = &$GLOBALS[$__local_pug_key];
    $__local_pug_value = &$$__local_pug_key;
    if($__local_pug_ref !== $__local_pug_value){
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
        continue;
    }
    $__local_pug_savedValue = $__local_pug_value;
    $__local_pug_value = ($__local_pug_value === true) ? false : true;
    $__local_pug_isGlobalReference = ($__local_pug_value === $__local_pug_ref);
    $__local_pug_value = $__local_pug_savedValue;
    if (!$__local_pug_isGlobalReference) {
        $__pug_mixin_vars[$__local_pug_key] = &$__local_pug_value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\HtmlFormat::merge_attributes'](['class' => 'bottom']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $__local_pug_key) {
        if (mb_substr($__local_pug_key, 0, 6) === '__pug_') {
            continue;
        }
        if(isset($pug_vars[$__local_pug_key])){
            $$__local_pug_key = &$pug_vars[$__local_pug_key];
            continue;
        }
        $__local_pug_ref = &$GLOBALS[$__local_pug_key];
        $__local_pug_value = &$__pug_children_vars[$__local_pug_key];
        if($__local_pug_ref !== $__local_pug_value){
            $$__local_pug_key = &$__local_pug_value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(453);
// PUG_DEBUG:453
 ?>©2020  Сенсерные сети  <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(456);
// PUG_DEBUG:456
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'link'], ['href' => '#'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(454);
// PUG_DEBUG:454
 ?>Политика конфиденциальности<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(455);
// PUG_DEBUG:455
 ?></a><?php
}); ?><?php
}); ?></main><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(461);
// PUG_DEBUG:461
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(463);
// PUG_DEBUG:463
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(462);
// PUG_DEBUG:462
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(464);
// PUG_DEBUG:464
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal-background'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(465);
// PUG_DEBUG:465
 ?><script<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => 'http://code.jquery.com/jquery-3.3.1.min.js'])
) ? var_export($_pug_temp, true) : $_pug_temp) ?>></script><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(466);
// PUG_DEBUG:466
 ?><script<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('src', $GLOBALS['__jpv_plus']("/js/app.js?v=", $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($Date, 'now')()))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></script></body></html>