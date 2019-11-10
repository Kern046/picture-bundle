<?php

namespace Kern\PictureBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class KernPictureExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $builder)
    {
        $loader = new YamlFileLoader($builder, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $builder->setParameter('kpb_upload_dir', $configs[0]['upload_dir']);
        $builder->setParameter('kpb_user_class', $configs[0]['user_class']);
    }
}