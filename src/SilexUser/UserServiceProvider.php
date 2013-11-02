<?php

namespace SilexUser;

use Silex\Application;
use Silex\ServiceProviderInterface;
use SilexUser\Form\UserType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;

class UserServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['silex_user.templates'] = (isset($app['silex_user.templates']) ? $app['silex_user.templates'] : []) + [
            'login'    => '@SilexUser/login.html.twig',
            'register' => '@SilexUser/register.html.twig',
            'layout'   => '@SilexUser/layout.html.twig',
        ];

        if (isset($app['twig'])) {
            $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
                $twig->addGlobal('silex_user', ['templates' => $app['silex_user.templates']]);
                $app['twig.loader.filesystem']->addPath(__DIR__ . '/../../views', 'SilexUser');

                return $twig;
            }));
        }

        $app['silex_user.email_as_identity'] = isset($app['silex_user.email_as_identity'])
            ? (boolean) $app['silex_user.email_as_identity']
            : true;

        $app['security.role_hierarchy'] = [
            'ROLE_ADMIN' => ['ROLE_USER'],
        ];

        $app['silex_user.entity_manager'] = $app->share(function () use ($app) {
            $ormKey = $app['silex_user.entity_manager_key'];
            return $app[$ormKey];
        });

        $app['silex_user.user_provider'] = $app->protect(function () use ($app) {
            return new UserProvider($app['silex_user.entity_manager']);
        });

        $app['silex_user.login.default_target_path'] = isset($app['silex_user.login.default_target_path'])
            ? $app['silex_user.login.default_target_path']
            : '/';

        $app['security.firewalls'] = $app->share(function () use ($app) {
            return [
                'global' => [
                    'pattern'   => '^.*$',
                    'anonymous' => true,
                    'form'      => [
                        'login_path' => '/login',
                        'check_path' => '/authenticate',
                        'default_target_path' => $app['silex_user.login.default_target_path'],
                    ],
                    'logout'    => ['logout_path' => '/logout'],
                    'users'     => $app['silex_user.user_provider'],
                ],
            ];
        });

        $app['silex_user.default_role'] = $app->share(function () use ($app) {
            return $app['silex_user.entity_manager']->getRepository('SilexUser\Role')->findOneByRole('ROLE_USER');
        });

        $app['silex_user.form.registration'] = $app->share(function () use ($app) {
            return new UserType(
                $app['silex_user.email_as_identity'],
                $app['security.encoder_factory']
            );
        });

        $app['silex_user.unique_entity_validator'] = $app->share(function () use ($app) {
            return new UniqueEntityValidator($app['doctrine']);
        });

        $app['silex_user.auth_controller'] = $app->share(function () use ($app) {
            return new Controller\AuthController();
        });

        $app->get('/login', 'silex_user.auth_controller:login')
            ->bind('login');

        $app->match('/register', 'silex_user.auth_controller:register')
            ->bind('register');
    }

    public function boot(Application $app)
    {
    }
}
