<?php
return [
    'custom' => [
        'email' => [
            'unique' => 'Der Benutzer ist schon registriert.',
        ],
        'password' => [
            'min' => 'Das Passwort muss mindestens 8 Zeichen lang sein.',
            'password.mixed' => 'Das Passwort muss mindestens ein Kleinbuchstaben und einen Großbuchstaben enthalten.',
            'password.symbols' => 'Das Passwort muss mindestens ein Sonderzeichen enthalten.',
            'password.numbers' => 'Das Passwort muss mindestens eine Zahl enthalten.',
        ]
    ]
];
