<?php

namespace Karser\Recaptcha3Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

final class Recaptcha3Type extends AbstractType
{
    /** @var string */
    private $siteKey;

    /** @var bool */
    private $enabled;

    public function __construct($siteKey, $enabled)
    {
        $this->siteKey = $siteKey;
        $this->enabled = $enabled;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['site_key'] = $this->siteKey;
        $view->vars['enabled'] = $this->enabled;
        $view->vars['action_name'] = $options['action_name'];
        $view->vars['script_nonce_csp'] = array_key_exists('script_nonce_csp', $options) ? $options['script_nonce_csp'] : '';
    }

    public function getParent()
    {
        return 'hidden';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'mapped' => false,
            'site_key' => null,
            'action_name' => 'homepage',
            'script_nonce_csp' => ''
        ));
    }

    public function getName()
    {
        return 'karser_recaptcha3';
    }
}
