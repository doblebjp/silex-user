<?php

namespace SilexUser;

use Silex\Application;
use Silex\ServiceProviderInterface;

class UserServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['silex_user.templates'] = [
            'login'        => '@silex_user/login.html.twig',
            'login_layout' => '@silex_user/layout.html.twig',
        ];

        if (isset($app['twig'])) {
            $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
                $twig->addGlobal('silex_user', ['templates' => $app['silex_user.templates']]);
                $app['twig.loader.filesystem']->addPath(__DIR__ . '/../../views', 'silex_user');

                return $twig;
            }));
        }

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

        $app['security.firewalls'] = [
            'login' => [
                'pattern' => '^/login$',
            ],
            'global' => [
                'pattern'   => '^.*$',
                'anonymous' => true,
                'form'      => ['login_path' => '/login', 'check_path' => '/authenticate'],
                'logout'    => ['logout_path' => '/logout'],
                'users'     => $app['silex_user.user_provider'],
            ],
        ];

        $app['security.access_rules'] = [
            ['^/admin/', 'ROLE_ADMIN'],
            ['^/user/', 'ROLE_USER'],
        ];

        $app['silex_user.auth_controller'] = $app->share(function () use ($app) {
            return new Controller\AuthController();
        });

        $app->get('/login', 'silex_user.auth_controller:login')
            ->bind('login');
    }

    public function boot(Application $app)
    {
    }
}
