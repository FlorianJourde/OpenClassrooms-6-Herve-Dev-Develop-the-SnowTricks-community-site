<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\MediaTricks;
use App\Entity\Tricks;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TricksFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tricks_name', options: [
                'label' =>  'Nom du tricks :'
            ])
            ->add('tricks_description', options: [
                'label' => 'Description :'
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'category_tricks',
                'label' => 'Categorie du tricks :'
            ])
            /*->add('media_tricks', FileType::class, [
                'label' => 'ajouter une image :',
                'multiple' => true,
                'mapped' => false,
            ])*/

            ->add('media_tricks', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'required' => false
            ])
//            ->add('media_tricks', CollectionType::class, [
//                'entry_type' => MediaTricksType::class,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__media_Tricks_index__',
//                'label' => false,
//            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tricks::class,
        ]);
    }
}
