<?php

namespace SilexUser\Controller;

use Silex\Application;

class AuthController
{
    public function login(Application $app)
    {
        return $app['twig']->render('login.html.twig', [
            'error' => $app['security.last_error']($app['request']),
            'last_username' => $app['session']->get('_security.last_username'),
        ]);
    }
}
