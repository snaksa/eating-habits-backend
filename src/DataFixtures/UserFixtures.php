<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('demo@gmail.com');
        $user->setName('Demo User');
        $user->setRoles(['ROLE_USER']);
        $password = $this->passwordEncoder
            ->encodePassword($user, '123456');
        $user->setPassword($password);
        $manager->persist($user);
        $this->setReference('user_demo', $user);

        $user = new User();
        $user->setUsername('demo2@gmail.com');
        $user->setName('John Doe');
        $user->setRoles(['ROLE_USER']);
        $password = $this->passwordEncoder
            ->encodePassword($user, '123456');
        $user->setPassword($password);
        $manager->persist($user);
        $this->setReference('user_demo2', $user);

        $manager->flush();
    }
}
