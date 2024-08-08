<?php

namespace App\Form;

use App\Entity\Meeting;
use App\Enum\MeetingVisibilityEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'meeting.form.name.label',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'meeting.form.description.label',
            ])
            ->add('visibility', EnumType::class, [
                'label' => 'meeting.form.visibility.label',
                'class' => MeetingVisibilityEnum::class,
            ])
            ->add('location', TextType::class, [
                'required' => false,
                'label' => 'meeting.form.location.label',
            ])
            ->add('startAt', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'meeting.form.start_at.label',
            ])
            ->add('endAt', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'meeting.form.end_at.label',
            ])
            ->add('meetingParticipants', CollectionType::class, [
                'label' => 'meeting.form.meeting_participants.label',
                'entry_type' => MeetingParticipantType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_options' => [
                    'label' => false,
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'meeting.form.submit.label',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meeting::class,
        ]);
    }
}
