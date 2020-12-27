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
$GLOBALS['__jpv_plus'] = function ($base) {
    foreach (array_slice(func_get_args(), 1) as $value) {
        $base = is_string($base) || is_string($value) ? $base . $value : $base + $value;
    }

    return $base;
};
$GLOBALS['__jpv_plus_with_ref'] = $GLOBALS['__jpv_plus'];
$pugModule = [
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
]; ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(143);
// PUG_DEBUG:143
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(141);
// PUG_DEBUG:141
 ?><?php $__eachScopeVariables = ['object' => isset($object) ? $object : null];foreach ($objects as $object) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(140);
// PUG_DEBUG:140
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(139);
// PUG_DEBUG:139
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card-wrapper'], ['data-obj-id' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('data-obj-id', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('ID'))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(66);
// PUG_DEBUG:66
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(4);
// PUG_DEBUG:4
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('images')), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(3);
// PUG_DEBUG:3
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-wrapper'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(2);
// PUG_DEBUG:2
 ?><?php $__eachScopeVariables = ['image' => isset($image) ? $image : null];foreach ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('images') as $image) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1);
// PUG_DEBUG:1
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-wrapper__item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(0);
// PUG_DEBUG:0
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['data-lazy' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('data-lazy', (isset($image) ? $image : null))])) ? var_export($_pug_temp, true) : $_pug_temp) ?> /></div><?php }extract($__eachScopeVariables); ?></div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(7);
// PUG_DEBUG:7
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-wrapper'], ['class' => 'card__image-wrapper--no-photo'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(6);
// PUG_DEBUG:6
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-wrapper__item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(5);
// PUG_DEBUG:5
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/img/no-photo.svg'])) ? var_export($_pug_temp, true) : $_pug_temp) ?> /></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(32);
// PUG_DEBUG:32
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'Проверено', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(11);
// PUG_DEBUG:11
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 0, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(10);
// PUG_DEBUG:10
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #f7fc00'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(9);
// PUG_DEBUG:9
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(8);
// PUG_DEBUG:8
 ?>Проверено сегодня</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(15);
// PUG_DEBUG:15
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 1, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(14);
// PUG_DEBUG:14
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #f7fc00'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(13);
// PUG_DEBUG:13
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(12);
// PUG_DEBUG:12
 ?>Проверено вчера</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(19);
// PUG_DEBUG:19
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 2, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(18);
// PUG_DEBUG:18
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #f7fc00'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(17);
// PUG_DEBUG:17
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(16);
// PUG_DEBUG:16
 ?>Проверено 2 дня назад</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(23);
// PUG_DEBUG:23
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 3, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(22);
// PUG_DEBUG:22
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #f7fc00'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(21);
// PUG_DEBUG:21
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(20);
// PUG_DEBUG:20
 ?>Проверено 3 дня назад</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(27);
// PUG_DEBUG:27
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_and'](($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') >= 4), function () use (&$object) { return ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') <= 10); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(26);
// PUG_DEBUG:26
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #F86464'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(25);
// PUG_DEBUG:25
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(24);
// PUG_DEBUG:24
 ?>Проверено недавно</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(31);
// PUG_DEBUG:31
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') > 10, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(30);
// PUG_DEBUG:30
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #F86464'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(29);
// PUG_DEBUG:29
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(28);
// PUG_DEBUG:28
 ?>Проверено давно</span></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(36);
// PUG_DEBUG:36
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'На проверке оператором', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(35);
// PUG_DEBUG:35
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #C38AD7'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(34);
// PUG_DEBUG:34
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(33);
// PUG_DEBUG:33
 ?>На проверке оператором</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(40);
// PUG_DEBUG:40
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'На проверке ботом', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(39);
// PUG_DEBUG:39
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #C38AD7'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(38);
// PUG_DEBUG:38
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(37);
// PUG_DEBUG:37
 ?>На проверке ботом</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(65);
// PUG_DEBUG:65
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'Добавлено', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(44);
// PUG_DEBUG:44
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 0, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(43);
// PUG_DEBUG:43
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #93D800'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(42);
// PUG_DEBUG:42
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(41);
// PUG_DEBUG:41
 ?>Добавлено сегодня</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(48);
// PUG_DEBUG:48
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 1, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(47);
// PUG_DEBUG:47
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #93D800'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(46);
// PUG_DEBUG:46
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(45);
// PUG_DEBUG:45
 ?>Добавлено вчера</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(52);
// PUG_DEBUG:52
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 2, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(51);
// PUG_DEBUG:51
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #93D800'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(50);
// PUG_DEBUG:50
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(49);
// PUG_DEBUG:49
 ?>Добавлено 2 дня назад</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(56);
// PUG_DEBUG:56
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 3, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(55);
// PUG_DEBUG:55
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #93D800'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(54);
// PUG_DEBUG:54
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(53);
// PUG_DEBUG:53
 ?>Добавлено 3 дня назад</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(60);
// PUG_DEBUG:60
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_and'](($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') >= 4), function () use (&$object) { return ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') <= 14); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(59);
// PUG_DEBUG:59
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #6496F8'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(58);
// PUG_DEBUG:58
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(57);
// PUG_DEBUG:57
 ?>Добавлено недавно</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(64);
// PUG_DEBUG:64
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') > 14, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(63);
// PUG_DEBUG:63
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #e85c5c'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(62);
// PUG_DEBUG:62
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(61);
// PUG_DEBUG:61
 ?>Добавлено давно</span></div><?php } ?><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(68);
