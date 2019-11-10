<?php

namespace Kern\PictureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\File;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="pictures")
 */
class Picture implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=80)
     */
    protected $hash;
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;
    /** @var File */
    protected $file;
    /**
     * @ORM\Column(type="string", length=40)
     */
    protected $mimeType;
    /**
     * @ORM\Column(type="array")
     */
    protected $formats;
    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $mode;
    /**
     * @ORM\Column(type="string", length=175, nullable=true)
     */
    protected $credits;
    /**
     * @var UserInterface
     */
    protected $uploader;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    const MODE_PORTRAIT = 'portrait';
    const MODE_LANDSCAPE = 'landscape';

    const FORMATS = [
        2880,
        1920,
        1280,
        980,
        720,
        480
    ];

    public function __construct()
    {
        $this->formats = new ArrayCollection();
        $this->mode = self::MODE_LANDSCAPE;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setFile(?File $file = null): self
    {
        $this->file = $file;

        if ($file !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }
    
    public function setFormats(array $formats = []): self
    {
        $this->formats = new ArrayCollection($formats);

        return $this;
    }

    public function addFormat(int $format): self
    {
        if (!$this->hasFormat($format)) {
            $this->formats->add($format);
        }
        return $this;
    }

    public function hasFormat(int $format): bool
    {
        return $this->formats->contains($format);
    }

    public function getFormats(): Collection
    {
        return $this->formats;
    }

    public function getSmallestFormat(): int
    {
        $format = $this->formats->first();

        foreach ($this->formats as $f) {
            if ($f < $format) {
                $format = $f;
            }
        }
        return $format;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setCredits(string $credits = null): self
    {
        $this->credits = $credits;

        return $this;
    }

    public function getCredits(): ?string
    {
        return $this->credits;
    }

    public function setUploader(UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUploader(): UserInterface
    {
        return $this->uploader;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function getExtension(): string
    {
        return [
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ][$this->mimeType];
    }

    public function jsonSerialize()
    {
        return [
            'hash' => $this->hash,
            'name' => $this->name,
            'mode' => $this->mode,
            'formats' => $this->formats->toArray(),
            'credits' => $this->credits,
            'uploader' => $this->uploader,
            'updated_at' => $this->updatedAt->format('c')
        ];
    }
}