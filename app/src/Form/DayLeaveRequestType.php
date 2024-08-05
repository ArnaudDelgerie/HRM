<?php

namespace App\Form;

use App\Entity\DayLeaveRequest;
use App\Enum\DayLeaveRequestPeriodEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DayLeaveRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dayDate', DateType::class, [
                'required' => true,
                'label' => 'day_leave_request.form.day_date.label',
                'widget' => 'single_text',
            ])
            ->add('period', EnumType::class, [
                'label' => 'day_leave_request.form.period.label',
                'class' => DayLeaveRequestPeriodEnum::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DayLeaveRequest::class,
        ]);
    }
}