// PUG_DEBUG:68
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card--viewed__status'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(67);
// PUG_DEBUG:67
 ?>Просмотрено</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(138);
// PUG_DEBUG:138
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(75);
// PUG_DEBUG:75
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-type'], ['data-rooms' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('data-rooms', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsGeneral'))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(71);
// PUG_DEBUG:71
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-type__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(69);
// PUG_DEBUG:69
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(70);
// PUG_DEBUG:70
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsGeneral')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(74);
// PUG_DEBUG:74
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-type__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(72);
// PUG_DEBUG:72
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(73);
// PUG_DEBUG:73
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsType')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(94);
// PUG_DEBUG:94
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-square'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(79);
// PUG_DEBUG:79
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_or'](($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsGeneral') == "Коттедж"), function () use (&$object) { return $GLOBALS['__jpv_or'](($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsGeneral') == "Дом"), function () use (&$object) { return ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsGeneral') == "Таунхаус"); }); })), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(78);
// PUG_DEBUG:78
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-floor'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(76);
// PUG_DEBUG:76
 ?>Этажей: <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(77);
// PUG_DEBUG:77
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floors')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(85);
// PUG_DEBUG:85
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-floor'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(80);
// PUG_DEBUG:80
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(81);
// PUG_DEBUG:81
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floor')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(82);
// PUG_DEBUG:82
 ?>/<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(83);
// PUG_DEBUG:83
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floors')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(84);
// PUG_DEBUG:84
 ?> Этаж</span><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(91);
// PUG_DEBUG:91
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-square__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(86);
// PUG_DEBUG:86
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(87);
// PUG_DEBUG:87
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(88);
// PUG_DEBUG:88
 ?> м<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(90);
// PUG_DEBUG:90
 ?><sup><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(89);
// PUG_DEBUG:89
 ?>2</sup></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(93);
// PUG_DEBUG:93
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-square__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(92);
// PUG_DEBUG:92
 ?>Площадь</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(108);
// PUG_DEBUG:108
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(97);
// PUG_DEBUG:97
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(95);
// PUG_DEBUG:95
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(96);
// PUG_DEBUG:96
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('price')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(100);
// PUG_DEBUG:100
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type') == 'Аренда', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(99);
// PUG_DEBUG:99
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(98);
// PUG_DEBUG:98
 ?>Руб/Месяц</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(107);
// PUG_DEBUG:107
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type') == 'Продажа', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(106);
// PUG_DEBUG:106
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(101);
// PUG_DEBUG:101
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(103);
// PUG_DEBUG:103
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__subtitle-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(102);
// PUG_DEBUG:102
 ?>Стоимость</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(105);
// PUG_DEBUG:105
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__subtitle-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(104);
// PUG_DEBUG:104
 ?>&nbsp;&#8381;</span></span><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(111);
// PUG_DEBUG:111
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-address'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(109);
// PUG_DEBUG:109
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(110);
// PUG_DEBUG:110
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('address')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(119);
// PUG_DEBUG:119
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-metro'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(113);
// PUG_DEBUG:113
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('iconMetro') === 'red', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(112);
// PUG_DEBUG:112
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/img/metro-red.svg'], ['class' => 'svg-sprite-icon icon-metro card__info-metro__icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?> /><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(115);
// PUG_DEBUG:115
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('iconMetro') === 'green', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(114);
// PUG_DEBUG:114
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\BasicFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/img/metro-green.svg'], ['class' => 'svg-sprite-icon icon-metro card__info-metro__icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?> /><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(118);
// PUG_DEBUG:118
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-metro__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(116);
// PUG_DEBUG:116
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(117);
// PUG_DEBUG:117
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('metro')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(121);
// PUG_DEBUG:121
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('favorite'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(120);
// PUG_DEBUG:120
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-favorites'], ['class' => 'favorites'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(122);
// PUG_DEBUG:122
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-favorites'], ['class' => 'favorites'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(125);
// PUG_DEBUG:125
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-construction'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(123);
// PUG_DEBUG:123
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(124);
// PUG_DEBUG:124
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type_house')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(127);
// PUG_DEBUG:127
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-btn--show-contacts'], ['class' => 'btn'], ['class' => 'btn-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(126);
// PUG_DEBUG:126
 ?>Показать контакты</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(132);
// PUG_DEBUG:132
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('favorite'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(131);
// PUG_DEBUG:131
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-btn--add-in-favorites'], ['class' => 'btn'], ['class' => 'btn-sec'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(128);
// PUG_DEBUG:128
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'btn-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(130);
// PUG_DEBUG:130
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'btn-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(129);
// PUG_DEBUG:129
 ?>В избранное</span></div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(136);
// PUG_DEBUG:136
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'card__info-btn--add-in-favorites'], ['class' => 'btn'], ['class' => 'btn-sec'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(133);
// PUG_DEBUG:133
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'btn-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(135);
// PUG_DEBUG:135
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => 'btn-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(134);
// PUG_DEBUG:134
 ?>В избранное</span></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(137);
// PUG_DEBUG:137
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['href' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('href', $GLOBALS['__jpv_plus']("/object/", $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('ID')))], ['target' => '_blank'], ['class' => 'card__info-link'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></a></div></div><?php }extract($__eachScopeVariables); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(142);
// PUG_DEBUG:142
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['type' => 'hidden'], ['value' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('value', (isset($countSearched) ? $countSearched : null))], ['class' => 'numCards'])) ? var_export($_pug_temp, true) : $_pug_temp) ?> />