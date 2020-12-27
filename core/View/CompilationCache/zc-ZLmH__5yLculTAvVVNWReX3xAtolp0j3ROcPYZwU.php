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
    $__pug_attributes = [[false, 'name', null], [false, 'mod', null]];
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
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(2);
// PUG_DEBUG:2
 ?><?php if (method_exists($_pug_temp = ((isset($mod) ? $mod : null)), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1);
// PUG_DEBUG:1
 ?><svg<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('class', $GLOBALS['__jpv_plus']("svg-sprite-icon icon-", (isset($name) ? $name : null), ' ', (isset($mod) ? $mod : null)))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(0);
// PUG_DEBUG:0
 ?><use<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['xlink:href' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('xlink:href', $GLOBALS['__jpv_plus']("/dist/assets/img/sprites/svg/symbol/sprite.svg#", (isset($name) ? $name : null)))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></use></svg><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(4);
// PUG_DEBUG:4
 ?><svg<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['class' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('class', $GLOBALS['__jpv_plus']("svg-sprite-icon icon-", (isset($name) ? $name : null)))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(3);
// PUG_DEBUG:3
 ?><use<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['xlink:href' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('xlink:href', $GLOBALS['__jpv_plus']("/dist/assets/img/sprites/svg/symbol/sprite.svg#", (isset($name) ? $name : null)))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></use></svg><?php } ?><?php
}; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['object-container'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
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
    $__pug_attributes = [[false, 'type', null]];
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
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1211);
// PUG_DEBUG:1211
 ?><section<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'section--page'], ['class' => 'object'], ['class' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('class', $GLOBALS['__jpv_plus']("object--", (isset($type) ? $type : null)))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1210);
// PUG_DEBUG:1210
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'container'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(395);
// PUG_DEBUG:395
 ?><header<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-header'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(394);
// PUG_DEBUG:394
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'adding-date'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(392);
// PUG_DEBUG:392
 ?>Объявление добавлено на сайт: <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(393);
// PUG_DEBUG:393
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('date_add')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div></header><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(396);
// PUG_DEBUG:396
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'hidden'], ['value' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('value', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('ID'))], ['id' => 'objId'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(397);
// PUG_DEBUG:397
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'hidden'], ['value' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('value', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('lat'))], ['id' => 'objLat'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(398);
// PUG_DEBUG:398
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'hidden'], ['value' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('value', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('lng'))], ['id' => 'objLng'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1056);
// PUG_DEBUG:1056
 ?><main<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(993);
// PUG_DEBUG:993
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(992);
// PUG_DEBUG:992
 ?><section<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-section'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(409);
// PUG_DEBUG:409
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-slider-wrapper'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(403);
// PUG_DEBUG:403
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-slider'], ['data-snap-ignore' => 'true'], ['data-images' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('data-images', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('images'))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(402);
// PUG_DEBUG:402
 ?><?php $__eachScopeVariables = ['image' => isset($image) ? $image : null];foreach ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('images') as $image) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(401);
// PUG_DEBUG:401
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-slider__imgs'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(399);
// PUG_DEBUG:399
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('src', (isset($image) ? $image : null))], ['alt' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('alt', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('rooms'))], ['class' => 'object-slider__imgs-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(400);
// PUG_DEBUG:400
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('src', (isset($image) ? $image : null))], ['alt' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('alt', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('rooms'))], ['class' => 'object-slider__imgs-bg'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php }extract($__eachScopeVariables); ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(408);
// PUG_DEBUG:408
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-slider__counter'], ['class' => 'slider__counter'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(404);
// PUG_DEBUG:404
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-slider__counter-current'], ['class' => 'counter-current'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(406);
// PUG_DEBUG:406
 ?><span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(405);
// PUG_DEBUG:405
 ?>/</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(407);
// PUG_DEBUG:407
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-slider__counter-all'], ['class' => 'counter-all'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(413);
// PUG_DEBUG:413
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('address'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(412);
// PUG_DEBUG:412
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title'], ['class' => 'object-title--address'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(410);
// PUG_DEBUG:410
 ?>Адрес: <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(411);
// PUG_DEBUG:411
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('address')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(417);
// PUG_DEBUG:417
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('metro'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(416);
// PUG_DEBUG:416
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title'], ['class' => 'object-title--metro'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(414);
// PUG_DEBUG:414
 ?>Метро: <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(415);
// PUG_DEBUG:415
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('metro')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(465);
// PUG_DEBUG:465
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(424);
// PUG_DEBUG:424
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info_item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(420);
// PUG_DEBUG:420
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(418);
// PUG_DEBUG:418
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(419);
// PUG_DEBUG:419
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('rooms')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(423);
// PUG_DEBUG:423
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(421);
// PUG_DEBUG:421
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(422);
// PUG_DEBUG:422
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsType')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(433);
// PUG_DEBUG:433
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info_item'], ['class' => 'object-info_item--square'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(430);
// PUG_DEBUG:430
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(425);
// PUG_DEBUG:425
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(426);
// PUG_DEBUG:426
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(427);
// PUG_DEBUG:427
 ?> м <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(429);
// PUG_DEBUG:429
 ?><sup><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(428);
// PUG_DEBUG:428
 ?>2</sup></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(432);
// PUG_DEBUG:432
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(431);
// PUG_DEBUG:431
 ?>Площадь</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(447);
// PUG_DEBUG:447
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info_item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(439);
// PUG_DEBUG:439
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('houses'), 'indexOf')($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('rooms')) !== -1, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(436);
// PUG_DEBUG:436
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(434);
// PUG_DEBUG:434
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(435);
// PUG_DEBUG:435
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floors')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(438);
// PUG_DEBUG:438
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(437);
// PUG_DEBUG:437
 ?>Этажей</div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(444);
// PUG_DEBUG:444
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(440);
// PUG_DEBUG:440
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(441);
// PUG_DEBUG:441
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floor')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(442);
// PUG_DEBUG:442
 ?>/<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(443);
// PUG_DEBUG:443
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floors')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(446);
// PUG_DEBUG:446
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(445);
// PUG_DEBUG:445
 ?>Этаж</div><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(464);
// PUG_DEBUG:464
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info_item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(451);
// PUG_DEBUG:451
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'Проверено', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(448);
// PUG_DEBUG:448
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__status'], ['class' => 'object-info__status--owner'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(450);
// PUG_DEBUG:450
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(449);
// PUG_DEBUG:449
 ?>Собственник</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(455);
// PUG_DEBUG:455
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'На проверке оператором', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(452);
// PUG_DEBUG:452
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__status'], ['class' => 'object-info__status--bot'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(454);
// PUG_DEBUG:454
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(453);
// PUG_DEBUG:453
 ?>Проверено ботом</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(459);
// PUG_DEBUG:459
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'На проверке ботом', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(456);
// PUG_DEBUG:456
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__status'], ['class' => 'object-info__status--operator'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(458);
// PUG_DEBUG:458
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(457);
// PUG_DEBUG:457
 ?>Проверено оператором</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(463);
// PUG_DEBUG:463
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'Добавлено', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(460);
// PUG_DEBUG:460
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__status'], ['class' => 'object-info__status--added'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(462);
// PUG_DEBUG:462
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-info__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(461);
// PUG_DEBUG:461
 ?>Добавлено</div><?php } ?></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(477);
// PUG_DEBUG:477
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'Проверено', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(476);
// PUG_DEBUG:476
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status'], ['class' => 'object-status--owner'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(470);
// PUG_DEBUG:470
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status_title'], ['class' => 'object-status_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(466);
// PUG_DEBUG:466
 ?>Объявление прошло проверку успешно <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(468);
// PUG_DEBUG:468
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'question'], ['id' => 'questionOwner'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(467);
// PUG_DEBUG:467
 ?>(?)</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(469);
// PUG_DEBUG:469
 ?>. Получите доступ ко всем объявлениям или поручите поиск персональному помощнику.</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(473);
// PUG_DEBUG:473
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'isSet')('ZASELITE_TYPE'), function () use (&$cookie) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'getValue')('ZASELITE_TYPE') == 'Аренда'; })), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(472);
// PUG_DEBUG:472
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status_btn'], ['class' => 'j-object-status_btn-rent'], ['class' => 'btn'], ['class' => 'btn-white'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(471);
// PUG_DEBUG:471
 ?>Приступить!</div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(475);
// PUG_DEBUG:475
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status_btn'], ['class' => 'j-object-status_btn-buy'], ['class' => 'btn'], ['class' => 'btn-white'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(474);
// PUG_DEBUG:474
 ?>Приступить!</div><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(489);
// PUG_DEBUG:489
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'На проверке оператором', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(488);
// PUG_DEBUG:488
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status'], ['class' => 'object-status--bot'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(482);
// PUG_DEBUG:482
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status_title'], ['class' => 'object-status_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(478);
// PUG_DEBUG:478
 ?>Объявление проверено ботом <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(480);
// PUG_DEBUG:480
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'question'], ['id' => 'questionBot'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(479);
// PUG_DEBUG:479
 ?>(?)</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(481);
// PUG_DEBUG:481
 ?>. Получите доступ ко всем объявлениям или поручите поиск персональному помощнику.</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(485);
// PUG_DEBUG:485
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'isSet')('ZASELITE_TYPE'), function () use (&$cookie) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'getValue')('ZASELITE_TYPE') == 'Аренда'; })), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(484);
// PUG_DEBUG:484
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status_btn'], ['class' => 'j-object-status_btn-rent'], ['class' => 'btn'], ['class' => 'btn-white'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(483);
// PUG_DEBUG:483
 ?>Приступить!</div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(487);
// PUG_DEBUG:487
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status_btn'], ['class' => 'j-object-status_btn-buy'], ['class' => 'btn'], ['class' => 'btn-white'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(486);
// PUG_DEBUG:486
 ?>Приступить!</div><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(501);
// PUG_DEBUG:501
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'На проверке ботом', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(500);
// PUG_DEBUG:500
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status'], ['class' => 'object-status--operator'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(494);
// PUG_DEBUG:494
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status_title'], ['class' => 'object-status_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(490);
// PUG_DEBUG:490
 ?>Объявление проверено звонком оператора <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(492);
// PUG_DEBUG:492
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'question'], ['id' => 'questionOperator'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(491);
// PUG_DEBUG:491
 ?>(?)</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(493);
// PUG_DEBUG:493
 ?>. Получите доступ ко всем объявлениям или поручите поиск персональному помощнику.</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(497);
// PUG_DEBUG:497
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'isSet')('ZASELITE_TYPE'), function () use (&$cookie) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'getValue')('ZASELITE_TYPE') == 'Аренда'; })), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(496);
// PUG_DEBUG:496
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status_btn'], ['class' => 'j-object-status_btn-rent'], ['class' => 'btn'], ['class' => 'btn-white'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(495);
// PUG_DEBUG:495
 ?>Приступить!</div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(499);
// PUG_DEBUG:499
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status_btn'], ['class' => 'j-object-status_btn-buy'], ['class' => 'btn'], ['class' => 'btn-white'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(498);
// PUG_DEBUG:498
 ?>Приступить!</div><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(513);
// PUG_DEBUG:513
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'Добавлено', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(512);
// PUG_DEBUG:512
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status'], ['class' => 'object-status--added'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(506);
// PUG_DEBUG:506
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status_title'], ['class' => 'object-status_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(502);
// PUG_DEBUG:502
 ?>Объявление добавлено на сайт <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(504);
// PUG_DEBUG:504
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'question'], ['id' => 'questionAdded'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(503);
// PUG_DEBUG:503
 ?>(?)</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(505);
// PUG_DEBUG:505
 ?>. Получите доступ ко всем объявлениям или поручите поиск персональному помощнику.</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(509);
// PUG_DEBUG:509
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'isSet')('ZASELITE_TYPE'), function () use (&$cookie) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'getValue')('ZASELITE_TYPE') == 'Аренда'; })), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(508);
// PUG_DEBUG:508
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status_btn'], ['class' => 'j-object-status_btn-rent'], ['class' => 'btn'], ['class' => 'btn-white'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(507);
// PUG_DEBUG:507
 ?>Приступить!</div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(511);
// PUG_DEBUG:511
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-status_btn'], ['class' => 'j-object-status_btn-buy'], ['class' => 'btn'], ['class' => 'btn-white'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(510);
// PUG_DEBUG:510
 ?>Приступить!</div><?php } ?></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(524);
// PUG_DEBUG:524
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-description'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(514);
// PUG_DEBUG:514
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(519);
// PUG_DEBUG:519
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-description_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(517);
// PUG_DEBUG:517
 ?><span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(515);
// PUG_DEBUG:515
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(516);
// PUG_DEBUG:516
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('description')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(518);
// PUG_DEBUG:518
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-description__gradient'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(523);
// PUG_DEBUG:523
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-description_learn-more'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(521);
// PUG_DEBUG:521
 ?><span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(520);
// PUG_DEBUG:520
 ?>Узнать больше</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(522);
// PUG_DEBUG:522
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

