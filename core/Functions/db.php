<?php

    function decodeTextDB(string $text): string
    {
        if(preg_match("/[а-яА-ЯЁё]/iu", $text) == 0)
        {
            return utf8_decode($text);
        }

        return $text;
    }