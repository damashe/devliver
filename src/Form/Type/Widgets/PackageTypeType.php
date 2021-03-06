<?php

namespace Shapecode\Devliver\Form\Type\Widgets;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PackageTypeType
 *
 * @package Shapecode\Devliver\Form\Type\Widgets
 * @author  Nikita Loges
 */
class PackageTypeType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required'          => true,
            'label'             => 'Type',
            'choices'           => [
                'vcs'           => 'vcs',
                'gitlab'        => 'gitlab',
                'github'        => 'github',
                'git-bitbucket' => 'git-bitbucket',
//                'git'      => 'git',
//                'hg'       => 'hg',
//                'svn'      => 'svn',
//                'artifact' => 'artifact',
//                'pear'     => 'pear',
//                'package'  => 'package'
            ],
            'choices_as_values' => true
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

}