$__pug_mixins[$__pug_mixin_name](false, array(  ), [[false, 'arrow-down']], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
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
}); ?></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(538);
// PUG_DEBUG:538
 ?><?php if (method_exists($_pug_temp = (isset($comment) ? $comment : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(525);
// PUG_DEBUG:525
 ?><hr<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'hr-assistant'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(527);
// PUG_DEBUG:527
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(526);
// PUG_DEBUG:526
 ?>Комментарии личных помощников</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(537);
// PUG_DEBUG:537
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-comments'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(528);
// PUG_DEBUG:528
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/img/photo/assistant.jpg'], ['alt' => 'Личный помощник'], ['class' => 'object-comments_avatar'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(536);
// PUG_DEBUG:536
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-comments_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(529);
// PUG_DEBUG:529
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(530);
// PUG_DEBUG:530
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($comment, 'text')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(535);
// PUG_DEBUG:535
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-comments_date'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(531);
// PUG_DEBUG:531
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(532);
// PUG_DEBUG:532
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($comment, 'date_add')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(533);
// PUG_DEBUG:533
 ?>, помощник <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(534);
// PUG_DEBUG:534
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($comment, 'name')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div></div></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(539);
// PUG_DEBUG:539
 ?><hr><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(726);
// PUG_DEBUG:726
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type') == 'Аренда', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(541);
// PUG_DEBUG:541
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(540);
// PUG_DEBUG:540
 ?>Удобства</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(674);
// PUG_DEBUG:674
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-container'], ['class' => 'checkbox-container--first'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(553);
// PUG_DEBUG:553
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(547);
// PUG_DEBUG:547
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('amenities_balcony'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(542);
// PUG_DEBUG:542
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_1'], ['name' => 'amenities_1'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(546);
// PUG_DEBUG:546
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_1'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(543);
// PUG_DEBUG:543
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(545);
// PUG_DEBUG:545
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(544);
// PUG_DEBUG:544
 ?>Балкон</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(548);
// PUG_DEBUG:548
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_1'], ['name' => 'amenities_1'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(552);
// PUG_DEBUG:552
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_1'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(549);
// PUG_DEBUG:549
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(551);
// PUG_DEBUG:551
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(550);
// PUG_DEBUG:550
 ?>Балкон</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(565);
// PUG_DEBUG:565
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(559);
// PUG_DEBUG:559
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('amenities_refrigerator'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(554);
// PUG_DEBUG:554
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_2'], ['name' => 'amenities_2'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(558);
// PUG_DEBUG:558
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_2'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(555);
// PUG_DEBUG:555
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(557);
// PUG_DEBUG:557
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(556);
// PUG_DEBUG:556
 ?>Холодильник</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(560);
// PUG_DEBUG:560
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_2'], ['name' => 'amenities_2'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(564);
// PUG_DEBUG:564
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_2'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(561);
// PUG_DEBUG:561
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(563);
// PUG_DEBUG:563
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(562);
// PUG_DEBUG:562
 ?>Холодильник</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(577);
// PUG_DEBUG:577
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(571);
// PUG_DEBUG:571
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('amenities_washing'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(566);
// PUG_DEBUG:566
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_3'], ['name' => 'amenities_3'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(570);
// PUG_DEBUG:570
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_3'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(567);
// PUG_DEBUG:567
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(569);
// PUG_DEBUG:569
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(568);
// PUG_DEBUG:568
 ?>Стиральная машина</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(572);
// PUG_DEBUG:572
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_3'], ['name' => 'amenities_3'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(576);
// PUG_DEBUG:576
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_3'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(573);
// PUG_DEBUG:573
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(575);
// PUG_DEBUG:575
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(574);
// PUG_DEBUG:574
 ?>Стиральная машина</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(589);
// PUG_DEBUG:589
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(583);
// PUG_DEBUG:583
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('amenities_dishwasher'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(578);
// PUG_DEBUG:578
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_4'], ['name' => 'amenities_4'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(582);
// PUG_DEBUG:582
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_4'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(579);
// PUG_DEBUG:579
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(581);
// PUG_DEBUG:581
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(580);
// PUG_DEBUG:580
 ?>Посудомоечная машина</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(584);
// PUG_DEBUG:584
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_4'], ['name' => 'amenities_4'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(588);
// PUG_DEBUG:588
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_4'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(585);
// PUG_DEBUG:585
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(587);
// PUG_DEBUG:587
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(586);
// PUG_DEBUG:586
 ?>Посудомоечная машина</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(601);
// PUG_DEBUG:601
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(595);
// PUG_DEBUG:595
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('amenities_heater'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(590);
// PUG_DEBUG:590
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_5'], ['name' => 'amenities_5'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(594);
// PUG_DEBUG:594
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_5'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(591);
// PUG_DEBUG:591
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(593);
// PUG_DEBUG:593
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(592);
// PUG_DEBUG:592
 ?>Нагреватель воды</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(596);
// PUG_DEBUG:596
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_5'], ['name' => 'amenities_5'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(600);
// PUG_DEBUG:600
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_5'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(597);
// PUG_DEBUG:597
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(599);
// PUG_DEBUG:599
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(598);
// PUG_DEBUG:598
 ?>Нагреватель воды</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(613);
// PUG_DEBUG:613
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(607);
// PUG_DEBUG:607
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('amenities_phone'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(602);
// PUG_DEBUG:602
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_6'], ['name' => 'amenities_6'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(606);
// PUG_DEBUG:606
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_6'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(603);
// PUG_DEBUG:603
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(605);
// PUG_DEBUG:605
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(604);
// PUG_DEBUG:604
 ?>Телефон</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(608);
// PUG_DEBUG:608
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_6'], ['name' => 'amenities_6'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(612);
// PUG_DEBUG:612
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_6'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(609);
// PUG_DEBUG:609
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(611);
// PUG_DEBUG:611
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(610);
// PUG_DEBUG:610
 ?>Телефон</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(625);
// PUG_DEBUG:625
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(619);
// PUG_DEBUG:619
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('amenities_appliances'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(614);
// PUG_DEBUG:614
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_7'], ['name' => 'amenities_7'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(618);
// PUG_DEBUG:618
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_7'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(615);
// PUG_DEBUG:615
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(617);
// PUG_DEBUG:617
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(616);
// PUG_DEBUG:616
 ?>Встроенная техника</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(620);
// PUG_DEBUG:620
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_7'], ['name' => 'amenities_7'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(624);
// PUG_DEBUG:624
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_7'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(621);
// PUG_DEBUG:621
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(623);
// PUG_DEBUG:623
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(622);
// PUG_DEBUG:622
 ?>Встроенная техника</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(637);
// PUG_DEBUG:637
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(631);
// PUG_DEBUG:631
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('amenities_tv'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(626);
// PUG_DEBUG:626
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_8'], ['name' => 'amenities_8'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(630);
// PUG_DEBUG:630
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_8'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(627);
// PUG_DEBUG:627
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(629);
// PUG_DEBUG:629
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(628);
// PUG_DEBUG:628
 ?>Телевизор</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(632);
// PUG_DEBUG:632
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_8'], ['name' => 'amenities_8'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(636);
// PUG_DEBUG:636
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_8'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(633);
// PUG_DEBUG:633
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(635);
// PUG_DEBUG:635
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(634);
// PUG_DEBUG:634
 ?>Телевизор</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(649);
// PUG_DEBUG:649
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(643);
// PUG_DEBUG:643
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('amenities_conditioner'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(638);
// PUG_DEBUG:638
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_9'], ['name' => 'amenities_9'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(642);
// PUG_DEBUG:642
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_9'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(639);
// PUG_DEBUG:639
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(641);
// PUG_DEBUG:641
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(640);
// PUG_DEBUG:640
 ?>Кондиционер</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(644);
// PUG_DEBUG:644
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_9'], ['name' => 'amenities_9'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(648);
// PUG_DEBUG:648
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_9'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(645);
// PUG_DEBUG:645
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(647);
// PUG_DEBUG:647
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(646);
// PUG_DEBUG:646
 ?>Кондиционер</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(661);
// PUG_DEBUG:661
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(655);
// PUG_DEBUG:655
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('amenities_kettle'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(650);
// PUG_DEBUG:650
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_10'], ['name' => 'amenities_10'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(654);
// PUG_DEBUG:654
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_10'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(651);
// PUG_DEBUG:651
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(653);
// PUG_DEBUG:653
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(652);
// PUG_DEBUG:652
 ?>Электрочайник</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(656);
// PUG_DEBUG:656
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_10'], ['name' => 'amenities_10'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(660);
// PUG_DEBUG:660
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_10'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(657);
// PUG_DEBUG:657
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(659);
// PUG_DEBUG:659
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(658);
// PUG_DEBUG:658
 ?>Электрочайник</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(673);
// PUG_DEBUG:673
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(667);
// PUG_DEBUG:667
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('amenities_microwave'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(662);
// PUG_DEBUG:662
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_11'], ['name' => 'amenities_11'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(666);
// PUG_DEBUG:666
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_11'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(663);
// PUG_DEBUG:663
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(665);
// PUG_DEBUG:665
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(664);
// PUG_DEBUG:664
 ?>Микроволновая печь</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(668);
// PUG_DEBUG:668
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'amenities_11'], ['name' => 'amenities_11'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(672);
// PUG_DEBUG:672
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'amenities_11'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(669);
// PUG_DEBUG:669
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(671);
// PUG_DEBUG:671
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(670);
// PUG_DEBUG:670
 ?>Микроволновая печь</div></label><?php } ?></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(676);
// PUG_DEBUG:676
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(675);
// PUG_DEBUG:675
 ?>УСЛОВИЯ</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(725);
// PUG_DEBUG:725
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-container'], ['class' => 'checkbox-container--last'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(688);
// PUG_DEBUG:688
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(682);
// PUG_DEBUG:682
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('conditions_smoke'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(677);
// PUG_DEBUG:677
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'conditions_1'], ['name' => 'conditions_1'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(681);
// PUG_DEBUG:681
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'conditions_1'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(678);
// PUG_DEBUG:678
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(680);
// PUG_DEBUG:680
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(679);
// PUG_DEBUG:679
 ?>Можно курить</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(683);
// PUG_DEBUG:683
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'conditions_1'], ['name' => 'conditions_1'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(687);
// PUG_DEBUG:687
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'conditions_1'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(684);
// PUG_DEBUG:684
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(686);
// PUG_DEBUG:686
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(685);
// PUG_DEBUG:685
 ?>Можно курить</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(700);
// PUG_DEBUG:700
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(694);
// PUG_DEBUG:694
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('conditions_events'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(689);
// PUG_DEBUG:689
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'conditions_2'], ['name' => 'conditions_2'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(693);
// PUG_DEBUG:693
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'conditions_2'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(690);
// PUG_DEBUG:690
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(692);
// PUG_DEBUG:692
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(691);
// PUG_DEBUG:691
 ?>Подходит для мероприятий</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(695);
// PUG_DEBUG:695
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'conditions_2'], ['name' => 'conditions_2'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(699);
// PUG_DEBUG:699
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'conditions_2'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(696);
// PUG_DEBUG:696
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(698);
// PUG_DEBUG:698
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(697);
// PUG_DEBUG:697
 ?>Подходит для мероприятий</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(712);
// PUG_DEBUG:712
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(706);
// PUG_DEBUG:706
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('conditions_animals'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(701);
// PUG_DEBUG:701
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'conditions_3'], ['name' => 'conditions_3'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(705);
// PUG_DEBUG:705
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'conditions_3'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(702);
// PUG_DEBUG:702
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(704);
// PUG_DEBUG:704
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(703);
// PUG_DEBUG:703
 ?>Можно с животными</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(707);
// PUG_DEBUG:707
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'conditions_3'], ['name' => 'conditions_3'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(711);
// PUG_DEBUG:711
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'conditions_3'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(708);
// PUG_DEBUG:708
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(710);
// PUG_DEBUG:710
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(709);
// PUG_DEBUG:709
 ?>Можно с животными</div></label><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(724);
// PUG_DEBUG:724
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(718);
// PUG_DEBUG:718
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('conditions_family'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(713);
// PUG_DEBUG:713
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'conditions_4'], ['name' => 'conditions_4'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(717);
// PUG_DEBUG:717
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'conditions_4'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(714);
// PUG_DEBUG:714
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(716);
// PUG_DEBUG:716
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(715);
// PUG_DEBUG:715
 ?>Подходит для семьи с детьми</div></label><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(719);
// PUG_DEBUG:719
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'conditions_4'], ['name' => 'conditions_4'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(723);
// PUG_DEBUG:723
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'conditions_4'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(720);
// PUG_DEBUG:720
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(722);
// PUG_DEBUG:722
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(721);
// PUG_DEBUG:721
 ?>Подходит для семьи с детьми</div></label><?php } ?></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(867);
// PUG_DEBUG:867
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type') == 'Продажа', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(728);
// PUG_DEBUG:728
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(727);
// PUG_DEBUG:727
 ?>Параметры</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(866);
// PUG_DEBUG:866
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-block'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(847);
// PUG_DEBUG:847
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-wrap'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(787);
// PUG_DEBUG:787
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('houses'), 'indexOf')($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('rooms')) !== -1, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(739);
// PUG_DEBUG:739
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(738);
// PUG_DEBUG:738
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(731);
// PUG_DEBUG:731
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(730);
// PUG_DEBUG:730
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(729);
// PUG_DEBUG:729
 ?>Общая</span></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(737);
// PUG_DEBUG:737
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__prop'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(732);
// PUG_DEBUG:732
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(733);
// PUG_DEBUG:733
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(734);
// PUG_DEBUG:734
 ?> м <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(736);
// PUG_DEBUG:736
 ?><sup><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(735);
// PUG_DEBUG:735
 ?>2</sup></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(750);
// PUG_DEBUG:750
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square_living'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(749);
// PUG_DEBUG:749
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(742);
// PUG_DEBUG:742
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(741);
// PUG_DEBUG:741
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(740);
// PUG_DEBUG:740
 ?>Жилая</span></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(748);
// PUG_DEBUG:748
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__prop'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(743);
// PUG_DEBUG:743
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(744);
// PUG_DEBUG:744
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square_living')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(745);
// PUG_DEBUG:745
 ?> м <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(747);
// PUG_DEBUG:747
 ?><sup><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(746);
// PUG_DEBUG:746
 ?>2</sup></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(761);
// PUG_DEBUG:761
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square_kitchen'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(760);
// PUG_DEBUG:760
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(753);
// PUG_DEBUG:753
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(752);
// PUG_DEBUG:752
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(751);
// PUG_DEBUG:751
 ?>Кухня</span></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(759);
// PUG_DEBUG:759
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__prop'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(754);
// PUG_DEBUG:754
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(755);
// PUG_DEBUG:755
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square_kitchen')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(756);
// PUG_DEBUG:756
 ?> м <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(758);
// PUG_DEBUG:758
 ?><sup><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(757);
// PUG_DEBUG:757
 ?>2</sup></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(770);
// PUG_DEBUG:770
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square_plot'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(769);
// PUG_DEBUG:769
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(764);
// PUG_DEBUG:764
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(763);
// PUG_DEBUG:763
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(762);
// PUG_DEBUG:762
 ?>Участок</span></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(768);
// PUG_DEBUG:768
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__prop'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(765);
// PUG_DEBUG:765
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(766);
// PUG_DEBUG:766
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square_plot')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(767);
// PUG_DEBUG:767
 ?> сот.</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(778);
// PUG_DEBUG:778
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floors'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(777);
// PUG_DEBUG:777
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(773);
// PUG_DEBUG:773
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(772);
// PUG_DEBUG:772
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(771);
// PUG_DEBUG:771
 ?>Этажность</span></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(776);
// PUG_DEBUG:776
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__prop'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(774);
// PUG_DEBUG:774
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(775);
// PUG_DEBUG:775
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floors')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(786);
// PUG_DEBUG:786
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type_house'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(785);
// PUG_DEBUG:785
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(781);
// PUG_DEBUG:781
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(780);
// PUG_DEBUG:780
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(779);
// PUG_DEBUG:779
 ?>Материал</span></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(784);
// PUG_DEBUG:784
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__prop'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(782);
// PUG_DEBUG:782
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(783);
// PUG_DEBUG:783
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type_house')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php } ?><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(798);
// PUG_DEBUG:798
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(797);
// PUG_DEBUG:797
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(790);
// PUG_DEBUG:790
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(789);
// PUG_DEBUG:789
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(788);
// PUG_DEBUG:788
 ?>Общая</span></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(796);
// PUG_DEBUG:796
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__prop'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(791);
// PUG_DEBUG:791
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(792);
// PUG_DEBUG:792
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(793);
// PUG_DEBUG:793
 ?> м <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(795);
// PUG_DEBUG:795
 ?><sup><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(794);
// PUG_DEBUG:794
 ?>2</sup></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(809);
// PUG_DEBUG:809
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square_living'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(808);
// PUG_DEBUG:808
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(801);
// PUG_DEBUG:801
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(800);
// PUG_DEBUG:800
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(799);
// PUG_DEBUG:799
 ?>Жилая</span></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(807);
// PUG_DEBUG:807
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__prop'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(802);
// PUG_DEBUG:802
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(803);
// PUG_DEBUG:803
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square_living')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(804);
// PUG_DEBUG:804
 ?> м <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(806);
// PUG_DEBUG:806
 ?><sup><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(805);
// PUG_DEBUG:805
 ?>2</sup></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(820);
// PUG_DEBUG:820
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square_kitchen'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(819);
// PUG_DEBUG:819
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(812);
// PUG_DEBUG:812
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(811);
// PUG_DEBUG:811
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(810);
// PUG_DEBUG:810
 ?>Кухня</span></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(818);
// PUG_DEBUG:818
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__prop'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(813);
// PUG_DEBUG:813
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(814);
// PUG_DEBUG:814
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square_kitchen')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(815);
// PUG_DEBUG:815
 ?> м <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(817);
// PUG_DEBUG:817
 ?><sup><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(816);
// PUG_DEBUG:816
 ?>2</sup></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(830);
// PUG_DEBUG:830
 }  if (method_exists($_pug_temp = ($GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floor'), function () use (&$object) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floors'); })), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(829);
// PUG_DEBUG:829
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(823);
// PUG_DEBUG:823
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(822);
// PUG_DEBUG:822
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(821);
// PUG_DEBUG:821
 ?>Этаж/этажей</span></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(828);
// PUG_DEBUG:828
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__prop'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(824);
// PUG_DEBUG:824
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(825);
// PUG_DEBUG:825
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floor')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(826);
// PUG_DEBUG:826
 ?>/<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(827);
// PUG_DEBUG:827
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floors')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(838);
// PUG_DEBUG:838
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('kind_object'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(837);
// PUG_DEBUG:837
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(833);
// PUG_DEBUG:833
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(832);
// PUG_DEBUG:832
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(831);
// PUG_DEBUG:831
 ?>Рынок</span></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(836);
// PUG_DEBUG:836
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__prop'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(834);
// PUG_DEBUG:834
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(835);
// PUG_DEBUG:835
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('kind_object')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(846);
// PUG_DEBUG:846
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type_house'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(845);
// PUG_DEBUG:845
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(841);
// PUG_DEBUG:841
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(840);
// PUG_DEBUG:840
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__name-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(839);
// PUG_DEBUG:839
 ?>Материал</span></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(844);
// PUG_DEBUG:844
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-item__prop'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(842);
// PUG_DEBUG:842
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(843);
// PUG_DEBUG:843
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type_house')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php } ?><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(865);
// PUG_DEBUG:865
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(851);
// PUG_DEBUG:851
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-content__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(848);
// PUG_DEBUG:848
 ?>Купи квартиру мечты <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(849);
// PUG_DEBUG:849
 ?><br><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(850);
// PUG_DEBUG:850
 ?>без затрат на услуги риэлтора</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(855);
// PUG_DEBUG:855
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'options-content__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(852);
// PUG_DEBUG:852
 ?>Сэкономим Вам деньги на услуги. <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(853);
// PUG_DEBUG:853
 ?><br><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(854);
// PUG_DEBUG:854
 ?>Поможем с ипотекой и окажем юридическую поддержку</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(864);
// PUG_DEBUG:864
 ?><form<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form'], ['class' => 'options-form'], ['class' => 'form--usual'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(863);
// PUG_DEBUG:863
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group__couple'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(858);
// PUG_DEBUG:858
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'], ['class' => 'form-group-phone'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(857);
// PUG_DEBUG:857
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(856);
// PUG_DEBUG:856
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'text'], ['name' => 'phone'], ['placeholder' => 'Телефон'], ['class' => 'input-phone'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(862);
// PUG_DEBUG:862
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group-down'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(861);
// PUG_DEBUG:861
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(860);
// PUG_DEBUG:860
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['type' => 'submit'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(859);
// PUG_DEBUG:859
 ?>Узнать подробнее</button></div></div></div></form></div></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(868);
// PUG_DEBUG:868
 ?><hr><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(897);
// PUG_DEBUG:897
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'Добавлено', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(871);
// PUG_DEBUG:871
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(869);
// PUG_DEBUG:869
 ?>Объявление ожидает проверки<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(870);
// PUG_DEBUG:870
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title_date'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(896);
// PUG_DEBUG:896
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-container'], ['class' => 'checkbox-container--check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(879);
// PUG_DEBUG:879
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(872);
// PUG_DEBUG:872
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'checked_base'], ['name' => 'checked_base'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(878);
// PUG_DEBUG:878
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'checked_base'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(873);
// PUG_DEBUG:873
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(877);
// PUG_DEBUG:877
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(874);
// PUG_DEBUG:874
 ?>Проверка по базам агентов<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(876);
// PUG_DEBUG:876
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text_check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(875);
// PUG_DEBUG:875
 ?>Пройдено</div></div></label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(887);
// PUG_DEBUG:887
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(880);
// PUG_DEBUG:880
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'checked_bot'], ['name' => 'checked_bot'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(886);
// PUG_DEBUG:886
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'checked_bot'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(881);
// PUG_DEBUG:881
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(885);
// PUG_DEBUG:885
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(882);
// PUG_DEBUG:882
 ?>Проверка ботом<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(884);
// PUG_DEBUG:884
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text_check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(883);
// PUG_DEBUG:883
 ?>Ожидает проверки</div></div></label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(895);
// PUG_DEBUG:895
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(888);
// PUG_DEBUG:888
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'checked_call'], ['name' => 'checked_call'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(894);
// PUG_DEBUG:894
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'checked_call'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(889);
// PUG_DEBUG:889
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(893);
// PUG_DEBUG:893
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(890);
// PUG_DEBUG:890
 ?>Проверка звонком оператора<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(892);
// PUG_DEBUG:892
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text_check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(891);
// PUG_DEBUG:891
 ?>Ожидает проверки</div></div></label></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(928);
// PUG_DEBUG:928
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'Проверено', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(902);
// PUG_DEBUG:902
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(898);
// PUG_DEBUG:898
 ?>Объявление проверено<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(901);
// PUG_DEBUG:901
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title_date'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(899);
// PUG_DEBUG:899
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(900);
// PUG_DEBUG:900
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('date_check')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(927);
// PUG_DEBUG:927
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-container'], ['class' => 'checkbox-container--check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(910);
// PUG_DEBUG:910
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(903);
// PUG_DEBUG:903
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'checked_base'], ['name' => 'checked_base'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(909);
// PUG_DEBUG:909
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'checked_base'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(904);
// PUG_DEBUG:904
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(908);
// PUG_DEBUG:908
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(905);
// PUG_DEBUG:905
 ?>Проверка по базам агентов<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(907);
// PUG_DEBUG:907
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text_check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(906);
// PUG_DEBUG:906
 ?>Пройдено</div></div></label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(918);
// PUG_DEBUG:918
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(911);
// PUG_DEBUG:911
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'checked_bot'], ['name' => 'checked_bot'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(917);
// PUG_DEBUG:917
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'checked_bot'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(912);
// PUG_DEBUG:912
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(916);
// PUG_DEBUG:916
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(913);
// PUG_DEBUG:913
 ?>Проверка ботом<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(915);
// PUG_DEBUG:915
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text_check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(914);
// PUG_DEBUG:914
 ?>Пройдено</div></div></label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(926);
// PUG_DEBUG:926
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(919);
// PUG_DEBUG:919
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'checked_call'], ['name' => 'checked_call'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(925);
// PUG_DEBUG:925
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'checked_call'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(920);
// PUG_DEBUG:920
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(924);
// PUG_DEBUG:924
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(921);
// PUG_DEBUG:921
 ?>Проверка звонком оператора<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(923);
// PUG_DEBUG:923
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text_check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(922);
// PUG_DEBUG:922
 ?>Пройдено</div></div></label></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(959);
// PUG_DEBUG:959
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'На проверке оператором', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(933);
// PUG_DEBUG:933
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(929);
// PUG_DEBUG:929
 ?>Объявление ожидает проверки<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(932);
// PUG_DEBUG:932
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title_date'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(930);
// PUG_DEBUG:930
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(931);
// PUG_DEBUG:931
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('date_check')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(958);
// PUG_DEBUG:958
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-container'], ['class' => 'checkbox-container--check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(941);
// PUG_DEBUG:941
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(934);
// PUG_DEBUG:934
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'checked_base'], ['name' => 'checked_base'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(940);
// PUG_DEBUG:940
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'checked_base'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(935);
// PUG_DEBUG:935
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(939);
// PUG_DEBUG:939
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(936);
// PUG_DEBUG:936
 ?>Проверка по базам агентов<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(938);
// PUG_DEBUG:938
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text_check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(937);
// PUG_DEBUG:937
 ?>Пройдено</div></div></label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(949);
// PUG_DEBUG:949
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(942);
// PUG_DEBUG:942
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'checked_bot'], ['name' => 'checked_bot'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(948);
// PUG_DEBUG:948
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'checked_bot'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(943);
// PUG_DEBUG:943
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(947);
// PUG_DEBUG:947
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(944);
// PUG_DEBUG:944
 ?>Проверка ботом<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(946);
// PUG_DEBUG:946
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text_check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(945);
// PUG_DEBUG:945
 ?>Пройдено</div></div></label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(957);
// PUG_DEBUG:957
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(950);
// PUG_DEBUG:950
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'checked_call'], ['name' => 'checked_call'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(956);
// PUG_DEBUG:956
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'checked_call'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(951);
// PUG_DEBUG:951
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(955);
// PUG_DEBUG:955
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(952);
// PUG_DEBUG:952
 ?>Проверка звонком оператора<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(954);
// PUG_DEBUG:954
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text_check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(953);
// PUG_DEBUG:953
 ?>Ожидает проверки</div></div></label></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(990);
// PUG_DEBUG:990
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'На проверке ботом', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(964);
// PUG_DEBUG:964
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(960);
// PUG_DEBUG:960
 ?>Объявление ожидает проверки<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(963);
// PUG_DEBUG:963
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title_date'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(961);
// PUG_DEBUG:961
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(962);
// PUG_DEBUG:962
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('date_check')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(989);
// PUG_DEBUG:989
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-container'], ['class' => 'checkbox-container--check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(972);
// PUG_DEBUG:972
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(965);
// PUG_DEBUG:965
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'checked_base'], ['name' => 'checked_base'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(971);
// PUG_DEBUG:971
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'checked_base'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(966);
// PUG_DEBUG:966
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(970);
// PUG_DEBUG:970
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(967);
// PUG_DEBUG:967
 ?>Проверка по базам агентов<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(969);
// PUG_DEBUG:969
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text_check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(968);
// PUG_DEBUG:968
 ?>Пройдено</div></div></label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(980);
// PUG_DEBUG:980
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(973);
// PUG_DEBUG:973
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'checked_bot'], ['name' => 'checked_bot'], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(979);
// PUG_DEBUG:979
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'checked_bot'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(974);
// PUG_DEBUG:974
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(978);
// PUG_DEBUG:978
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(975);
// PUG_DEBUG:975
 ?>Проверка ботом<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(977);
// PUG_DEBUG:977
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text_check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(976);
// PUG_DEBUG:976
 ?>Ожидает проверки</div></div></label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(988);
// PUG_DEBUG:988
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(981);
// PUG_DEBUG:981
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'checked_call'], ['name' => 'checked_call'], ['checked' => true], ['disabled' => true])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(987);
// PUG_DEBUG:987
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'checked_call'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(982);
// PUG_DEBUG:982
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(986);
// PUG_DEBUG:986
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(983);
// PUG_DEBUG:983
 ?>Проверка звонком оператора<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(985);
// PUG_DEBUG:985
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text_check'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(984);
// PUG_DEBUG:984
 ?>Пройдено</div></div></label></div></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(991);
// PUG_DEBUG:991
 ?><hr></section><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1055);
// PUG_DEBUG:1055
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1054);
// PUG_DEBUG:1054
 ?><aside<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-aside'], ['class' => 'card--main--list'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1053);
// PUG_DEBUG:1053
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(997);
// PUG_DEBUG:997
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-type'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(996);
// PUG_DEBUG:996
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-type__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(994);
// PUG_DEBUG:994
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(995);
// PUG_DEBUG:995
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsGeneral')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1001);
// PUG_DEBUG:1001
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-date'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1000);
// PUG_DEBUG:1000
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-date__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(998);
// PUG_DEBUG:998
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(999);
// PUG_DEBUG:999
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('date_check')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1009);
// PUG_DEBUG:1009
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-metro'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1003);
// PUG_DEBUG:1003
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('iconMetro') === 'red', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1002);
// PUG_DEBUG:1002
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/img/metro-red.svg'], ['class' => 'svg-sprite-icon icon-metro card__info-metro__icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1005);
// PUG_DEBUG:1005
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('iconMetro') === 'green', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1004);
// PUG_DEBUG:1004
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/img/metro-green.svg'], ['class' => 'svg-sprite-icon icon-metro card__info-metro__icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1008);
// PUG_DEBUG:1008
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-metro__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1006);
// PUG_DEBUG:1006
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1007);
// PUG_DEBUG:1007
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('metro')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1010);
// PUG_DEBUG:1010
 ?><hr<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-hr'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1028);
// PUG_DEBUG:1028
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1016);
// PUG_DEBUG:1016
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type') == 'Аренда', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1013);
// PUG_DEBUG:1013
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1011);
// PUG_DEBUG:1011
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1012);
// PUG_DEBUG:1012
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('price')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1015);
// PUG_DEBUG:1015
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1014);
// PUG_DEBUG:1014
 ?>Руб/Месяц</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1027);
