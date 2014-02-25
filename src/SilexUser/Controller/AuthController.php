<?php

namespace SilexUser\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use SilexUser\Form\UserType;
use SilexUser\User;
use SilexUser\Entity;

class AuthController
{
    public function login(Application $app)
    {
        if ($app['security']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $path = $app['session']->get('_security.global.target_path')
                ?: $app['url_generator']->generate($app['silex_user.login.default_target_path']);

            return $app->redirect($path);
        }

        return $app['twig']->render($app['silex_user.templates']['login'], [
            'error' => $app['security.last_error']($app['request']),
            'last_username' => $app['session']->get('_security.last_username'),
        ]);
    }

    public function register(Request $request, Application $app)
    {
        $form = $app['silex_user.form_factory.registration']();
        $em = $app['silex_user.entity_manager'];

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                $user = $form->getData();
                $user->addAssignedRole($app['silex_user.default_role']);

                $em->persist($user);
                $em->flush();
                $em->getConnection()->commit();

                $app['security']->setToken(new UsernamePasswordToken($user, null, 'global', $user->getRoles()));

                $path = $app['session']->get('_security.global.target_path')
                    ?: $app['url_generator']->generate($app['silex_user.login.default_target_path']);

                return $app->redirect($path);

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

    public function recovery(Request $request, Application $app)
    {
        $form = $app['form.factory']->createBuilder('form')
            ->add('email', 'email', ['required' => true])
            ->getForm();

        $form->handleRequest($request);

        $message = null;

        if ($form->isValid()) {
            $data = $form->getData();
            $em = $app['silex_user.entity_manager'];
            $user = $em->getRepository(Entity::$user)->findOneByEmail($data['email']);

            if (null === $user) {
                $form->addError(new FormError('No user account found with this email address'));
            } else {
                $app['session']->getFlashBag()->add('success', 'Please check your mailbox for your password reset instruction');

                return $app->redirect($app['url_generator']->generate('recovery'));
            }
        }

        return $app['twig']->render($app['silex_user.templates']['recovery'], [
            'form' => $form->createView(),
            'message' => $message
        ]);
    }
}
