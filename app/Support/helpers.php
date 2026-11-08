<?php

if (!function_exists('sroute')) {
    /** route() qui suit l'espace de l'utilisateur connecté (BC, BE ou MOE) pour les noms « cheflot.* » et « controleur.* ». */
    function sroute(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return route(\App\Support\Espaces::nomRoute($name), $parameters, $absolute);
    }
}
