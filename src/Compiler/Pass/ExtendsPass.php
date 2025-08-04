<?php

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Compiler\Node;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerPassInterface;
use Millancore\Pesto\Contract\LoaderInterface;
use Millancore\Pesto\Exception\CompilerException;

class ExtendsPass implements CompilerPassInterface
{
    private LoaderInterface $loader;
    private array $sections = [];

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @throws CompilerException
     */
    public function compile(Pesto $pesto): void
    {
        $extendsNodes = $pesto->find('[php-extends]');

        if ($extendsNodes->isEmpty()) {
            return;
        }

        if($extendsNodes->count() > 1) {
            throw new CompilerException('Only one [php-extends] tag is allowed per template.');
        }

        $pesto->find('[php-section]')->each(function (Node $node) {
            $sectionName = $node->getAttribute('php-section');

            if ($sectionName) {
                $this->sections[$sectionName][] = $node;
            }
        });

        $parentLayout = $extendsNodes->getIterator()->current()->getAttribute('php-extends');
        $parentTemplate = $this->loader->getSource($parentLayout);
        $parentPesto = new Pesto($parentTemplate);

        $parentPesto->find('[php-yield]')->each(function (Node $yieldNode) {
            $yieldName = $yieldNode->getAttribute('php-yield');

            if (isset($this->sections[$yieldName])) {
                $fragment = $yieldNode->createDocumentFragment();
                $sectionNodes = array_reverse($this->sections[$yieldName]);

                foreach ($sectionNodes as $sectionNode) {
                    $sectionNode->removeAttribute('php-section');
                    $fragment->appendXML($sectionNode->getOuterXML());
                }
                $yieldNode->replaceWith($fragment);
            } else {
                $yieldNode->removeAttribute('php-yield');
            }
        });

        $childDocument = $pesto->getDocument();
        $parentDocument = $parentPesto->getDocument();

        $originalRoot = $childDocument->documentElement;

        $newRoot = $childDocument->importNode($parentDocument->documentElement, true);

        $childDocument->replaceChild($newRoot, $originalRoot);
    }
}