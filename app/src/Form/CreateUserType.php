<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'required' => true,
                'label' => 'user.form.email.label',
            ])
            ->add('username', null, [
                'required' => true,
                'label' => 'user.form.username.label',
            ])
            ->add('firstname', null, [
                'required' => true,
                'label' => 'user.form.firstname.label',
            ])
            ->add('lastname', null, [
                'required' => true,
                'label' => 'user.form.lastname.label',
            ])
            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'label' => 'user.form.roles.label',
                'choices' => UserRoleEnum::formChoices(),
            ])
            ->add('invite', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label'    => 'user.form.invite.label',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'user.form.submit.label'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
