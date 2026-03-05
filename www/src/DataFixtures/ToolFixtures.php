<?php

namespace App\DataFixtures;

use App\Entity\Tool;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ToolFixtures extends Fixture
{
    public const TOOL_URL = 'tool-url';
    public const TOOL_HTML = 'tool-html';
    public const TOOL_WYSIWYG = 'tool-wysiwyg';
    public const TOOL_MERGE = 'tool-merge';
    public const TOOL_SPLIT = 'tool-split';

    public function load(ObjectManager $manager): void
    {
        $toolUrl = new Tool();
        $toolUrl->setName('URL vers PDF');
        $toolUrl->setSlug('url');
        $toolUrl->setDescription('Convertir une page web en PDF');
        $toolUrl->setActive(true);
        $manager->persist($toolUrl);
        $this->addReference(self::TOOL_URL, $toolUrl);

        $toolHtml = new Tool();
        $toolHtml->setName('Fichier HTML vers PDF');
        $toolHtml->setSlug('html');
        $toolHtml->setDescription('Upload un fichier HTML et le convertir en PDF');
        $toolHtml->setActive(true);
        $manager->persist($toolHtml);
        $this->addReference(self::TOOL_HTML, $toolHtml);

        $toolWysiwyg = new Tool();
        $toolWysiwyg->setName('Éditeur HTML');
        $toolWysiwyg->setSlug('wysiwyg');
        $toolWysiwyg->setDescription('Écrire du HTML directement dans un éditeur');
        $toolWysiwyg->setActive(true);
        $manager->persist($toolWysiwyg);
        $this->addReference(self::TOOL_WYSIWYG, $toolWysiwyg);

        $toolMerge = new Tool();
        $toolMerge->setName('Fusionner des PDFs');
        $toolMerge->setSlug('merge');
        $toolMerge->setDescription('Combiner plusieurs PDFs en un seul');
        $toolMerge->setActive(true);
        $manager->persist($toolMerge);
        $this->addReference(self::TOOL_MERGE, $toolMerge);

        $toolSplit = new Tool();
        $toolSplit->setName('Séparer un PDF');
        $toolSplit->setSlug('split');
        $toolSplit->setDescription('Extraire des pages d\'un PDF');
        $toolSplit->setActive(true);
        $manager->persist($toolSplit);
        $this->addReference(self::TOOL_SPLIT, $toolSplit);

        $manager->flush();
    }
}