<?php

namespace SilexUser;

use Silex\Application;
use Silex\ServiceProviderInterface;

class UserServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['silex_user.templates'] = (isset($app['silex_user.templates']) ? $app['silex_user.templates'] : []) + [
            'login'      => '@SilexUser/login.html.twig',
            'register'   => '@SilexUser/register.html.twig',
            'recovery'   => '@SilexUser/recovery.html.twig',
            'password'   => '@SilexUser/password.html.twig',
            'layout'     => '@SilexUser/layout.html.twig',
            'mail_reset' => '@SilexUser/mail_reset.txt.twig',
        ];

        if (isset($app['twig'])) {
            $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
                $twig->addGlobal('silex_user', ['templates' => $app['silex_user.templates']]);
                $app['twig.loader.filesystem']->addPath(__DIR__ . '/../../views', 'SilexUser');

                return $twig;
            }));
        }

        $app['silex_user.email_as_identity'] = true;

        $app['security.role_hierarchy'] = [
            'ROLE_ADMIN' => ['ROLE_USER'],
        ];

        $app['silex_user.entity_manager'] = $app->share(function () use ($app) {
            $key = $app['silex_user.entity_manager_key'];

            return $app[$key];
        });

        $app['silex_user.user_provider'] = $app->protect(function () use ($app) {
            return new UserProvider($app['silex_user.entity_manager']);
        });

        $app['silex_user.login.default_target_path'] = '/';

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
            return $app['silex_user.entity_manager']->getRepository(Entity::$role)->findOneByRole('ROLE_USER');
        });

        $app['silex_user.classnames'] = [
            'entity.user' => 'SilexUser\User',
            'entity.role' => 'SilexUser\Role',
        ];

        $app['silex_user.password_encoder'] = $app->share(function () use ($app) {
            return $app['security.encoder_factory']->getEncoder($app['silex_user.classnames']['entity.user']);
        });

        $app['silex_user.form_factory.registration'] = $app->protect(function () use ($app) {
            $type = new Form\UserType($app['silex_user.password_encoder']);
            $options = ['email_as_identity' => $app['silex_user.email_as_identity']];

            return $app['form.factory']->create($type, null, $options);
        });

        $app['silex_user.form_factory.password'] = $app->protect(function (User $user) use ($app) {
            $type = new Form\CredentialsType($app['silex_user.password_encoder']);

            return $app['form.factory']->create($type, $user);
        });

        $app['silex_user.mail.sender'] = 'noreply@localhost';

        $app['silex_user.mail.reset_password'] = $app->protect(function (User $user) use ($app) {
            $message = \Swift_Message::newInstance()
                ->setSubject('Password reset')
                ->setFrom([$app['silex_user.mail.sender']])
                ->setTo([$user->getEmail()])
                ->setBody($app['twig']->render($app['silex_user.templates']['mail_reset'], [
                    'user' => $user,
                ]));

            return $app['mailer']->send($message);
        });

        $app['silex_user.auth_controller'] = $app->share(function () use ($app) {
            return new Controller\AuthController();
        });

        $app->get('/login', 'silex_user.auth_controller:login')
            ->bind('login');

        $app->match('/register', 'silex_user.auth_controller:register')
            ->bind('register');

        $app->match('/recover-password', 'silex_user.auth_controller:recovery')
            ->bind('recovery');

        $app->match('/change-password/{token}', 'silex_user.auth_controller:password')
            ->bind('password');
    }

    public function boot(Application $app)
    {
        Entity::$user = $app['silex_user.classnames']['entity.user'];
        Entity::$role = $app['silex_user.classnames']['entity.role'];
    }
}
