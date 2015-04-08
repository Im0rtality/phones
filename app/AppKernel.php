<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            /* Other bundles */
            new Phones\FrontEndBundle\PhonesFrontEndBundle(),
            new Phones\PhoneBundle\PhonesPhoneBundle(),

            /* Data providers */
            new Phones\DataProviders\GsmArenaComBundle\PhonesDataProvidersGsmArenaComBundle(),

            /* Cost providers */
            new Phones\CostProviders\TeleArenaLtBundle\PhonesCostProvidersTeleArenaLtBundle(),
            new Phones\CostProviders\GsmArenaLtBundle\PhonesCostProvidersGsmArenaLtBundle(),
            new Phones\CostProviders\MobiliLinijaBundle\PhonesCostProvidersMobiliLinijaBundle(),

            /* Stat providers */
            new Phones\StatProviders\DxOMarkComBundle\PhonesStatProvidersDxOMarkComBundle(),
        ];

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
