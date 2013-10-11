<?php

namespace SilexUser;

use Silex\Application;
use Silex\ServiceProviderInterface;

class UserServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['security.role_hierarchy'] = [
            'ROLE_ADMIN' => ['ROLE_USER'],
        ];

        $app['silex_user.user_provider'] = $app->protect(function () use ($app) {
            return new UserProvider($app['orm.em']);
        });

        $app['security.firewalls'] = [
            'login' => [
                'pattern' => '^/login$',
            ],
            'private' => [
                'pattern' => '^/user/',
                'form'    => ['login_path' => '/login', 'check_path' => '/user/login_check'],
                'logout'  => ['logout_path' => '/user/logout'],
                'users'   => $app['silex_user.user_provider'],
            ],
            'unsecured' => ['anonymous' => true],
        ];

        $app['security.access_rules'] = [
            ['^/admin/', 'ROLE_ADMIN'],
            ['^/user/', 'ROLE_USER'],
        ];

        $app['silex_user.auth_controller'] = $app->share(function () use ($app) {
            return new Controller\AuthController();
        });

        $app->get('/login', 'silex_user.auth_controller:login');
    }

    public function boot(Application $app)
    {
    }
}
