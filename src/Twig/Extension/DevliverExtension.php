<?php

namespace Shapecode\Devliver\Twig\Extension;

use Composer\Package\CompletePackageInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Devliver\Composer\ComposerManager;
use Shapecode\Devliver\Entity\Download;
use Shapecode\Devliver\Entity\PackageInterface;
use Shapecode\Devliver\Entity\VersionInterface;
use Shapecode\Devliver\Model\PackageAdapter;
use Shapecode\Devliver\Repository\DownloadRepository;
use Shapecode\Devliver\Service\GitHubRelease;
use Shapecode\Devliver\Service\RepositoryHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class DevliverExtension
 *
 * @package Shapecode\Devliver\Twig\Extension
 * @author  Nikita Loges
 */
class DevliverExtension extends AbstractExtension implements GlobalsInterface
{

    /** @var ManagerRegistry */
    protected $registry;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var GitHubRelease */
    protected $github;

    /** @var ComposerManager */
    protected $composerManager;

    /** @var RepositoryHelper */
    protected $repositoryHelper;

    /**
     * @param ManagerRegistry       $registry
     * @param UrlGeneratorInterface $router
     * @param GitHubRelease         $github
     * @param ComposerManager       $composerManager
     * @param RepositoryHelper      $repositoryHelper
     */
    public function __construct(
        ManagerRegistry $registry,
        UrlGeneratorInterface $router,
        GitHubRelease $github,
        ComposerManager $composerManager,
        RepositoryHelper $repositoryHelper
    )
    {
        $this->registry = $registry;
        $this->router = $router;
        $this->github = $github;
        $this->composerManager = $composerManager;
        $this->repositoryHelper = $repositoryHelper;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('version_downloads', [$this, 'getVersionDownloadsCounter']),
            new TwigFunction('package_downloads', [$this, 'getPackageDownloadsCounter']),
            new TwigFunction('package_download_url', [$this, 'getPackageDownloadUrl']),
            new TwigFunction('package_adapter', [$this, 'getPackageAdapter']),
            new TwigFunction('package_readme', [$this, 'getPackageReadme'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new TwigFilter('sha1', 'sha1'),
            new TwigFilter('package_adapter', [$this, 'getPackageAdapter'])
        ];
    }

    /**
     * @param PackageInterface $package
     *
     * @return int
     */
    public function getPackageDownloadsCounter(PackageInterface $package)
    {
        /** @var DownloadRepository $repo */
        $repo = $this->registry->getRepository(Download::class);

        return $repo->countPackageDownloads($package);
    }

    /**
     * @param PackageInterface $version
     *
     * @return int
     */
    public function getVersionDownloadsCounter(VersionInterface $version)
    {
        /** @var DownloadRepository $repo */
        $repo = $this->registry->getRepository(Download::class);

        return $repo->countVersionDownloads($version);
    }

    /**
     * @param CompletePackageInterface $package
     *
     * @return PackageAdapter
     */
    public function getPackageAdapter(CompletePackageInterface $package)
    {
        return new PackageAdapter($package);
    }

    /**
     * @param CompletePackageInterface $package
     *
     * @return string
     */
    public function getPackageDownloadUrl(CompletePackageInterface $package)
    {
        $adapter = $this->getPackageAdapter($package);

        return $this->router->generate('devliver_repository_dist_web', [
            'vendor'  => $adapter->getVendorName(),
            'project' => $adapter->getProjectName(),
            'ref'     => $package->getSourceReference(),
            'type'    => 'zip',
        ]);
    }

    /**
     * @param PackageInterface $package
     *
     * @return null|string
     */
    public function getPackageReadme(PackageInterface $package)
    {
        $repository = $this->composerManager->createRepoByPackage(null, $package);

        return $this->repositoryHelper->getReadme($repository);
    }

    /**
     * @inheritdoc
     */
    public function getGlobals()
    {
        return [
            'current_release' => $this->github->getCurrentTag(),
            'latest_release'  => $this->github->getLastTag(),
        ];
    }

}
