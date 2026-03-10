<?php

/**
 * Opis: Konfiguracja komponentów i parametrów aplikacji.
 */


return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    // Optional lists for contact validation; profanity filtering uses contactBlacklistWords.
    'contactBlacklistEmails' => [
        'spam@example.com',
    ],
    'contactBlacklistDomains' => [
        'tempmail.com',
    ],
    'contactBlacklistWords' => [
        'cwelu',
        'cwel',
        'chuj',
        'cipa',
        'pizda',
        'pizdo',
        'chuju',
        'kurwa','kurwy','kurwo','kurwie','kurwami','kurwą',
'chuj','chuja','chuje','chujowy','chujowo','chujnia',
'pizda','pizdy','pizdą','pizdzie','pizdą','pizdka','pizdunie',
'pierdol','pierdolić','pierdoli','pierdolony','pierdolenie','pierdolić się','pierdolnik',
'jebac','jebać','jebany','jebana','jebane','jebie','jebanie','jebanko','jebiesz','jebnąć','zajebać','wyjebać',
'gówno','gowno','gówniany','gowniany','gówniarz',
'skurwysyn','skurwysyny','skurwysyński',
'skurwiel','skurwiele','Suka','suki','sukinsyn','sukinsyny',
'dupa','dupą','dupy','dupie','dupek','dupka','dupek',
'odpierdol','spierdalaj','wypierdalaj','rozpierdol','rozpierdolić',
'zjebać','zjebany','zjeb','zjeby',
'debil','debilny',
'idiota','idioci',
'kretyn','kretyni',
'przyjeb','przyjebany',
'pojebany','pojeb','pojebani',
'matole','matol',
'frajer','frajerzy',
'szmata','szmaty',
'gnój','gnida',
'świnia','świnie',
'ciota','cioty','nigga','nigger','pedał','pedale','pecie','kutas','kutasie','pedalisko','jeahbac','yeahbac'
    ],
];
