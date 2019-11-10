<?php

namespace Kern\PictureBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Kern\PictureBundle\Manager\PictureManager;
use Kern\PictureBundle\Registry\PictureRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Picture\Bundle\Entity\Picture;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProcessPictureCommand extends Command
{
    /** @var EntityManagerInterface */
    protected $em;
    /** @var PictureManager */
    protected $pictureManager;
    /** @var PictureRegistry */
    protected $pictureRegistry;
    /** @var string */
    protected $uploadDir;

    public function __construct(EntityManagerInterface $em, PictureManager $pictureManager, PictureRegistry $pictureRegistry, string $uploadDir)
    {
        parent::__construct();

        $this->em = $em;
        $this->pictureManager = $pictureManager;
        $this->pictureRegistry = $pictureRegistry;
        $this->uploadDir = $uploadDir;
    }

    public function configure()
    {
        $this
            ->setName('app:picture:process')
            ->setDescription('Process a picture in public/ repository')
            ->addArgument('file', InputArgument::REQUIRED, 'the file to process')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('file');

        if (($picture = $this->em->getRepository(Picture::class)->findOneByName($filename)) === null) {
            throw new \ErrorException('Picture not found');
        }
        $picture->setFile(new UploadedFile("{$this->uploadDir}/{$filename}", $filename));
        $this->pictureRegistry->add($picture);
        $this->pictureManager->processUploadedPictures();
    }
}