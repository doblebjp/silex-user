<?php

namespace SilexUser\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use SilexUser\Form\UserType;
use SilexUser\User;

class AuthController
{
    public function login(Application $app)
    {
        return $app['twig']->render($app['silex_user.templates']['login'], [
            'error' => $app['security.last_error']($app['request']),
            'last_username' => $app['session']->get('_security.last_username'),
        ]);
    }

    public function register(Request $request, Application $app)
    {
        $form = $app['form.factory']->create($app['silex_user.form.registration'], new User());

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {

            } catch (\Exception $e) {

            }
        }

        return $app['twig']->render($app['silex_user.templates']['register'], [
            'form' => $form->createView(),
        ]);
    }
}
