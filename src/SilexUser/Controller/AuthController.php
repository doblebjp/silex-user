<?php

namespace SilexUser\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpKernel\HttpKernelInterface;
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
        $form = $app['form.factory']->create($app['silex_user.form.registration']);
        $em = $app['silex_user.entity_manager'];

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                $user = $form->getData();
                $em->persist($user);
                $em->flush();
                $em->getConnection()->commit();

                return $app->redirect('/login');

            } catch (\Exception $e) {
                $em->getConnection()->rollback();
                $em->close();

                $form->addError(new FormError(sprintf('Registration failed %s', $app['debug'] ? $e->getMessage() : null)));
            }
        }

        return $app['twig']->render($app['silex_user.templates']['register'], [
            'form' => $form->createView(),
        ]);
    }
}