// PUG_DEBUG:1027
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type') == 'Продажа', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1020);
// PUG_DEBUG:1020
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1017);
// PUG_DEBUG:1017
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1018);
// PUG_DEBUG:1018
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('price')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1019);
// PUG_DEBUG:1019
 ?>&nbsp;&#8381;</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1026);
// PUG_DEBUG:1026
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1021);
// PUG_DEBUG:1021
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1022);
// PUG_DEBUG:1022
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('pricePerSquareMeter')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1023);
// PUG_DEBUG:1023
 ?> &#8381;/м<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1025);
// PUG_DEBUG:1025
 ?><sup><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1024);
// PUG_DEBUG:1024
 ?>2</sup></span><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1035);
// PUG_DEBUG:1035
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type') == 'Аренда', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1034);
// PUG_DEBUG:1034
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-square'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1031);
// PUG_DEBUG:1031
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-square__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1029);
// PUG_DEBUG:1029
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1030);
// PUG_DEBUG:1030
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('deposit')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1033);
// PUG_DEBUG:1033
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-square__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1032);
// PUG_DEBUG:1032
 ?>Залог</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1041);
// PUG_DEBUG:1041
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type') == 'Продажа', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1040);
// PUG_DEBUG:1040
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-mortgage'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1037);
// PUG_DEBUG:1037
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-mortgage__title'], ['class' => 'j-mortgage-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1036);
// PUG_DEBUG:1036
 ?>Рассчитать ипотеку</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1039);
// PUG_DEBUG:1039
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-mortgage__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1038);
// PUG_DEBUG:1038
 ?>экономия до 0,5% на ставки</span></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1043);
// PUG_DEBUG:1043
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-btn--show-contacts'], ['class' => 'btn'], ['class' => 'btn-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1042);
// PUG_DEBUG:1042
 ?>Связаться с владельцем</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1048);
// PUG_DEBUG:1048
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('favorite'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1047);
// PUG_DEBUG:1047
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-btn--add-in-favorites'], ['class' => 'btn'], ['class' => 'btn-sec'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1044);
// PUG_DEBUG:1044
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1046);
// PUG_DEBUG:1046
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1045);
// PUG_DEBUG:1045
 ?>В избранное</span></div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1052);
// PUG_DEBUG:1052
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-btn--add-in-favorites'], ['class' => 'btn'], ['class' => 'btn-sec'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1049);
// PUG_DEBUG:1049
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1051);
// PUG_DEBUG:1051
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1050);
// PUG_DEBUG:1050
 ?>В избранное</span></div><?php } ?></div></aside></main><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1209);
// PUG_DEBUG:1209
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1208);
// PUG_DEBUG:1208
 ?><section<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'section-bottom'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1058);
// PUG_DEBUG:1058
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1057);
// PUG_DEBUG:1057
 ?>На карте</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1059);
// PUG_DEBUG:1059
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'object-map'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1207);
// PUG_DEBUG:1207
 ?><?php if (method_exists($_pug_temp = (isset($objects) ? $objects : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1061);
// PUG_DEBUG:1061
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h2'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1060);
// PUG_DEBUG:1060
 ?>Похожие предложения</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1206);
// PUG_DEBUG:1206
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card--large'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1205);
// PUG_DEBUG:1205
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'last-tried__cards'], ['class' => 'card'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1204);
// PUG_DEBUG:1204
 ?><?php $__eachScopeVariables = ['object' => isset($object) ? $object : null];foreach ($objects as $object) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1203);
// PUG_DEBUG:1203
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1202);
// PUG_DEBUG:1202
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card-wrapper'], ['data-obj-id' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('data-obj-id', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('ID'))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1128);
// PUG_DEBUG:1128
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1066);
// PUG_DEBUG:1066
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('images')), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1065);
// PUG_DEBUG:1065
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-wrapper'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1064);
// PUG_DEBUG:1064
 ?><?php $__eachScopeVariables = ['image' => isset($image) ? $image : null];foreach ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('images') as $image) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1063);
// PUG_DEBUG:1063
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-wrapper__item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1062);
// PUG_DEBUG:1062
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['data-lazy' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('data-lazy', (isset($image) ? $image : null))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php }extract($__eachScopeVariables); ?></div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1069);
// PUG_DEBUG:1069
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-wrapper'], ['class' => 'card__image-wrapper--no-photo'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1068);
// PUG_DEBUG:1068
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-wrapper__item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1067);
// PUG_DEBUG:1067
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/img/no-photo.svg'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1094);
// PUG_DEBUG:1094
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'Проверено', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1073);
// PUG_DEBUG:1073
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 0, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1072);
// PUG_DEBUG:1072
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #f7fc00'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1071);
// PUG_DEBUG:1071
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1070);
// PUG_DEBUG:1070
 ?>Проверено сегодня</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1077);
// PUG_DEBUG:1077
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 1, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1076);
// PUG_DEBUG:1076
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #f7fc00'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1075);
// PUG_DEBUG:1075
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1074);
// PUG_DEBUG:1074
 ?>Проверено вчера</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1081);
// PUG_DEBUG:1081
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 2, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1080);
// PUG_DEBUG:1080
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #f7fc00'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1079);
// PUG_DEBUG:1079
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1078);
// PUG_DEBUG:1078
 ?>Проверено 2 дня назад</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1085);
// PUG_DEBUG:1085
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 3, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1084);
// PUG_DEBUG:1084
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #f7fc00'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1083);
// PUG_DEBUG:1083
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1082);
// PUG_DEBUG:1082
 ?>Проверено 3 дня назад</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1089);
// PUG_DEBUG:1089
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_and'](($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') >= 4), function () use (&$object) { return ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') <= 10); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1088);
// PUG_DEBUG:1088
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #F86464'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1087);
// PUG_DEBUG:1087
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1086);
// PUG_DEBUG:1086
 ?>Проверено недавно</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1093);
// PUG_DEBUG:1093
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') > 10, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1092);
// PUG_DEBUG:1092
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #F86464'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1091);
// PUG_DEBUG:1091
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1090);
// PUG_DEBUG:1090
 ?>Проверено давно</span></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1098);
// PUG_DEBUG:1098
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'На проверке оператором', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1097);
// PUG_DEBUG:1097
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #C38AD7'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1096);
// PUG_DEBUG:1096
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1095);
// PUG_DEBUG:1095
 ?>На проверке оператором</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1102);
// PUG_DEBUG:1102
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'На проверке ботом', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1101);
// PUG_DEBUG:1101
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #C38AD7'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1100);
// PUG_DEBUG:1100
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1099);
// PUG_DEBUG:1099
 ?>На проверке ботом</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1127);
// PUG_DEBUG:1127
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('globalStatus') === 'Добавлено', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1106);
// PUG_DEBUG:1106
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 0, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1105);
// PUG_DEBUG:1105
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #93D800'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1104);
// PUG_DEBUG:1104
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1103);
// PUG_DEBUG:1103
 ?>Добавлено сегодня</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1110);
// PUG_DEBUG:1110
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 1, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1109);
// PUG_DEBUG:1109
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #93D800'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1108);
// PUG_DEBUG:1108
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1107);
// PUG_DEBUG:1107
 ?>Добавлено вчера</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1114);
// PUG_DEBUG:1114
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 2, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1113);
// PUG_DEBUG:1113
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #93D800'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1112);
// PUG_DEBUG:1112
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1111);
// PUG_DEBUG:1111
 ?>Добавлено 2 дня назад</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1118);
// PUG_DEBUG:1118
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') === 3, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1117);
// PUG_DEBUG:1117
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #93D800'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1116);
// PUG_DEBUG:1116
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1115);
// PUG_DEBUG:1115
 ?>Добавлено 3 дня назад</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1122);
// PUG_DEBUG:1122
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_and'](($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') >= 4), function () use (&$object) { return ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') <= 14); }), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1121);
// PUG_DEBUG:1121
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #6496F8'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1120);
// PUG_DEBUG:1120
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1119);
// PUG_DEBUG:1119
 ?>Добавлено недавно</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1126);
