<?php

namespace App\DataFixtures;

use App\Constant\Gender;
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
        $user->setWaterAmount(3400);
        $user->setWaterCalculation(false);
        $user->setGender(Gender::MALE);
        $user->setHeight(177);
        $user->setAge(23);
        $manager->persist($user);
        $this->setReference('user_demo', $user);

        $user = new User();
        $user->setUsername('demo2@gmail.com');
        $user->setName('John Doe');
        $user->setRoles(['ROLE_USER']);
        $password = $this->passwordEncoder
            ->encodePassword($user, '123456');
        $user->setPassword($password);;
        $user->setWaterCalculation(true);
        $user->setGender(Gender::FEMALE);
        $user->setHeight(165);
        $user->setAge(34);
        $manager->persist($user);
        $this->setReference('user_demo2', $user);

        $user = new User();
        $user->setUsername('demo3@gmail.com');
        $user->setName('John Doe');
        $user->setRoles(['ROLE_USER']);
        $password = $this->passwordEncoder
            ->encodePassword($user, '123456');
        $user->setPassword($password);
        $user->setWaterAmount(4500);
        $user->setWaterCalculation(false);
        $user->setGender(Gender::MALE);
        $user->setHeight(190);
        $user->setAge(28);
        $manager->persist($user);
        $this->setReference('user_demo3', $user);

        $manager->flush();
    }
}
