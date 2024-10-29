<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Section;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user_id')
            ->add('title_slug')
            ->add('text')
            ->add('article_date_create', null, [
                'widget' => 'single_text',
            ])
            ->add('article_date_posted', null, [
                'widget' => 'single_text',
            ])
            ->add('published')
            ->add('title')
            ->add('sections', EntityType::class, [
                'class' => Section::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
