<?php

if (!function_exists('sroute')) {
    /** route() qui suit l'espace de l'utilisateur connecté (BC, BE ou MOE) pour les noms « cheflot.* » et « controleur.* ». */
    function sroute(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return route(\App\Support\Espaces::nomRoute($name), $parameters, $absolute);
    }
}

if (!function_exists('eroute')) {
    /** route() pour les pages d'envoi de documents (« entreprise.* ») : suit la structure de l'utilisateur connecté. */
    function eroute(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return route(\App\Support\Espaces::nomRouteEnvoi($name), $parameters, $absolute);
    }
}
