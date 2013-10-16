<?php

namespace SilexUser\Console;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use SilexUser\Role;
use SilexUser\User;

class CreateUserCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('silex-user:user:create')
            ->setDescription('Create user');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();
        $em = $app['silex_user.entity_manager'];

        $roles = $em->getRepository('SilexUser\Role')->findAll();

        if (empty($roles)) {
            $output->writeln('No roles found, creating default roles');
            $this->getApplication()->get('silex-user:role:create-defaults')->execute($input, $output);
            $roles = $em->getRepository('SilexUser\Role')->findAll();

            if (empty($roles)) {
                throw new \RuntimeException('Failed creating default roles');
            }
        }

        $dialog = $this->getHelperSet()->get('dialog');

        $username = $dialog->ask($output, 'Username: ');
        $user = $em->getRepository('SilexUser\User')->findOneByUsername($username);
        if (null !== $user) {
            if (!$dialog->askConfirmation($output, '<question>Username exists, update data? (y/n)</question> ', false)) {
                $output->writeln('Exiting');
                return;
            }
        } else {
            $user = new User();
            $user->setUsername($username);
        }

        if (!$user->getId() || $dialog->askConfirmation($output, '<question>Update pasword? (y/n)</question> ', false)) {
            $encoder = $app['security.encoder_factory']->getEncoder($user);
            $user->setSalt(md5(date('YmdHis')));
            $user->setPassword($encoder->encodePassword($dialog->askHiddenResponse($output, 'Password: '), $user->getSalt()));
        }

        $existingRoleNames = array_map(function ($role) {
            return $role->getRole();
        }, $roles);

        $output->writeln('Please specify user roles (separate by comma for multiple roles)');
        $roleNames = array_map('trim', explode(',', $dialog->ask($output, 'Roles (' . implode(', ', $existingRoleNames) . '): ')));

        $validRoleNames = array_intersect($existingRoleNames, $roleNames);
        array_walk($roles, function ($role) use ($user, $validRoleNames) {
            if (in_array($role->getRole(), $validRoleNames) && !$user->getAssignedRoles()->contains($role)) {
                $user->addAssignedRole($role);
            } elseif (!in_array($role->getRole(), $validRoleNames) && $user->getAssignedRoles()->contains($role)) {
                $user->removeAssignedRole($role);
            }
        });

        $em->persist($user);
        $em->flush();

        $output->writeln('User saved');
        $output->writeln('Username: ' . $user->getUsername());
        $output->writeln('Roles: ' . implode(', ', $user->getRoles()));
    }
}
