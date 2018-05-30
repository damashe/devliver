<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Repo
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 *
 * @ORM\Entity(repositoryClass="Shapecode\Devliver\Repository\RepoRepository")
 */
class Repo extends BaseEntity implements RepoInterface
{

    /**
     * @var Package
     * @ORM\OneToOne(targetEntity="Shapecode\Devliver\Entity\Package", mappedBy="repo", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $package;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="Shapecode\Devliver\Entity\User", inversedBy="repos", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $creator;

    /**
     * @var string
     * @ORM\Column(type="string", options={"default": "vcs"})
     */
    protected $type = 'vcs';

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $url;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": true})
     */
    protected $enable = true;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $abandoned = false;

    /**
     * @inheritdoc
     */
    public function getPackage(): ?Package
    {
        return $this->package;
    }

    /**
     * @inheritdoc
     */
    public function setPackage(Package $package): void
    {
        $this->package = $package;
    }

    /**
     * @return null|User
     */
    public function getCreator(): ?User
    {
        return $this->creator;
    }

    /**
     * @param null|User $creator
     */
    public function setCreator(?User $creator): void
    {
        $this->creator = $creator;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @inheritdoc
     */
    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }

    /**
     * @return bool
     */
    public function isAbandoned(): bool
    {
        return $this->abandoned;
    }

    /**
     * @param bool $abandoned
     */
    public function setAbandoned(bool $abandoned): void
    {
        $this->abandoned = $abandoned;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'type' => $this->getType(),
            'url'  => $this->getUrl(),
        ];
    }

}
