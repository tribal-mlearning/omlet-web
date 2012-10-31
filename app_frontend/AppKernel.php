<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\DoctrineFixturesBundle\DoctrineFixturesBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new Ideup\SimplePaginatorBundle\IdeupSimplePaginatorBundle(),
            new Core\Library\EntityBundle\CoreLibraryEntityBundle(),
            new Core\Security\AccessControlBundle\CoreSecurityAccessControlBundle(),
            new Core\Security\FormSecurityBundle\CoreSecurityFormSecurityBundle(),
            new Frontend\PortalBundle\FrontendPortalBundle(),
            new Frontend\Extension\TwigBundle\FrontendExtensionTwigBundle(),
            new Core\Network\RequestBundle\CoreNetworkRequestBundle(),
            new Service\TimeBundle\ServiceTimeBundle(),
            new Service\TrackBundle\ServiceTrackBundle(),
            new Service\UserBundle\ServiceUserBundle(),
            new Service\PackageBundle\ServicePackageBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Service\ImageBundle\ServiceImageBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }
}
