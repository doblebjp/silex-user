<?php

namespace SilexUser\Console;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use SilexUser\Entity;

class DefaultRolesCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('silex-user:role:create-defaults')
            ->setDescription('Create default user roles');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();
        $app->boot();

        $em = $app['silex_user.entity_manager'];
        $results = $em->getRepository(Entity::$role)->findAll();

        $existingRoles = array_map(function ($role) {
            return $role->getRole();
        }, $results);

        $defaultRoles = ['ROLE_USER', 'ROLE_ADMIN'];
        $missingRoles = array_diff($defaultRoles, $existingRoles);

        if (empty($missingRoles)) {
            $output->writeln('All default roles exist');
            return;
        }

        foreach ($missingRoles as $roleName) {
            $output->writeln("Creating $roleName");
            $role = new Entity::$role();
            $role->setRole($roleName);
            $em->persist($role);
        }

        $em->flush();
        $output->writeln('Done');
    }
}