// PUG_DEBUG:1126
 }  if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('daysHavePassed') > 14, "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1125);
// PUG_DEBUG:1125
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker'], ['style' => 'background-color: #e85c5c'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1124);
// PUG_DEBUG:1124
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__image-sticker__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1123);
// PUG_DEBUG:1123
 ?>Добавлено давно</span></div><?php } ?><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1130);
// PUG_DEBUG:1130
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card--viewed__status'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1129);
// PUG_DEBUG:1129
 ?>Просмотрено</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1201);
// PUG_DEBUG:1201
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1137);
// PUG_DEBUG:1137
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-type'], ['data-rooms' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('data-rooms', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsGeneral'))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1133);
// PUG_DEBUG:1133
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-type__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1131);
// PUG_DEBUG:1131
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1132);
// PUG_DEBUG:1132
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsGeneral')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1136);
// PUG_DEBUG:1136
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-type__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1134);
// PUG_DEBUG:1134
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1135);
// PUG_DEBUG:1135
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsType')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1156);
// PUG_DEBUG:1156
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-square'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1141);
// PUG_DEBUG:1141
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_or'](($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsGeneral') == "Коттедж"), function () use (&$object) { return $GLOBALS['__jpv_or'](($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsGeneral') == "Дом"), function () use (&$object) { return ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('roomsGeneral') == "Таунхаус"); }); })), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1140);
// PUG_DEBUG:1140
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-floor'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1138);
// PUG_DEBUG:1138
 ?>Этажей: <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1139);
// PUG_DEBUG:1139
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floors')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1147);
// PUG_DEBUG:1147
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-floor'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1142);
// PUG_DEBUG:1142
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1143);
// PUG_DEBUG:1143
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floor')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1144);
// PUG_DEBUG:1144
 ?>/<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1145);
// PUG_DEBUG:1145
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('floors')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1146);
// PUG_DEBUG:1146
 ?> Этаж</span><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1153);
// PUG_DEBUG:1153
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-square__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1148);
// PUG_DEBUG:1148
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1149);
// PUG_DEBUG:1149
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('square')) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1150);
// PUG_DEBUG:1150
 ?> м<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1152);
// PUG_DEBUG:1152
 ?><sup><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1151);
// PUG_DEBUG:1151
 ?>2</sup></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1155);
// PUG_DEBUG:1155
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-square__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1154);
// PUG_DEBUG:1154
 ?>Площадь</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1170);
// PUG_DEBUG:1170
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1159);
// PUG_DEBUG:1159
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1157);
// PUG_DEBUG:1157
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1158);
// PUG_DEBUG:1158
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('price')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1162);
// PUG_DEBUG:1162
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type') == 'Аренда', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1161);
// PUG_DEBUG:1161
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1160);
// PUG_DEBUG:1160
 ?>Руб/Месяц</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1169);
// PUG_DEBUG:1169
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type') == 'Продажа', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1168);
// PUG_DEBUG:1168
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__subtitle'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1163);
// PUG_DEBUG:1163
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1165);
// PUG_DEBUG:1165
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__subtitle-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1164);
// PUG_DEBUG:1164
 ?>Стоимость</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1167);
// PUG_DEBUG:1167
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-cost__subtitle-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1166);
// PUG_DEBUG:1166
 ?>&nbsp;&#8381;</span></span><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1173);
// PUG_DEBUG:1173
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-address'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1171);
// PUG_DEBUG:1171
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1172);
// PUG_DEBUG:1172
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('address')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1181);
// PUG_DEBUG:1181
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-metro'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1175);
// PUG_DEBUG:1175
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('iconMetro') === 'red', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1174);
// PUG_DEBUG:1174
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/img/metro-red.svg'], ['class' => 'svg-sprite-icon icon-metro card__info-metro__icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1177);
// PUG_DEBUG:1177
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('iconMetro') === 'green', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1176);
// PUG_DEBUG:1176
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\Formatter\Format\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/img/metro-green.svg'], ['class' => 'svg-sprite-icon icon-metro card__info-metro__icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1180);
// PUG_DEBUG:1180
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-metro__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1178);
// PUG_DEBUG:1178
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1179);
// PUG_DEBUG:1179
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('metro')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1183);
// PUG_DEBUG:1183
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('favorite'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1182);
// PUG_DEBUG:1182
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-favorites'], ['class' => 'favorites'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1184);
// PUG_DEBUG:1184
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-favorites'], ['class' => 'favorites'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1187);
// PUG_DEBUG:1187
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-construction'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1185);
// PUG_DEBUG:1185
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1186);
// PUG_DEBUG:1186
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type_house')) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1189);
// PUG_DEBUG:1189
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-btn--show-contacts'], ['class' => 'btn'], ['class' => 'btn-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1188);
// PUG_DEBUG:1188
 ?>Показать контакты</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1194);
// PUG_DEBUG:1194
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getOption')('favorite'), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1193);
// PUG_DEBUG:1193
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-btn--add-in-favorites'], ['class' => 'btn'], ['class' => 'btn-sec'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1190);
// PUG_DEBUG:1190
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1192);
// PUG_DEBUG:1192
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1191);
// PUG_DEBUG:1191
 ?>В избранное</span></div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1198);
// PUG_DEBUG:1198
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'card__info-btn--add-in-favorites'], ['class' => 'btn'], ['class' => 'btn-sec'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1195);
// PUG_DEBUG:1195
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1197);
// PUG_DEBUG:1197
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1196);
// PUG_DEBUG:1196
 ?>В избранное</span></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1200);
// PUG_DEBUG:1200
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('href', $GLOBALS['__jpv_plus']("/object/", $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('ID')))], ['target' => '_blank'], ['class' => 'card__info-link'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1199);
// PUG_DEBUG:1199
 ?></a></div></div><?php }extract($__eachScopeVariables); ?></div></div><?php } ?></section></div></section><?php
}; ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1929);
// PUG_DEBUG:1929
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(14);
// PUG_DEBUG:14
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(15);
// PUG_DEBUG:15
 ?><!DOCTYPE html><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1928);
// PUG_DEBUG:1928
 ?><html<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['lang' => 'ru-RU'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(27);
// PUG_DEBUG:27
 ?><head><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(16);
// PUG_DEBUG:16
 ?><meta<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['charset' => 'utf-8'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(19);
// PUG_DEBUG:19
 ?><title><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(17);
// PUG_DEBUG:17
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(18);
// PUG_DEBUG:18
 ?><?= htmlspecialchars((is_bool($_pug_temp = (isset($title) ? $title : null)) ? var_export($_pug_temp, true) : $_pug_temp)) ?></title><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(20);
// PUG_DEBUG:20
 ?><!--[if IE]><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(21);
// PUG_DEBUG:21
 ?><meta<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['http-equiv' => 'X-UA-Compatible'], ['content' => 'IE = edge'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(22);
// PUG_DEBUG:22
 ?><![endif]--><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(23);
// PUG_DEBUG:23
 ?><meta<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['name' => 'viewport'], ['content' => 'width=device-width,initial-scale=1'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(24);
// PUG_DEBUG:24
 ?><meta<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['name' => 'keywords'], ['content' => ''])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(25);
// PUG_DEBUG:25
 ?><link<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['rel' => 'shortcut icon'], ['href' => '/favicon.ico'], ['type' => 'image/x-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(26);
// PUG_DEBUG:26
 ?><link<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['rel' => 'stylesheet'], ['type' => 'text/css'], ['href' => '/dist/assets/css/style.min.css'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></head><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1927);
// PUG_DEBUG:1927
 ?><body<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['id' => 'body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(206);
// PUG_DEBUG:206
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($user, 'getAuth')(), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(205);
// PUG_DEBUG:205
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(92);
// PUG_DEBUG:92
 ?><header<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'header'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(91);
// PUG_DEBUG:91
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'container'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(90);
// PUG_DEBUG:90
 ?><nav<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(29);
// PUG_DEBUG:29
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/'], ['class' => 'main-nav__logo'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(28);
// PUG_DEBUG:28
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

$__pug_mixins[$__pug_mixin_name](false, array(  ), [[false, 'logo-black']], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
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
}); ?></a><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(42);
// PUG_DEBUG:42
 ?><ul<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__sections'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(35);
// PUG_DEBUG:35
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__section'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(32);
// PUG_DEBUG:32
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'isSet')('ZASELITE_TYPE'), function () use (&$cookie) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'getValue')('ZASELITE_TYPE') == 'Аренда'; })), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(31);
// PUG_DEBUG:31
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/assistant'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(30);
// PUG_DEBUG:30
 ?>Арендаторам</a><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(34);
// PUG_DEBUG:34
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/assistant'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(33);
// PUG_DEBUG:33
 ?>Покупателям</a><?php } ?></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(38);
// PUG_DEBUG:38
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__section'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(37);
// PUG_DEBUG:37
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/how-it-work'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(36);
// PUG_DEBUG:36
 ?>Собственникам</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(41);
// PUG_DEBUG:41
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__section'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(40);
// PUG_DEBUG:40
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/find'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(39);
// PUG_DEBUG:39
 ?>Каталог</a></li></ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(43);
// PUG_DEBUG:43
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__favorites'], ['class' => 'favorites'], ['href' => '/profile#favorites'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></a><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(83);
// PUG_DEBUG:83
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__user'], ['class' => 'drop-down-list'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(47);
// PUG_DEBUG:47
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__user-avatar'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(45);
// PUG_DEBUG:45
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($user, 'getAvatar')(), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(44);
// PUG_DEBUG:44
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('src', $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($user, 'getAvatar')())])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(46);
// PUG_DEBUG:46
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/img/avatar.svg'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php } ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(81);
// PUG_DEBUG:81
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__user-name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(49);
// PUG_DEBUG:49
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__user-name__title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(48);
// PUG_DEBUG:48
 ?>Профиль</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(80);
// PUG_DEBUG:80
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__user-name__options'], ['class' => 'drop-down-list__content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(50);
// PUG_DEBUG:50
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'dropdown-pointer'], ['class' => 'dropdown-pointer-before'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(78);
// PUG_DEBUG:78
 ?><ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(53);
// PUG_DEBUG:53
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(52);
// PUG_DEBUG:52
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/profile'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(51);
// PUG_DEBUG:51
 ?>Профиль</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(57);
// PUG_DEBUG:57
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(54);
// PUG_DEBUG:54
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(56);
// PUG_DEBUG:56
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/profile#history'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(55);
// PUG_DEBUG:55
 ?>История просмотров</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(61);
// PUG_DEBUG:61
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(58);
// PUG_DEBUG:58
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(60);
// PUG_DEBUG:60
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/profile#settings'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(59);
// PUG_DEBUG:59
 ?>Настройки</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(65);
// PUG_DEBUG:65
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(62);
// PUG_DEBUG:62
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(64);
// PUG_DEBUG:64
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/profile#payments'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(63);
// PUG_DEBUG:63
 ?>Платежи</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(69);
// PUG_DEBUG:69
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(66);
// PUG_DEBUG:66
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(68);
// PUG_DEBUG:68
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/profile#subscriptions'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(67);
// PUG_DEBUG:67
 ?>Подписки на рассылку</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(73);
// PUG_DEBUG:73
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(70);
// PUG_DEBUG:70
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(72);
// PUG_DEBUG:72
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['id' => 'tab-filter-autoselect'], ['class' => 'j-filter-autoselect'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(71);
// PUG_DEBUG:71
 ?>Фильтр автоподбра</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(77);
// PUG_DEBUG:77
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(74);
// PUG_DEBUG:74
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(76);
// PUG_DEBUG:76
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/logout'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(75);
// PUG_DEBUG:75
 ?>Выход</a></li></ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(79);
// PUG_DEBUG:79
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'dropdown-pointer'], ['class' => 'dropdown-pointer-after'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(82);
// PUG_DEBUG:82
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

$__pug_mixins[$__pug_mixin_name](false, array(  ), [[false, 'arrow-down'], [false, 'main-nav__user-arrow-down']], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
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
}); ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(85);
// PUG_DEBUG:85
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/rent'], ['class' => 'btn'], ['class' => 'main-nav__btn'], ['class' => 'btn-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(84);
// PUG_DEBUG:84
 ?>+ Разместить</a><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(89);
// PUG_DEBUG:89
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'burger-menu'], ['id' => 'burger-menu'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(86);
// PUG_DEBUG:86
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'burger-menu_line-up'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(87);
// PUG_DEBUG:87
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'burger-menu_line-mid'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(88);
// PUG_DEBUG:88
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'burger-menu_line-down'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div></nav></div></header><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(145);
// PUG_DEBUG:145
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu'], ['id' => 'mob-menu'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(118);
// PUG_DEBUG:118
 ?><ul<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_first-list'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(95);
// PUG_DEBUG:95
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_first-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(94);
// PUG_DEBUG:94
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/profile'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(93);
// PUG_DEBUG:93
 ?>Профиль</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(98);
// PUG_DEBUG:98
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_first-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(97);
// PUG_DEBUG:97
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/profile#history'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(96);
// PUG_DEBUG:96
 ?>История просмотров</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(101);
// PUG_DEBUG:101
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_first-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(100);
// PUG_DEBUG:100
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/profile#settings'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(99);
// PUG_DEBUG:99
 ?>Настройки</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(104);
// PUG_DEBUG:104
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_first-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(103);
// PUG_DEBUG:103
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/profile#payments'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(102);
// PUG_DEBUG:102
 ?>Платежи</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(107);
// PUG_DEBUG:107
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_first-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(106);
// PUG_DEBUG:106
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/profile#subscriptions'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(105);
// PUG_DEBUG:105
 ?>Подписки на рассылку</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(110);
// PUG_DEBUG:110
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_first-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(109);
// PUG_DEBUG:109
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/profile#favorites'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(108);
// PUG_DEBUG:108
 ?>Избранное</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(114);
// PUG_DEBUG:114
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_first-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(111);
// PUG_DEBUG:111
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(113);
// PUG_DEBUG:113
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['id' => 'tab-filter-autoselect'], ['class' => 'j-filter-autoselect'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(112);
// PUG_DEBUG:112
 ?>Фильтр автоподбра</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(117);
// PUG_DEBUG:117
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_first-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(116);
// PUG_DEBUG:116
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/logout'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(115);
// PUG_DEBUG:115
 ?>Выход</a></li></ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(144);
// PUG_DEBUG:144
 ?><ul<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_last-list'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(124);
// PUG_DEBUG:124
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_last-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(121);
// PUG_DEBUG:121
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'isSet')('ZASELITE_TYPE'), function () use (&$cookie) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'getValue')('ZASELITE_TYPE') == 'Аренда'; })), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(120);
// PUG_DEBUG:120
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/assistant'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(119);
// PUG_DEBUG:119
 ?>Арендаторам</a><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(123);
// PUG_DEBUG:123
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/assistant'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(122);
// PUG_DEBUG:122
 ?>Покупателям</a><?php } ?></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(127);
// PUG_DEBUG:127
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_last-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(126);
// PUG_DEBUG:126
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/assistant'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(125);
// PUG_DEBUG:125
 ?>Собственникам</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(130);
// PUG_DEBUG:130
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_last-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(129);
// PUG_DEBUG:129
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/find'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(128);
// PUG_DEBUG:128
 ?>Каталог</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(133);
// PUG_DEBUG:133
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_last-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(132);
// PUG_DEBUG:132
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/contacts'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(131);
// PUG_DEBUG:131
 ?>Контакты</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(143);
// PUG_DEBUG:143
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_last-list__li'], ['class' => 'location'], ['class' => 'select'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(137);
// PUG_DEBUG:137
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'select__header'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(136);
// PUG_DEBUG:136
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'select__current'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(134);
// PUG_DEBUG:134
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(135);
// PUG_DEBUG:135
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($locator, 'getCity')()) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(142);
// PUG_DEBUG:142
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'select__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(141);
// PUG_DEBUG:141
 ?><?php $__eachScopeVariables = ['city' => isset($city) ? $city : null];foreach ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($locator, 'getCities')() as $city) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(140);
// PUG_DEBUG:140
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'select__item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(138);
// PUG_DEBUG:138
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(139);
// PUG_DEBUG:139
 ?><?= htmlspecialchars((is_bool($_pug_temp = (isset($city) ? $city : null)) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div><?php }extract($__eachScopeVariables); ?></div></li></ul></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(146);
// PUG_DEBUG:146
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'head-empty'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(204);
// PUG_DEBUG:204
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(203);
// PUG_DEBUG:203
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modal-filter-autoselect'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(202);
// PUG_DEBUG:202
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(150);
// PUG_DEBUG:150
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(147);
// PUG_DEBUG:147
 ?>Фильтр автоподбора<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(149);
// PUG_DEBUG:149
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(148);
// PUG_DEBUG:148
 ?>&#215;</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(201);
// PUG_DEBUG:201
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(200);
// PUG_DEBUG:200
 ?><form<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form'], ['class' => 'modal-filter-autoselect-form'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(162);
// PUG_DEBUG:162
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(152);
// PUG_DEBUG:152
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(151);
// PUG_DEBUG:151
 ?>Вы ищете</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(161);
// PUG_DEBUG:161
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(156);
// PUG_DEBUG:156
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(153);
// PUG_DEBUG:153
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'filter-autoselect__flat'], ['name' => 'find'], ['checked' => true], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(155);
// PUG_DEBUG:155
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'filter-autoselect__flat'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(154);
// PUG_DEBUG:154
 ?>Квартира</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(160);
