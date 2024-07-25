<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'required' => true,
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'user.form.plain_password.label',
                    'constraints' => [
                        new NotBlank(message: 'user.password.assert.not_blank'),
                        new Length(min: 6, minMessage: 'user.password.assert.length'),
                    ]
                ],
                'second_options' => [
                    'label' => 'user.form.plain_password_repeat.label',
                ],
                'invalid_message' => 'user.form.plain_password_repeat_invalid'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'user.form.submit.label'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
