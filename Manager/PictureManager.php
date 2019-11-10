<?php

namespace Kern\PictureBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Kern\PictureBundle\Entity\Picture;
use App\Entity\User\User;
use Kern\PictureBundle\Registry\PictureRegistry;

class PictureManager
{
    /** @var EntityManagerInterface */
    protected $em;
    /** @var PictureRegistry */
    protected $pictureRegistry;
    /** @var string */
    protected $uploadDir;

    public function __construct(EntityManagerInterface $em, PictureRegistry $pictureRegistry, string $uploadDir)
    {
        $this->em = $em;
        $this->pictureRegistry = $pictureRegistry;
        $this->uploadDir = $uploadDir;
    }

    public function create(UploadedFile $file, User $user = null, string $credits = null): Picture
    {
        $picture =
            (new Picture())
            ->setHash(md5(uniqid() . date('c') . $file->getFilename()))
            ->setName($file->getFilename())
            ->setMimeType($file->getMimeType())
            ->setFile($file)
            ->setUploader($user)
            ->setCredits($credits)
        ;
        $this->pictureRegistry->add($picture);
        $this->em->persist($picture);
        $this->em->flush();

        $file->move($this->uploadDir, "{$file->getFilename()}.{$picture->getExtension()}");

        return $picture;
    }

    public function find(string $hash): ?Picture
    {
        return $this->em->getRepository(Picture::class)->find($hash);
    }

    public function processUploadedPictures()
    {
        foreach ($this->pictureRegistry->getAll() as $picture) {
            $this->processPicture($picture);
        }
        $this->em->flush();
    }

    protected function processPicture(Picture $picture)
    {
        $file = $picture->getFile();
        $extension = $picture->getExtension();
        // For newer uploaded images, the filename has no extension, but it does when using the command for a specific picture
        $filename = (!strpos($file->getFilename(), '.')) ? "{$file->getFilename()}.{$extension}" : $file->getFilename();
        $dimensions = \getimagesize("{$this->uploadDir}/{$filename}");
        // The folder containing all the images formats
        $picturePath = "{$this->uploadDir}/{$picture->getHash()}";
        $originalPath = "{$picturePath}/{$dimensions[0]}px.{$extension}";
        // Set mode and original format
        $picture->setMode(($dimensions[0] > $dimensions[1]) ? Picture::MODE_LANDSCAPE : Picture::MODE_PORTRAIT);
        $picture->setFormats([$dimensions[0]]);

        mkdir($picturePath);
        rename("{$this->uploadDir}/{$filename}", $originalPath);

        foreach (Picture::FORMATS as $format) {
            if ($format > $dimensions[0]) {
                continue;
            }
            $this->formatPicture($picture, $picturePath, $originalPath, $extension, $format);
        }
    }

    protected function formatPicture(Picture $picture, string $picturePath, string $originalPath, string $extension, int $format)
    {
        $picture->addFormat($format);
        switch ($picture->getMimeType()) {
            case 'image/png':
                $image = \imagecreatefrompng($originalPath);
                imagealphablending($image, false);
                imagesavealpha($image, true);

                $width = imagesx($image);
                $height = imagesy($image);
                $ratio = $format / $width;
                $newWidth = $format;
                $newHeight = $height * $ratio;

                $newImg = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($newImg, false);
                imagesavealpha($newImg,true);
                $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
                imagefilledrectangle($newImg, 0, 0, $newWidth, $newHeight, $transparent);
                imagecopyresampled($newImg, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                \imagepng($newImg, "{$picturePath}/{$format}px.{$extension}");
                break;
            case 'image/jpeg':
                $image = \imagecreatefromjpeg($originalPath);
                \imagejpeg(\imagescale($image, $format), "{$picturePath}/{$format}px.{$extension}");
                break;
            default:
                throw new \ErrorException('Unsupported MIME type');
        }
        // The original ratio is preserved
    }
}