// PUG_DEBUG:160
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(157);
// PUG_DEBUG:157
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'filter-autoselect__room'], ['name' => 'find'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(159);
// PUG_DEBUG:159
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'filter-autoselect__room'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(158);
// PUG_DEBUG:158
 ?>Комната</label></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(182);
// PUG_DEBUG:182
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(164);
// PUG_DEBUG:164
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(163);
// PUG_DEBUG:163
 ?>Комнат в квартире</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(181);
// PUG_DEBUG:181
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_checkbox-btn-row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(168);
// PUG_DEBUG:168
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_checkbox-btn-wrap'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(165);
// PUG_DEBUG:165
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'filter-autoselect__number-rooms1'], ['name' => 'number_rooms1'], ['class' => 'inp-checkbox-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(167);
// PUG_DEBUG:167
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'filter-autoselect__number-rooms1'], ['class' => 'checkbox-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(166);
// PUG_DEBUG:166
 ?>1</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(172);
// PUG_DEBUG:172
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_checkbox-btn-wrap'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(169);
// PUG_DEBUG:169
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'filter-autoselect__number-rooms2'], ['name' => 'number_rooms2'], ['class' => 'inp-checkbox-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(171);
// PUG_DEBUG:171
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'filter-autoselect__number-rooms2'], ['class' => 'checkbox-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(170);
// PUG_DEBUG:170
 ?>2</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(176);
// PUG_DEBUG:176
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_checkbox-btn-wrap'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(173);
// PUG_DEBUG:173
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'filter-autoselect__number-rooms3'], ['name' => 'number_rooms3'], ['class' => 'inp-checkbox-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(175);
// PUG_DEBUG:175
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'filter-autoselect__number-rooms3'], ['class' => 'checkbox-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(174);
// PUG_DEBUG:174
 ?>3</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(180);
// PUG_DEBUG:180
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_checkbox-btn-wrap'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(177);
// PUG_DEBUG:177
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'filter-autoselect__number-rooms4'], ['name' => 'number_rooms4'], ['class' => 'inp-checkbox-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(179);
// PUG_DEBUG:179
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'filter-autoselect__number-rooms4'], ['class' => 'checkbox-btn'], ['class' => 'checkbox-btn_last'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(178);
// PUG_DEBUG:178
 ?>4+</label></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(188);
// PUG_DEBUG:188
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(184);
// PUG_DEBUG:184
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(183);
// PUG_DEBUG:183
 ?>Укажите бюджет, руб. в месяц</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(187);
// PUG_DEBUG:187
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(185);
// PUG_DEBUG:185
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'text'], ['name' => 'min_cost'], ['placeholder' => 'От'], ['class' => 'min-cost'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(186);
// PUG_DEBUG:186
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'text'], ['name' => 'max_cost'], ['placeholder' => 'До'], ['class' => 'max-cost'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(194);
// PUG_DEBUG:194
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(190);
// PUG_DEBUG:190
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(189);
// PUG_DEBUG:189
 ?>Укажите метро и район</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(193);
// PUG_DEBUG:193
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--row'], ['class' => 'form-group_data--selects'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(191);
// PUG_DEBUG:191
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'text'], ['name' => 'metro'], ['placeholder' => 'Любое метро'], ['class' => 'input-metro'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(192);
// PUG_DEBUG:192
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'text'], ['name' => 'district'], ['placeholder' => 'Любой район'], ['class' => 'input-district'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(199);
// PUG_DEBUG:199
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(198);
// PUG_DEBUG:198
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(197);
// PUG_DEBUG:197
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(195);
// PUG_DEBUG:195
 ?>Сохранить<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(196);
// PUG_DEBUG:196
 ?></button></div></div></form></div></div></div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(373);
// PUG_DEBUG:373
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(245);
// PUG_DEBUG:245
 ?><header<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'header'], ['class' => 'no-auth'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(244);
// PUG_DEBUG:244
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'container'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(243);
// PUG_DEBUG:243
 ?><nav<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(208);
// PUG_DEBUG:208
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/'], ['class' => 'main-nav__logo'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(207);
// PUG_DEBUG:207
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

$__pug_mixins[$__pug_mixin_name](false, array(  ), [[false, 'logo-black']], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
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
}); ?></a><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(221);
// PUG_DEBUG:221
 ?><ul<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__sections'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(214);
// PUG_DEBUG:214
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__section'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(211);
// PUG_DEBUG:211
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'isSet')('ZASELITE_TYPE'), function () use (&$cookie) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'getValue')('ZASELITE_TYPE') == 'Аренда'; })), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(210);
// PUG_DEBUG:210
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/assistant'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(209);
// PUG_DEBUG:209
 ?>Арендаторам</a><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(213);
// PUG_DEBUG:213
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/assistant'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(212);
// PUG_DEBUG:212
 ?>Покупателям</a><?php } ?></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(217);
// PUG_DEBUG:217
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__section'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(216);
// PUG_DEBUG:216
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/how-it-work'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(215);
// PUG_DEBUG:215
 ?>Собственникам</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(220);
// PUG_DEBUG:220
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__section'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(219);
// PUG_DEBUG:219
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/find'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(218);
// PUG_DEBUG:218
 ?>Каталог</a></li></ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(231);
// PUG_DEBUG:231
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'location'], ['class' => 'select'], ['class' => 'selectCity'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(225);
// PUG_DEBUG:225
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'select__header'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(224);
// PUG_DEBUG:224
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'select__current'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(222);
// PUG_DEBUG:222
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(223);
// PUG_DEBUG:223
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($locator, 'getCity')()) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(230);
// PUG_DEBUG:230
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'select__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(229);
// PUG_DEBUG:229
 ?><?php $__eachScopeVariables = ['city' => isset($city) ? $city : null];foreach ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($locator, 'getCities')() as $city) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(228);
// PUG_DEBUG:228
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'select__item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(226);
// PUG_DEBUG:226
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(227);
// PUG_DEBUG:227
 ?><?= htmlspecialchars((is_bool($_pug_temp = (isset($city) ? $city : null)) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div><?php }extract($__eachScopeVariables); ?></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(236);
// PUG_DEBUG:236
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/rent'], ['class' => 'main-nav__btn'], ['class' => 'btn'], ['class' => 'btn-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(233);
// PUG_DEBUG:233
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__btn-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(232);
// PUG_DEBUG:232
 ?>Разместить объявление</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(235);
// PUG_DEBUG:235
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__btn-text--min'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(234);
// PUG_DEBUG:234
 ?>+ Сдать</span></a><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(238);
// PUG_DEBUG:238
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '#'], ['class' => 'main-nav__login'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(237);
// PUG_DEBUG:237
 ?>Войти</a><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(242);
// PUG_DEBUG:242
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'burger-menu'], ['id' => 'burger-menu'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(239);
// PUG_DEBUG:239
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'burger-menu_line-up'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(240);
// PUG_DEBUG:240
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'burger-menu_line-mid'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(241);
// PUG_DEBUG:241
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'burger-menu_line-down'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div></nav></div></header><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(305);
// PUG_DEBUG:305
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(304);
// PUG_DEBUG:304
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modal-log-in'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(303);
// PUG_DEBUG:303
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(253);
// PUG_DEBUG:253
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(250);
// PUG_DEBUG:250
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-panel'], ['class' => 'tab-panel--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(247);
// PUG_DEBUG:247
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-panel_title'], ['data-tab-panel' => 'log-in'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(246);
// PUG_DEBUG:246
 ?>Вход</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(249);
// PUG_DEBUG:249
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-panel_title'], ['class' => 'tab-panel_title-last'], ['data-tab-panel' => 'register'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(248);
// PUG_DEBUG:248
 ?>Регистрация</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(252);
// PUG_DEBUG:252
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(251);
// PUG_DEBUG:251
 ?>&#215;</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(302);
// PUG_DEBUG:302
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(301);
// PUG_DEBUG:301
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-all-content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(267);
// PUG_DEBUG:267
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-content'], ['class' => 'tab-content--log-in'], ['data-tab-content' => 'log-in'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(266);
// PUG_DEBUG:266
 ?><form<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form'], ['class' => 'log-in-form'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(261);
// PUG_DEBUG:261
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(256);
// PUG_DEBUG:256
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--col'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(254);
// PUG_DEBUG:254
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'tel'], ['name' => 'phone'], ['placeholder' => 'Введите телефон'], ['class' => 'input-tel'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(255);
// PUG_DEBUG:255
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'password'], ['name' => 'password'], ['placeholder' => 'Введите пароль'], ['class' => 'input-pass'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(260);
// PUG_DEBUG:260
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(257);
// PUG_DEBUG:257
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(259);
// PUG_DEBUG:259
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '#'], ['class' => 'remind-password'], ['class' => 'link--main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(258);
// PUG_DEBUG:258
 ?>Забыли пароль?</a></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(265);
// PUG_DEBUG:265
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(264);
// PUG_DEBUG:264
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(263);
// PUG_DEBUG:263
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(262);
// PUG_DEBUG:262
 ?>Войти</button></div></div></form></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(300);
// PUG_DEBUG:300
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-content'], ['class' => 'tab-content--register'], ['data-tab-content' => 'register'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(299);
// PUG_DEBUG:299
 ?><form<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form'], ['class' => 'register-form'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(277);
// PUG_DEBUG:277
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(276);
// PUG_DEBUG:276
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(271);
// PUG_DEBUG:271
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(268);
// PUG_DEBUG:268
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'renter'], ['name' => 'roleId'], ['value' => '1'], ['checked' => true], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(270);
// PUG_DEBUG:270
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'renter'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(269);
// PUG_DEBUG:269
 ?>Сниму</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(275);
// PUG_DEBUG:275
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(272);
// PUG_DEBUG:272
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'owner'], ['name' => 'roleId'], ['value' => '2'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(274);
// PUG_DEBUG:274
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'owner'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(273);
// PUG_DEBUG:273
 ?>Куплю</label></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(282);
// PUG_DEBUG:282
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(281);
// PUG_DEBUG:281
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--col'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(278);
// PUG_DEBUG:278
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'tel'], ['name' => 'phone'], ['placeholder' => 'Введите телефон'], ['class' => 'input-tel'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(279);
// PUG_DEBUG:279
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'password'], ['name' => 'password'], ['placeholder' => 'Введите пароль'], ['class' => 'input-pass'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(280);
// PUG_DEBUG:280
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'password'], ['name' => 'password2'], ['placeholder' => 'Повторите пароль'], ['class' => 'input-repeat-pass'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(293);
// PUG_DEBUG:293
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(292);
// PUG_DEBUG:292
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'checkbox-container'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(291);
// PUG_DEBUG:291
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(283);
// PUG_DEBUG:283
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'agree'], ['name' => 'agreement'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(290);
// PUG_DEBUG:290
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'agree'], ['class' => 'checkbox'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(284);
// PUG_DEBUG:284
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(285);
// PUG_DEBUG:285
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-icon'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(289);
// PUG_DEBUG:289
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'checkbox-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(286);
// PUG_DEBUG:286
 ?>Я согласен с <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(288);
// PUG_DEBUG:288
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/terms-of-service'], ['class' => 'link--main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(287);
// PUG_DEBUG:287
 ?>пользовательским соглашением</a></div></label></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(298);
// PUG_DEBUG:298
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(297);
// PUG_DEBUG:297
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(296);
// PUG_DEBUG:296
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(294);
// PUG_DEBUG:294
 ?>Далее<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(295);
// PUG_DEBUG:295
 ?></button></div></div></form></div></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(340);
// PUG_DEBUG:340
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(339);
// PUG_DEBUG:339
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modal-password-recovery'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(338);
// PUG_DEBUG:338
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(311);
// PUG_DEBUG:311
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(308);
// PUG_DEBUG:308
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-panel'], ['class' => 'tab-panel--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(307);
// PUG_DEBUG:307
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-panel_title'], ['data-tab-panel' => 'recovery'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(306);
// PUG_DEBUG:306
 ?>Восстановление пароля</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(310);
// PUG_DEBUG:310
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(309);
// PUG_DEBUG:309
 ?>&#215;</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(337);
// PUG_DEBUG:337
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(336);
// PUG_DEBUG:336
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-all-content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(335);
// PUG_DEBUG:335
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'tab-content'], ['class' => 'tab-content--recovery'], ['data-tab-content' => 'recovery'], ['class' => 'active'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(334);
// PUG_DEBUG:334
 ?><form<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form'], ['class' => 'password-recovery-form'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(318);
// PUG_DEBUG:318
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'], ['class' => 'password-recovery_step1'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(313);
// PUG_DEBUG:313
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(312);
// PUG_DEBUG:312
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'tel'], ['name' => 'phone'], ['placeholder' => 'Введите телефон'], ['class' => 'input-tel'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(315);
// PUG_DEBUG:315
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(314);
// PUG_DEBUG:314
 ?>На ваш телефон будет отправлен код</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(317);
// PUG_DEBUG:317
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(316);
// PUG_DEBUG:316
 ?>Отправить код</button></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(325);
// PUG_DEBUG:325
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'], ['class' => 'password-recovery_step2'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(320);
// PUG_DEBUG:320
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(319);
// PUG_DEBUG:319
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'text'], ['name' => 'code'], ['placeholder' => 'Введите код'], ['class' => 'input-code'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(322);
// PUG_DEBUG:322
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(321);
// PUG_DEBUG:321
 ?>На ваш телефон отправлен код</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(324);
// PUG_DEBUG:324
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(323);
// PUG_DEBUG:323
 ?>Проверить код</button></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(331);
// PUG_DEBUG:331
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'], ['class' => 'password-recovery_step3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(328);
// PUG_DEBUG:328
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--col'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(326);
// PUG_DEBUG:326
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'password'], ['name' => 'password'], ['placeholder' => 'Введите новый пароль'], ['class' => 'input-pass'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(327);
// PUG_DEBUG:327
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'password'], ['name' => 'password2'], ['placeholder' => 'Повторите пароль'], ['class' => 'input-pass'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(330);
// PUG_DEBUG:330
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(329);
// PUG_DEBUG:329
 ?>Изменить</button></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(332);
// PUG_DEBUG:332
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'hidden'], ['name' => 'step'], ['value' => '1'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(333);
// PUG_DEBUG:333
 ?></form></div></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(371);
// PUG_DEBUG:371
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu'], ['id' => 'mob-menu'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(344);
// PUG_DEBUG:344
 ?><ul<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_first-list'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(343);
// PUG_DEBUG:343
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_first-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(342);
// PUG_DEBUG:342
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'main-nav__login'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(341);
// PUG_DEBUG:341
 ?>Вход / Регистрация</div></li></ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(370);
// PUG_DEBUG:370
 ?><ul<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_last-list'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(350);
// PUG_DEBUG:350
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_last-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(347);
// PUG_DEBUG:347
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'isSet')('ZASELITE_TYPE'), function () use (&$cookie) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'getValue')('ZASELITE_TYPE') == 'Аренда'; })), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(346);
// PUG_DEBUG:346
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/assistant'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(345);
// PUG_DEBUG:345
 ?>Арендаторам</a><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(349);
// PUG_DEBUG:349
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/assistant'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(348);
// PUG_DEBUG:348
 ?>Покупателям</a><?php } ?></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(353);
// PUG_DEBUG:353
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_last-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(352);
// PUG_DEBUG:352
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/how-it-work'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(351);
// PUG_DEBUG:351
 ?>Собственникам</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(356);
// PUG_DEBUG:356
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_last-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(355);
// PUG_DEBUG:355
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/find'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(354);
// PUG_DEBUG:354
 ?>Каталог</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(359);
// PUG_DEBUG:359
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_last-list__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(358);
// PUG_DEBUG:358
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/contacts'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(357);
// PUG_DEBUG:357
 ?>Контакты</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(369);
// PUG_DEBUG:369
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'mob-menu_last-list__li'], ['class' => 'location'], ['class' => 'select'], ['class' => 'selectCity'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(363);
// PUG_DEBUG:363
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'select__header'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(362);
// PUG_DEBUG:362
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'select__current'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(360);
// PUG_DEBUG:360
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(361);
// PUG_DEBUG:361
 ?><?= htmlspecialchars((is_bool($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($locator, 'getCity')()) ? var_export($_pug_temp, true) : $_pug_temp)) ?></span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(368);
// PUG_DEBUG:368
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'select__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(367);
// PUG_DEBUG:367
 ?><?php $__eachScopeVariables = ['city' => isset($city) ? $city : null];foreach ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($locator, 'getCities')() as $city) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(366);
// PUG_DEBUG:366
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'select__item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(364);
// PUG_DEBUG:364
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(365);
// PUG_DEBUG:365
 ?><?= htmlspecialchars((is_bool($_pug_temp = (isset($city) ? $city : null)) ? var_export($_pug_temp, true) : $_pug_temp)) ?></div><?php }extract($__eachScopeVariables); ?></div></li></ul></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(372);
