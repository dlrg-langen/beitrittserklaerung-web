<?php
if (!defined('INCLUDE')) { exit; }

define('PATH', dirname(__FILE__)); // nicht ändern

$fields = [
    'beitrittsdatum' => [
        'name' => 'Beitrittsdatum',
        'type' => 'date',
        'required' => true,
        'default' => date('Y-m-d', strtotime('first day of next month'))
    ],
    'titel' => [
        'name' => 'Titel',
        'type' => 'text',
        'required' => false
    ],
    'vorname' => [
        'name' => 'Vorname',
        'type' => 'text',
        'required' => false
    ],
    'name' => [
        'name' => '(Firmen-)Name',
        'type' => 'text',
        'required' => true
    ],
    'strasse' => [
        'name' => 'Straße/Nr.',
        'type' => 'text',
        'required' => true
    ],
    'wohnort' => [
        'name' => 'PLZ/Wohnort',
        'type' => 'text',
        'required' => true
    ],
    'geburtsdatum' => [
        'name' => 'Geburtsdatum',
        'type' => 'date',
        'required' => true,
        'default' => ''
    ],
    'telefon' => [
        'name' => 'Telefon',
        'type' => 'text',
        'required' => false
    ],
    'email' => [
        'name' => 'E-Mail-Adresse',
        'type' => 'email',
        'required' => true
    ],
    'beitragsart' => [
        'name' => 'Beitragsart',
        'type' => 'select',
        'required' => true,
        'options' => [
            'Familie (55,00 €)', 'Erwachsene (28,00 €)', 'Kinder und Jugendliche bis 18 Jahre (23,00 €)'
        ],
        'info' => 'Der Familienbeitrag enthält nur Kinder und Jugendliche bis zum vollendeten 18. Lebensjahr. In dem Jahr, das auf die Vollendung des 18. Lebensjahres folgt, wird der gültige Beitrag für Erwachsene fällig. Soweit keine geänderte Bankverbindung mitgeteilt wird, erfolgt die Abbuchung vom uns bekannten Konto.',
    ]
];

$triggerFields = [
    'beitragsart' => [
        0 => [
            'id' => 'familienmitglieder',
            'name' => 'Bitte weitere Familienmitglieder (Vorname, Name, Geburtsdatum) hier eintragen.',
            'type' => 'textfield',
            'required' => true
        ]
    ]
];

$zahlungsarten = [
    [
        'name' => 'SEPA-Lastschriftmandat (Einzugsermächtigung)',
        'fields' => [
            'iban' => [
                'name' => 'IBAN',
                'required' => true,
                'type' => 'text',
            ],
            'bic' => [
                'name' => 'BIC',
                'required' => true,
                'type' => 'text',
            ],
            'kontoinhaber' => [
                'name' => 'Kontoinhaber',
                'required' => true,
                'type' => 'text',
            ],
            'kontoinhaber_anschrift' => [
                'name' => 'Anschrift des Kontoinhabers (wenn abweichend)',
                'required' => false,
                'type' => 'textfield',
            ],
        ]
    ],
    [
        'name' => 'Überweisung',
        'fields' => []
    ],
    [
        'name' => 'Bar',
        'fields' => []
    ]
];

$infos = [
    'datenverarbeitung' => [
        'name' => 'Datenverarbeitung',
        'default' => 'Im Zusammenhang mit der Mitgliedschaft stehende Daten werden zum Zwecke der Mitgliederverwaltung elektronisch gespeichert. Hiermit willige ich in die Speicherung der Daten ein.',
        'required' => false,
        'type' => 'infotext'
    ],
    'bildrechte' => [
        'name' => 'Bildrechte',
        'default' => 'Aus rechtlichen Gründen machen wir Sie darauf aufmerksam, dass bei Veranstaltungen der DLRG Aufnahmen in Form von z. B. Fotos oder auch Videoaufnahmen gemacht werden können, welche zum Teil veröffentlicht werden (z. B. in Form von Zeitungsartikeln oder auf unserer Website). Mit dem Beitritt gilt die Zustimmung hierzu als erteilt.',
        'required' => false,
        'type' => 'infotext'
    ],
    'kuendigung' => [
        'name' => 'Kündigung',
        'default' => 'Die Mitgliedschaft kann nur schriftlich einen Monat vor Ablauf des Geschäftsjahres gekündigt werden.',
        'required' => false,
        'type' => 'infotext'
    ],
    'satzung' => [
        'name' => 'Satzung',
        'default' => 'Ich erkenne die Satzung der DLRG Ortsgruppe Test e.V. an.',
        'required' => false,
        'type' => 'infotext'
    ],
    'mitgliedsbeitrag' => [
        'name' => 'Mitgliedsbeitrag',
        'default' => 'Die Höhe des jährlichen Mitgliedsbeitrages ist mir bekannt (%1$.2f €).',
        'required' => false,
        'type' => 'infotext-replace',
        'replace' => 'beitragsart',
        'options' => [
            55, 28, 23
        ]
    ]
];

$parts = [
    0 => 'fields',
    1 => 'triggerFields',
    2 => 'zahlungsarten',
    3 => 'infos'
];

$submit = [
    'type' => 'email',
    'config' => [
        'address' => 'info@test.dlrg.de',
        'fromAddress' => 'info@test.dlrg.de',
        'fromName' => 'DLRG Ortsgruppe Test e.V. - Test',
        'smtp' => [
            'use' => false,
            'server' => 'test.example.com',
            'user' => 'username',
            'password' => 'password',
            'port' => 587,
        ]
    ]
];

$gliederung = 'Ortsgruppe Test e.V.';
$back = 'http://test.dlrg.de/ueber-uns/mitmachen.html';

/* alternative */
//$submit = [
//    'type' => 'database',
//    'config' => [
//        'host' => '127.0.0.1',
//        'port' => 3306,
//        'database' => 'name',
//        'user' => 'user',
//        'password' => 'passwrod'
//    ]
//];


