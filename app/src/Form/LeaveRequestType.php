<?php

namespace App\Form;

use App\Entity\LeaveRequest;
use App\Enum\LeaveRequestTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LeaveRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EnumType::class, [
                'label' => 'leave_request.form.type.label',
                'class' => LeaveRequestTypeEnum::class,
            ])
            ->add('dayLeaveRequests', CollectionType::class, [
                'label' => 'leave_request.form.day_leave_requests.label',
                'entry_type' => DayLeaveRequestType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_options' => [
                    'label' => false,
                ]
            ])
            ->add('requestComment', TextareaType::class, [
                'required' => false,
                'label' => 'leave_request.form.requestComment.label',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'leave_request.form.submit.label',
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