// PUG_DEBUG:372
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'head-empty'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(391);
// PUG_DEBUG:391
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(390);
// PUG_DEBUG:390
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modal-views-slider'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(389);
// PUG_DEBUG:389
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(377);
// PUG_DEBUG:377
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(374);
// PUG_DEBUG:374
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(376);
// PUG_DEBUG:376
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(375);
// PUG_DEBUG:375
 ?>&#215;</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(388);
// PUG_DEBUG:388
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(387);
// PUG_DEBUG:387
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'views-slider-wrapper'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(381);
// PUG_DEBUG:381
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'views-slider'], ['data-snap-ignore' => 'true'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(380);
// PUG_DEBUG:380
 ?><?php $__eachScopeVariables = ['image' => isset($image) ? $image : null];foreach ($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('images') as $image) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(379);
// PUG_DEBUG:379
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'views-slider__imgs'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(378);
// PUG_DEBUG:378
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => $pugModule['Phug\\Formatter\\Format\\HtmlFormat::array_escape']('src', (isset($image) ? $image : null))], ['alt' => 'Объект'], ['class' => 'views-slider__imgs-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php }extract($__eachScopeVariables); ?></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(386);
// PUG_DEBUG:386
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'views-slider__counter'], ['class' => 'slider__counter'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(382);
// PUG_DEBUG:382
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'views-slider__counter-current'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(384);
// PUG_DEBUG:384
 ?><span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(383);
// PUG_DEBUG:383
 ?>/</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(385);
// PUG_DEBUG:385
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'views-slider__counter-all'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span></div></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1213);
// PUG_DEBUG:1213
 ?><?php if (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type') == 'Аренда', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1212);
// PUG_DEBUG:1212
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
$__pug_mixin_name = 'object-container';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, array(  ), [[false, 'rent']], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
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
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1215);
// PUG_DEBUG:1215
 }  elseif (method_exists($_pug_temp = $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($object, 'getProperty')('type') == 'Продажа', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1214);
// PUG_DEBUG:1214
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
$__pug_mixin_name = 'object-container';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, array(  ), [[false, 'buy']], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
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
}); ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1245);
// PUG_DEBUG:1245
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1244);
// PUG_DEBUG:1244
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modals-object'], ['class' => 'modal-verified'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1243);
// PUG_DEBUG:1243
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1220);
// PUG_DEBUG:1220
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1216);
// PUG_DEBUG:1216
 ?>Что значит «Объявление прошло проверку»<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1218);
// PUG_DEBUG:1218
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1217);
// PUG_DEBUG:1217
 ?>&#215;</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1219);
// PUG_DEBUG:1219
 ?><hr></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1242);
// PUG_DEBUG:1242
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1241);
// PUG_DEBUG:1241
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1225);
// PUG_DEBUG:1225
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1222);
// PUG_DEBUG:1222
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1221);
// PUG_DEBUG:1221
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1224);
// PUG_DEBUG:1224
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1223);
// PUG_DEBUG:1223
 ?>Номер владельца объявления проверяется по базам агентов на совпадение.</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1230);
// PUG_DEBUG:1230
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1227);
// PUG_DEBUG:1227
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1226);
// PUG_DEBUG:1226
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1229);
// PUG_DEBUG:1229
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1228);
// PUG_DEBUG:1228
 ?>Бот с искусственным интеллектом по заданному алгоритму анализирует объявление по ряду параметров на соответствие актуальности.</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1235);
// PUG_DEBUG:1235
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1232);
// PUG_DEBUG:1232
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1231);
// PUG_DEBUG:1231
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1234);
// PUG_DEBUG:1234
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1233);
// PUG_DEBUG:1233
 ?>Проверка звонком оператора, дополняет детали предлагаемого объекта и ведёт заключительные шаги, на выявление посредников по обученной технологии.</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1240);
// PUG_DEBUG:1240
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item--last'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1237);
// PUG_DEBUG:1237
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1236);
// PUG_DEBUG:1236
 ?>!</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1239);
// PUG_DEBUG:1239
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1238);
// PUG_DEBUG:1238
 ?>Важно: никогда не вносите предоплату или залог, прежде чем осмотрите квартиру и проверите документы собственника.</div></div></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1270);
// PUG_DEBUG:1270
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1269);
// PUG_DEBUG:1269
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modals-object'], ['class' => 'modal-checked-bot'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1268);
// PUG_DEBUG:1268
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1250);
// PUG_DEBUG:1250
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1246);
// PUG_DEBUG:1246
 ?>Что значит «Проверено ботом» <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1248);
// PUG_DEBUG:1248
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1247);
// PUG_DEBUG:1247
 ?>&#215;</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1249);
// PUG_DEBUG:1249
 ?><hr></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1267);
// PUG_DEBUG:1267
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1266);
// PUG_DEBUG:1266
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1255);
// PUG_DEBUG:1255
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1252);
// PUG_DEBUG:1252
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1251);
// PUG_DEBUG:1251
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1254);
// PUG_DEBUG:1254
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1253);
// PUG_DEBUG:1253
 ?>Искусственный интеллект проверяет соотношение цены относительно расположению и площади объекта.</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1260);
// PUG_DEBUG:1260
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1257);
// PUG_DEBUG:1257
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1256);
// PUG_DEBUG:1256
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1259);
// PUG_DEBUG:1259
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1258);
// PUG_DEBUG:1258
 ?>Анализирует базу проверенных объектов и сопоставляет с проверяемым обьектом.</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1265);
// PUG_DEBUG:1265
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item--last'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1262);
// PUG_DEBUG:1262
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1261);
// PUG_DEBUG:1261
 ?>!</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1264);
// PUG_DEBUG:1264
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1263);
// PUG_DEBUG:1263
 ?>Важно: никогда не вносите предоплату или залог, прежде чем осмотрите квартиру и проверите документы собственника.</div></div></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1300);
// PUG_DEBUG:1300
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1299);
// PUG_DEBUG:1299
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modals-object'], ['class' => 'modal-checked-moderator'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1298);
// PUG_DEBUG:1298
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1275);
// PUG_DEBUG:1275
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1271);
// PUG_DEBUG:1271
 ?>Что значит «Проверено модератором»<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1273);
// PUG_DEBUG:1273
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1272);
// PUG_DEBUG:1272
 ?>&#215;</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1274);
// PUG_DEBUG:1274
 ?><hr></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1297);
// PUG_DEBUG:1297
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1296);
// PUG_DEBUG:1296
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1280);
// PUG_DEBUG:1280
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1277);
// PUG_DEBUG:1277
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1276);
// PUG_DEBUG:1276
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1279);
// PUG_DEBUG:1279
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1278);
// PUG_DEBUG:1278
 ?>Отдел модерации обзванивает предложение, чтобы убедиться, что квартиру сдаёт именно собственник и формирует дополнительную информацию об объекте.</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1285);
// PUG_DEBUG:1285
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1282);
// PUG_DEBUG:1282
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1281);
// PUG_DEBUG:1281
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1284);
// PUG_DEBUG:1284
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1283);
// PUG_DEBUG:1283
 ?>В случае подозрения модератор запрашивает свидетельство о собственности на квартиру.</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1290);
// PUG_DEBUG:1290
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1287);
// PUG_DEBUG:1287
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1286);
// PUG_DEBUG:1286
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1289);
// PUG_DEBUG:1289
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1288);
// PUG_DEBUG:1288
 ?>После публикации, каждые 3 дня объявление проверяется на актуальность.</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1295);
// PUG_DEBUG:1295
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item--last'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1292);
// PUG_DEBUG:1292
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1291);
// PUG_DEBUG:1291
 ?>!</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1294);
// PUG_DEBUG:1294
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1293);
// PUG_DEBUG:1293
 ?>Важно: никогда не вносите предоплату или залог, прежде чем осмотрите квартиру и проверите документы собственника.</div></div></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1320);
// PUG_DEBUG:1320
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1319);
// PUG_DEBUG:1319
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modals-object'], ['class' => 'modal-added'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1318);
// PUG_DEBUG:1318
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1305);
// PUG_DEBUG:1305
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1301);
// PUG_DEBUG:1301
 ?>Что значит «Объект добавлен на сайт»<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1303);
// PUG_DEBUG:1303
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1302);
// PUG_DEBUG:1302
 ?>&#215;</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1304);
// PUG_DEBUG:1304
 ?><hr></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1317);
// PUG_DEBUG:1317
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1316);
// PUG_DEBUG:1316
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1310);
// PUG_DEBUG:1310
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1307);
// PUG_DEBUG:1307
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1306);
// PUG_DEBUG:1306
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1309);
// PUG_DEBUG:1309
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1308);
// PUG_DEBUG:1308
 ?>Данное объявление можно посмотреть на сайте, но оно ещё не проверено. После активации подписки Вам будет открыта вся проверенная база.</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1315);
// PUG_DEBUG:1315
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item--last'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1312);
// PUG_DEBUG:1312
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__number'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1311);
// PUG_DEBUG:1311
 ?>!</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1314);
// PUG_DEBUG:1314
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-enumeration_item__text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1313);
// PUG_DEBUG:1313
 ?>Важно: никогда не вносите предоплату или залог, прежде чем осмотрите квартиру и проверите документы собственника.</div></div></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1333);
// PUG_DEBUG:1333
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1332);
// PUG_DEBUG:1332
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modal-contact'], ['class' => 'modal--not-full'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1331);
// PUG_DEBUG:1331
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1325);
// PUG_DEBUG:1325
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1321);
// PUG_DEBUG:1321
 ?>Контактные данные<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1323);
// PUG_DEBUG:1323
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1322);
// PUG_DEBUG:1322
 ?>&#215;</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1324);
// PUG_DEBUG:1324
 ?><hr></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1330);
// PUG_DEBUG:1330
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1326);
// PUG_DEBUG:1326
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body-phone'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1327);
// PUG_DEBUG:1327
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body-name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1329);
// PUG_DEBUG:1329
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1328);
// PUG_DEBUG:1328
 ?>Позвонить</a></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1585);
// PUG_DEBUG:1585
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1584);
// PUG_DEBUG:1584
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modal-save-on-rent'], ['class' => 'modal-save-on-rent--rent'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1583);
// PUG_DEBUG:1583
 ?><form<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form'], ['class' => 'form-quize'], ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1363);
// PUG_DEBUG:1363
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1362);
// PUG_DEBUG:1362
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-1'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1350);
// PUG_DEBUG:1350
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1334);
// PUG_DEBUG:1334
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1336);
// PUG_DEBUG:1336
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-btn'], ['class' => 'btn'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1335);
// PUG_DEBUG:1335
 ?>Закрыть</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1349);
// PUG_DEBUG:1349
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1338);
// PUG_DEBUG:1338
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1337);
// PUG_DEBUG:1337
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1340);
// PUG_DEBUG:1340
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1339);
// PUG_DEBUG:1339
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1342);
// PUG_DEBUG:1342
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1341);
// PUG_DEBUG:1341
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1344);
// PUG_DEBUG:1344
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1343);
// PUG_DEBUG:1343
 ?>4</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1346);
// PUG_DEBUG:1346
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1345);
// PUG_DEBUG:1345
 ?>5</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1348);
// PUG_DEBUG:1348
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1347);
// PUG_DEBUG:1347
 ?>6</div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1357);
// PUG_DEBUG:1357
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1352);
// PUG_DEBUG:1352
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1351);
// PUG_DEBUG:1351
 ?>Сэкономь на стоимости арендной платы до 6 000 рублей</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1354);
// PUG_DEBUG:1354
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'simple-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1353);
// PUG_DEBUG:1353
 ?>Так уже делают все!</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1356);
// PUG_DEBUG:1356
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'simple-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1355);
// PUG_DEBUG:1355
 ?>Ответь на простые вопросы и получи скидку</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1361);
// PUG_DEBUG:1361
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__footer'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1360);
// PUG_DEBUG:1360
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['class' => 'btn-next'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1358);
// PUG_DEBUG:1358
 ?>Начать<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1359);
// PUG_DEBUG:1359
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1403);
// PUG_DEBUG:1403
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1402);
// PUG_DEBUG:1402
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-2'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1380);
// PUG_DEBUG:1380
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1364);
// PUG_DEBUG:1364
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1366);
// PUG_DEBUG:1366
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-btn'], ['class' => 'btn-back'], ['class' => 'btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1365);
// PUG_DEBUG:1365
 ?>Назад</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1379);
// PUG_DEBUG:1379
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1368);
// PUG_DEBUG:1368
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1367);
// PUG_DEBUG:1367
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1370);
// PUG_DEBUG:1370
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1369);
// PUG_DEBUG:1369
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1372);
// PUG_DEBUG:1372
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1371);
// PUG_DEBUG:1371
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1374);
// PUG_DEBUG:1374
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1373);
// PUG_DEBUG:1373
 ?>4</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1376);
// PUG_DEBUG:1376
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1375);
// PUG_DEBUG:1375
 ?>5</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1378);
// PUG_DEBUG:1378
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1377);
// PUG_DEBUG:1377
 ?>6</div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1397);
// PUG_DEBUG:1397
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1382);
// PUG_DEBUG:1382
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1381);
// PUG_DEBUG:1381
 ?>Сколько раз вы снимали квартиру?</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1396);
// PUG_DEBUG:1396
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1395);
// PUG_DEBUG:1395
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1386);
// PUG_DEBUG:1386
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1383);
// PUG_DEBUG:1383
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'rented_appartment1'], ['name' => 'rented_appartment'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1385);
// PUG_DEBUG:1385
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'rented_appartment1'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1384);
// PUG_DEBUG:1384
 ?>1 раз</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1390);
// PUG_DEBUG:1390
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1387);
// PUG_DEBUG:1387
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'rented_appartment2'], ['name' => 'rented_appartment'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1389);
// PUG_DEBUG:1389
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'rented_appartment2'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1388);
// PUG_DEBUG:1388
 ?>Более 1 раза</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1394);
// PUG_DEBUG:1394
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1391);
// PUG_DEBUG:1391
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'rented_appartment3'], ['name' => 'rented_appartment'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1393);
// PUG_DEBUG:1393
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'rented_appartment3'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1392);
// PUG_DEBUG:1392
 ?>Ни разу</label></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1401);
// PUG_DEBUG:1401
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__footer'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1400);
// PUG_DEBUG:1400
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['class' => 'btn-next'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1398);
// PUG_DEBUG:1398
 ?>Продолжить<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1399);
// PUG_DEBUG:1399
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1443);
// PUG_DEBUG:1443
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1442);
// PUG_DEBUG:1442
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1420);
// PUG_DEBUG:1420
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1404);
// PUG_DEBUG:1404
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1406);
// PUG_DEBUG:1406
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-btn'], ['class' => 'btn-back'], ['class' => 'btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1405);
// PUG_DEBUG:1405
 ?>Назад</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1419);
// PUG_DEBUG:1419
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1408);
// PUG_DEBUG:1408
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1407);
// PUG_DEBUG:1407
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1410);
// PUG_DEBUG:1410
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1409);
// PUG_DEBUG:1409
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1412);
// PUG_DEBUG:1412
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1411);
// PUG_DEBUG:1411
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1414);
// PUG_DEBUG:1414
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1413);
// PUG_DEBUG:1413
 ?>4</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1416);
// PUG_DEBUG:1416
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1415);
// PUG_DEBUG:1415
 ?>5</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1418);
// PUG_DEBUG:1418
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1417);
// PUG_DEBUG:1417
 ?>6</div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1437);
// PUG_DEBUG:1437
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1422);
// PUG_DEBUG:1422
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1421);
// PUG_DEBUG:1421
 ?>На какой срок хотите снять?</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1436);
// PUG_DEBUG:1436
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1435);
// PUG_DEBUG:1435
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1426);
// PUG_DEBUG:1426
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1423);
// PUG_DEBUG:1423
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'how_long1'], ['name' => 'how_long'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1425);
// PUG_DEBUG:1425
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'how_long1'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1424);
// PUG_DEBUG:1424
 ?>Менее 6 месяцев</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1430);
// PUG_DEBUG:1430
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1427);
// PUG_DEBUG:1427
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'how_long2'], ['name' => 'how_long'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1429);
// PUG_DEBUG:1429
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'how_long2'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1428);
// PUG_DEBUG:1428
 ?>6-12 месяцев</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1434);
// PUG_DEBUG:1434
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1431);
// PUG_DEBUG:1431
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'how_long3'], ['name' => 'how_long'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1433);
// PUG_DEBUG:1433
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'how_long3'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1432);
// PUG_DEBUG:1432
 ?>Более года</label></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1441);
// PUG_DEBUG:1441
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__footer'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1440);
// PUG_DEBUG:1440
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['class' => 'btn-next'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1438);
// PUG_DEBUG:1438
 ?>Продолжить<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1439);
