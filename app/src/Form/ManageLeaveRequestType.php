<?php

namespace App\Form;

use App\Entity\LeaveRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManageLeaveRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('responseComment', TextareaType::class, [
                'required' => false,
                'label' => 'leave_request.form.responseComment.label'
            ])
            ->add('submitAccept', SubmitType::class, [
                'label' => 'leave_request.form.submit_accept.label'
            ])
            ->add('submitReject', SubmitType::class, [
                'label' => 'leave_request.form.submit_reject.label'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LeaveRequest::class,
        ]);
    }
}
