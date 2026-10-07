<?php

return [
    'salt' => env('HASHIDS_SALT'),
    'length' => env('HASHIDS_LENGTH', 8),
    'alphabet' => env('HASHIDS_ALPHABET', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'),
];
