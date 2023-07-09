<?php

namespace App\Form;

use App\Entity\CompanySymbol;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CompanySymbolFormType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('symbol', EntityType::class, [
                'class'=> CompanySymbol::class,
                'choice_label' => function(CompanySymbol $companySymbol) {
                    return $companySymbol->getSymbol();
                },
                'data' =>  $this->entityManager->getRepository(CompanySymbol::class)->findOneBy([
                    'id' => 6
                ]),
            ])
            ->add('start_date', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'js-datepicker',
                    'max' => date('Y-m-d'),
                ],
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
                'data' => new \DateTime('2023/6/20'),
            ])
            ->add('end_date', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'js-datepicker',
                    'max' => date('Y-m-d'),
                ],
                'data' => new \DateTime(),
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Callback(function($object, ExecutionContextInterface $context) {
                        $startDate = $context->getRoot()->getData()['start_date'];
                        $endDate = $object;
                        $currentDate = new \DateTime(); // Current date

                        if (is_a($startDate, \DateTime::class) && is_a($endDate, \DateTime::class)) {
                            if ($endDate > $currentDate && $endDate->format('U') - $startDate->format('U') < 0) {
                                $context
                                    ->buildViolation('End Date must be after Start Date')
                                    ->addViolation();
                            }
                        }
                    })
                ]
            ])
            ->add('email', EmailType::class, [
                'label'=> 'Email',
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
                'data' => 'adeel@ad.com'
            ])
            ->add('submit', SubmitType::class, [
                'label'=> 'Submit',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // enable/disable CSRF protection for this form
            'csrf_protection' => true,
            // the name of the hidden HTML field that stores the token
            'csrf_field_name' => '_token',
        ]);
    }
}
