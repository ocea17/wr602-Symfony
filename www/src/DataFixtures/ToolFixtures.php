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

        $manager->flush();
    }
}
