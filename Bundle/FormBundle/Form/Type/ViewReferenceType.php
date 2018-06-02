<?php

namespace Victoire\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ViewReferenceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'                          => 'form.link_type.view_reference.label',
            'required'                       => true,
            'attr'                           => [
                'novalidate'                   => 'novalidate',
                'tabindex'                     => '-1',
            ],
            'placeholder'                    => 'form.link_type.view_reference.blank',
            'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group'],
            'locale'                         => null,
            'choices'                        => null,
        ]);
        parent::configureOptions($resolver);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['choices'] = $options['choices'];
    }

    public function getParent()
    {
        return TextType::class;
    }
}