// PUG_DEBUG:1439
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1495);
// PUG_DEBUG:1495
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1494);
// PUG_DEBUG:1494
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-4'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1460);
// PUG_DEBUG:1460
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1444);
// PUG_DEBUG:1444
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1446);
// PUG_DEBUG:1446
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-btn'], ['class' => 'btn-back'], ['class' => 'btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1445);
// PUG_DEBUG:1445
 ?>Назад</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1459);
// PUG_DEBUG:1459
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1448);
// PUG_DEBUG:1448
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1447);
// PUG_DEBUG:1447
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1450);
// PUG_DEBUG:1450
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1449);
// PUG_DEBUG:1449
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1452);
// PUG_DEBUG:1452
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1451);
// PUG_DEBUG:1451
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1454);
// PUG_DEBUG:1454
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1453);
// PUG_DEBUG:1453
 ?>4</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1456);
// PUG_DEBUG:1456
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1455);
// PUG_DEBUG:1455
 ?>5</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1458);
// PUG_DEBUG:1458
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1457);
// PUG_DEBUG:1457
 ?>6</div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1489);
// PUG_DEBUG:1489
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1462);
// PUG_DEBUG:1462
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1461);
// PUG_DEBUG:1461
 ?>Сколько комнат?</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1488);
// PUG_DEBUG:1488
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1487);
// PUG_DEBUG:1487
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1466);
// PUG_DEBUG:1466
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1463);
// PUG_DEBUG:1463
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'quantity_rooms1'], ['name' => 'rooms[]'], ['value' => 'Комната'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1465);
// PUG_DEBUG:1465
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'quantity_rooms1'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1464);
// PUG_DEBUG:1464
 ?>Комната</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1470);
// PUG_DEBUG:1470
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1467);
// PUG_DEBUG:1467
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'quantity_rooms2'], ['name' => 'rooms[]'], ['value' => 'Студия'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1469);
// PUG_DEBUG:1469
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'quantity_rooms2'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1468);
// PUG_DEBUG:1468
 ?>Студия</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1474);
// PUG_DEBUG:1474
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1471);
// PUG_DEBUG:1471
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'quantity_rooms3'], ['name' => 'rooms[]'], ['value' => '1-к квартира'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1473);
// PUG_DEBUG:1473
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'quantity_rooms3'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1472);
// PUG_DEBUG:1472
 ?>1 комната</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1478);
// PUG_DEBUG:1478
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1475);
// PUG_DEBUG:1475
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'quantity_rooms4'], ['name' => 'rooms[]'], ['value' => '2-к квартира'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1477);
// PUG_DEBUG:1477
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'quantity_rooms4'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1476);
// PUG_DEBUG:1476
 ?>2 комнаты</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1482);
// PUG_DEBUG:1482
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1479);
// PUG_DEBUG:1479
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'quantity_rooms5'], ['name' => 'rooms[]'], ['value' => '3-к квартира'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1481);
// PUG_DEBUG:1481
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'quantity_rooms5'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1480);
// PUG_DEBUG:1480
 ?>3 комнаты</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1486);
// PUG_DEBUG:1486
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1483);
// PUG_DEBUG:1483
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'checkbox'], ['id' => 'quantity_rooms6'], ['name' => 'rooms[]'], ['value' => 'Многокомнатная'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1485);
// PUG_DEBUG:1485
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'quantity_rooms6'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1484);
// PUG_DEBUG:1484
 ?>4+</label></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1493);
// PUG_DEBUG:1493
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__footer'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1492);
// PUG_DEBUG:1492
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['class' => 'btn-next'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1490);
// PUG_DEBUG:1490
 ?>Продолжить<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1491);
// PUG_DEBUG:1491
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1532);
// PUG_DEBUG:1532
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1531);
// PUG_DEBUG:1531
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-5'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1512);
// PUG_DEBUG:1512
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1496);
// PUG_DEBUG:1496
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1498);
// PUG_DEBUG:1498
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-btn'], ['class' => 'btn-back'], ['class' => 'btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1497);
// PUG_DEBUG:1497
 ?>Назад</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1511);
// PUG_DEBUG:1511
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1500);
// PUG_DEBUG:1500
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1499);
// PUG_DEBUG:1499
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1502);
// PUG_DEBUG:1502
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1501);
// PUG_DEBUG:1501
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1504);
// PUG_DEBUG:1504
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1503);
// PUG_DEBUG:1503
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1506);
// PUG_DEBUG:1506
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1505);
// PUG_DEBUG:1505
 ?>4</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1508);
// PUG_DEBUG:1508
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1507);
// PUG_DEBUG:1507
 ?>5</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1510);
// PUG_DEBUG:1510
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1509);
// PUG_DEBUG:1509
 ?>6</div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1526);
// PUG_DEBUG:1526
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1514);
// PUG_DEBUG:1514
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1513);
// PUG_DEBUG:1513
 ?>На какую сумму рассчитываете?</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1520);
// PUG_DEBUG:1520
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1515);
// PUG_DEBUG:1515
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1517);
// PUG_DEBUG:1517
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1516);
// PUG_DEBUG:1516
 ?>От</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1519);
// PUG_DEBUG:1519
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1518);
// PUG_DEBUG:1518
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'number'], ['name' => 'price_min'], ['placeholder' => '15 000'], ['class' => 'input-min-cost'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1525);
// PUG_DEBUG:1525
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1522);
// PUG_DEBUG:1522
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1521);
// PUG_DEBUG:1521
 ?>До</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1524);
// PUG_DEBUG:1524
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1523);
// PUG_DEBUG:1523
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'number'], ['name' => 'price_max'], ['placeholder' => '20 000'], ['class' => 'input-max-cost'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1530);
// PUG_DEBUG:1530
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__footer'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1529);
// PUG_DEBUG:1529
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['class' => 'btn-next'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1527);
// PUG_DEBUG:1527
 ?>Продолжить<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1528);
// PUG_DEBUG:1528
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1573);
// PUG_DEBUG:1573
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1572);
// PUG_DEBUG:1572
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-6'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1549);
// PUG_DEBUG:1549
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1533);
// PUG_DEBUG:1533
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1535);
// PUG_DEBUG:1535
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-btn'], ['class' => 'btn-back'], ['class' => 'btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1534);
// PUG_DEBUG:1534
 ?>Назад</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1548);
// PUG_DEBUG:1548
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1537);
// PUG_DEBUG:1537
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1536);
// PUG_DEBUG:1536
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1539);
// PUG_DEBUG:1539
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1538);
// PUG_DEBUG:1538
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1541);
// PUG_DEBUG:1541
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1540);
// PUG_DEBUG:1540
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1543);
// PUG_DEBUG:1543
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1542);
// PUG_DEBUG:1542
 ?>4</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1545);
// PUG_DEBUG:1545
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1544);
// PUG_DEBUG:1544
 ?>5</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1547);
// PUG_DEBUG:1547
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1546);
// PUG_DEBUG:1546
 ?>6</div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1565);
// PUG_DEBUG:1565
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1551);
// PUG_DEBUG:1551
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1550);
// PUG_DEBUG:1550
 ?>Оставьте контакты и получите спецпредложение «как сэкономить на аренде до 6 000»</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1559);
// PUG_DEBUG:1559
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1552);
// PUG_DEBUG:1552
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1554);
// PUG_DEBUG:1554
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1553);
// PUG_DEBUG:1553
 ?>Телефон</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1558);
// PUG_DEBUG:1558
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1555);
// PUG_DEBUG:1555
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'tel'], ['name' => 'phone'], ['placeholder' => '+7 (900) 755 43 23'], ['class' => 'input-phone'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1557);
// PUG_DEBUG:1557
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-small'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1556);
// PUG_DEBUG:1556
 ?>С вами свяжется наш струдник и расскажет о спец предложении</div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1564);
// PUG_DEBUG:1564
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1561);
// PUG_DEBUG:1561
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1560);
// PUG_DEBUG:1560
 ?>E-mail</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1563);
// PUG_DEBUG:1563
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1562);
// PUG_DEBUG:1562
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'email'], ['name' => 'email'], ['placeholder' => 'example@mail.ru'], ['class' => 'input-email'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1571);
// PUG_DEBUG:1571
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__footer'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1567);
// PUG_DEBUG:1567
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['class' => 'btn-next'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1566);
// PUG_DEBUG:1566
 ?>Перейти</button><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1570);
// PUG_DEBUG:1570
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-small'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1568);
// PUG_DEBUG:1568
 ?>Нажимая кнопку перейти вы принимаете условия пользовательского соглашения<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1569);
// PUG_DEBUG:1569
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1580);
// PUG_DEBUG:1580
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1579);
// PUG_DEBUG:1579
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-7'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1574);
// PUG_DEBUG:1574
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1578);
// PUG_DEBUG:1578
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1577);
// PUG_DEBUG:1577
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1575);
// PUG_DEBUG:1575
 ?>С вами свяжется наш сотрудник и расскажет о спецпредложении<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1576);
// PUG_DEBUG:1576
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1581);
// PUG_DEBUG:1581
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'hidden'], ['name' => 'roleId'], ['value' => '1'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1582);
// PUG_DEBUG:1582
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'hidden'], ['name' => 'name'], ['value' => 'Не указано'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></form></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1797);
// PUG_DEBUG:1797
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1796);
// PUG_DEBUG:1796
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modal-save-on-rent'], ['class' => 'modal-save-on-rent--buy'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1795);
// PUG_DEBUG:1795
 ?><form<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form'], ['class' => 'form-quize'], ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1615);
// PUG_DEBUG:1615
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1614);
// PUG_DEBUG:1614
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-1'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1600);
// PUG_DEBUG:1600
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1586);
// PUG_DEBUG:1586
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1588);
// PUG_DEBUG:1588
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-btn'], ['class' => 'btn'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1587);
// PUG_DEBUG:1587
 ?>Закрыть</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1599);
// PUG_DEBUG:1599
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1590);
// PUG_DEBUG:1590
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1589);
// PUG_DEBUG:1589
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1592);
// PUG_DEBUG:1592
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1591);
// PUG_DEBUG:1591
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1594);
// PUG_DEBUG:1594
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1593);
// PUG_DEBUG:1593
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1596);
// PUG_DEBUG:1596
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1595);
// PUG_DEBUG:1595
 ?>4</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1598);
// PUG_DEBUG:1598
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1597);
// PUG_DEBUG:1597
 ?>5</div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1609);
// PUG_DEBUG:1609
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1604);
// PUG_DEBUG:1604
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1601);
// PUG_DEBUG:1601
 ?>Купи квартиру мечты <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1602);
// PUG_DEBUG:1602
 ?><br><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1603);
// PUG_DEBUG:1603
 ?>без затрат на услуги риэлтора</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1606);
// PUG_DEBUG:1606
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'simple-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1605);
// PUG_DEBUG:1605
 ?>Так уже делают все!</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1608);
// PUG_DEBUG:1608
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'simple-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1607);
// PUG_DEBUG:1607
 ?>Ответь на простые вопросы и мы сэкономим Вам деньги на услуги, поможем с ипотекой, а также окажем юридическую поддержку</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1613);
// PUG_DEBUG:1613
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__footer'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1612);
// PUG_DEBUG:1612
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['class' => 'btn-next'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1610);
// PUG_DEBUG:1610
 ?>Начать<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1611);
// PUG_DEBUG:1611
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1673);
// PUG_DEBUG:1673
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1672);
// PUG_DEBUG:1672
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-2'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1630);
// PUG_DEBUG:1630
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1616);
// PUG_DEBUG:1616
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1618);
// PUG_DEBUG:1618
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-btn'], ['class' => 'btn-back'], ['class' => 'btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1617);
// PUG_DEBUG:1617
 ?>Назад</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1629);
// PUG_DEBUG:1629
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1620);
// PUG_DEBUG:1620
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1619);
// PUG_DEBUG:1619
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1622);
// PUG_DEBUG:1622
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1621);
// PUG_DEBUG:1621
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1624);
// PUG_DEBUG:1624
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1623);
// PUG_DEBUG:1623
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1626);
// PUG_DEBUG:1626
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1625);
// PUG_DEBUG:1625
 ?>4</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1628);
// PUG_DEBUG:1628
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1627);
// PUG_DEBUG:1627
 ?>5</div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1667);
// PUG_DEBUG:1667
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1632);
// PUG_DEBUG:1632
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1631);
// PUG_DEBUG:1631
 ?>КАКОЙ ТИП НЕДВИЖИМОСТИ?</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1666);
// PUG_DEBUG:1666
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1665);
// PUG_DEBUG:1665
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1636);
// PUG_DEBUG:1636
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1633);
// PUG_DEBUG:1633
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'property_type1'], ['name' => 'rooms'], ['value' => 'Студия'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1635);
// PUG_DEBUG:1635
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'property_type1'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1634);
// PUG_DEBUG:1634
 ?>Студия</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1640);
// PUG_DEBUG:1640
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1637);
// PUG_DEBUG:1637
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'property_type2'], ['name' => 'rooms'], ['value' => 'Однокомнатная'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1639);
// PUG_DEBUG:1639
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'property_type2'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1638);
// PUG_DEBUG:1638
 ?>1 комн. квартира</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1644);
// PUG_DEBUG:1644
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1641);
// PUG_DEBUG:1641
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'property_type3'], ['name' => 'rooms'], ['value' => 'Двухкомнатная'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1643);
// PUG_DEBUG:1643
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'property_type3'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1642);
// PUG_DEBUG:1642
 ?>2 комн. квартира</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1648);
// PUG_DEBUG:1648
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1645);
// PUG_DEBUG:1645
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'property_type4'], ['name' => 'rooms'], ['value' => 'Трехкомнатная'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1647);
// PUG_DEBUG:1647
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'property_type4'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1646);
// PUG_DEBUG:1646
 ?>3 комн. квартира</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1652);
// PUG_DEBUG:1652
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1649);
// PUG_DEBUG:1649
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'property_type5'], ['name' => 'rooms'], ['value' => 'Многокомнатная'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1651);
// PUG_DEBUG:1651
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'property_type5'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1650);
// PUG_DEBUG:1650
 ?>4+ комн. квартира</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1656);
// PUG_DEBUG:1656
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1653);
// PUG_DEBUG:1653
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'property_type6'], ['name' => 'rooms'], ['value' => 'Коттедж'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1655);
// PUG_DEBUG:1655
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'property_type6'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1654);
// PUG_DEBUG:1654
 ?>Коттедж</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1660);
// PUG_DEBUG:1660
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1657);
// PUG_DEBUG:1657
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'property_type7'], ['name' => 'rooms'], ['value' => 'Дом'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1659);
// PUG_DEBUG:1659
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'property_type7'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1658);
// PUG_DEBUG:1658
 ?>Дом</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1664);
// PUG_DEBUG:1664
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1661);
// PUG_DEBUG:1661
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'property_type8'], ['name' => 'rooms'], ['value' => 'Таунхаус'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1663);
// PUG_DEBUG:1663
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'property_type8'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1662);
// PUG_DEBUG:1662
 ?>Таунхаус</label></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1671);
// PUG_DEBUG:1671
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__footer'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1670);
// PUG_DEBUG:1670
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['class' => 'btn-next'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1668);
// PUG_DEBUG:1668
 ?>Продолжить<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1669);
// PUG_DEBUG:1669
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1711);
// PUG_DEBUG:1711
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1710);
// PUG_DEBUG:1710
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1688);
// PUG_DEBUG:1688
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1674);
// PUG_DEBUG:1674
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1676);
// PUG_DEBUG:1676
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-btn'], ['class' => 'btn-back'], ['class' => 'btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1675);
// PUG_DEBUG:1675
 ?>Назад</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1687);
// PUG_DEBUG:1687
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1678);
// PUG_DEBUG:1678
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1677);
// PUG_DEBUG:1677
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1680);
// PUG_DEBUG:1680
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1679);
// PUG_DEBUG:1679
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1682);
// PUG_DEBUG:1682
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1681);
// PUG_DEBUG:1681
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1684);
// PUG_DEBUG:1684
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1683);
// PUG_DEBUG:1683
 ?>4</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1686);
// PUG_DEBUG:1686
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1685);
// PUG_DEBUG:1685
 ?>5</div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1705);
// PUG_DEBUG:1705
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1690);
// PUG_DEBUG:1690
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1689);
// PUG_DEBUG:1689
 ?>СПОСОБ ПРИОБРЕТЕНИЯ?</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1704);
// PUG_DEBUG:1704
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1703);
// PUG_DEBUG:1703
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1694);
// PUG_DEBUG:1694
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1691);
// PUG_DEBUG:1691
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'purchase_method1'], ['name' => 'purchase_method'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1693);
// PUG_DEBUG:1693
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'purchase_method1'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1692);
// PUG_DEBUG:1692
 ?>Ипотека</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1698);
// PUG_DEBUG:1698
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1695);
// PUG_DEBUG:1695
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'purchase_method2'], ['name' => 'purchase_method'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1697);
// PUG_DEBUG:1697
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'purchase_method2'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1696);
// PUG_DEBUG:1696
 ?>Наличные</label></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1702);
