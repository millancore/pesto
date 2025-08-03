<?php

namespace Millancore\Pesto\Compiler\Pass;



use Millancore\Pesto\Compiler\Node;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerPassInterface;
use Millancore\Pesto\Contract\LoaderInterface;

class ExtendsPass implements CompilerPassInterface
{
    private LoaderInterface $loader;
    private array $sections = [];

    public function compile(Pesto $pesto): void
    {
        $extendsNodes = $pesto->find('[php-extends]');

        if ($extendsNodes->isEmpty()) {
            return;
        }

        $pesto->find('[php-section]')->each(function (Node $node) {
            $sectionName = $node->getAttribute('php-section');

            if($sectionName) {
                $this->sections[$sectionName][] = $node;
            }
        });

        $parentLayout = $extendsNodes->getIterator()->current()->getAttribute('php-extends');
        $parentTemplate = $this->loader->getSource($parentLayout);
        $parentPesto = new Pesto($parentTemplate);



    }
}