// PUG_DEBUG:1702
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_radio'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1699);
// PUG_DEBUG:1699
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'radio'], ['id' => 'purchase_method3'], ['name' => 'purchase_method'], ['class' => 'form-group_radio-input'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1701);
// PUG_DEBUG:1701
 ?><label<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['for' => 'purchase_method3'], ['class' => 'btn'], ['class' => 'form-group_radio-btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1700);
// PUG_DEBUG:1700
 ?>Сертификат</label></div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1709);
// PUG_DEBUG:1709
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__footer'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1708);
// PUG_DEBUG:1708
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['class' => 'btn-next'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1706);
// PUG_DEBUG:1706
 ?>Продолжить<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1707);
// PUG_DEBUG:1707
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1746);
// PUG_DEBUG:1746
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1745);
// PUG_DEBUG:1745
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-5'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1726);
// PUG_DEBUG:1726
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1712);
// PUG_DEBUG:1712
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1714);
// PUG_DEBUG:1714
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-btn'], ['class' => 'btn-back'], ['class' => 'btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1713);
// PUG_DEBUG:1713
 ?>Назад</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1725);
// PUG_DEBUG:1725
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1716);
// PUG_DEBUG:1716
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1715);
// PUG_DEBUG:1715
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1718);
// PUG_DEBUG:1718
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1717);
// PUG_DEBUG:1717
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1720);
// PUG_DEBUG:1720
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1719);
// PUG_DEBUG:1719
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1722);
// PUG_DEBUG:1722
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1721);
// PUG_DEBUG:1721
 ?>4</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1724);
// PUG_DEBUG:1724
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1723);
// PUG_DEBUG:1723
 ?>5</div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1740);
// PUG_DEBUG:1740
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1728);
// PUG_DEBUG:1728
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1727);
// PUG_DEBUG:1727
 ?>СТОИМОСТЬ НЕДВИЖИМОСТИ?</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1734);
// PUG_DEBUG:1734
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1729);
// PUG_DEBUG:1729
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1731);
// PUG_DEBUG:1731
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1730);
// PUG_DEBUG:1730
 ?>От</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1733);
// PUG_DEBUG:1733
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1732);
// PUG_DEBUG:1732
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'number'], ['name' => 'price_min'], ['placeholder' => '1 500 000'], ['class' => 'input-min-cost'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1739);
// PUG_DEBUG:1739
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1736);
// PUG_DEBUG:1736
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1735);
// PUG_DEBUG:1735
 ?>До</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1738);
// PUG_DEBUG:1738
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1737);
// PUG_DEBUG:1737
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'number'], ['name' => 'price_max'], ['placeholder' => '2 000 000'], ['class' => 'input-max-cost'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1744);
// PUG_DEBUG:1744
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__footer'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1743);
// PUG_DEBUG:1743
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['class' => 'btn-next'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1741);
// PUG_DEBUG:1741
 ?>Продолжить<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1742);
// PUG_DEBUG:1742
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1785);
// PUG_DEBUG:1785
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1784);
// PUG_DEBUG:1784
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-6'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1761);
// PUG_DEBUG:1761
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1747);
// PUG_DEBUG:1747
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1749);
// PUG_DEBUG:1749
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-btn'], ['class' => 'btn-back'], ['class' => 'btn'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1748);
// PUG_DEBUG:1748
 ?>Назад</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1760);
// PUG_DEBUG:1760
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1751);
// PUG_DEBUG:1751
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1750);
// PUG_DEBUG:1750
 ?>1</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1753);
// PUG_DEBUG:1753
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1752);
// PUG_DEBUG:1752
 ?>2</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1755);
// PUG_DEBUG:1755
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1754);
// PUG_DEBUG:1754
 ?>3</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1757);
// PUG_DEBUG:1757
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1756);
// PUG_DEBUG:1756
 ?>4</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1759);
// PUG_DEBUG:1759
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'enumeration-item'], ['class' => 'on'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1758);
// PUG_DEBUG:1758
 ?>5</div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1777);
// PUG_DEBUG:1777
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1763);
// PUG_DEBUG:1763
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1762);
// PUG_DEBUG:1762
 ?>ОСТАВЬ КОНТАКТЫ И МЫ С ВАМИ СВЯЖЕМСЯ. РАССКАЖЕМ КАК КУПИТЬ КВАРТИРУ ВЫГОДНО!</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1768);
// PUG_DEBUG:1768
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1765);
// PUG_DEBUG:1765
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1764);
// PUG_DEBUG:1764
 ?>Имя</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1767);
// PUG_DEBUG:1767
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1766);
// PUG_DEBUG:1766
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'text'], ['name' => 'name'], ['placeholder' => 'Введите имя'], ['class' => 'input-name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1776);
// PUG_DEBUG:1776
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1769);
// PUG_DEBUG:1769
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1771);
// PUG_DEBUG:1771
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1770);
// PUG_DEBUG:1770
 ?>Телефон</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1775);
// PUG_DEBUG:1775
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'], ['class' => 'form-group_data--row'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1772);
// PUG_DEBUG:1772
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'tel'], ['name' => 'phone'], ['placeholder' => '+7 (900) 755 43 23'], ['class' => 'input-phone'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1774);
// PUG_DEBUG:1774
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-small'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1773);
// PUG_DEBUG:1773
 ?>С вами свяжется наш струдник и расскажет о спец предложении</div></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1783);
// PUG_DEBUG:1783
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__footer'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1779);
// PUG_DEBUG:1779
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['class' => 'btn-next'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1778);
// PUG_DEBUG:1778
 ?>Перейти</button><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1782);
// PUG_DEBUG:1782
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-small'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1780);
// PUG_DEBUG:1780
 ?>Нажимая кнопку перейти вы принимаете условия пользовательского соглашения<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1781);
// PUG_DEBUG:1781
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1792);
// PUG_DEBUG:1792
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1791);
// PUG_DEBUG:1791
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'block-save'], ['class' => 'block-save-7'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1786);
// PUG_DEBUG:1786
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1790);
// PUG_DEBUG:1790
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1789);
// PUG_DEBUG:1789
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'title-h3'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1787);
// PUG_DEBUG:1787
 ?>С вами свяжется наш сотрудник и расскажет о спецпредложении<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1788);
// PUG_DEBUG:1788
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1793);
// PUG_DEBUG:1793
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'hidden'], ['name' => 'roleId'], ['value' => '2'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1794);
// PUG_DEBUG:1794
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'hidden'], ['name' => 'email'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></form></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1833);
// PUG_DEBUG:1833
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1832);
// PUG_DEBUG:1832
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modal-mortgage'], ['class' => 'modal--list'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1831);
// PUG_DEBUG:1831
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1802);
// PUG_DEBUG:1802
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1799);
// PUG_DEBUG:1799
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1798);
// PUG_DEBUG:1798
 ?>Выгодная ипотека</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1801);
// PUG_DEBUG:1801
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1800);
// PUG_DEBUG:1800
 ?>&#215;</span></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1830);
// PUG_DEBUG:1830
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1804);
// PUG_DEBUG:1804
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body-title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1803);
// PUG_DEBUG:1803
 ?>Получите экономию от 0.5% на ставке</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1817);
// PUG_DEBUG:1817
 ?><ul<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body-ul'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1808);
// PUG_DEBUG:1808
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body-li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1805);
// PUG_DEBUG:1805
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'icon'], ['src' => '/dist/assets/img/quickly-find.svg'], ['alt' => 'Получите экономию от 0.5% на ставке'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1807);
// PUG_DEBUG:1807
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body-li-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1806);
// PUG_DEBUG:1806
 ?>Быстро подберем выгодную программу</div></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1812);
// PUG_DEBUG:1812
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body-li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1809);
// PUG_DEBUG:1809
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'icon'], ['src' => '/dist/assets/img/prepare-documents.svg'], ['alt' => 'Подготовим пакет документов для успешного одобрения'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1811);
// PUG_DEBUG:1811
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body-li-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1810);
// PUG_DEBUG:1810
 ?>Подготовим пакет документов для успешного одобрения</div></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1816);
// PUG_DEBUG:1816
 ?><li<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body-li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1813);
// PUG_DEBUG:1813
 ?><img<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'icon'], ['src' => '/dist/assets/img/find-solution.svg'], ['alt' => 'Найдём решение в сложной ситуации'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1815);
// PUG_DEBUG:1815
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body-li-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1814);
// PUG_DEBUG:1814
 ?>Найдём решение в сложной ситуации</div></li></ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1829);
// PUG_DEBUG:1829
 ?><form<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form'], ['class' => 'mortgage-form'], ['class' => 'form--triple'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1824);
// PUG_DEBUG:1824
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group__couple'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1820);
// PUG_DEBUG:1820
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'], ['class' => 'form-group-name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1819);
// PUG_DEBUG:1819
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1818);
// PUG_DEBUG:1818
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'text'], ['name' => 'name'], ['placeholder' => 'Имя'], ['class' => 'input-name'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1823);
// PUG_DEBUG:1823
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group'], ['class' => 'form-group-phone'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1822);
// PUG_DEBUG:1822
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1821);
// PUG_DEBUG:1821
 ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['type' => 'text'], ['name' => 'phone'], ['placeholder' => 'Телефон'], ['class' => 'input-phone'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1828);
// PUG_DEBUG:1828
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group-down'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1827);
// PUG_DEBUG:1827
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'form-group_data'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1826);
// PUG_DEBUG:1826
 ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'btn'], ['class' => 'btn-main'], ['type' => 'submit'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1825);
// PUG_DEBUG:1825
 ?>Получить одобрение</button></div></div></form></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1908);
// PUG_DEBUG:1908
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1907);
// PUG_DEBUG:1907
 ?><footer<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1899);
// PUG_DEBUG:1899
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'container'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1838);
// PUG_DEBUG:1838
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer__main'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1835);
// PUG_DEBUG:1835
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/'], ['class' => 'footer__main-logo'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1834);
// PUG_DEBUG:1834
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

$__pug_mixins[$__pug_mixin_name](false, array(  ), [[false, 'logo-white']], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, $pug_vars, &$pugModule) {
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
}); ?></a><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1837);
// PUG_DEBUG:1837
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer__main-description'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1836);
// PUG_DEBUG:1836
 ?>Онлайн маркет недвижимости. Самая большая, проверенная и актуальная база от собственников жилья.</div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1856);
// PUG_DEBUG:1856
 ?><?php if (method_exists($_pug_temp = ($GLOBALS['__jpv_and']($GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'isSet')('ZASELITE_TYPE'), function () use (&$cookie) { return $GLOBALS['__jpv_dotWithArrayPrototype_with_ref']($cookie, 'getValue')('ZASELITE_TYPE') == 'Аренда'; })), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1855);
// PUG_DEBUG:1855
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer__renters'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1839);
// PUG_DEBUG:1839
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1841);
// PUG_DEBUG:1841
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer__renters-title'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1840);
// PUG_DEBUG:1840
 ?>Арендаторам</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1854);
// PUG_DEBUG:1854
 ?><ul<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer__renters__ul'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1844);
// PUG_DEBUG:1844
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1843);
// PUG_DEBUG:1843
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/find'], ['class' => 'footer__renters__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1842);
// PUG_DEBUG:1842
 ?>Снять квартиру</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1847);
// PUG_DEBUG:1847
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1846);
// PUG_DEBUG:1846
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/assistant'], ['class' => 'footer__renters__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1845);
// PUG_DEBUG:1845
 ?>Персональный помощник</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1850);
// PUG_DEBUG:1850
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1849);
// PUG_DEBUG:1849
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/tariffs'], ['class' => 'footer__renters__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1848);
// PUG_DEBUG:1848
 ?>Тарифы</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1853);
// PUG_DEBUG:1853
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1852);
// PUG_DEBUG:1852
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/documents'], ['class' => 'footer__renters__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1851);
// PUG_DEBUG:1851
 ?>Документы</a></li></ul></div><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1868);
// PUG_DEBUG:1868
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer__renters'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1857);
// PUG_DEBUG:1857
 ?>Покупателям<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1867);
// PUG_DEBUG:1867
 ?><ul<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer__renters__ul'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1860);
// PUG_DEBUG:1860
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1859);
// PUG_DEBUG:1859
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/find'], ['class' => 'footer__renters__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1858);
// PUG_DEBUG:1858
 ?>Купить квартиру</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1863);
// PUG_DEBUG:1863
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1862);
// PUG_DEBUG:1862
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/assistant'], ['class' => 'footer__renters__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1861);
// PUG_DEBUG:1861
 ?>Персональный помощник</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1866);
// PUG_DEBUG:1866
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1865);
// PUG_DEBUG:1865
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/tariffs'], ['class' => 'footer__renters__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1864);
// PUG_DEBUG:1864
 ?>Тарифы</a></li></ul></div><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1883);
// PUG_DEBUG:1883
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer__owners'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1869);
// PUG_DEBUG:1869
 ?>Собственникам<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1882);
// PUG_DEBUG:1882
 ?><ul<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer__owners__ul'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1872);
// PUG_DEBUG:1872
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1871);
// PUG_DEBUG:1871
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/rent'], ['class' => 'footer__owners__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1870);
// PUG_DEBUG:1870
 ?>Разместить объявление</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1875);
// PUG_DEBUG:1875
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1874);
// PUG_DEBUG:1874
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/how-it-work#how-it-work'], ['class' => 'footer__owners__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1873);
// PUG_DEBUG:1873
 ?>Как это работает?</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1878);
// PUG_DEBUG:1878
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1877);
// PUG_DEBUG:1877
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/how-it-work#tariffs-wrap'], ['class' => 'footer__owners__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1876);
// PUG_DEBUG:1876
 ?>Тарифы</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1881);
// PUG_DEBUG:1881
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1880);
// PUG_DEBUG:1880
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/documents'], ['class' => 'footer__owners__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1879);
// PUG_DEBUG:1879
 ?>Документы</a></li></ul></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1898);
// PUG_DEBUG:1898
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer__primary'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1884);
// PUG_DEBUG:1884
 ?>ZASELITE<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1897);
// PUG_DEBUG:1897
 ?><ul<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer__primary__ul'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1887);
// PUG_DEBUG:1887
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1886);
// PUG_DEBUG:1886
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/about'], ['class' => 'footer__primary__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1885);
// PUG_DEBUG:1885
 ?>О компании</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1890);
// PUG_DEBUG:1890
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1889);
// PUG_DEBUG:1889
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/contacts'], ['class' => 'footer__primary__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1888);
// PUG_DEBUG:1888
 ?>Контакты</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1893);
// PUG_DEBUG:1893
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1892);
// PUG_DEBUG:1892
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/terms-of-service'], ['class' => 'footer__primary__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1891);
// PUG_DEBUG:1891
 ?>Пользовательское соглашение</a></li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1896);
// PUG_DEBUG:1896
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1895);
// PUG_DEBUG:1895
 ?><a<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['href' => '/processing-data'], ['class' => 'footer__primary__ul__li'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1894);
// PUG_DEBUG:1894
 ?>Обработка персональных данных</a></li></ul></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1906);
// PUG_DEBUG:1906
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer-down'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1905);
// PUG_DEBUG:1905
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'container'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1901);
// PUG_DEBUG:1901
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer-down__left'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1900);
// PUG_DEBUG:1900
 ?>© 2020 ZASELITE.RU, Онлайн маркет недвижимости</div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1904);
// PUG_DEBUG:1904
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'footer-down__right'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1902);
// PUG_DEBUG:1902
 ?>Использование сайта, в том числе подача объявлений, означает согласие с пользовательским соглашением<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1903);
// PUG_DEBUG:1903
 ?></div></div></div></footer><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1910);
// PUG_DEBUG:1910
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'container'], ['style' => 'position: relative'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1909);
// PUG_DEBUG:1909
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'up-arrow'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1911);
// PUG_DEBUG:1911
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal-background'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1921);
// PUG_DEBUG:1921
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1920);
// PUG_DEBUG:1920
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal'], ['class' => 'modal-error'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1919);
// PUG_DEBUG:1919
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal_content'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1916);
// PUG_DEBUG:1916
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1912);
// PUG_DEBUG:1912
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__head-text'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1914);
// PUG_DEBUG:1914
 ?><span<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'close'], ['data-close' => 'close'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1913);
// PUG_DEBUG:1913
 ?>&#215;</span><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1915);
// PUG_DEBUG:1915
 ?><hr></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1918);
// PUG_DEBUG:1918
 ?><div<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['class' => 'modal__body'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1917);
// PUG_DEBUG:1917
 ?></div></div></div><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1922);
// PUG_DEBUG:1922
 ?><script<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => 'https://api-maps.yandex.ru/2.1/?apikey=fc24af6b-2846-4f99-a792-d519cf4eb71f&amp;lang=ru_RU'])
) ? var_export($_pug_temp, true) : $_pug_temp) ?>></script><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1923);
// PUG_DEBUG:1923
 ?><script<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/js/vendor/jquery-3.5.1.min.js'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></script><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1924);
// PUG_DEBUG:1924
 ?><script<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/js/vendor/snap.js'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></script><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1925);
// PUG_DEBUG:1925
 ?><script<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/js/vendor/slick.min.js'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></script><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(1926);
// PUG_DEBUG:1926
 ?><script<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\HtmlFormat::attributes_assignment'](array(  ), ['src' => '/dist/assets/js/main.js'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></script></body></html>